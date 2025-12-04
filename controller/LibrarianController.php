<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../model/LibrarianModel.php';

class LibrarianController extends BaseController {
    private $librarianModel;

    public function __construct() {
        parent::__construct();
        $this->requireAuth('Librarian');
        $this->librarianModel = new LibrarianModel($this->db);
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? '';

        switch ($action) {
            case 'create':
                $this->createBook();
                break;
            case 'update':
                $this->updateBook();
                break;
            case 'delete':
                $this->deleteBook();
                break;
            default:
                $this->redirect('../view/Librarian_Dashboard.php');
        }
    }

    private function createBook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Librarian_Functions/Add_Book.php');
        }

        $data = [
            'title' => $_POST['title'] ?? '',
            'author' => $_POST['author'] ?? '',
            'category' => $_POST['category'] ?? '',
            'copies' => $_POST['copies'] ?? 0,
            'price' => $_POST['price'] ?? 0,
            'status' => $_POST['status'] ?? 'Available'
        ];

        $validation = $this->librarianModel->validate($data);

        if (!$validation['valid']) {
            $this->redirect(
                '../view/Librarian_Functions/Add_Book.php',
                implode(', ', $validation['errors']),
                'error'
            );
        }

        $result = $this->librarianModel->create(
            $validation['data']['title'],
            $validation['data']['author'],
            $validation['data']['category'],
            $validation['data']['copies'],
            $validation['data']['price'],
            $validation['data']['status']
        );

        $message = $result ? 'Book added successfully!' : 'Failed to add book';
        $type = $result ? 'success' : 'error';
        $url = $result ? '../view/Librarian_Dashboard.php' : '../view/Librarian_Functions/Add_Book.php';
        
        $this->redirect($url, $message, $type);
    }

    private function updateBook() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('../view/Librarian_Dashboard.php');
        }

        $book_id = $_POST['book_id'] ?? 0;
        $data = [
            'title' => $_POST['title'] ?? '',
            'author' => $_POST['author'] ?? '',
            'category' => $_POST['category'] ?? '',
            'copies' => $_POST['copies'] ?? 0,
            'price' => $_POST['price'] ?? 0,
            'status' => $_POST['status'] ?? 'Available'
        ];

        $validation = $this->librarianModel->validate($data);

        if (!$validation['valid']) {
            $this->redirect(
                '../view/Librarian_Functions/Edit_Book.php?id=' . $book_id,
                implode(', ', $validation['errors']),
                'error'
            );
        }

        $result = $this->librarianModel->update(
            $book_id,
            $validation['data']['title'],
            $validation['data']['author'],
            $validation['data']['category'],
            $validation['data']['copies'],
            $validation['data']['price'],
            $validation['data']['status']
        );

        $message = $result ? 'Book updated successfully!' : 'Failed to update book';
        $type = $result ? 'success' : 'error';
        
        $this->redirect('../view/Librarian_Dashboard.php', $message, $type);
    }

    private function deleteBook() {
        $book_id = $_GET['id'] ?? 0;
        $result = $this->librarianModel->archive($book_id);
        
        $message = $result ? 'Book archived successfully!' : 'Failed to archive book';
        $type = $result ? 'success' : 'error';
        
        $this->redirect('../view/Librarian_Dashboard.php', $message, $type);
    }
}

$controller = new LibrarianController();
$controller->handleRequest();
?>