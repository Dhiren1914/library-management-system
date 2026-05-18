<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Dashboard'; ?> | Library System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="header">
    <div class="header-logo text-white fw-bold">
        <i class="fas fa-book-reader me-2"></i> LMS
    </div>
    
    <div class="header-title">Librarian control panel</div>
    
    <div class="header-actions d-flex align-items-center">
        <div class="dropdown">
            <a href="#" class="text-white text-decoration-none dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center me-2 shadow-sm" style="width: 35px; height: 35px;">
                    <i class="fas fa-user-circle" style="font-size: 18px; color: white;"></i>
                </div>
                <span class="fw-bold"><?php echo $_SESSION['name'] ?? 'Admin'; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="../actions/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</header>
