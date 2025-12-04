/**
 * Staff Dashboard JavaScript
 * Handles borrower management, clearance processing, and reservation approval
 */

/**
 * Borrower Pagination variables
 */
let currentBorrowerPage = 1;
const borrowersPerPage = 2;
let allBorrowers = [];
let filteredBorrowers = [];

/**
 * Clearance Records Pagination variables
 */
let currentClearancePage = 1;
const clearancePerPage = 2;
let allClearanceRecords = [];

/**
 * Borrowed Books Pagination variables
 */
let currentBorrowedPage = 1;
const borrowedPerPage = 2;
let allBorrowedBooks = [];

/**
 * Initialize all paginations on page load
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize borrower pagination
    allBorrowers = Array.from(document.querySelectorAll('.borrower-item'));
    filteredBorrowers = [...allBorrowers];
    updateBorrowerPagination();

    // Initialize clearance records pagination
    allClearanceRecords = Array.from(document.querySelectorAll('.clearance-row'));
    if (allClearanceRecords.length > 0) {
        updateClearancePagination();
    }

    // Initialize borrowed books pagination
    allBorrowedBooks = Array.from(document.querySelectorAll('.borrowed-book-row'));
    if (allBorrowedBooks.length > 0) {
        updateBorrowedBooksPagination();
    }
});

/**
 * Clearance Records Pagination Functions
 */
function changeClearancePage(direction) {
    const totalPages = Math.ceil(allClearanceRecords.length / clearancePerPage);
    currentClearancePage += direction;
    
    if (currentClearancePage < 1) currentClearancePage = 1;
    if (currentClearancePage > totalPages) currentClearancePage = totalPages;
    
    updateClearancePagination();
}

function updateClearancePagination() {
    const totalPages = Math.ceil(allClearanceRecords.length / clearancePerPage);
    const start = (currentClearancePage - 1) * clearancePerPage;
    const end = start + clearancePerPage;

    // Hide all records first
    allClearanceRecords.forEach(record => record.style.display = 'none');

    // Show only current page records
    allClearanceRecords.slice(start, end).forEach(record => record.style.display = '');

    // Update page info
    const currentPageElement = document.getElementById('currentClearancePage');
    const totalPagesElement = document.getElementById('totalClearancePages');
    const countElement = document.getElementById('clearanceCount');
    
    if (currentPageElement) currentPageElement.textContent = currentClearancePage;
    if (totalPagesElement) totalPagesElement.textContent = totalPages || 1;
    if (countElement) countElement.textContent = Math.min(end, allClearanceRecords.length);

    // Update button states
    const prevBtn = document.getElementById('prevClearanceBtn');
    const nextBtn = document.getElementById('nextClearanceBtn');
    
    if (prevBtn) prevBtn.disabled = currentClearancePage === 1;
    if (nextBtn) nextBtn.disabled = currentClearancePage === totalPages || totalPages === 0;
}

/**
 * Borrowed Books Pagination Functions
 */
function changeBorrowedBooksPage(direction) {
    const totalPages = Math.ceil(allBorrowedBooks.length / borrowedPerPage);
    currentBorrowedPage += direction;
    
    if (currentBorrowedPage < 1) currentBorrowedPage = 1;
    if (currentBorrowedPage > totalPages) currentBorrowedPage = totalPages;
    
    updateBorrowedBooksPagination();
}

function updateBorrowedBooksPagination() {
    const totalPages = Math.ceil(allBorrowedBooks.length / borrowedPerPage);
    const start = (currentBorrowedPage - 1) * borrowedPerPage;
    const end = start + borrowedPerPage;

    // Hide all books first
    allBorrowedBooks.forEach(book => book.style.display = 'none');

    // Show only current page books
    allBorrowedBooks.slice(start, end).forEach(book => book.style.display = '');

    // Update page info
    const currentPageElement = document.getElementById('currentBorrowedPage');
    const totalPagesElement = document.getElementById('totalBorrowedPages');
    const countElement = document.getElementById('borrowedCount');
    
    if (currentPageElement) currentPageElement.textContent = currentBorrowedPage;
    if (totalPagesElement) totalPagesElement.textContent = totalPages || 1;
    if (countElement) countElement.textContent = Math.min(end, allBorrowedBooks.length);

    // Update button states
    const prevBtn = document.getElementById('prevBorrowedBtn');
    const nextBtn = document.getElementById('nextBorrowedBtn');
    
    if (prevBtn) prevBtn.disabled = currentBorrowedPage === 1;
    if (nextBtn) nextBtn.disabled = currentBorrowedPage === totalPages || totalPages === 0;
}

/**
 * Borrower Pagination Functions
 */
function changeBorrowerPage(direction) {
    const totalPages = Math.ceil(filteredBorrowers.length / borrowersPerPage);
    currentBorrowerPage += direction;
    
    if (currentBorrowerPage < 1) currentBorrowerPage = 1;
    if (currentBorrowerPage > totalPages) currentBorrowerPage = totalPages;
    
    updateBorrowerPagination();
}

function updateBorrowerPagination() {
    const totalPages = Math.ceil(filteredBorrowers.length / borrowersPerPage);
    const start = (currentBorrowerPage - 1) * borrowersPerPage;
    const end = start + borrowersPerPage;

    // Hide all borrowers first
    allBorrowers.forEach(borrower => borrower.style.display = 'none');

    // Show only current page borrowers
    filteredBorrowers.slice(start, end).forEach(borrower => borrower.style.display = 'flex');

    // Update page info
    const currentPageElement = document.getElementById('currentBorrowerPage');
    const totalPagesElement = document.getElementById('totalBorrowerPages');
    const countElement = document.getElementById('borrowerCount');
    
    if (currentPageElement) currentPageElement.textContent = currentBorrowerPage;
    if (totalPagesElement) totalPagesElement.textContent = totalPages || 1;
    if (countElement) countElement.textContent = Math.min(end, filteredBorrowers.length);

    // Update button states
    const prevBtn = document.getElementById('prevBorrowerBtn');
    const nextBtn = document.getElementById('nextBorrowerBtn');
    
    if (prevBtn) prevBtn.disabled = currentBorrowerPage === 1;
    if (nextBtn) nextBtn.disabled = currentBorrowerPage === totalPages || totalPages === 0;
}

/**
 * Search borrowers by name or email
 */
function searchBorrowers() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();

    if (searchTerm === '') {
        filteredBorrowers = [...allBorrowers];
    } else {
        filteredBorrowers = allBorrowers.filter(item => {
            const name = item.getAttribute('data-name').toLowerCase();
            const email = item.getAttribute('data-email').toLowerCase();
            return name.includes(searchTerm) || email.includes(searchTerm);
        });
    }

    currentBorrowerPage = 1;
    updateBorrowerPagination();
}

document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchBorrowers();
    }
});

document.getElementById('searchInput')?.addEventListener('input', function(e) {
    if (e.target.value === '') {
        searchBorrowers();
    }
});

/**
 * Handle borrow action for a user
 * Opens modal with user details and available books
 */
function handleBorrow(userId, userName, userRole) {
    fetch(`../controller/StaffController.php?action=get_user_details&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            let userInfoHtml = `<div class="alert alert-info">
                <strong>${userName}</strong> (${userRole})<br>
                Books Borrowed: ${data.borrowed_books.length}`;
            
            if (!data.can_borrow) {
                userInfoHtml += `<br><span class="text-danger"><strong>Cannot Borrow:</strong> ${data.borrow_reason}</span>`;
                document.getElementById('borrowForm').querySelector('button[type="submit"]').disabled = true;
            } else {
                userInfoHtml += `<br><span class="text-success">Eligible to borrow</span>`;
                document.getElementById('borrowForm').querySelector('button[type="submit"]').disabled = false;
            }
            
            userInfoHtml += `</div>`;
            
            document.getElementById('userInfo').innerHTML = userInfoHtml;
            document.getElementById('borrow_user_id').value = userId;
            
            const modal = new bootstrap.Modal(document.getElementById('borrowModal'));
            modal.show();
        })
        .catch(error => {
            alert('Error loading user details');
            console.error(error);
        });
}

/**
 * Handle return action for a user
 * Opens modal showing borrowed books
 */
function handleReturn(userId, userName) {
    fetch(`../controller/StaffController.php?action=get_user_details&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            let returnUserInfo = `<div class="alert alert-info">
                <strong>${userName}</strong><br>
                Currently Borrowed: ${data.borrowed_books.length} book(s)
            </div>`;
            
            let booksList = '';
            if (data.borrowed_books.length === 0) {
                booksList = '<p class="text-muted">No borrowed books to return.</p>';
            } else {
                booksList = '<div class="list-group">';
                data.borrowed_books.forEach(book => {
                    const isOverdue = new Date(book.dueDate) < new Date();
                    booksList += `
                        <div class="list-group-item ${isOverdue ? 'list-group-item-danger' : ''}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>${book.title}</strong><br>
                                    <small>Due: ${new Date(book.dueDate).toLocaleDateString()}</small>
                                    ${isOverdue ? '<br><span class="badge bg-danger">OVERDUE</span>' : ''}
                                </div>
                                <button class="btn btn-sm btn-success" onclick="processReturn(${book.borrow_id})">Return</button>
                            </div>
                        </div>
                    `;
                });
                booksList += '</div>';
            }
            
            document.getElementById('returnUserInfo').innerHTML = returnUserInfo;
            document.getElementById('returnBooksList').innerHTML = booksList;
            
            const modal = new bootstrap.Modal(document.getElementById('returnModal'));
            modal.show();
        })
        .catch(error => {
            alert('Error loading borrowed books');
            console.error(error);
        });
}

/**
 * Handle penalty action for a user
 * Opens modal showing user's penalties
 */
function handlePenalty(userId, userName) {
    fetch(`../controller/StaffController.php?action=get_user_details&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            let penaltyUserInfo = `<div class="alert alert-info">
                <strong>${userName}</strong><br>
                Total Penalties: ${data.penalties.length}
            </div>`;
            
            let penaltyList = '';
            if (data.penalties.length === 0) {
                penaltyList = '<p class="text-muted">No penalties.</p>';
            } else {
                const totalUnpaid = data.penalties
                    .filter(p => p.status === 'Unpaid')
                    .reduce((sum, p) => sum + parseFloat(p.amount), 0);
                
                if (totalUnpaid > 0) {
                    penaltyList += `<div class="alert alert-danger">Total Unpaid: ₱${totalUnpaid.toFixed(2)}</div>`;
                }
                
                penaltyList += '<div class="list-group">';
                data.penalties.forEach(penalty => {
                    let statusBadge = '';
                    let actionButtons = '';
                    
                    if (penalty.status === 'Unpaid') {
                        statusBadge = '<span class="badge bg-danger">Unpaid</span>';
                        actionButtons = `
                            <div class="mt-2">
                                <button class="btn btn-sm btn-success" onclick="markAsPaid(${penalty.penalty_id})">Mark as Paid</button>
                                <button class="btn btn-sm btn-warning" onclick="waivePenalty(${penalty.penalty_id})">Waive Penalty</button>
                            </div>
                        `;
                    } else if (penalty.status === 'Paid') {
                        statusBadge = '<span class="badge bg-success">Paid</span>';
                    } else if (penalty.status === 'Waived') {
                        statusBadge = '<span class="badge bg-info">Waived</span>';
                    }
                    
                    penaltyList += `
                        <div class="list-group-item">
                            <div>
                                <strong>${penalty.title}</strong><br>
                                <small>Amount: ₱${parseFloat(penalty.amount).toFixed(2)}</small><br>
                                <small>Date: ${new Date(penalty.issueDate).toLocaleDateString()}</small><br>
                                ${statusBadge}
                                ${actionButtons}
                            </div>
                        </div>
                    `;
                });
                penaltyList += '</div>';
            }
            
            document.getElementById('penaltyUserInfo').innerHTML = penaltyUserInfo;
            document.getElementById('penaltyList').innerHTML = penaltyList;
            
            const modal = new bootstrap.Modal(document.getElementById('penaltyModal'));
            modal.show();
        })
        .catch(error => {
            alert('Error loading penalties');
            console.error(error);
        });
}

/**
 * Process book return
 */
function processReturn(borrowId) {
    customConfirm('Confirm book return?', function() {
        window.location.href = `../controller/StaffController.php?action=return&borrow_id=${borrowId}`;
    });
}

/**
 * Add penalty for overdue book
 */
function addPenalty(borrowId) {
    customConfirm('Add penalty for this unreturned book?', function() {
        window.location.href = `../controller/StaffController.php?action=add_penalty&borrow_id=${borrowId}`;
    });
}

/**
 * Approve reservation and issue book
 */
function approveReservation(reserveId, userId, bookId) {
    customConfirm('Approve this reservation and issue the book?', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../controller/StaffController.php?action=borrow';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        const bookIdInput = document.createElement('input');
        bookIdInput.type = 'hidden';
        bookIdInput.name = 'book_id';
        bookIdInput.value = bookId;
        
        const reserveIdInput = document.createElement('input');
        reserveIdInput.type = 'hidden';
        reserveIdInput.name = 'reserve_id';
        reserveIdInput.value = reserveId;
        
        form.appendChild(userIdInput);
        form.appendChild(bookIdInput);
        form.appendChild(reserveIdInput);
        document.body.appendChild(form);
        form.submit();
    });
}

/**
 * Reject reservation
 */
function rejectReservation(reserveId) {
    customConfirm('Reject this reservation?', function() {
        window.location.href = `../controller/StaffController.php?action=reject_reservation&reserve_id=${reserveId}`;
    });
}

/**
 * Waive penalty
 */
function waivePenalty(penaltyId) {
    customConfirm('Are you sure you want to waive this penalty? This action cannot be undone.', function() {
        window.location.href = `../controller/StaffController.php?action=waive_penalty&penalty_id=${penaltyId}`;
    });
}

/**
 * Mark penalty as paid
 */
function markAsPaid(penaltyId) {
    customConfirm('Mark this penalty as paid?', function() {
        window.location.href = `../controller/StaffController.php?action=mark_paid&penalty_id=${penaltyId}`;
    });
}

/**
 * Check if a user is eligible for clearance
 * Validates all requirements before allowing clearance processing
 */
function checkClearanceEligibility() {
    const userId = document.getElementById('clearance_user').value;
    const semester = document.getElementById('clearance_semester').value;
    const resultDiv = document.getElementById('clearanceResult');

    // Validate form inputs
    if (!userId || !semester) {
        resultDiv.innerHTML = '<div class="alert alert-warning">Please select both user and semester</div>';
        return;
    }

    // Get user details
    const userSelect = document.getElementById('clearance_user');
    const selectedOption = userSelect.options[userSelect.selectedIndex];
    const userName = selectedOption.getAttribute('data-name');
    const userRole = selectedOption.getAttribute('data-role');

    console.log('Checking clearance for:', userId, semester);

    // Fetch user eligibility from server
    fetch(`../controller/StaffController.php?action=check_clearance&user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Eligibility data:', data);
            
            let html = '<div class="card mt-3">';
            html += '<div class="card-body">';
            html += `<h5 class="card-title">Clearance Check: ${userName} (${userRole})</h5>`;
            html += `<p><strong>Semester:</strong> ${semester}</p>`;
            
            // Display eligibility status
            html += '<ul class="list-group list-group-flush mb-3">';
            html += `<li class="list-group-item ${data.borrowed_books === 0 ? 'list-group-item-success' : 'list-group-item-danger'}">
                        <strong>Borrowed Books:</strong> ${data.borrowed_books} 
                        ${data.borrowed_books === 0 ? '✓' : '✗ Must return all books'}
                     </li>`;
            html += `<li class="list-group-item ${data.unpaid_penalties == 0 ? 'list-group-item-success' : 'list-group-item-danger'}">
                        <strong>Unpaid Penalties:</strong> ₱${parseFloat(data.unpaid_penalties || 0).toFixed(2)}
                        ${data.unpaid_penalties == 0 ? '✓' : '✗ Must pay all penalties'}
                     </li>`;
            html += `<li class="list-group-item ${data.pending_reservations === 0 ? 'list-group-item-success' : 'list-group-item-warning'}">
                        <strong>Pending Reservations:</strong> ${data.pending_reservations}
                        ${data.pending_reservations === 0 ? '✓' : '⚠ Has pending reservations'}
                     </li>`;
            html += '</ul>';

            // Show action button if eligible
            if (data.can_clear) {
                html += '<div class="alert alert-success"><strong>✓ Eligible for Clearance</strong></div>';
                html += `<button type="button" class="btn btn-success" onclick="processClearance(${userId}, '${semester}', '${userName.replace(/'/g, "\\'")}')">
                            Approve Clearance
                         </button>`;
            } else {
                html += '<div class="alert alert-danger"><strong>✗ Not Eligible for Clearance</strong></div>';
                html += `<p class="text-danger">${data.reason}</p>`;
            }

            html += '</div></div>';
            resultDiv.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = '<div class="alert alert-danger">Error checking eligibility. Please try again.</div>';
        });
}

/**
 * Process clearance for eligible user
 * Creates a clearance record in the database
 */
function processClearance(userId, semester, userName) {
    console.log('processClearance called with:', userId, semester, userName);
    
    customConfirm(
        `Approve clearance for ${userName} for ${semester}? This action will create a permanent clearance record.`,
        function() {
            console.log('User confirmed, submitting form...');
            
            // Create form data
            const formData = new FormData();
            formData.append('user_id', userId);
            formData.append('semester', semester);

            console.log('Sending POST request to clearance endpoint...');

            // Use fetch to submit the form
            fetch('../controller/StaffController.php?action=clearance', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                // Redirect to dashboard to see result
                window.location.href = '../view/Staff_Dashboard.php';
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('An error occurred while processing clearance. Please check the console.');
            });
        },
        function() {
            console.log('User cancelled clearance');
        }
    );
}