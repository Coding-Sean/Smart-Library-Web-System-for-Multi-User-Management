/**
 * Librarian Dashboard JavaScript
 * Handles book management pagination, search, and archive functionality
 */

/**
 * Pagination variables
 */
let currentPage = 1;
const itemsPerPage = 5;
let allBooks = [];
let filteredBooks = [];

/**
 * Initialize pagination on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    allBooks = Array.from(document.querySelectorAll('.book-item'));
    filteredBooks = [...allBooks];
    updatePagination();
});

/**
 * Change page (previous or next)
 * @param {number} direction - 1 for next, -1 for previous
 */
function changePage(direction) {
    const totalPages = Math.ceil(filteredBooks.length / itemsPerPage);
    currentPage += direction;
    
    // Boundary checks
    if (currentPage < 1) currentPage = 1;
    if (currentPage > totalPages) currentPage = totalPages;
    
    updatePagination();
}

/**
 * Update pagination display
 */
function updatePagination() {
    const totalPages = Math.ceil(filteredBooks.length / itemsPerPage);
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;

    // Hide all books first
    allBooks.forEach(book => book.style.display = 'none');

    // Show only current page books
    filteredBooks.slice(start, end).forEach(book => book.style.display = 'flex');

    // Update page info
    document.getElementById('currentPage').textContent = currentPage;
    document.getElementById('totalPages').textContent = totalPages || 1;
    document.getElementById('bookCount').textContent = Math.min(end, filteredBooks.length);

    // Update button states
    document.getElementById('prevBtn').disabled = currentPage === 1;
    document.getElementById('nextBtn').disabled = currentPage === totalPages || totalPages === 0;
}

/**
 * Archive a book with confirmation
 * @param {number} bookId - The book ID to archive
 */
function archiveBook(bookId) {
    customConfirm('Are you sure you want to archive this book?', function() {
        window.location.href = '../controller/LibrarianController.php?action=delete&id=' + bookId;
    });
}

/**
 * Search books by title or author
 */
function searchBooks() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    
    if (searchTerm === '') {
        // Reset to all books
        filteredBooks = [...allBooks];
    } else {
        // Filter books based on search term
        filteredBooks = allBooks.filter(item => {
            const title = item.querySelector('.book-title').textContent.toLowerCase();
            return title.includes(searchTerm);
        });
    }

    // Reset to page 1 after search
    currentPage = 1;
    updatePagination();
}

/**
 * Allow search on Enter key
 */
document.getElementById('searchInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchBooks();
    }
});

/**
 * Clear search and reset pagination
 */
document.getElementById('searchInput').addEventListener('input', function(e) {
    if (e.target.value === '') {
        searchBooks();
    }
});