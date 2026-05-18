<?php
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['user_id'];

    if ($action === 'reserve') {
        $book_id = (int)$_POST['book_id'];

        try {
            // Check if already reserved
            $stmt = $pdo->prepare("SELECT id FROM reservations WHERE book_id = ? AND user_id = ? AND status = 'pending'");
            $stmt->execute([$book_id, $user_id]);
            if ($stmt->fetch()) {
                setFlash('warning', 'You already have a pending reservation for this book.');
                redirect('../student/search.php');
            }

            // Insert reservation
            $stmt = $pdo->prepare("INSERT INTO reservations (book_id, user_id, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$book_id, $user_id]);

            setFlash('success', 'Book reserved successfully! You will be notified when it is available.');
            redirect('../student/search.php');

        } catch (PDOException $e) {
            setFlash('danger', 'Reservation failed: ' . $e->getMessage());
            redirect('../student/search.php');
        }
    }
    
    if ($action === 'renew') {
        $issue_id = (int)($_POST['issue_id'] ?? 0);

        try {
            $stmt = $pdo->prepare("SELECT * FROM issues WHERE id = ? AND user_id = ?");
            $stmt->execute([$issue_id, $user_id]);
            $issue = $stmt->fetch();

            if (!$issue) {
                setFlash('danger', 'Issue record not found.');
                redirect('../student/my_books.php');
            }

            if ($issue['status'] === 'overdue') {
                setFlash('warning', 'Cannot renew an overdue book. Please contact the library.');
                redirect('../student/my_books.php');
            }

            // Get max days allowed from settings
            $max_days = (int)($pdo->query("SELECT max_days_allowed FROM settings LIMIT 1")->fetchColumn() ?: 14);

            // Calculate new due date (extend from current due date)
            $new_due_date = date('Y-m-d', strtotime($issue['due_date'] . " + {$max_days} days"));

            $stmt = $pdo->prepare("UPDATE issues SET due_date = ? WHERE id = ?");
            $stmt->execute([$new_due_date, $issue_id]);

            setFlash('success', 'Book renewed successfully. New due date: ' . date('M d, Y', strtotime($new_due_date)));
            redirect('../student/my_books.php');

        } catch (PDOException $e) {
            setFlash('danger', 'Renewal failed: ' . $e->getMessage());
            redirect('../student/my_books.php');
        }
    }
    
    if ($action === 'fulfil') {
        // Only admins can fulfil reservations
        if (!isAdmin()) {
            setFlash('danger', 'Unauthorized action.');
            redirect('../admin/reservations.php');
        }

        $reservation_id = (int)($_POST['reservation_id'] ?? 0);

        try {
            $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
            $stmt->execute([$reservation_id]);
            $res = $stmt->fetch();

            if (!$res) {
                setFlash('danger', 'Reservation not found.');
                redirect('../admin/reservations.php');
            }

            if ($res['status'] !== 'pending') {
                setFlash('warning', 'Reservation is not pending.');
                redirect('../admin/reservations.php');
            }

            // Check book availability
            $book = $pdo->prepare("SELECT available_quantity FROM books WHERE id = ?");
            $book->execute([$res['book_id']]);
            $b = $book->fetch();

            if (!$b) {
                setFlash('danger', 'Book record not found.');
                redirect('../admin/reservations.php');
            }

            if ($b['available_quantity'] <= 0) {
                setFlash('warning', 'Book is not currently available to fulfil.');
                redirect('../admin/reservations.php');
            }

            // Start transaction
            $pdo->beginTransaction();

            // Mark reservation fulfilled
            $stmt = $pdo->prepare("UPDATE reservations SET status = 'fulfilled' WHERE id = ?");
            $stmt->execute([$reservation_id]);

            // Decrement book availability
            $pdo->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?")->execute([$res['book_id']]);

            $pdo->commit();

            setFlash('success', 'Reservation fulfilled successfully.');
            redirect('../admin/reservations.php');

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            setFlash('danger', 'Fulfil failed: ' . $e->getMessage());
            redirect('../admin/reservations.php');
        }
    }
    
    if ($action === 'cancel') {
        $reservation_id = (int)($_POST['reservation_id'] ?? 0);
        try {
            $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ? AND user_id = ?");
            $stmt->execute([$reservation_id, $user_id]);
            $r = $stmt->fetch();

            if (!$r) {
                setFlash('danger', 'Reservation not found.');
                redirect('../student/reservations.php');
            }

            if ($r['status'] !== 'pending') {
                setFlash('warning', 'Only pending reservations can be cancelled.');
                redirect('../student/reservations.php');
            }

            $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
            $stmt->execute([$reservation_id]);

            setFlash('success', 'Reservation cancelled.');
            redirect('../student/reservations.php');
        } catch (PDOException $e) {
            setFlash('danger', 'Cancel failed: ' . $e->getMessage());
            redirect('../student/reservations.php');
        }
    }
} else {
    redirect('../student/dashboard.php');
}
?>
