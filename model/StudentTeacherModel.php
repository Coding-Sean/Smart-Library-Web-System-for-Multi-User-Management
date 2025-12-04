<?php
// filepath: c:\xampp\htdocs\MyLibrary\model\StudentTeacherModel.php
require_once __DIR__ . '/BaseModel.php';

class StudentTeacherModel extends BaseModel {
    protected $table = 'reservation';

    public function __construct($db) {
        parent::__construct($db);
    }

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

    public function getUserBorrowedCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM BorrowTransaction 
                  WHERE user_id = :user_id AND status = 'Borrowed'";
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['count' => 0];
        return $result['count'];
    }

    public function getUserBorrowedBooks($user_id) {
        $query = "SELECT bt.*, b.title, b.author 
                  FROM BorrowTransaction bt
                  JOIN Book b ON bt.book_id = b.book_id
                  WHERE bt.user_id = :user_id AND bt.status = 'Borrowed'
                  ORDER BY bt.dueDate ASC";
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getUserReservations($user_id) {
        $query = "SELECT r.*, b.title, b.author, b.status as book_status
                  FROM Reservation r
                  JOIN Book b ON r.book_id = b.book_id
                  WHERE r.user_id = :user_id
                  ORDER BY r.reservationDate DESC";
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function reserveBook($user_id, $book_id) {
        $user_id = $this->validateInt($user_id);
        $book_id = $this->validateInt($book_id);

        // Check if book exists
        $query = "SELECT * FROM Book WHERE book_id = :book_id";
        $stmt = $this->executeQuery($query, [':book_id' => $book_id]);

        if (!$stmt || $stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        // Check duplicate reservation
        $query = "SELECT * FROM Reservation 
                  WHERE user_id = :user_id AND book_id = :book_id AND status = 'Pending'";
        $stmt = $this->executeQuery($query, [':user_id' => $user_id, ':book_id' => $book_id]);

        if ($stmt && $stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'You already have a pending reservation for this book'];
        }

        // Create reservation
        $query = "INSERT INTO Reservation (reservationDate, status, user_id, book_id) 
                  VALUES (CURDATE(), 'Pending', :user_id, :book_id)";
        $result = $this->executeQuery($query, [':user_id' => $user_id, ':book_id' => $book_id]);

        return $result !== false
            ? ['success' => true, 'message' => 'Book reserved successfully! Please wait for staff approval.']
            : ['success' => false, 'message' => 'Failed to reserve book'];
    }

    public function cancelReservation($reserve_id, $user_id) {
        $query = "DELETE FROM Reservation 
                  WHERE reserve_id = :reserve_id AND user_id = :user_id AND status = 'Pending'";
        return $this->executeQuery($query, [
            ':reserve_id' => $this->validateInt($reserve_id),
            ':user_id' => $this->validateInt($user_id)
        ]) !== false;
    }

    public function canStudentBorrow($user_id) {
        return $this->getUserBorrowedCount($user_id) < 3;
    }

    public function getUserClearanceStatus($user_id) {
        $query = "SELECT * FROM Clearance 
                  WHERE user_id = :user_id 
                  ORDER BY date DESC LIMIT 1";
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
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

    public function getTotalUnpaidPenalties($user_id) {
        $query = "SELECT SUM(p.amount) as total
                  FROM Penalty p
                  JOIN BorrowTransaction bt ON p.borrow_id = bt.borrow_id
                  WHERE bt.user_id = :user_id AND p.status = 'Unpaid'";
        $stmt = $this->executeQuery($query, [':user_id' => $this->validateInt($user_id)]);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : ['total' => 0];
        return $result['total'] ?? 0;
    }

    /**
     * Get user's clearance history
     * 
     * Returns all clearance records for the logged-in student/teacher.
     * 
     * @param int $user_id User's unique ID
     * @return array Array of clearance records
     * 
     * @example
     * $history = $studentTeacherModel->getUserClearanceHistory($_SESSION['user_id']);
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