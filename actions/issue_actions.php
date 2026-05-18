<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'issue') {
        // Accept either direct IDs (from admin form) or search strings
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
        $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : null;
        $student_search = sanitize($_POST['student_search'] ?? '');
        $book_search = sanitize($_POST['book_search'] ?? '');
        $issue_date = $_POST['issue_date'];
        $due_date = $_POST['due_date'];
        $default_fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;
        $fine_per_day = isset($_POST['fine_per_day']) && $_POST['fine_per_day'] !== ''
            ? max(0, (float)$_POST['fine_per_day'])
            : (float)$default_fine_rate;

        try {
            // Resolve student/book either by provided IDs or search terms
            if ($user_id) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$user_id]);
                $student = $stmt->fetch();
            } else {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE user_id_code = ? OR name LIKE ? LIMIT 1");
                $stmt->execute([$student_search, "%$student_search%"]);
                $student = $stmt->fetch();
            }

            if ($book_id) {
                $stmt = $pdo->prepare("SELECT id, available_quantity FROM books WHERE id = ? LIMIT 1");
                $stmt->execute([$book_id]);
                $book = $stmt->fetch();
            } else {
                $stmt = $pdo->prepare("SELECT id, available_quantity FROM books WHERE isbn = ? OR title LIKE ? LIMIT 1");
                $stmt->execute([$book_search, "%$book_search%"]);
                $book = $stmt->fetch();
            }

            if (!$student) {
                setFlash('danger', 'Student not found.');
                redirect('../admin/issues.php');
            }

            if (!$book || $book['available_quantity'] <= 0) {
                setFlash('danger', 'Book not found or not available.');
                redirect('../admin/issues.php');
            }

            // Resolve final ids to use (prefer provided ids)
            $resolved_user_id = $user_id ? $user_id : ($student['id'] ?? null);
            $resolved_book_id = $book_id ? $book_id : ($book['id'] ?? null);

            // Start transaction
            $pdo->beginTransaction();

            // New issues never start with a fine. Fine is calculated only after due date.
            $stmt = $pdo->prepare("INSERT INTO issues (book_id, user_id, issue_date, due_date, status, fine, fine_per_day) VALUES (?, ?, ?, ?, 'issued', ?, ?)");
            $stmt->execute([$resolved_book_id, $resolved_user_id, $issue_date, $due_date, 0.00, $fine_per_day]);

            // Update book quantity
            $pdo->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?")->execute([$resolved_book_id]);

            $pdo->commit();
            setFlash('success', 'Book issued successfully!');
            redirect('../admin/issues.php');

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash('danger', 'Transaction failed: ' . $e->getMessage());
            redirect('../admin/issues.php');
        }
    }

    if ($action === 'return') {
        $issue_id = (int)$_POST['issue_id'];
        $return_date = date('Y-m-d');

        try {
            $stmt = $pdo->prepare("SELECT * FROM issues WHERE id = ?");
            $stmt->execute([$issue_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                setFlash('danger', 'Issue record not found.');
                redirect('../admin/issues.php');
            }

            // Fine is based only on overdue days. On-time returns are always zero fine.
            $default_fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;
            $fine_rate = getIssueFineRate($issue, $default_fine_rate);
            $fine = calculateFine($issue['due_date'], $fine_rate, $return_date);

            // Start transaction
            $pdo->beginTransaction();

            // Update issue record
            $stmt = $pdo->prepare("UPDATE issues SET return_date = ?, fine = ?, status = 'returned' WHERE id = ?");
            $stmt->execute([$return_date, $fine, $issue_id]);

            // Update book quantity
            $pdo->query("UPDATE books SET available_quantity = available_quantity + 1 WHERE id = " . $issue['book_id']);

            $pdo->commit();
            
            $msg = 'Book returned successfully!';
            if ($fine > 0) $msg .= " Fine calculated: Rs " . number_format($fine, 2);
            setFlash('success', $msg);
            redirect('../admin/issues.php');

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash('danger', 'Return failed: ' . $e->getMessage());
            redirect('../admin/issues.php');
        }
    }
} else {
    redirect('../admin/issues.php');
}
?>
