<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/User.php';

// ✅ Make sure the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../view/Log_In.php');
    exit;
}

// ✅ Get email and password from form
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    header('Location: ../view/Log_In.php?error=' . urlencode('Please enter email and password'));
    exit;
}

// ✅ Connect to database
$db = (new Database())->getConnection();
$userModel = new User($db);

// ✅ Try to log in
$user = $userModel->login($email, $password);

if ($user) {
    // Create session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    // Redirect to shared Teacher/Student dashboard
    header('Location: ../view/Teach_Stud_Dashboard.php');
    exit;
} else {
    header('Location: ../view/Log_In.php?error=' . urlencode('Invalid email or password'));
    exit;
}
?>