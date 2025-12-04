<?php
// filepath: c:\xampp\htdocs\MyLibrary\model\LibrarianModel.php
require_once __DIR__ . '/BaseModel.php';

class LibrarianModel extends BaseModel {
    protected $table = 'book';

    public function __construct($db) {
        parent::__construct($db);
    }

    // Implement polymorphism - validate method specific to books
    public function validate($data) {
        $errors = [];

        $title = $this->sanitize($data['title'] ?? '');
        $author = $this->sanitize($data['author'] ?? '');
        $category = $this->sanitize($data['category'] ?? '');
        $copies = $this->validateInt($data['copies'] ?? 0);
        $price = $this->validateFloat($data['price'] ?? 0);
        $status = $this->sanitize($data['status'] ?? 'Available');

        if (empty($title)) $errors[] = 'Title is required';
        if (empty($author)) $errors[] = 'Author is required';
        if ($copies === false || $copies < 0) $errors[] = 'Copies must be a positive number';
        if ($price === false || $price < 0) $errors[] = 'Price must be a positive number';

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'data' => compact('title', 'author', 'category', 'copies', 'price', 'status')
        ];
    }

    public function create($title, $author, $category, $copies, $price, $status = 'Available') {
        $query = "INSERT INTO {$this->table} (title, author, category, copies, price, status) 
                  VALUES (:title, :author, :category, :copies, :price, :status)";
        
        $params = [
            ':title' => $this->sanitize($title),
            ':author' => $this->sanitize($author),
            ':category' => $this->sanitize($category),
            ':copies' => $this->validateInt($copies),
            ':price' => $this->validateFloat($price),
            ':status' => $this->sanitize($status)
        ];

        return $this->executeQuery($query, $params) !== false;
    }

    public function getAllBooks() {
        $query = "SELECT * FROM {$this->table} ORDER BY book_id DESC";
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function getBookById($book_id) {
        $query = "SELECT * FROM {$this->table} WHERE book_id = :book_id LIMIT 1";
        $stmt = $this->executeQuery($query, [':book_id' => $this->validateInt($book_id)]);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * Get all currently borrowed books with borrower details
     * 
     * @return array Array of borrowed books with user and book information
     */
    public function getCurrentlyBorrowedBooks() {
        $query = "SELECT 
                    bt.borrow_id,
                    bt.borrowDate,
                    bt.dueDate,
                    bt.status,
                    b.book_id,
                    b.title,
                    b.author,
                    b.category,
                    u.user_id,
                    u.name as borrower_name,
                    u.role as borrower_role,
                    u.email as borrower_email,
                    DATEDIFF(bt.dueDate, CURDATE()) as days_remaining,
                    CASE 
                        WHEN CURDATE() > bt.dueDate THEN 'Overdue'
                        WHEN DATEDIFF(bt.dueDate, CURDATE()) <= 3 THEN 'Due Soon'
                        ELSE 'Active'
                    END as borrow_status
                  FROM BorrowTransaction bt
                  JOIN Book b ON bt.book_id = b.book_id
                  JOIN User u ON bt.user_id = u.user_id
                  WHERE bt.status = 'Borrowed'
                  ORDER BY bt.dueDate ASC";
        
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    /**
     * Get borrowing statistics
     * 
     * @return array Statistics about borrowed books
     */
    public function getBorrowingStats() {
        $query = "SELECT 
                    COUNT(*) as total_borrowed,
                    SUM(CASE WHEN CURDATE() > dueDate THEN 1 ELSE 0 END) as overdue_count,
                    SUM(CASE WHEN DATEDIFF(dueDate, CURDATE()) BETWEEN 0 AND 3 THEN 1 ELSE 0 END) as due_soon_count
                  FROM BorrowTransaction
                  WHERE status = 'Borrowed'";
        
        $stmt = $this->executeQuery($query);
        return $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : [
            'total_borrowed' => 0,
            'overdue_count' => 0,
            'due_soon_count' => 0
        ];
    }

    public function update($book_id, $title, $author, $category, $copies, $price, $status) {
        $query = "UPDATE {$this->table} 
                  SET title = :title, author = :author, category = :category, 
                      copies = :copies, price = :price, status = :status 
                  WHERE book_id = :book_id";
        
        $params = [
            ':book_id' => $this->validateInt($book_id),
            ':title' => $this->sanitize($title),
            ':author' => $this->sanitize($author),
            ':category' => $this->sanitize($category),
            ':copies' => $this->validateInt($copies),
            ':price' => $this->validateFloat($price),
            ':status' => $this->sanitize($status)
        ];

        return $this->executeQuery($query, $params) !== false;
    }

    public function archive($book_id) {
        $query = "UPDATE {$this->table} SET status = 'Archived' WHERE book_id = :book_id";
        return $this->executeQuery($query, [':book_id' => $this->validateInt($book_id)]) !== false;
    }

    public function searchBooks($search) {
        $search = '%' . $this->sanitize($search) . '%';
        $query = "SELECT * FROM {$this->table} 
                  WHERE title LIKE :search 
                  OR author LIKE :search 
                  OR category LIKE :search 
                  ORDER BY book_id DESC";
        
        $stmt = $this->executeQuery($query, [':search' => $search]);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
?>