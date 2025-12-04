CREATE DATABASE MyLibrary;
USE MyLibrary;

CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Teacher', 'Student', 'Librarian', 'Staff') NOT NULL
);

CREATE TABLE Book (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    copies INT DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'Available',
    author VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    price DOUBLE(10,2)
);

-- Reservation Table
CREATE TABLE Reservation (
    reserve_id INT AUTO_INCREMENT PRIMARY KEY,
    reservationDate DATE NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Book(book_id) ON DELETE CASCADE
);

-- Borrow/Transaction Table
CREATE TABLE BorrowTransaction (
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,
    borrowDate DATE NOT NULL,
    dueDate DATE NOT NULL,
    returnDate DATE,
    status VARCHAR(50) DEFAULT 'Borrowed',
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES Book(book_id) ON DELETE CASCADE
);

-- Clearance Table
CREATE TABLE Clearance (
    clearance_id INT AUTO_INCREMENT PRIMARY KEY,
    semester VARCHAR(50) NOT NULL,
    clearanceStatus VARCHAR(50) DEFAULT 'Cleared',  -- ✅ Changed default from 'Pending' to 'Cleared'
    date DATE NOT NULL,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- Penalty Table
CREATE TABLE Penalty (
    penalty_id INT AUTO_INCREMENT PRIMARY KEY,
    amount DOUBLE(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Unpaid',
    issueDate DATE NOT NULL,
    borrow_id INT NOT NULL,
    FOREIGN KEY (borrow_id) REFERENCES BorrowTransaction(borrow_id) ON DELETE CASCADE
);