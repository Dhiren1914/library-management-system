<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        setFlash('danger', 'Please fill in all fields.');
        redirect('../index.php');
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            if ($user['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            } else {
                redirect('../student/dashboard.php');
            }
        } else {
            setFlash('danger', 'Invalid email or password.');
            redirect('../index.php');
        }
    } catch (PDOException $e) {
        setFlash('danger', 'Something went wrong: ' . $e->getMessage());
        redirect('../index.php');
    }
} else {
    redirect('../index.php');
}
?>
