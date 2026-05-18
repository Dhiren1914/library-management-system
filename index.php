<?php
require_once 'config/db.php';
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'student/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Library System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
            display: flex;
            max-width: 850px;
            width: 100%;
        }
        .login-banner {
            background: var(--navy-sidebar);
            color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            width: 40%;
        }
        .login-form-side {
            padding: 50px;
            width: 60%;
        }
        @media (max-width: 768px) {
            .login-banner { display: none; }
            .login-form-side { width: 100%; }
        }
    </style>
</head>
<body class="auth-container">

    <div class="login-box">
        <div class="login-banner">
            <h2 class="fw-bold mb-4">LMS</h2>
            <p class="opacity-75">Welcome to the next generation of library management. Secure, fast, and easy to use.</p>
            <div class="mt-auto">
                <small>© 2023 Library System</small>
            </div>
        </div>
        <div class="login-form-side">
            <div class="mb-5">
                <h3 class="fw-bold" style="color: var(--navy-sidebar);">Account Login</h3>
                <p class="text-muted small">Please enter your credentials to continue</p>
            </div>

            <?php displayFlash(); ?>

            <form action="actions/auth_login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
                    <div class="input-group border rounded">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-0 shadow-none" placeholder="name@example.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">PASSWORD</label>
                    <div class="input-group border rounded">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-0 shadow-none" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="background: var(--navy-sidebar); border: none;">
                    LOGIN SECURELY <i class="fas fa-sign-in-alt ms-2"></i>
                </button>
            </form>

            <div class="mt-5 text-center">
                <p class="text-muted small">Don't have an account? <a href="student_register.php" style="color: var(--navy-sidebar); font-weight: 700; text-decoration: none;">REGISTER NOW</a></p>
            </div>
        </div>
    </div>

</body>
</html>