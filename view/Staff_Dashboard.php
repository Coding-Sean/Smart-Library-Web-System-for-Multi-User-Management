<?php
// filepath: c:\xampp\htdocs\MyLibrary\view\Staff_Dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff') {
    header('Location: Log_In.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/LibrarianModel.php';
require_once __DIR__ . '/../model/StaffModel.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/confirm_modal.php';

$db = (new Database())->getConnection();
$librarianModel = new LibrarianModel($db);
$staffModel = new StaffModel($db);

$books = $librarianModel->getAllBooks();
$borrowers = $staffModel->getAllBorrowers();
$borrowedBooks = $staffModel->getAllBorrowedBooks();
$pendingReservations = $staffModel->getPendingReservations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary - Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/staff.css">
</head>
<body>
    <?php echo displayMessage(); ?>
    <?php echo getConfirmModal(); ?>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <h1 class="header-title">MyLibrary - Staff Dashboard</h1>
            <div class="header-right">
                <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="../controller/LogoutController.php" class="btn-logout">LOG OUT</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- MyLibrary Inventory Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">MyLibrary Inventory</h2>
                    <div class="card-content">
                        <h3 class="text-center" style="font-size: 2.5rem; color: #2c3e50;">
                            <?= count($books) ?> Books
                        </h3>
                        <p class="text-center text-muted">Total books in library</p>
                    </div>
                </div>
            </div>

            <!-- Currently Borrowed Books Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">Currently Borrowed Books</h2>
                    <div class="card-content">
                        <h3 class="text-center" style="font-size: 2.5rem; color: #2c3e50;">
                            <?= count($borrowedBooks) ?>
                        </h3>
                        <p class="text-center text-muted">Books currently borrowed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Reservations Card -->
        <?php if (!empty($pendingReservations)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Pending Reservations (<?= count($pendingReservations) ?>)</h2>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Book</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReservations as $reservation): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($reservation['name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($reservation['role']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($reservation['title']) ?><br>
                                                <small class="text-muted">by <?= htmlspecialchars($reservation['author']) ?></small>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($reservation['reservationDate'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="approveReservation(<?= $reservation['reserve_id'] ?>, <?= $reservation['user_id'] ?>, <?= $reservation['book_id'] ?>)">
                                                    Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="rejectReservation(<?= $reservation['reserve_id'] ?>)">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clearance Processing Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Process Semester Clearance</h2>
                    <div class="card-content">
                        <div class="alert alert-info">
                            <strong>Clearance Requirements:</strong>
                            <ul class="mb-0">
                                <li>All borrowed books must be returned</li>
                                <li>All penalties must be paid</li>
                                <li>Student/Teacher must not have pending reservations</li>
                            </ul>
                        </div>

                        <!-- Clearance Form -->
                        <form id="clearanceForm" class="mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="clearance_user" class="form-label">Select Student/Teacher</label>
                                    <select class="form-control" id="clearance_user" required>
                                        <option value="">-- Select User --</option>
                                        <?php foreach ($borrowers as $borrower): ?>
                                            <option value="<?= $borrower['user_id'] ?>" 
                                                    data-name="<?= htmlspecialchars($borrower['name']) ?>"
                                                    data-role="<?= htmlspecialchars($borrower['role']) ?>">
                                                <?= htmlspecialchars($borrower['name']) ?> (<?= htmlspecialchars($borrower['role']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="clearance_semester" class="form-label">Semester</label>
                                    <select class="form-control" id="clearance_semester" required>
                                        <option value="">-- Select Semester --</option>
                                        <option value="1st Semester 2024-2025">1st Semester 2024-2025</option>
                                        <option value="2nd Semester 2024-2025">2nd Semester 2024-2025</option>
                                        <option value="Summer 2024-2025">Summer 2024-2025</option>
                                        <option value="1st Semester 2025-2026">1st Semester 2025-2026</option>
                                        <option value="2nd Semester 2025-2026">2nd Semester 2025-2026</option>
                                    </select>
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" onclick="checkClearanceEligibility()">
                                        Check Eligibility
                                    </button>
                                </div>
                            </div>

                            <div id="clearanceResult" class="mt-3"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users with Clearance Status -->
        <?php
        // Get users with clearance records
        $clearanceQuery = "SELECT c.*, u.name, u.role 
                        FROM Clearance c
                        JOIN User u ON c.user_id = u.user_id
                        ORDER BY c.date DESC
                        LIMIT 20";
        $clearanceStmt = $db->prepare($clearanceQuery);
        $clearanceStmt->execute();
        $clearanceRecords = $clearanceStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($clearanceRecords)):
        ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Recent Clearance Records (Showing <span id="clearanceCount">2</span> of <?= count($clearanceRecords) ?>)</h2>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Student/Teacher</th>
                                        <th>Role</th>
                                        <th>Semester</th>
                                        <th>Status</th>
                                        <th>Date Processed</th>
                                    </tr>
                                </thead>
                                <tbody id="clearanceTableBody">
                                    <?php foreach ($clearanceRecords as $index => $record): ?>
                                        <tr class="clearance-row" data-index="<?= $index ?>" style="<?= $index >= 2 ? 'display: none;' : '' ?>">
                                            <td><strong><?= htmlspecialchars($record['name']) ?></strong></td>
                                            <td><?= htmlspecialchars($record['role']) ?></td>
                                            <td><?= htmlspecialchars($record['semester']) ?></td>
                                            <td>
                                                <span class="badge <?= $record['clearanceStatus'] === 'Cleared' ? 'bg-success' : 'bg-warning' ?>">
                                                    <?= htmlspecialchars($record['clearanceStatus']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls for Clearance -->
                        <?php if (count($clearanceRecords) > 2): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-secondary btn-sm" id="prevClearanceBtn" onclick="changeClearancePage(-1)" disabled>Previous</button>
                            <span class="pagination-info">Page <span id="currentClearancePage">1</span> of <span id="totalClearancePages"><?= ceil(count($clearanceRecords) / 2) ?></span></span>
                            <button class="btn btn-secondary btn-sm" id="nextClearanceBtn" onclick="changeClearancePage(1)">Next</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Borrower Management Card - UPDATE PAGINATION TO SHOW 2 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card borrower-management-card">
                    <h2 class="card-title">Borrower Management (Showing <span id="borrowerCount">2</span> of <?= count($borrowers) ?>)</h2>
                    <div class="card-content">
                        <!-- Search Bar -->
                        <div class="search-container">
                            <input type="text" class="search-input" id="searchInput" placeholder="Search borrowers by name or email...">
                            <button class="btn-search" onclick="searchBorrowers()">SEARCH</button>
                        </div>

                        <!-- Borrower List -->
                        <div class="borrower-list mt-3" id="borrowerList">
                            <?php if (empty($borrowers)): ?>
                                <p class="text-muted text-center py-4">No registered borrowers at the moment.</p>
                            <?php else: ?>
                                <?php foreach ($borrowers as $index => $borrower): ?>
                                    <div class="borrower-item" data-index="<?= $index ?>" data-name="<?= htmlspecialchars($borrower['name']) ?>" data-email="<?= htmlspecialchars($borrower['email']) ?>" style="<?= $index >= 2 ? 'display: none;' : '' ?>">
                                        <div class="borrower-info-container">
                                            <span class="borrower-info">
                                                <?= htmlspecialchars($borrower['name']) ?> (<?= htmlspecialchars($borrower['role']) ?>)
                                                <?php if ($borrower['unpaid_penalties'] > 0): ?>
                                                    <span class="badge bg-danger ms-2">₱<?= number_format($borrower['unpaid_penalties'], 2) ?> penalty</span>
                                                <?php endif; ?>
                                                <?php if ($borrower['borrowed_count'] > 0): ?>
                                                    <span class="badge bg-info ms-2"><?= $borrower['borrowed_count'] ?> borrowed</span>
                                                <?php endif; ?>
                                            </span>
                                            <small class="text-white-50 d-block"><?= htmlspecialchars($borrower['email']) ?></small>
                                        </div>
                                        <div class="borrower-actions">
                                            <button class="btn-borrow" onclick="handleBorrow(<?= $borrower['user_id'] ?>, '<?= htmlspecialchars($borrower['name']) ?>', '<?= htmlspecialchars($borrower['role']) ?>')">borrow</button>
                                            <button class="btn-return" onclick="handleReturn(<?= $borrower['user_id'] ?>, '<?= htmlspecialchars($borrower['name']) ?>')">return</button>
                                            <button class="btn-penalty" onclick="handlePenalty(<?= $borrower['user_id'] ?>, '<?= htmlspecialchars($borrower['name']) ?>')">penalty</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination Controls for Borrowers -->
                        <?php if (count($borrowers) > 2): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-secondary btn-sm" id="prevBorrowerBtn" onclick="changeBorrowerPage(-1)" disabled>Previous</button>
                            <span class="pagination-info">Page <span id="currentBorrowerPage">1</span> of <span id="totalBorrowerPages"><?= ceil(count($borrowers) / 2) ?></span></span>
                            <button class="btn btn-secondary btn-sm" id="nextBorrowerBtn" onclick="changeBorrowerPage(1)">Next</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Borrowed Books Details - ADD PAGINATION -->
        <?php if (!empty($borrowedBooks)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Currently Borrowed Books Details (Showing <span id="borrowedCount">2</span> of <?= count($borrowedBooks) ?>)</h2>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Borrower</th>
                                        <th>Book</th>
                                        <th>Borrow Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="borrowedBooksTableBody">
                                    <?php foreach ($borrowedBooks as $index => $borrowed): ?>
                                        <?php $isOverdue = strtotime($borrowed['dueDate']) < strtotime(date('Y-m-d')); ?>
                                        <tr class="borrowed-book-row <?= $isOverdue ? 'table-danger' : '' ?>" data-index="<?= $index ?>" style="<?= $index >= 2 ? 'display: none;' : '' ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($borrowed['borrower_name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($borrowed['role']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($borrowed['title']) ?><br>
                                                <small class="text-muted">by <?= htmlspecialchars($borrowed['author']) ?></small>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($borrowed['borrowDate'])) ?></td>
                                            <td>
                                                <?= date('M d, Y', strtotime($borrowed['dueDate'])) ?>
                                                <?php if ($isOverdue): ?>
                                                    <br><span class="badge bg-danger">OVERDUE</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?= htmlspecialchars($borrowed['status']) ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="processReturn(<?= $borrowed['borrow_id'] ?>)">
                                                    Return
                                                </button>
                                                <?php if ($isOverdue): ?>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="addPenalty(<?= $borrowed['borrow_id'] ?>)">
                                                        Add Penalty
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls for Borrowed Books -->
                        <?php if (count($borrowedBooks) > 2): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-secondary btn-sm" id="prevBorrowedBtn" onclick="changeBorrowedBooksPage(-1)" disabled>Previous</button>
                            <span class="pagination-info">Page <span id="currentBorrowedPage">1</span> of <span id="totalBorrowedPages"><?= ceil(count($borrowedBooks) / 2) ?></span></span>
                            <button class="btn btn-secondary btn-sm" id="nextBorrowedBtn" onclick="changeBorrowedBooksPage(1)">Next</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Borrow Modal -->
    <div class="modal fade" id="borrowModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Borrow Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="userInfo"></div>
                    <form id="borrowForm" method="POST" action="../controller/StaffController.php?action=borrow">
                        <input type="hidden" name="user_id" id="borrow_user_id">
                        <input type="hidden" name="reserve_id" value="0">
                        
                        <div class="mb-3">
                            <label for="book_id" class="form-label">Select Book</label>
                            <select class="form-control" name="book_id" id="book_id" required>
                                <option value="">-- Select a book --</option>
                                <?php foreach ($books as $book): ?>
                                    <?php if ($book['status'] === 'Available' && $book['copies'] > 0): ?>
                                        <option value="<?= $book['book_id'] ?>">
                                            <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?> (<?= $book['copies'] ?> available)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Confirm Borrow</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Return Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="returnUserInfo"></div>
                    <div id="returnBooksList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Penalty Modal -->
    <div class="modal fade" id="penaltyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Penalties</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="penaltyUserInfo"></div>
                    <div id="penaltyList"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/staff-dashboard.js"></script>
</body>
</html>