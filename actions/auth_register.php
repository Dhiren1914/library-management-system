<?php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $user_id_code = sanitize($_POST['user_id_code']);
    $email = sanitize($_POST['email']);
    $role = sanitize($_POST['role'] ?? 'student');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($_POST['phone']);

    if (empty($name) || empty($user_id_code) || empty($email) || empty($password)) {
        setFlash('danger', 'Please fill in all required fields.');
        redirect('../student_register.php');
    }

    if ($password !== $confirm_password) {
        setFlash('danger', 'Passwords do not match.');
        redirect('../student_register.php');
    }

    try {
        // Check if email or user_id_code already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR user_id_code = ?");
        $stmt->execute([$email, $user_id_code]);
        if ($stmt->fetch()) {
            setFlash('danger', 'Email or ID Code already registered.');
            redirect('../student_register.php');
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (name, user_id_code, email, password, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $user_id_code, $email, $hashed_password, $phone, $role]);

        setFlash('success', 'Registration successful! You can now login.');
        redirect('../index.php');

    } catch (PDOException $e) {
        setFlash('danger', 'Registration failed: ' . $e->getMessage());
        redirect('../student_register.php');
    }
} else {
    redirect('../student_register.php');
}
?>
