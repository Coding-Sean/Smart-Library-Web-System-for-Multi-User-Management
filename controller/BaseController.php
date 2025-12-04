<?php
// filepath: c:\xampp\htdocs\MyLibrary\controller\BaseController.php
abstract class BaseController {
    protected $db;

    public function __construct() {
        session_start();
        require_once __DIR__ . '/../config/database.php';
        $this->db = (new Database())->getConnection();
    }

    // Sanitize output for XSS prevention
    protected function sanitizeOutput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeOutput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    // Validate CSRF token (optional enhancement)
    protected function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    // Generate CSRF token
    protected function generateCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Redirect with message
    protected function redirect($url, $message = null, $type = 'success') {
        $param = $type === 'success' ? 'success' : 'error';
        $redirect = $message ? $url . '?' . $param . '=' . urlencode($message) : $url;
        header('Location: ' . $redirect);
        exit;
    }

    // Check authentication
    protected function requireAuth($role = null) {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('../view/Log_In.php', 'Please log in to continue', 'error');
        }

        if ($role && $_SESSION['user_role'] !== $role) {
            $this->redirect('../view/Log_In.php', 'Access denied', 'error');
        }
    }

    // Abstract method for handling requests
    abstract public function handleRequest();
}
?>