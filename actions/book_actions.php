<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

// Handle GET requests (like Delete)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'delete' && $id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Book deleted successfully!');
        } catch (PDOException $e) {
            setFlash('danger', 'Error deleting book: ' . $e->getMessage());
        }
        redirect('../admin/books.php');
    }
}

// Handle POST requests (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $title = sanitize($_POST['title']);
        $isbn = sanitize($_POST['isbn']);
        $quantity = (int)$_POST['quantity'];
        
        // Handle Author (New or Existing)
        $author_id = (int)($_POST['author_id'] ?? 0);
        $new_author = sanitize($_POST['new_author'] ?? '');
        if (!empty($new_author)) {
            $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (?)");
            $stmt->execute([$new_author]);
            $author_id = $pdo->lastInsertId();
        }

        // Handle Category (New or Existing)
        $category_id = (int)($_POST['category_id'] ?? 0);
        $new_category = sanitize($_POST['new_category'] ?? '');
        if (!empty($new_category)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$new_category]);
            $category_id = $pdo->lastInsertId();
        }

        // Handle Location (New or Existing)
        $location_id = (int)($_POST['location_id'] ?? 0);
        $new_location = sanitize($_POST['new_location'] ?? '');
        if (!empty($new_location)) {
            $stmt = $pdo->prepare("INSERT INTO locations (name) VALUES (?)");
            $stmt->execute([$new_location]);
            $location_id = $pdo->lastInsertId();
        }

        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO books (title, author_id, category_id, location_id, isbn, quantity, available_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $author_id, $category_id, $location_id, $isbn, $quantity, $quantity]);
                setFlash('success', 'Book added successfully!');
            } else {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE books SET title = ?, author_id = ?, category_id = ?, location_id = ?, isbn = ?, quantity = ? WHERE id = ?");
                $stmt->execute([$title, $author_id, $category_id, $location_id, $isbn, $quantity, $id]);
                setFlash('success', 'Book updated successfully!');
            }
        } catch (PDOException $e) {
            setFlash('danger', 'Error: ' . $e->getMessage());
        }
        redirect('../admin/books.php');
    }
}

redirect('../admin/books.php');
?>
