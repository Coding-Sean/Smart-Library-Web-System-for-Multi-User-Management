<?php
// filepath: c:\xampp\htdocs\MyLibrary\model\BaseModel.php
abstract class BaseModel {
    protected $conn;
    protected $table;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Prevent SQL Injection with prepared statements
    protected function executeQuery($query, $params = []) {
        try {
            // Log the query for debugging
            error_log("Executing query: {$query}");
            error_log("With params: " . json_encode($params));

            // Prepare the SQL statement
            $stmt = $this->conn->prepare($query);

            // Bind each parameter to its value
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            // Execute the prepared statement
            $success = $stmt->execute();

            // Log execution result
            if ($success) {
                error_log("Query executed successfully. Rows affected: " . $stmt->rowCount());
            } else {
                error_log("Query execution returned false");
                error_log("Error info: " . json_encode($stmt->errorInfo()));
            }

            // Return the executed statement for fetching results
            return $stmt;
        } catch (PDOException $e) {
            // Log detailed error and return false on failure
            error_log("Database Error: " . $e->getMessage());
            error_log("Query was: {$query}");
            error_log("Params were: " . json_encode($params));
            return false;
        }
    }

    // Sanitize input to prevent XSS
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    // Validate email
    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Validate integer
    protected function validateInt($value) {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    // Validate float
    protected function validateFloat($value) {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    // Abstract method - must be implemented by child classes
    abstract public function validate($data);
}
?>