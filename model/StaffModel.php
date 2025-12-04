<?php
// filepath: c:\xampp\htdocs\MyLibrary\model\StaffModel.php
require_once __DIR__ . '/BaseModel.php';

class StaffModel extends BaseModel {
    protected $table = 'borrowtransaction';

    public function __construct($db) {
        parent::__construct($db);
    }

    // Polymorphic validate method
    public function validate($data) {
        $errors = [];
        $user_id = $this->validateInt($data['user_id'] ?? 0);
        $book_id = $this->validateInt($data['book_id'] ?? 0);

        if (!$user_id) $errors[] = 'Invalid user ID';
        if (!$book_id) $errors[] = 'Invalid book ID';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => compact('user_id', 'book_id')
        ];
    }

    public function getAllBorrowers() {
        $query = "SELECT u.user_id, u.name, u.email, u.role,
                  COUNT(DISTINCT bt.borrow_id) as borrowed_count,
                  SUM(CASE WHEN p.status = 'Unpaid' THEN p.amount ELSE 0 END) as unpaid_penalties
                  FROM User u
                  LEFT JOIN BorrowTransaction bt ON u.user_id = bt.user_id AND bt.status = 'Borrowed'
                  LEFT JOIN Penalty p ON bt.borrow_id = p.borrow_id
                  WHERE u.role IN ('Student', 'Teacher')
                  GROUP BY u.user_id
                  ORDER BY u.name ASC";
        
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getUserDetails($user_id) {
        $query = "SELECT u.*, 
                  COUNT(DISTINCT bt.borrow_id) as borrowed_count,
                  SUM(CASE WHEN p.status = 'Unpaid' THEN p.amount ELSE 0 END) as unpaid_penalties
                  FROM User u
                  LEFT JOIN BorrowTransaction bt ON u.user_id = bt.user_id AND bt.status = 'Borrowed'
                  LEFT JOIN Penalty p ON bt.borrow_id = p.borrow_id
                  WHERE u.user_id = :user_id
                  GROUP BY u.user_id";
        
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
    }

    public function getUserBorrowedBooks($user_id) {
        $query = "SELECT bt.*, b.title, b.author, b.price
                  FROM BorrowTransaction bt
                  JOIN Book b ON bt.book_id = b.book_id
                  WHERE bt.user_id = :user_id AND bt.status = 'Borrowed'
                  ORDER BY bt.dueDate ASC";
        
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getUserPenalties($user_id) {
        $query = "SELECT p.*, bt.book_id, b.title, b.price
                  FROM Penalty p
                  JOIN BorrowTransaction bt ON p.borrow_id = bt.borrow_id
                  JOIN Book b ON bt.book_id = b.book_id
                  WHERE bt.user_id = :user_id
                  ORDER BY p.issueDate DESC";
        
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getPendingReservations() {
        $query = "SELECT r.*, u.name, u.role, u.email, b.title, b.author, b.copies, b.status as book_status
                  FROM Reservation r
                  JOIN User u ON r.user_id = u.user_id
                  JOIN Book b ON r.book_id = b.book_id
                  WHERE r.status = 'Pending'
                  ORDER BY r.reservationDate ASC";
        
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function canUserBorrow($user_id, $user_role) {
        $user_id = $this->validateInt($user_id);
        
        // Check penalties
        $query = "SELECT SUM(p.amount) as total
                  FROM Penalty p
                  JOIN BorrowTransaction bt ON p.borrow_id = bt.borrow_id
                  WHERE bt.user_id = :user_id AND p.status = 'Unpaid'";
        
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        $penalties = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        
        if ($penalties['total'] > 0) {
            return ['can_borrow' => false, 'reason' => 'User has unpaid penalties of ₱' . number_format($penalties['total'], 2)];
        }

        // Check clearance
        $query = "SELECT * FROM Clearance 
                  WHERE user_id = :user_id AND clearanceStatus = 'Pending'
                  ORDER BY date DESC LIMIT 1";
        
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        
        if ($stmt && $stmt->rowCount() > 0) {
            return ['can_borrow' => false, 'reason' => 'User has pending clearance'];
        }

        // Check student limit
        if ($this->sanitize($user_role) === 'Student') {
            $query = "SELECT COUNT(*) as count FROM BorrowTransaction 
                      WHERE user_id = :user_id AND status = 'Borrowed'";
            
            $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
            $borrowed = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
            
            if ($borrowed['count'] >= 3) {
                return ['can_borrow' => false, 'reason' => 'Student has reached the maximum limit of 3 borrowed books'];
            }
        }

        return ['can_borrow' => true, 'reason' => ''];
    }

    public function borrowBook($user_id, $book_id, $reserve_id = null) {
        $user_id = $this->validateInt($user_id);
        $book_id = $this->validateInt($book_id);
        $reserve_id = $reserve_id ? $this->validateInt($reserve_id) : null;

        try {
            // Check book availability
            $query = "SELECT * FROM Book WHERE book_id = :book_id AND copies > 0";
            $stmt = $this->executeQuery($query, [':book_id' => $book_id]);
            
            if (!$stmt || $stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Book is not available'];
            }

            $this->conn->beginTransaction();

            // Create borrow transaction
            $query = "INSERT INTO BorrowTransaction (borrowDate, dueDate, status, user_id, book_id)
                      VALUES (CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Borrowed', :user_id, :book_id)";
            $this->executeQuery($query, [':user_id' => $user_id, ':book_id' => $book_id]);

            // Update book copies
            $query = "UPDATE Book SET copies = copies - 1 WHERE book_id = :book_id";
            $this->executeQuery($query, [':book_id' => $book_id]);

            // Update reservation if applicable
            if ($reserve_id) {
                $query = "UPDATE Reservation SET status = 'Approved' WHERE reserve_id = :reserve_id";
                $this->executeQuery($query, [':reserve_id' => $reserve_id]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Book borrowed successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Borrow error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process borrowing'];
        }
    }

    public function returnBook($borrow_id) {
        $borrow_id = $this->validateInt($borrow_id);

        try {
            $query = "SELECT bt.*, b.price FROM BorrowTransaction bt
                      JOIN Book b ON bt.book_id = b.book_id
                      WHERE bt.borrow_id = :borrow_id AND bt.status = 'Borrowed'";
            
            $stmt = $this->executeQuery($query, [':borrow_id' => $borrow_id]);
            $borrow = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

            if (!$borrow) {
                return ['success' => false, 'message' => 'Borrow transaction not found'];
            }

            $this->conn->beginTransaction();

            // Update borrow transaction
            $query = "UPDATE BorrowTransaction 
                      SET returnDate = CURDATE(), status = 'Returned' 
                      WHERE borrow_id = :borrow_id";
            $this->executeQuery($query, [':borrow_id' => $borrow_id]);

            // Update book copies
            $query = "UPDATE Book SET copies = copies + 1 WHERE book_id = :book_id";
            $this->executeQuery($query, [':book_id' => $borrow['book_id']]);

            // Check if late and add penalty
            if (strtotime($borrow['dueDate']) < strtotime(date('Y-m-d'))) {
                $daysLate = (strtotime(date('Y-m-d')) - strtotime($borrow['dueDate'])) / 86400;
                $penaltyAmount = $daysLate * 10;

                $query = "INSERT INTO Penalty (amount, status, issueDate, borrow_id)
                          VALUES (:amount, 'Unpaid', CURDATE(), :borrow_id)";
                $this->executeQuery($query, [':amount' => $penaltyAmount, ':borrow_id' => $borrow_id]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Book returned successfully'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Return error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to process return'];
        }
    }

    public function addPenalty($borrow_id) {
        $borrow_id = $this->validateInt($borrow_id);

        $query = "SELECT bt.*, b.price FROM BorrowTransaction bt
                  JOIN Book b ON bt.book_id = b.book_id
                  WHERE bt.borrow_id = :borrow_id";
        
        $stmt = $this->executeQuery($query, [':borrow_id' => $borrow_id]);
        $borrow = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

        if (!$borrow) {
            return ['success' => false, 'message' => 'Borrow transaction not found'];
        }

        $query = "INSERT INTO Penalty (amount, status, issueDate, borrow_id)
                  VALUES (:amount, 'Unpaid', CURDATE(), :borrow_id)";
        
        $result = $this->executeQuery($query, [
            ':amount' => $this->validateFloat($borrow['price']),
            ':borrow_id' => $borrow_id
        ]);

        return $result !== false 
            ? ['success' => true, 'message' => 'Penalty added successfully']
            : ['success' => false, 'message' => 'Failed to add penalty'];
    }

    /**
     * Process clearance for a user
     * 
     * Steps:
     * 1. Check if user has unreturned books
     * 2. Check if user has unpaid penalties
     * 3. If both checks pass, create clearance record
     * 
     * @param int $user_id User's unique ID
     * @param string $semester Semester period (e.g., "1st Semester 2024-2025")
     * @return array Result with success status and message
     */
    public function processClearance($user_id, $semester) {
        error_log("===== StaffModel::processClearance START =====");
        
        // Validate and sanitize inputs
        $user_id = $this->validateInt($user_id);
        $semester = $this->sanitize($semester);

        error_log("Validated user_id: {$user_id}");
        error_log("Sanitized semester: {$semester}");

        // Check unreturned books
        $query = "SELECT COUNT(*) as count FROM BorrowTransaction 
                  WHERE user_id = :user_id AND status = 'Borrowed'";
        
        error_log("Checking borrowed books...");
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        $borrowed = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];

        $borrowedCount = intval($borrowed['count'] ?? 0);
        error_log("Borrowed books count: {$borrowedCount}");

        if ($borrowedCount > 0) {
            error_log("FAILED: User has unreturned books");
            return ['success' => false, 'message' => 'User has unreturned books'];
        }

        // Check unpaid penalties
        $query = "SELECT COALESCE(SUM(p.amount), 0) as total
                  FROM Penalty p
                  JOIN BorrowTransaction bt ON p.borrow_id = bt.borrow_id
                  WHERE bt.user_id = :user_id AND p.status = 'Unpaid'";
        
        error_log("Checking unpaid penalties...");
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        $penalties = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];

        $unpaidTotal = floatval($penalties['total'] ?? 0);
        error_log("Unpaid penalties: {$unpaidTotal}");
        
        if ($unpaidTotal > 0) {
            error_log("FAILED: User has unpaid penalties");
            return ['success' => false, 'message' => 'User has unpaid penalties of ₱' . number_format($unpaidTotal, 2)];
        }

        // Check if clearance already exists for this semester
        $checkQuery = "SELECT clearance_id FROM Clearance 
                       WHERE user_id = :user_id AND semester = :semester";
        
        error_log("Checking for existing clearance...");
        $checkStmt = $this->executeQuery($checkQuery, [
            ':user_id' => $user_id,
            ':semester' => $semester
        ]);

        if ($checkStmt && $checkStmt->rowCount() > 0) {
            error_log("FAILED: Clearance already exists");
            return ['success' => false, 'message' => 'Clearance already exists for this semester'];
        }

        // Insert clearance record
        $insertQuery = "INSERT INTO Clearance (semester, clearanceStatus, date, user_id)
                        VALUES (:semester, 'Cleared', CURDATE(), :user_id)";
        
        $params = [
            ':semester' => $semester,
            ':user_id' => $user_id
        ];

        error_log("Attempting to insert clearance...");
        error_log("Insert query: {$insertQuery}");
        error_log("Insert params: " . json_encode($params));

        try {
            $result = $this->executeQuery($insertQuery, $params);

            if ($result === false) {
                error_log("FAILED: executeQuery returned false");
                return ['success' => false, 'message' => 'Database error: Failed to process clearance'];
            }

            // Verify the insert was successful
            $lastId = $this->conn->lastInsertId();
            error_log("Last insert ID: {$lastId}");

            if ($lastId > 0) {
                error_log("SUCCESS: Clearance inserted with ID {$lastId}");
                error_log("===== StaffModel::processClearance END (SUCCESS) =====");
                return ['success' => true, 'message' => 'User cleared successfully for ' . $semester];
            } else {
                error_log("FAILED: No last insert ID");
                error_log("===== StaffModel::processClearance END (FAILED) =====");
                return ['success' => false, 'message' => 'Clearance may not have been saved properly'];
            }

        } catch (PDOException $e) {
            error_log("EXCEPTION: " . $e->getMessage());
            error_log("===== StaffModel::processClearance END (EXCEPTION) =====");
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function rejectReservation($reserve_id) {
        $query = "UPDATE Reservation SET status = 'Rejected' WHERE reserve_id = :reserve_id";
        return $this->executeQuery($query, [':reserve_id' => $this->validateInt($reserve_id)]) !== false;
    }

    public function searchBorrowers($search) {
        $search = '%' . $this->sanitize($search) . '%';
        $query = "SELECT u.user_id, u.name, u.email, u.role,
                  COUNT(DISTINCT bt.borrow_id) as borrowed_count,
                  SUM(CASE WHEN p.status = 'Unpaid' THEN p.amount ELSE 0 END) as unpaid_penalties
                  FROM User u
                  LEFT JOIN BorrowTransaction bt ON u.user_id = bt.user_id AND bt.status = 'Borrowed'
                  LEFT JOIN Penalty p ON bt.borrow_id = p.borrow_id
                  WHERE u.role IN ('Student', 'Teacher')
                  AND (u.name LIKE :search OR u.email LIKE :search)
                  GROUP BY u.user_id
                  ORDER BY u.name ASC";
        
        $stmt = $this->executeQuery($query, [':search' => $search]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getAllBorrowedBooks() {
        $query = "SELECT bt.*, b.title, b.author, u.name as borrower_name, u.role
                  FROM BorrowTransaction bt
                  JOIN Book b ON bt.book_id = b.book_id
                  JOIN User u ON bt.user_id = u.user_id
                  WHERE bt.status = 'Borrowed'
                  ORDER BY bt.dueDate ASC";
        
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function waivePenalty($penalty_id) {
        $query = "UPDATE Penalty SET status = 'Waived' WHERE penalty_id = :penalty_id";
        $result = $this->executeQuery($query, [':penalty_id' => $this->validateInt($penalty_id)]);
        
        return $result !== false
            ? ['success' => true, 'message' => 'Penalty waived successfully']
            : ['success' => false, 'message' => 'Failed to waive penalty'];
    }

    public function markPenaltyAsPaid($penalty_id) {
        $query = "UPDATE Penalty SET status = 'Paid' WHERE penalty_id = :penalty_id";
        $result = $this->executeQuery($query, [':penalty_id' => $this->validateInt($penalty_id)]);
        
        return $result !== false
            ? ['success' => true, 'message' => 'Penalty marked as paid']
            : ['success' => false, 'message' => 'Failed to update penalty'];
    }

    /**
     * Check if a user is eligible for clearance
     * 
     * Requirements:
     * 1. No borrowed books (all returned)
     * 2. No unpaid penalties
     * 3. No pending reservations (optional warning)
     * 
     * @param int $user_id User's unique ID
     * @return array Eligibility status with details
     */
    public function checkClearanceEligibility($user_id) {
        $user_id = $this->validateInt($user_id);
        
        // Check borrowed books
        $query = "SELECT COUNT(*) as count FROM BorrowTransaction 
                  WHERE user_id = :user_id AND status = 'Borrowed'";
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        $borrowed = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
        
        // Check unpaid penalties - FIX: Use COALESCE to handle NULL
        $query = "SELECT COALESCE(SUM(p.amount), 0) as total
                  FROM Penalty p
                  JOIN BorrowTransaction bt ON p.borrow_id = bt.borrow_id
                  WHERE bt.user_id = :user_id AND p.status = 'Unpaid'";
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        $penalties = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        
        // Check pending reservations
        $query = "SELECT COUNT(*) as count FROM Reservation 
                  WHERE user_id = :user_id AND status = 'Pending'";
        $stmt = $this->executeQuery($query, [':user_id' => $user_id]);
        $reservations = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
        
        // Determine eligibility - FIX: Properly convert to numeric types
        $borrowedCount = intval($borrowed['count'] ?? 0);
        $unpaidTotal = floatval($penalties['total'] ?? 0);
        $pendingReservations = intval($reservations['count'] ?? 0);
        
        // FIX: Explicit comparison with 0
        $canClear = ($borrowedCount === 0 && $unpaidTotal == 0);
        
        $reason = '';
        if ($borrowedCount > 0) {
            $reason = "User has {$borrowedCount} unreturned book(s). ";
        }
        if ($unpaidTotal > 0) {
            $reason .= "User has ₱" . number_format($unpaidTotal, 2) . " in unpaid penalties. ";
        }
        
        return [
            'can_clear' => $canClear,
            'borrowed_books' => $borrowedCount,
            'unpaid_penalties' => $unpaidTotal,
            'pending_reservations' => $pendingReservations,
            'reason' => trim($reason) ?: 'User is eligible for clearance'
        ];
    }

    /**
     * Get user's clearance history
     * 
     * Returns all clearance records for a specific user.
     * 
     * @param int $user_id User's unique ID
     * @return array Array of clearance records
     * 
     * @example
     * $history = $staffModel->getUserClearanceHistory(5);
     * foreach ($history as $record) {
     *     echo $record['semester'] . ": " . $record['clearanceStatus'];
     * }
     */
    public function getUserClearanceHistory($user_id) {
        $query = "SELECT * FROM Clearance 
                  WHERE user_id = :user_id 
                  ORDER BY date DESC";
        
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
?>