# 📚 MyLibrary - Smart Library Web System for Multi-User Management

<img src="https://img.shields.io/badge/version-1.0.0-blue.svg" alt="Version">
<img src="https://img. shields.io/badge/PHP-8.0+-purple.svg" alt="PHP">
<img src="https://img. shields.io/badge/MySQL-8.0+-orange.svg" alt="MySQL">
<img src="https://img.shields. io/badge/Bootstrap-5.3. 3-purple.svg" alt="Bootstrap">
<img src="https://img. shields.io/badge/license-MIT-green.svg" alt="License">

A comprehensive web-based library management system built with PHP, MySQL, and Bootstrap.  Designed for educational institutions to manage books, borrowers, reservations, and penalties efficiently with role-based access control.

---

## 📋 Table of Contents

- [Features](#-features)
- [User Roles & Permissions](#-user-roles--permissions)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Database Setup](#-database-setup)
- [Project Structure](#-project-structure)
- [Architecture & Design Patterns](#-architecture--design-patterns)
- [Usage Guide](#-usage-guide)
- [Security Features](#-security-features)
- [Technologies Used](#-technologies-used)
- [Business Logic](#-business-logic)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### Core Functionality

✅ **User Authentication** - Secure login/signup with password hashing (bcrypt)  
✅ **Role-Based Access Control** - Four distinct user roles with specific permissions  
✅ **Book Management** - Complete CRUD operations for library inventory  
✅ **Borrowing System** - Track book checkouts and returns with due dates  
✅ **Reservation System** - Online book reservation with approval workflow  
✅ **Penalty Management** - Automatic calculation of late fees (₱10/day)  
✅ **Clearance Processing** - Semester-end clearance verification  
✅ **Real-time Search** - Instant book and borrower search functionality  

### Technical Features

🔒 **Security First** - SQL injection prevention, XSS protection, password hashing  
🎨 **Modern UI** - Responsive design with custom styled components  
📱 **Mobile Friendly** - Works seamlessly on all devices  
🏗️ **MVC Architecture** - Clean code organization and separation of concerns  
🔄 **OOP Principles** - Inheritance, polymorphism, and encapsulation  
⚡ **Optimized Performance** - Efficient database queries with PDO prepared statements  

---

## 👥 User Roles & Permissions

### 1. 🎓 Student
- **Borrowing Limit**: 3 books per semester
- **Clearance**: Must return all books or pay book price for unreturned items
- **Permissions**: 
  - Reserve books online
  - View borrowed books and due dates
  - View penalties and payment status
  - Check clearance status

### 2. 👨‍🏫 Teacher
- **Borrowing Limit**: Unlimited books
- **Clearance**: Must return all books at semester end
- **Permissions**:
  - Reserve books online
  - View borrowed books and due dates
  - View penalties
  - Check clearance status

### 3.  📖 Librarian
- **Primary Role**: Book Inventory Management
- **Permissions**:
  - Add new books to inventory
  - Update book information (title, author, ISBN, category, price, copies)
  - Archive books (soft delete)
  - View complete book catalog
  - Search and filter books

### 4. 👔 Staff
- **Primary Role**: Transaction Management
- **Permissions**:
  - Process book borrowing
  - Process book returns
  - Add and manage penalties
  - Approve/reject reservations
  - Process semester clearances
  - View borrower status and transaction history
  - Waive or mark penalties as paid

---

## 💻 System Requirements

### Minimum Requirements

| Component | Requirement |
|-----------|-------------|
| **Web Server** | Apache 2.4+ (XAMPP recommended) |
| **PHP** | Version 8.0 or higher |
| **MySQL** | Version 8.0 or higher |
| **Browser** | Chrome 90+, Firefox 88+, Safari 14+, Edge 90+ |
| **RAM** | 2GB minimum (4GB recommended) |
| **Storage** | 500MB free space |

### Recommended Development Environment

- **XAMPP**: Version 8.0. x or higher
- **PHP Extensions**: PDO, MySQLi, mbstring, openssl
- **Text Editor**: VS Code, Sublime Text, or PHPStorm
- **Screen Resolution**: 1920x1080 or higher

---

## 🚀 Installation

### Step 1: Download and Extract

```bash
# Clone the repository
git clone https://github.com/Coding-Sean/MyLibrary.git

# OR extract downloaded ZIP to
C:\xampp\htdocs\MyLibrary
```

### Step 2: Install XAMPP

1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install with default settings
3. Start **Apache** and **MySQL** modules from XAMPP Control Panel

### Step 3: Configure Database

1.  Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named **MyLibrary**
3. Import the database schema (see [Database Setup](#-database-setup))

### Step 4: Configure Connection

Edit `config/database.php` if needed (default settings work with XAMPP):

```php
private $host = 'localhost';
private $db_name = 'MyLibrary';
private $username = 'root';
private $password = ''; // Default XAMPP password is empty
```

### Step 5: Verify Installation

Open your browser and navigate to:
```
http://localhost/MyLibrary
```

You should see the login page.

---

## 🗄️ Database Setup

### Database Name: `MyLibrary`

### Main Tables

1. **User** - Stores user information and roles
   - Fields: `user_id`, `name`, `email`, `password`, `role`

2. **Book** - Library inventory
   - Fields: `book_id`, `title`, `author`, `isbn`, `category`, `copies`, `price`, `status`

3. **BorrowTransaction** - Borrowing records
   - Fields: `borrow_id`, `user_id`, `book_id`, `borrowDate`, `dueDate`, `returnDate`, `status`

4. **Reservation** - Book reservations
   - Fields: `reserve_id`, `user_id`, `book_id`, `reservationDate`, `status`

5.  **Penalty** - Late fees and penalties
   - Fields: `penalty_id`, `borrow_id`, `amount`, `status`, `issueDate`

6. **Clearance** - Semester clearance records
   - Fields: `clearance_id`, `user_id`, `semester`, `status`, `date`

---

## 📁 Project Structure

```
MyLibrary/
│
├── assets/                      # CSS Stylesheets
│   ├── login.css               # Login page styles
│   ├── signup.css              # Signup page styles
│   ├── librarian.css           # Librarian dashboard styles
│   ├── staff.css               # Staff dashboard styles
│   └── stud_teacher.css        # Student/Teacher dashboard styles
│
├── config/                      # Configuration Files
│   └── database.php            # Database connection (PDO)
│
├── controller/                  # Request Handlers (Controllers)
│   ├── BaseController.php      # Parent controller with common methods
│   ├── LoginController.php     # Authentication handler
│   ├── SignupController.php    # User registration handler
│   ├── LogoutController.php    # Session termination
│   ├── LibrarianController.php # Book CRUD operations
│   ├── StaffController.php     # Borrowing/Return/Penalty handlers
│   ├── ReservationController.php # Reservation management
│   └── UserController.php      # User-related operations
│
├── database/                    # Database Scripts
│   └── db_schema.sql           # Database schema and sample data
│
├── includes/                    # Reusable Components
│   ├── messages.php            # Custom alert/notification system
│   └── confirm_modal.php       # Custom confirmation dialogs
│
├── model/                       # Business Logic (Models)
│   ├── BaseModel.php           # Parent model with validation & sanitization
│   ├── User.php                # User authentication & management
│   ├── LibrarianModel.php      # Book inventory operations
│   ├── StaffModel.php          # Staff operations (borrow/return/penalty)
│   └── StudentTeacherModel.php # Student/Teacher operations
│
├── view/                        # User Interfaces (Views)
│   ├── Log_In.php              # Login page
│   ├── Sign_Up.php             # Registration page
│   ├── Librarian_Dashboard.php # Librarian interface
│   ├── Staff_Dashboard.php     # Staff interface
│   ├── Teach_Stud_Dashboard.php # Student/Teacher interface
│   └── Librarian_Functions/
│       ├── Add_Book.php        # Add book form
│       └── Edit_Book.php       # Edit book form
│
├── index.php                    # Entry point (redirects to login)
└── README.md                    # This file
```

---

## 🏗️ Architecture & Design Patterns

### MVC (Model-View-Controller) Pattern

- **Model**: Handles business logic and database operations
- **View**: Presents data to users (HTML/PHP templates)
- **Controller**: Processes user requests and coordinates Model-View

### Object-Oriented Programming (OOP)

#### Inheritance
```php
BaseModel. php → User.php, LibrarianModel.php, StaffModel.php
BaseController.php → LoginController.php, StaffController.php
```

#### Encapsulation
- Private database connection in models
- Protected methods for validation and sanitization

#### Polymorphism
- Abstract `validate()` method implemented differently in each model
- `executeQuery()` method handling various SQL operations

---

## 📖 Usage Guide

### For Students/Teachers

1. **Register**: Click "Sign Up" and select your role
2. **Login**: Use your registered email and password
3. **Browse Books**: View available books in the catalog
4. **Reserve Book**: Click "Reserve" on desired book
5. **Check Status**: Monitor borrowed books and due dates
6. **View Penalties**: Check any late fees or unpaid penalties

### For Librarians

1. **Login**: Use librarian credentials
2. **Add Books**: Click "Add Book" and fill in details
3. **Edit Books**: Click "Edit" on any book card
4. **Archive Books**: Mark books as archived when needed
5. **Search**: Use search bar to find specific books

### For Staff

1. **Login**: Use staff credentials
2. **Process Borrowing**: 
   - Search for borrower
   - Click "Borrow" → Select book → Confirm
3. **Process Returns**:
   - Click "Return" on borrower
   - Select book to return
   - System auto-calculates penalties if overdue
4. **Approve Reservations**: Review and approve pending reservations
5. **Process Clearance**: Verify student/teacher clearance for semester

---

## 🔒 Security Features

### 1. Password Security
- **Bcrypt Hashing**: `password_hash()` with `PASSWORD_DEFAULT`
- **Minimum Length**: 6 characters required
- **Verification**: `password_verify()` for login

### 2. SQL Injection Prevention
- **PDO Prepared Statements**: All queries use parameterized statements
- **Input Validation**: Type checking with `validateInt()`, `validateFloat()`
- **Input Sanitization**: `htmlspecialchars()` and custom `sanitize()` method

### 3. XSS Protection
```php
htmlspecialchars($data, ENT_QUOTES, 'UTF-8')
```

### 4. Session Management
- Session-based authentication
- Role verification on protected pages
- Secure logout with session destruction

### 5. Email Validation
```php
filter_var($email, FILTER_VALIDATE_EMAIL)
```

---

## 🛠️ Technologies Used

| Technology | Purpose | Version |
|------------|---------|---------|
| **PHP** | Backend Logic | 8.0+ |
| **MySQL** | Database | 8.0+ |
| **PDO** | Database Access Layer | Native |
| **Bootstrap** | UI Framework | 5.3.3 |
| **JavaScript** | Frontend Interactivity | ES6+ |
| **HTML5** | Structure | - |
| **CSS3** | Styling | - |
| **Apache** | Web Server | 2. 4+ |

### Key PHP Features Used
- Object-Oriented Programming (OOP)
- PDO for database operations
- Session management
- Password hashing
- Error handling with try-catch
- Type validation

---

## 💼 Business Logic

### Borrowing Rules

**Students:**
- Maximum 3 books at a time
- Cannot borrow if limit reached
- Cannot borrow with unpaid penalties

**Teachers:**
- Unlimited borrowing
- Cannot borrow with unpaid penalties

**Due Date:** 14 days from borrow date

### Penalty Calculation

```php
// Automatic penalty on late return
$daysLate = (current_date - due_date) / 86400;
$penaltyAmount = $daysLate * 10; // ₱10 per day
```

**Penalty Types:**
- **Late Return**: ₱10 per day overdue
- **Lost Book**: Full book price
- **Status**: Unpaid, Paid, Waived

### Clearance Requirements

**Students:**
- All books returned
- All penalties paid
- Maximum 3 books borrowed per semester enforced

**Teachers:**
- All books returned at semester end
- All penalties paid

### Reservation System

1. User reserves available book
2. Staff receives notification
3. Staff approves/rejects reservation
4. Upon approval, book is issued immediately
5. Reservation status updated to "Approved"

---

## 🐛 Troubleshooting

### Common Issues

#### 1. "Database connection failed"
**Solution:**
- Ensure MySQL is running in XAMPP
- Verify database name is `MyLibrary`
- Check credentials in `config/database.php`

#### 2. "Page not found" errors
**Solution:**
- Verify files are in `C:\xampp\htdocs\MyLibrary`
- Check Apache is running
- Access via `http://localhost/MyLibrary` not `http://localhost`

#### 3. "Session errors"
**Solution:**
- Clear browser cookies/cache
- Ensure `session_start()` is called
- Check PHP session configuration

#### 4. Login not working
**Solution:**
- Verify email is registered
- Check password (case-sensitive)
- Ensure user table has correct password hash

#### 5. Books not showing
**Solution:**
- Check book status is not "Archived"
- Verify database connection
- Check if books exist in database

---


## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License. 

---

## 📞 Contact
**Name:** Jansean Libera

**Course, Year & Section:** BSIS 3-B

**Developer:** Coding-Sean  
**Repository:** [https://github.com/Coding-Sean/MyLibrary](https://github.com/Coding-Sean/MyLibrary)  
**Email:** liberajansean34@gmail.com

---

## 🎯 Project Status

**Version:** 1.0.0  
**Status:** ✅ Complete and Functional  
**Last Updated:** 2025

---

### 🌟 Key Highlights

- ✨ Full MVC implementation with OOP
- 🔐 Enterprise-level security practices
- 📱 Responsive design for all devices
- ⚡ Optimized database queries
- 🎨 Custom UI components
- 📊 Comprehensive penalty system
- 🔄 Real-time search and filtering
- 📖 Complete documentation

---

**Built with ❤️ for Educational Institutions**
