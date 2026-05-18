<div class="sidebar">
    <div class="sidebar-profile">
        <div class="profile-icon-placeholder">
            <i class="fas fa-user-tie"></i>
        </div>
        <h5>Welcome!</h5>
        <h4><?php echo $_SESSION['name'] ?? 'User'; ?></h4>
    </div>
    
    <div class="sidebar-menu">
        <div class="sidebar-section-title">General</div>
        
        <?php if (isAdmin()): ?>
            <!-- Admin Links -->
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="profile.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> Profile
            </a>
            <a href="students.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">
                <i class="fas fa-graduation-cap"></i> All Student Information
            </a>
            <a href="teachers.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : ''; ?>">
                <i class="fas fa-chalkboard-teacher"></i> All Teacher Information
            </a>
            <a href="books.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Manage Book
            </a>
            <a href="issue_book.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'issue_book.php' ? 'active' : ''; ?>">
                <i class="fas fa-bookmark"></i> Issue Book
            </a>
                <a href="issues.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'issues.php' ? 'active' : ''; ?>">
                    <i class="fas fa-list"></i> Issue & Return
                </a>
            <a href="users_manage.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users_manage.php' ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> Manage Users
            </a>
            <a href="issued_books.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'issued_books.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Issued Books
            </a>
            <a href="reservations.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reservations.php' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> View Requested Books
            </a>
        <?php else: ?>
            <!-- Student/Teacher Links -->
            <a href="dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> My Dashboard
            </a>
            <a href="search.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'search.php' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> Search Books
            </a>
            <a href="my_books.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_books.php' ? 'active' : ''; ?>">
                <i class="fas fa-book-reader"></i> My Issued Books
            </a>
            <a href="history.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i> My History
            </a>
        <?php endif; ?>

        <a href="../actions/logout.php" class="nav-link text-danger mt-3">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>
