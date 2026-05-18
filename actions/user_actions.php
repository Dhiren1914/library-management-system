<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        // Prevent admin from deleting themselves
        if ($id == $_SESSION['user_id']) {
            setFlash('danger', 'You cannot delete your own account.');
            redirect('../admin/users_manage.php');
        }

        try {
            // First check if user has any active issues
            $issues = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id = ? AND status != 'returned'");
            $issues->execute([$id]);
            if ($issues->fetchColumn() > 0) {
                setFlash('danger', 'Cannot delete user with active book issues.');
                redirect('../admin/users_manage.php');
            }

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'User deleted successfully.');
        } catch (PDOException $e) {
            setFlash('danger', 'Error: ' . $e->getMessage());
        }
    }
}

redirect('../admin/users_manage.php');
?>
