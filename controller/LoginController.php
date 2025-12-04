<?php
// filepath: c:\xampp\htdocs\MyLibrary\controller\LoginController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../model/User.php';

class LoginController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Log_In.php');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->redirect('../view/Log_In.php', 'Please enter email and password', 'error');
        }

        $user = $this->userModel->login($email, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $this->generateCsrfToken();

            // Polymorphic redirect based on role
            $dashboards = [
                'Librarian' => '../view/Librarian_Dashboard.php',
                'Staff' => '../view/Staff_Dashboard.php',
                'Teacher' => '../view/Teach_Stud_Dashboard.php',
                'Student' => '../view/Teach_Stud_Dashboard.php'
            ];

            $dashboard = $dashboards[$user['role']] ?? '../view/Log_In.php';
            $this->redirect($dashboard);
        } else {
            $this->redirect('../view/Log_In.php', 'Invalid email or password', 'error');
        }
    }
}

$controller = new LoginController();
$controller->handleRequest();
?>