<?php
require_once '../config/db.php';

// Check if admin
if (!isAdmin()) {
    die("Unauthorized access.");
}

echo "<h2>Seeding database with dummy data...</h2>";

try {
    // 1. Clear existing dummy data (except current admin)
    $pdo->exec("DELETE FROM issues");
    $pdo->exec("DELETE FROM reservations");
    $pdo->exec("DELETE FROM books");
    $pdo->exec("DELETE FROM users WHERE role = 'student'");
    
    // Reset auto-increment
    $pdo->exec("ALTER TABLE books AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE users AUTO_INCREMENT = 2");
    $pdo->exec("ALTER TABLE issues AUTO_INCREMENT = 1");

    // 2. Insert Students
    $students = [
        ['Ali Khan', 'ali@student.com', 'ST001'],
        ['Sara Ahmed', 'sara@student.com', 'ST002'],
        ['Usman Bin Tariq', 'usman@student.com', 'ST003'],
        ['Zainab Bibi', 'zainab@student.com', 'ST004'],
        ['Bilal Sheikh', 'bilal@student.com', 'ST005'],
    ];

    $password = password_hash('password', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, user_id_code) VALUES (?, ?, ?, 'student', ?)");
    foreach ($students as $s) {
        $stmt->execute([$s[0], $s[1], $password, $s[2]]);
    }
    echo "<p style='color: green;'>✅ Inserted students.</p>";

    // 3. Insert Books
    $categories = ['Computer Science', 'Business Management', 'Engineering', 'Literature', 'History'];
    $books = [
        ['Clean Code', 'Robert C. Martin', '9780132350884', 'Computer Science', 10],
        ['Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 'Computer Science', 5],
        ['The Lean Startup', 'Eric Ries', '9780307887894', 'Business Management', 8],
        ['Thinking, Fast and Slow', 'Daniel Kahneman', '9780374275631', 'Business Management', 12],
        ['Engineering Mechanics', 'R.C. Hibbeler', '9780133915426', 'Engineering', 15],
        ['The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Literature', 20],
        ['Sapiens', 'Yuval Noah Harari', '9780062316097', 'History', 7],
        ['JavaScript: The Good Parts', 'Douglas Crockford', '9780596517748', 'Computer Science', 6],
        ['The Art of Computer Programming', 'Donald Knuth', '9780201896831', 'Computer Science', 3],
        ['Rich Dad Poor Dad', 'Robert Kiyosaki', '9781612680194', 'Business Management', 15],
    ];

    $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category, quantity, available_quantity) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($books as $b) {
        $stmt->execute([$b[0], $b[1], $b[2], $b[3], $b[4], $b[4]]);
    }
    echo "<p style='color: green;'>✅ Inserted books.</p>";

    // 4. Insert Issues
    $bookIds = $pdo->query("SELECT id FROM books")->fetchAll(PDO::FETCH_COLUMN);
    $userIds = $pdo->query("SELECT id FROM users WHERE role = 'student'")->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, ?)");
    
    // Some issued books
    for ($i = 0; $i < 15; $i++) {
        $bid = $bookIds[array_rand($bookIds)];
        $uid = $userIds[array_rand($userIds)];
        $issue_date = date('Y-m-d', strtotime('-' . rand(1, 10) . ' days'));
        $due_date = date('Y-m-d', strtotime($issue_date . ' + 14 days'));
        $stmt->execute([$bid, $uid, $issue_date, $due_date, 'issued']);
        
        // Decrement available quantity
        $pdo->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?")->execute([$bid]);
    }

    // Some overdue books
    for ($i = 0; $i < 5; $i++) {
        $bid = $bookIds[array_rand($bookIds)];
        $uid = $userIds[array_rand($userIds)];
        $issue_date = date('Y-m-d', strtotime('-25 days'));
        $due_date = date('Y-m-d', strtotime($issue_date . ' + 14 days'));
        $stmt->execute([$bid, $uid, $issue_date, $due_date, 'overdue']);
        
        $pdo->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?")->execute([$bid]);
    }

    // Some returned books
    for ($i = 0; $i < 10; $i++) {
        $bid = $bookIds[array_rand($bookIds)];
        $uid = $userIds[array_rand($userIds)];
        $issue_date = date('Y-m-d', strtotime('-30 days'));
        $due_date = date('Y-m-d', strtotime($issue_date . ' + 14 days'));
        $return_date = date('Y-m-d', strtotime($issue_date . ' + 7 days'));
        $stmt = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, return_date, status) VALUES (?, ?, ?, ?, ?, 'returned')");
        $stmt->execute([$bid, $uid, $issue_date, $due_date, $return_date]);
    }

    echo "<p style='color: green;'>✅ Inserted issue records.</p>";
    echo "<h3>Seeding completed successfully!</h3>";
    echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
