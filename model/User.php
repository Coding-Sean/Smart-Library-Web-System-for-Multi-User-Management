<?php
// filepath: c:\xampp\htdocs\MyLibrary\model\User.php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel {
    protected $table = 'user';

    public function __construct($db) {
        parent::__construct($db);
    }

    // Implement abstract validate method
    public function validate($data) {
        $errors = [];
        $allowedRoles = ['Teacher', 'Student', 'Librarian', 'Staff'];

        // Sanitize inputs
        $name = $this->sanitize($data['name'] ?? '');
        $email = $this->sanitize($data['email'] ?? '');
        $role = $this->sanitize($data['role'] ?? '');
        $password = $data['password'] ?? '';
        $confirm = $data['confirm_password'] ?? '';

        if (empty($name)) $errors[] = 'Name is required';
        if (!$this->validateEmail($email)) $errors[] = 'Invalid email format';
        if (!in_array($role, $allowedRoles, true)) $errors[] = 'Invalid role';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
        if ($password !== $confirm) $errors[] = 'Passwords do not match';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => compact('name', 'email', 'role', 'password')
        ];
    }

    public function create($name, $email, $password, $role) {
        $query = "INSERT INTO {$this->table} (name, email, password, role) 
                  VALUES (:name, :email, :password, :role)";
        
        $params = [
            ':name' => $this->sanitize($name),
            ':email' => $this->sanitize($email),
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $this->sanitize($role)
        ];

        return $this->executeQuery($query, $params) !== false;
    }

    public function emailExists($email) {
        $query = "SELECT user_id FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->executeQuery($query, [':email' => $this->sanitize($email)]);
        return $stmt && $stmt->rowCount() > 0;
    }

    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->executeQuery($query, [':email' => $this->sanitize($email)]);
        
        if ($stmt) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                // Remove password from returned data
                unset($user['password']);
                return $user;
            }
        }
        return false;
    }
}
?>
