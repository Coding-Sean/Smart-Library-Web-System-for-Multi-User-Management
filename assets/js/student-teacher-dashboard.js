/**
 * Student/Teacher Dashboard JavaScript
 * Handles book search, reservation, and pagination
 */

/**
 * Book Pagination variables
 */
let currentBookPage = 1;
const booksPerPage = 5;
let allDisplayBooks = [];
let filteredDisplayBooks = [];

/**
 * Reservation Pagination variables
 */
let currentReservationPage = 1;
const reservationsPerPage = 2;
let allReservations = [];

/**
 * Borrowed Books Pagination variables
 */
let currentBorrowedBookPage = 1;
const borrowedBooksPerPage = 2;
let allBorrowedBooks = [];

/**
 * Initialize pagination on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize book pagination
    allDisplayBooks = Array.from(document.querySelectorAll('.book-item'));
    filteredDisplayBooks = [...allDisplayBooks];
    updateBookPagination();

    // Initialize reservation pagination
    allReservations = Array.from(document.querySelectorAll('.reservation-item'));
    if (allReservations.length > 0) {
        updateReservationPagination();
    }

    // Initialize borrowed books pagination
    allBorrowedBooks = Array.from(document.querySelectorAll('.borrowed-book-item'));
    if (allBorrowedBooks.length > 0) {
        updateBorrowedBookPagination();
    }
});

/**
 * Borrowed Books Pagination Functions
 */
function changeBorrowedBookPage(direction) {
    const totalPages = Math.ceil(allBorrowedBooks.length / borrowedBooksPerPage);
    currentBorrowedBookPage += direction;
    
    if (currentBorrowedBookPage < 1) currentBorrowedBookPage = 1;
    if (currentBorrowedBookPage > totalPages) currentBorrowedBookPage = totalPages;
    
    updateBorrowedBookPagination();
}

function updateBorrowedBookPagination() {
    const totalPages = Math.ceil(allBorrowedBooks.length / borrowedBooksPerPage);
    const start = (currentBorrowedBookPage - 1) * borrowedBooksPerPage;
    const end = start + borrowedBooksPerPage;

    // Hide all borrowed books first
    allBorrowedBooks.forEach(book => book.style.display = 'none');

    // Show only current page borrowed books
    allBorrowedBooks.slice(start, end).forEach(book => book.style.display = 'block');

    // Update page info
    const currentPageElement = document.getElementById('currentBorrowedBookPage');
    const totalPagesElement = document.getElementById('totalBorrowedBookPages');
    const countElement = document.getElementById('borrowedBookCount');
    
    if (currentPageElement) currentPageElement.textContent = currentBorrowedBookPage;
    if (totalPagesElement) totalPagesElement.textContent = totalPages || 1;
    if (countElement) countElement.textContent = Math.min(end, allBorrowedBooks.length);

    // Update button states
    const prevBtn = document.getElementById('prevBorrowedBookBtn');
    const nextBtn = document.getElementById('nextBorrowedBookBtn');
    
    if (prevBtn) prevBtn.disabled = currentBorrowedBookPage === 1;
    if (nextBtn) nextBtn.disabled = currentBorrowedBookPage === totalPages || totalPages === 0;
}

/**
 * Change reservation page (previous or next)
 * @param {number} direction - 1 for next, -1 for previous
 */
function changeReservationPage(direction) {
    const totalPages = Math.ceil(allReservations.length / reservationsPerPage);
    currentReservationPage += direction;
    
    if (currentReservationPage < 1) currentReservationPage = 1;
    if (currentReservationPage > totalPages) currentReservationPage = totalPages;
    
    updateReservationPagination();
}

/**
 * Update reservation pagination display
 */
function updateReservationPagination() {
    const totalPages = Math.ceil(allReservations.length / reservationsPerPage);
    const start = (currentReservationPage - 1) * reservationsPerPage;
    const end = start + reservationsPerPage;

    // Hide all reservations first
    allReservations.forEach(reservation => reservation.style.display = 'none');

    // Show only current page reservations
    allReservations.slice(start, end).forEach(reservation => reservation.style.display = 'block');

    // Update page info
    const currentPageElement = document.getElementById('currentReservationPage');
    const totalPagesElement = document.getElementById('totalReservationPages');
    const countElement = document.getElementById('reservationCount');
    
    if (currentPageElement) currentPageElement.textContent = currentReservationPage;
    if (totalPagesElement) totalPagesElement.textContent = totalPages || 1;
    if (countElement) countElement.textContent = Math.min(end, allReservations.length);

    // Update button states
    const prevBtn = document.getElementById('prevReservationBtn');
    const nextBtn = document.getElementById('nextReservationBtn');
    
    if (prevBtn) prevBtn.disabled = currentReservationPage === 1;
    if (nextBtn) nextBtn.disabled = currentReservationPage === totalPages || totalPages === 0;
}

/**
 * Change book page (previous or next)
 * @param {number} direction - 1 for next, -1 for previous
 */
function changeBookPage(direction) {
    const totalPages = Math.ceil(filteredDisplayBooks.length / booksPerPage);
    currentBookPage += direction;
    
    if (currentBookPage < 1) currentBookPage = 1;
    if (currentBookPage > totalPages) currentBookPage = totalPages;
    
    updateBookPagination();
}

/**
 * Update book pagination display
 */
function updateBookPagination() {
    const totalPages = Math.ceil(filteredDisplayBooks.length / booksPerPage);
    const start = (currentBookPage - 1) * booksPerPage;
    const end = start + booksPerPage;

    // Hide all books first
    allDisplayBooks.forEach(book => book.style.display = 'none');

    // Show only current page books
    filteredDisplayBooks.slice(start, end).forEach(book => book.style.display = 'flex');

    // Update page info
    document.getElementById('currentBookPage').textContent = currentBookPage;
    document.getElementById('totalBookPages').textContent = totalPages || 1;
    document.getElementById('bookDisplayCount').textContent = Math.min(end, filteredDisplayBooks.length);

    // Update button states
    const prevBtn = document.getElementById('prevBookBtn');
    const nextBtn = document.getElementById('nextBookBtn');
    if (prevBtn) prevBtn.disabled = currentBookPage === 1;
    if (nextBtn) nextBtn.disabled = currentBookPage === totalPages || totalPages === 0;
}

/**
 * Search books by title or author
 * Filters the book list in real-time without page reload
 */
function searchBooks() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();

    if (searchTerm === '') {
        filteredDisplayBooks = [...allDisplayBooks];
    } else {
        filteredDisplayBooks = allDisplayBooks.filter(item => {
            const title = item.getAttribute('data-title').toLowerCase();
            const author = item.getAttribute('data-author').toLowerCase();
            return title.includes(searchTerm) || author.includes(searchTerm);
        });
    }

    currentBookPage = 1;
    updateBookPagination();
}

/**
 * Allow search on Enter key press
 */
document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchBooks();
    }
});

/**
 * Clear search and reset pagination
 */
document.getElementById('searchInput')?.addEventListener('input', function(e) {
    if (e.target.value === '') {
        searchBooks();
    }
});

/**
 * Reserve a book with custom confirmation modal
 * 
 * @param {number} bookId - The ID of the book to reserve
 * @param {string} bookTitle - The title of the book (for display)
 */
function reserveBook(bookId, bookTitle) {
    customConfirm(
        'Are you sure you want to reserve "' + bookTitle + '"? Staff will review your reservation.',
        function() {
            location.href = '../controller/ReservationController.php?action=reserve&book_id=' + bookId;
        }
    );
}

/**
 * Cancel a reservation with custom confirmation modal
 * 
 * @param {number} reserveId - The ID of the reservation to cancel
 */
function cancelReservation(reserveId) {
    customConfirm(
        'Are you sure you want to cancel this reservation?',
        function() {
            location.href = '../controller/ReservationController.php?action=cancel&reserve_id=' + reserveId;
        }
    );
}