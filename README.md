# Library Management System

A PHP and MySQL based Library Management System for managing books, members, reservations, book issues, returns, overdue tracking, and fines. The project is designed for a local XAMPP environment and includes separate admin and member/student areas.

## Features

- Admin login and member login
- Student/teacher registration
- Admin dashboard with members, books, issued books, requests, overdue status, and fine summary
- Book management with categories, authors, locations, ISBN, quantity, and available copies
- Member management for students and teachers
- Reservation/request management
- Issue and return management
- Per-issue fine-per-day amount set by admin at issue time
- Fine calculation only after the due date is exceeded
- Student dashboard with current books, due dates, overdue status, fine/day, and accrued fine
- Student loan history with paid fine information
- System settings for default fine per day, maximum days allowed, and maximum books allowed

## Tech Stack

- PHP
- MySQL / MariaDB
- PDO for database access
- Bootstrap based UI
- Font Awesome icons
- XAMPP local server

## Project Structure

```text
lbs/
├── actions/              # Form handlers and business actions
├── admin/                # Admin dashboard and management pages
├── assets/css/           # Application styles
├── config/db.php         # Database connection
├── includes/             # Shared header, footer, sidebar, helpers
├── student/              # Student/member dashboard and pages
├── database.sql          # Database schema and seed data
├── index.php             # Login page
└── student_register.php  # Public registration page
```

## Requirements

- XAMPP or equivalent local PHP/MySQL stack
- PHP 8.x recommended
- MySQL or MariaDB
- Web browser

## Installation

1. Place the project folder inside your XAMPP `htdocs` directory.

   Example:

   ```text
   C:\Xampp\New folder\htdocs\lbs
   ```

2. Start Apache and MySQL from the XAMPP control panel.

3. Create a database named:

   ```sql
   library_db
   ```

4. Import `database.sql` into `library_db`.

   You can use phpMyAdmin:

   - Open `http://localhost/phpmyadmin`
   - Create/select `library_db`
   - Go to Import
   - Choose `database.sql`
   - Click Go

5. Confirm database settings in `config/db.php`:

   ```php
   $host = 'localhost';
   $dbname = 'library_db';
   $username = 'root';
   $password = '';
   ```

6. Open the app:

   ```text
   http://localhost/lbs/
   ```

## Default Login Accounts

The seed data creates these users:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@library.com` | `password` |
| Student | `student@library.com` | `password` |
| Teacher | `teacher@library.com` | `password` |

## Main Workflows

### Admin

1. Log in as admin.
2. Add or manage books from the Books section.
3. Manage students, teachers, and users.
4. Approve or manage reservations.
5. Issue a book to a student/teacher.
6. Set the due date and the fine per day for that specific issued book.
7. Mark books as returned when received.
8. Review fines, overdue records, and recent issued books from dashboard and issue pages.

### Student / Member

1. Log in as a student or teacher.
2. Search books from the catalog.
3. Request/reserve available books.
4. View currently issued books.
5. Check due date, status, fine per day, and accrued fine.
6. View returned book history and paid fine.

## Fine Calculation Rules

Fine is not charged while the book is within the due date.

When a book is issued, admin can set:

```text
Fine Per Day After Due Date
```

The actual fine is calculated as:

```text
overdue days * issue fine per day
```

Examples:

- Due date: May 10
- Return date: May 10
- Fine: `0`

- Due date: May 10
- Return date: May 13
- Fine/day: `100`
- Fine: `3 * 100 = 300`

The `issues.fine` column stores the final charged fine after return. The `issues.fine_per_day` column stores the per-day fine amount that was set when the book was issued.

## Important Files

- `config/db.php` - database connection and shared function loading
- `includes/functions.php` - helper functions, auth checks, date formatting, fine calculation
- `actions/auth_login.php` - login handler
- `actions/auth_register.php` - registration handler
- `actions/book_actions.php` - book create/update/delete actions
- `actions/issue_actions.php` - book issue and return logic
- `actions/reservation_actions.php` - reservation and renewal actions
- `admin/issues.php` - admin issue/return management
- `admin/issue_book.php` - dedicated issue-book form
- `student/my_books.php` - current issued books for student/member
- `student/history.php` - returned book history
- `database.sql` - schema and sample data

## Database Tables

- `users` - admins, students, and teachers
- `categories` - book categories
- `authors` - book authors
- `locations` - physical book locations
- `books` - catalog and stock information
- `issues` - issued books, return dates, status, fine, and fine/day
- `reservations` - book reservation requests
- `settings` - default fine/day and borrowing limits

## Notes

- This project assumes a local trusted environment.
- Passwords are stored with PHP `password_hash`.
- Some pages use direct IDs from the session and should be further hardened before public deployment.
- For production use, add CSRF protection, stricter input validation, HTTPS, and role-based authorization checks on every action.

## Troubleshooting

### Database connection failed

Check that MySQL is running and the credentials in `config/db.php` match your local setup.

### Login does not work

Make sure `database.sql` was imported successfully. The seeded accounts use `password` as the password.

### Fine is not showing

Fine only appears after the due date is exceeded. For active books, the app shows the fine/day and accrued fine. For returned books, the final charged fine is saved in `issues.fine`.

### Page not found

Confirm the folder is inside `htdocs` and open:

```text
http://localhost/lbs/
```
