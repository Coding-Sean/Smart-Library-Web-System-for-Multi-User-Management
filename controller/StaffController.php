<?php
// filepath: c:\xampp\htdocs\MyLibrary\controller\StaffController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../model/StaffModel.php';

class StaffController extends BaseController {
    private $staffModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth('Staff');
        $this->staffModel = new StaffModel($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        $actions = [
            'borrow' => 'borrowBook',
            'return' => 'returnBook',
            'add_penalty' => 'addPenalty',
            'clearance' => 'processClearance',        // ✅ Make sure this exists
            'check_clearance' => 'checkClearance',   // ✅ Make sure this exists
            'reject_reservation' => 'rejectReservation',
            'get_user_details' => 'getUserDetails',
            'waive_penalty' => 'waivePenalty',
            'mark_paid' => 'markPaid'
        ];

        if (isset($actions[$action])) {
            $this->{$actions[$action]}();
        } else {
            $this->redirect('../view/Staff_Dashboard.php');
        }
    }

    private function borrowBook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Staff_Dashboard.php');
        }

        $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $book_id = filter_var($_POST['book_id'] ?? 0, FILTER_VALIDATE_INT);
        $reserve_id = filter_var($_POST['reserve_id'] ?? 0, FILTER_VALIDATE_INT);

        $user = $this->staffModel->getUserDetails($user_id);
        if (!$user) {
            $this->redirect('../view/Staff_Dashboard.php', 'User not found', 'error');
        }

        $eligibility = $this->staffModel->canUserBorrow($user_id, $user['role']);
        if (!$eligibility['can_borrow']) {
            $this->redirect('../view/Staff_Dashboard.php', $eligibility['reason'], 'error');
        }

        $result = $this->staffModel->borrowBook($user_id, $book_id, $reserve_id > 0 ? $reserve_id : null);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function returnBook() {
        $borrow_id = filter_var($_GET['borrow_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->returnBook($borrow_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function addPenalty() {
        $borrow_id = filter_var($_GET['borrow_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->addPenalty($borrow_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function processClearance() {
        error_log("===== CLEARANCE PROCESSING STARTED =====");
        error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("ERROR: Not a POST request");
            $this->redirect('../view/Staff_Dashboard.php', 'Invalid request method', 'error');
            return;
        }

        $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
        $semester = trim($_POST['semester'] ?? '');

        error_log("Parsed user_id: {$user_id}");
        error_log("Parsed semester: {$semester}");

        if (!$user_id || $user_id <= 0) {
            error_log("ERROR: Invalid user_id");
            $this->redirect('../view/Staff_Dashboard.php', 'Invalid user ID', 'error');
            return;
        }

        if (empty($semester)) {
            error_log("ERROR: Semester is empty");
            $this->redirect('../view/Staff_Dashboard.php', 'Semester is required', 'error');
            return;
        }

        $semester = htmlspecialchars($semester, ENT_QUOTES, 'UTF-8');

        try {
            error_log("Calling staffModel->processClearance()");
            $result = $this->staffModel->processClearance($user_id, $semester);
            
            error_log("Result: " . json_encode($result));

            $this->redirect(
                '../view/Staff_Dashboard.php',
                $result['message'],
                $result['success'] ? 'success' : 'error'
            );
        } catch (Exception $e) {
            error_log("EXCEPTION: " . $e->getMessage());
            $this->redirect('../view/Staff_Dashboard.php', 'An error occurred while processing clearance', 'error');
        }

        error_log("===== CLEARANCE PROCESSING ENDED =====");
    }

    private function rejectReservation() {
        $reserve_id = filter_var($_GET['reserve_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->rejectReservation($reserve_id);
        
        $message = $result ? 'Reservation rejected' : 'Failed to reject reservation';
        $this->redirect('../view/Staff_Dashboard.php', $message, $result ? 'success' : 'error');
    }

    private function getUserDetails() {
        $user_id = filter_var($_GET['user_id'] ?? 0, FILTER_VALIDATE_INT);
        
        $user = $this->staffModel->getUserDetails($user_id);
        $borrowedBooks = $this->staffModel->getUserBorrowedBooks($user_id);
        $penalties = $this->staffModel->getUserPenalties($user_id);
        $eligibility = $this->staffModel->canUserBorrow($user_id, $user['role']);

        header('Content-Type: application/json');
        echo json_encode([
            'user' => $user,
            'borrowed_books' => $borrowedBooks,
            'penalties' => $penalties,
            'can_borrow' => $eligibility['can_borrow'],
            'borrow_reason' => $eligibility['reason']
        ]);
        exit;
    }

    private function waivePenalty() {
        $penalty_id = filter_var($_GET['penalty_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->waivePenalty($penalty_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function markPaid() {
        $penalty_id = filter_var($_GET['penalty_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->staffModel->markPenaltyAsPaid($penalty_id);
        $this->redirect(
            '../view/Staff_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    // Add this new method to the StaffController class
    private function checkClearance() {
        $user_id = filter_var($_GET['user_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if (!$user_id) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid user ID']);
            exit;
        }

        // Get clearance eligibility
        $eligibility = $this->staffModel->checkClearanceEligibility($user_id);
        
        header('Content-Type: application/json');
        echo json_encode($eligibility);
        exit;
    }
}

$controller = new StaffController();
$controller->handleRequest();
?>