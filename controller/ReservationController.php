<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../model/StudentTeacherModel.php';

class ReservationController extends BaseController {
    private $studentTeacherModel;

    public function __construct() {
        parent::__construct();
        
        if (!in_array($_SESSION['user_role'] ?? '', ['Student', 'Teacher'])) {
            $this->redirect('../view/Log_In.php', 'Access denied', 'error');
        }

        $this->studentTeacherModel = new StudentTeacherModel($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'reserve':
                $this->reserveBook();
                break;
            case 'cancel':
                $this->cancelReservation();
                break;
            default:
                $this->redirect('../view/Teach_Stud_Dashboard.php');
        }
    }

    private function reserveBook() {
        $book_id = filter_var($_GET['book_id'] ?? 0, FILTER_VALIDATE_INT);
        $user_id = $_SESSION['user_id'];
        $user_role = $_SESSION['user_role'];

        if ($user_role === 'Student' && !$this->studentTeacherModel->canStudentBorrow($user_id)) {
            $this->redirect(
                '../view/Teach_Stud_Dashboard.php',
                'You have reached the maximum limit of 3 borrowed books',
                'error'
            );
        }

        $result = $this->studentTeacherModel->reserveBook($user_id, $book_id);
        $this->redirect(
            '../view/Teach_Stud_Dashboard.php',
            $result['message'],
            $result['success'] ? 'success' : 'error'
        );
    }

    private function cancelReservation() {
        $reserve_id = filter_var($_GET['reserve_id'] ?? 0, FILTER_VALIDATE_INT);
        $result = $this->studentTeacherModel->cancelReservation($reserve_id, $_SESSION['user_id']);
        
        $message = $result ? 'Reservation cancelled successfully' : 'Failed to cancel reservation';
        $this->redirect('../view/Teach_Stud_Dashboard.php', $message, $result ? 'success' : 'error');
    }
}

$controller = new ReservationController();
$controller->handleRequest();
?>