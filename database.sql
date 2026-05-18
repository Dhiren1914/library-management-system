-- Digital Library Management System Database
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student','teacher') NOT NULL DEFAULT 'student',
  `user_id_code` varchar(50) DEFAULT NULL, -- Student ID or Teacher ID
  `phone` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default_user.png',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `categories`
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `authors`
CREATE TABLE IF NOT EXISTS `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `locations`
CREATE TABLE IF NOT EXISTS `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL, -- e.g. Rack A1, Shelf 2
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `books`
CREATE TABLE IF NOT EXISTS `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL UNIQUE,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `available_quantity` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT 'default_book.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `issues`
CREATE TABLE IF NOT EXISTS `issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine` decimal(10,2) DEFAULT 0.00,
  `fine_per_day` decimal(10,2) DEFAULT 10.00,
  `status` enum('issued','returned','overdue') DEFAULT 'issued',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `reservations`
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reservation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','fulfilled','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `settings`
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fine_per_day` decimal(10,2) DEFAULT 10.00,
  `max_days_allowed` int(11) DEFAULT 14,
  `max_books_allowed` int(11) DEFAULT 3,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Dummy Data
-- --------------------------------------------------------

-- Users (Password is 'admin123' hashed)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `user_id_code`, `phone`) VALUES
('Administrator', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'ADM-001', '1234567890'),
('John Student', 'student@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'STU-001', '0987654321'),
('Dr. Smith', 'teacher@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 'TEA-001', '1122334455');

-- Categories
INSERT INTO `categories` (`name`) VALUES ('Computer Science'), ('Mathematics'), ('Physics'), ('Literature'), ('History');

-- Authors
INSERT INTO `authors` (`name`) VALUES ('Robert C. Martin'), ('Martin Fowler'), ('Albert Einstein'), ('J.K. Rowling'), ('Yuval Noah Harari');

-- Locations
INSERT INTO `locations` (`name`) VALUES ('Rack A1'), ('Rack A2'), ('Rack B1'), ('Rack B2'), ('Rack C1');

-- Books
INSERT INTO `books` (`title`, `author_id`, `category_id`, `location_id`, `isbn`, `quantity`, `available_quantity`) VALUES
('Clean Code', 1, 1, 1, '978-0132350884', 5, 4),
('Refactoring', 2, 1, 1, '978-0201485677', 3, 3),
('The World as I See It', 3, 3, 2, '978-1607963363', 2, 2),
('Harry Potter', 4, 4, 3, '978-0747532699', 10, 10),
('Sapiens', 5, 5, 4, '978-0062316097', 4, 4);

-- Issues
INSERT INTO `issues` (`book_id`, `user_id`, `issue_date`, `due_date`, `fine_per_day`, `status`) VALUES
(1, 2, '2023-10-01', '2023-10-15', 10.00, 'issued');

-- Settings
INSERT INTO `settings` (`fine_per_day`, `max_days_allowed`, `max_books_allowed`) VALUES (10.00, 14, 3);

COMMIT;
