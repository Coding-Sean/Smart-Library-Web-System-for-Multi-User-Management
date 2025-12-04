<?php
// filepath: c:\xampp\htdocs\MyLibrary\controller\SignupController.php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../model/User.php';

class SignupController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User($this->db);
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Sign_Up.php');
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? '',
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];

        // Validate input
        $validation = $this->userModel->validate($data);

        if (!$validation['valid']) {
            $this->redirect('../view/Sign_Up.php', implode(', ', $validation['errors']), 'error');
        }

        // Check email exists
        if ($this->userModel->emailExists($validation['data']['email'])) {
            $this->redirect('../view/Sign_Up.php', 'Email already registered', 'error');
        }

        // Create user
        if ($this->userModel->create(
            $validation['data']['name'],
            $validation['data']['email'],
            $validation['data']['password'],
            $validation['data']['role']
        )) {
            $this->redirect('../view/Log_In.php', 'Account created successfully! Please log in.');
        } else {
            $this->redirect('../view/Sign_Up.php', 'Failed to create account', 'error');
        }
    }
}

$controller = new SignupController();
$controller->handleRequest();
?>