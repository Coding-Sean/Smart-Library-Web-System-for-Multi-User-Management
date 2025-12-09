<?php
// filepath: c:\xampp\htdocs\MyLibrary\view\Teach_Stud_Dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Teacher','Student'])) {
    header('Location: Log_In.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/LibrarianModel.php';
require_once __DIR__ . '/../model/StudentTeacherModel.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/confirm_modal.php';

$db = (new Database())->getConnection();
$librarianModel = new LibrarianModel($db);
$studentTeacherModel = new StudentTeacherModel($db);

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Define missing variables
$isStudent = ($user_role === 'Student');
$borrowLimit = 3; // Student limit
$borrowedCount = $studentTeacherModel->getUserBorrowedCount($user_id);
$currentBorrowed = $borrowedCount; // Current borrowed count

// Get data
$books = $librarianModel->getAllBooks();
$borrowedBooks = $studentTeacherModel->getUserBorrowedBooks($user_id);
$allReservations = $studentTeacherModel->getUserReservations($user_id);

// Sort reservations: Pending first, then by date descending
usort($allReservations, function($a, $b) {
    // Priority: Pending > Approved > Rejected
    $statusPriority = ['Pending' => 1, 'Approved' => 2, 'Rejected' => 3];
    $aPriority = $statusPriority[$a['status']] ?? 4;
    $bPriority = $statusPriority[$b['status']] ?? 4;
    
    if ($aPriority !== $bPriority) {
        return $aPriority - $bPriority;
    }
    
    // If same status, sort by date (newest first)
    return strtotime($b['reservationDate']) - strtotime($a['reservationDate']);
});

$reservations = $allReservations; // Keep for compatibility

$penalties = $studentTeacherModel->getUserPenalties($user_id);
$totalUnpaid = $studentTeacherModel->getTotalUnpaidPenalties($user_id);
$totalUnpaidPenalties = $totalUnpaid;
$clearanceStatus = $studentTeacherModel->getUserClearanceStatus($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary - <?= $_SESSION['user_role'] ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/stud_teacher.css">
</head>
<body>
    <?php echo displayMessage(); ?>
    <?php echo getConfirmModal(); ?>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <h1 class="header-title">MyLibrary - <?= $_SESSION['user_role'] ?> Dashboard</h1>
            <div class="header-right">
                <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="../controller/LogoutController.php" class="btn-logout">LOG OUT</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <?php if ($isStudent): ?>
            <div class="alert-warning">
                <strong>Student Borrowing Rules:</strong> You can borrow up to 3 books per semester. You must return all books to be cleared, otherwise you must pay the book price.
            </div>
        <?php else: ?>
            <div class="alert-info">
                <strong>Teacher Borrowing Rules:</strong> You can borrow unlimited books. You must return all books at semester end for clearance.
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- MyLibrary Status Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">MyLibrary Status</h2>
                    <div class="card-content">
                        <div class="status-info">
                            <div class="status-item">
                                <span class="status-label">Role:</span>
                                <span class="status-value"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Books Borrowed:</span>
                                <span class="status-value"><?= $currentBorrowed ?> / <?= $isStudent ? $borrowLimit : 'Unlimited' ?></span>
                            </div>
                            <?php if ($isStudent): ?>
                                <div class="status-item">
                                    <span class="status-label">Available Slots:</span>
                                    <span class="status-value"><?= $borrowLimit - $currentBorrowed ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($totalUnpaidPenalties > 0): ?>
                                <div class="status-item">
                                    <span class="status-label">Unpaid Penalties:</span>
                                    <span class="status-value text-danger">₱<?= number_format($totalUnpaidPenalties, 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($clearanceStatus): ?>
                                <div class="status-item">
                                    <span class="status-label">Clearance Status:</span>
                                    <span class="status-value <?= $clearanceStatus['clearanceStatus'] === 'Cleared' ? 'text-success' : 'text-warning' ?>">
                                        <?= htmlspecialchars($clearanceStatus['clearanceStatus']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Currently Borrowed Books Card with Fixed Height -->
            <div class="col-md-6">
                <div class="dashboard-card borrowed-books-card">
                    <h2 class="card-title">
                        Currently Borrowed Books 
                        <?php if (!empty($borrowedBooks)): ?>
                            (Showing <span id="borrowedBookCount">2</span> of <?= count($borrowedBooks) ?>)
                        <?php endif; ?>
                    </h2>
                    <div class="card-content">
                        <?php if (empty($borrowedBooks)): ?>
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <p class="text-muted mb-0">No books currently borrowed.</p>
                            </div>
                        <?php else: ?>
                            <div class="borrowed-list" id="borrowedBooksList">
                                <?php foreach ($borrowedBooks as $index => $borrowed): ?>
                                    <?php 
                                    $isOverdue = strtotime($borrowed['dueDate']) < strtotime(date('Y-m-d'));
                                    $daysRemaining = ceil((strtotime($borrowed['dueDate']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
                                    ?>
                                    <div class="borrowed-item borrowed-book-item" data-index="<?= $index ?>" style="<?= $index >= 2 ? 'display: none;' : '' ?>">
                                        <div class="borrowed-title">
                                            <?= htmlspecialchars($borrowed['title']) ?> by <?= htmlspecialchars($borrowed['author']) ?>
                                        </div>
                                        <div class="borrowed-date">
                                            Borrowed: <?= date('M d, Y', strtotime($borrowed['borrowDate'])) ?><br>
                                            Due: <?= date('M d, Y', strtotime($borrowed['dueDate'])) ?>
                                            <?php if ($isOverdue): ?>
                                                <br><span class="badge bg-danger">OVERDUE</span>
                                            <?php elseif ($daysRemaining <= 3 && $daysRemaining > 0): ?>
                                                <br><span class="badge bg-warning text-dark">Due in <?= $daysRemaining ?> day<?= $daysRemaining > 1 ? 's' : '' ?></span>
                                            <?php elseif ($daysRemaining > 0): ?>
                                                <br><span class="badge bg-info text-dark"><?= $daysRemaining ?> days remaining</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Pagination Controls for Borrowed Books -->
                            <?php if (count($borrowedBooks) > 2): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3 flex-shrink-0">
                                <button class="btn btn-secondary btn-sm" id="prevBorrowedBookBtn" onclick="changeBorrowedBookPage(-1)" disabled>Previous</button>
                                <span class="pagination-info">Page <span id="currentBorrowedBookPage">1</span> of <span id="totalBorrowedBookPages"><?= ceil(count($borrowedBooks) / 2) ?></span></span>
                                <button class="btn btn-secondary btn-sm" id="nextBorrowedBookBtn" onclick="changeBorrowedBookPage(1)">Next</button>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        
        <!-- My Reservations Card -->
        <?php if (!empty($reservations)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">My Reservations (Showing <span id="reservationCount">2</span> of <?= count($reservations) ?>)</h2>
                    <div class="card-content">
                        <div class="borrowed-list" id="reservationList">
                            <?php foreach ($reservations as $index => $reservation): ?>
                                <div class="borrowed-item reservation-item" data-index="<?= $index ?>" style="<?= $index >= 2 ? 'display: none;' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="borrowed-title"><?= htmlspecialchars($reservation['title']) ?> by <?= htmlspecialchars($reservation['author']) ?></div>
                                            <div class="borrowed-date">
                                                Reserved: <?= date('M d, Y', strtotime($reservation['reservationDate'])) ?><br>
                                                Status: <span class="badge <?= $reservation['status'] === 'Approved' ? 'bg-success' : ($reservation['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                                    <?= htmlspecialchars($reservation['status']) ?>
                                                </span>
                                                <?php if ($reservation['status'] === 'Pending'): ?>
                                                    <span class="badge bg-info text-dark ms-1">Awaiting Approval</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if ($reservation['status'] === 'Pending'): ?>
                                            <button class="btn btn-sm btn-danger" onclick="cancelReservation(<?= $reservation['reserve_id'] ?>)">Cancel</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination Controls -->
                        <?php if (count($reservations) > 2): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-secondary btn-sm" id="prevReservationBtn" onclick="changeReservationPage(-1)" disabled>Previous</button>
                            <span class="pagination-info">Page <span id="currentReservationPage">1</span> of <span id="totalReservationPages"><?= ceil(count($reservations) / 2) ?></span></span>
                            <button class="btn btn-secondary btn-sm" id="nextReservationBtn" onclick="changeReservationPage(1)">Next</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Penalties Card -->
        <?php if (!empty($penalties)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Penalties</h2>
                    <div class="card-content">
                        <div class="borrowed-list">
                            <?php foreach ($penalties as $penalty): ?>
                                <div class="borrowed-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="borrowed-title"><?= htmlspecialchars($penalty['title']) ?></div>
                                            <div class="borrowed-date">
                                                Amount: ₱<?= number_format($penalty['amount'], 2) ?><br>
                                                Issued: <?= date('M d, Y', strtotime($penalty['issueDate'])) ?><br>
                                                Status: <span class="badge <?= $penalty['status'] === 'Paid' ? 'bg-success' : ($penalty['status'] === 'Waived' ? 'bg-info' : 'bg-danger') ?>">
                                                    <?= htmlspecialchars($penalty['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Clearance Status Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">My Clearance Status</h2>
                    <div class="card-content">
                        <?php
                        // Get clearance history
                        $clearanceHistory = $studentTeacherModel->getUserClearanceHistory($user_id);
                        
                        if (empty($clearanceHistory)):
                        ?>
                            <div class="alert alert-info">
                                <strong>No clearance records yet.</strong><br>
                                Contact the library staff at the end of the semester for clearance processing.
                            </div>
                            
                            <!-- Show clearance requirements -->
                            <div class="card bg-light mt-3">
                                <div class="card-body">
                                    <h6 class="card-title">Clearance Requirements:</h6>
                                    <ul class="mb-0">
                                        <li>Return all borrowed books</li>
                                        <li>Pay all penalties and fines</li>
                                        <li>No pending reservations</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <!-- Show current status -->
                            <div class="mt-3">
                                <h6>Your Current Status:</h6>
                                <ul class="list-group">
                                    <li class="list-group-item <?= $currentBorrowed === 0 ? 'list-group-item-success' : 'list-group-item-danger' ?>">
                                        <strong>Borrowed Books:</strong> <?= $currentBorrowed ?>
                                        <?= $currentBorrowed === 0 ? '✓' : '✗ Please return all books' ?>
                                    </li>
                                    <li class="list-group-item <?= $totalUnpaidPenalties === 0 ? 'list-group-item-success' : 'list-group-item-danger' ?>">
                                        <strong>Unpaid Penalties:</strong> ₱<?= number_format($totalUnpaidPenalties, 2) ?>
                                        <?= $totalUnpaidPenalties === 0 ? '✓' : '✗ Please pay penalties' ?>
                                    </li>
                                </ul>
                                
                                <?php if ($currentBorrowed === 0 && $totalUnpaidPenalties === 0): ?>
                                    <div class="alert alert-success mt-3 mb-0">
                                        <strong>✓ You are eligible for clearance!</strong><br>
                                        Please visit the library staff to process your clearance.
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning mt-3 mb-0">
                                        <strong>⚠ Not yet eligible for clearance</strong><br>
                                        Please complete the requirements above.
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Display clearance history -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Semester</th>
                                            <th>Status</th>
                                            <th>Date Processed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clearanceHistory as $record): ?>
                                            <tr>
                                                <td><strong><?= htmlspecialchars($record['semester']) ?></strong></td>
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
                            
                            <!-- Latest clearance status -->
                            <?php $latestClearance = $clearanceHistory[0]; ?>
                            <div class="alert <?= $latestClearance['clearanceStatus'] === 'Cleared' ? 'alert-success' : 'alert-warning' ?> mt-3">
                                <strong>Latest Clearance:</strong> 
                                <?= htmlspecialchars($latestClearance['semester']) ?> - 
                                <?= htmlspecialchars($latestClearance['clearanceStatus']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Reserve Books Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="search-section">
                    <h2 class="search-header">Search and Reserve Books (Showing <span id="bookDisplayCount">5</span> of <?= count($books) ?>)</h2>
                    
                    <!-- Search Bar -->
                    <div class="search-bar">
                        <input type="text" class="search-input" id="searchInput" placeholder="Search books by title, author, or category...">
                        <button class="btn-search" onclick="searchBooks()">SEARCH</button>
                    </div>

                    <!-- Book List -->
                    <div class="book-list" id="bookList">
                        <?php if (empty($books)): ?>
                            <p class="text-muted text-center py-4">No books available at the moment.</p>
                        <?php else: ?>
                            <?php foreach ($books as $index => $book): ?>
                                <div class="book-item" data-index="<?= $index ?>" data-title="<?= htmlspecialchars($book['title']) ?>" data-author="<?= htmlspecialchars($book['author']) ?>" style="<?= $index >= 5 ? 'display: none;' : '' ?>">
                                    <span class="book-title">
                                        <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?>
                                        <?php if (!empty($book['category'])): ?>
                                            <small class="text-white-50">(<?= htmlspecialchars($book['category']) ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                    <div class="book-actions">
                                        <?php if ($book['status'] === 'Available' && $book['copies'] > 0): ?>
                                            <span class="badge-available">Available (<?= $book['copies'] ?> copies)</span>
                                            <?php 
                                            $canReserve = true;
                                            if ($isStudent && $currentBorrowed >= $borrowLimit) {
                                                $canReserve = false;
                                            }
                                            ?>
                                            <button class="btn-reserve" 
                                                    onclick="reserveBook(<?= $book['book_id'] ?>, '<?= htmlspecialchars($book['title'], ENT_QUOTES) ?>')"
                                                    <?= !$canReserve ? 'disabled' : '' ?>>
                                                RESERVE
                                            </button>
                                        <?php else: ?>
                                            <span class="badge-unavailable">Unavailable</span>
                                            <button class="btn-reserve" disabled>RESERVE</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination Controls -->
                    <?php if (count($books) > 5): ?>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <button class="btn btn-secondary" id="prevBookBtn" onclick="changeBookPage(-1)" disabled>Previous</button>
                        <span class="pagination-info">Page <span id="currentBookPage">1</span> of <span id="totalBookPages"><?= ceil(count($books) / 5) ?></span></span>
                        <button class="btn btn-secondary" id="nextBookBtn" onclick="changeBookPage(1)">Next</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/student-teacher-dashboard.js"></script>
</body>
</html>
