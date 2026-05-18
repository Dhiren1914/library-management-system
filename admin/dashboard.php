<?php
require_once '../config/db.php';

// Check if admin
if (!isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Dashboard';

// Fetch stats for the new UI
$total_members = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('student', 'teacher')")->fetchColumn() ?: 0;
$issued_books = $pdo->query("SELECT COUNT(*) FROM issues WHERE status != 'returned' AND due_date >= CURDATE()")->fetchColumn() ?: 0;
$total_books = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn() ?: 0;
$fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;
$total_fine = $pdo->prepare("
    SELECT IFNULL(SUM(CASE
        WHEN status = 'returned' AND return_date > due_date THEN DATEDIFF(return_date, due_date) * COALESCE(fine_per_day, ?)
        WHEN status != 'returned' AND due_date < CURDATE() THEN DATEDIFF(CURDATE(), due_date) * COALESCE(fine_per_day, ?)
        ELSE 0
    END), 0)
    FROM issues
");
$total_fine->execute([$fine_rate, $fine_rate]);
$total_fine = (float)$total_fine->fetchColumn();

$total_copies = $pdo->query("SELECT SUM(quantity) FROM books")->fetchColumn() ?: 0;
$active_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn() ?: 0;
$overdue_count = $pdo->query("SELECT COUNT(*) FROM issues WHERE status != 'returned' AND due_date < CURDATE()")->fetchColumn() ?: 0;
$requested_books = $pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'")->fetchColumn() ?: 0;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="breadcrumb-section">
        <div>
            <span class="fw-bold">Dashboard</span> <span class="text-muted">Control panel</span>
        </div>
        <div>
            <i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Dashboard
        </div>
    </div>

<div class="row">
        <!-- Row 1 -->
        <div class="col-md-3">
            <a href="students.php" class="text-decoration-none">
                <div class="dash-card bg-cyan">
                    <h2><?php echo $total_members; ?></h2>
                    <p>Members</p>
                    <i class="fas fa-users"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="issued_books.php" class="text-decoration-none">
                <div class="dash-card bg-green">
                    <h2><?php echo $issued_books; ?></h2>
                    <p>Issued Books</p>
                    <i class="fas fa-rocket"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="books.php" class="text-decoration-none">
                <div class="dash-card bg-red">
                    <h2><?php echo $total_books; ?></h2>
                    <p>Books</p>
                    <i class="fas fa-book"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="issued_books.php" class="text-decoration-none">
                <div class="dash-card bg-orange">
                    <h2>Rs <?php echo number_format($total_fine); ?></h2>
                    <p>Fine</p>
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </a>
        </div>

        <!-- Row 2 -->
        <div class="col-md-3">
            <a href="books.php" class="text-decoration-none">
                <div class="dash-card bg-red">
                    <h2><?php echo $total_copies; ?></h2>
                    <p>Manage Book</p>
                    <i class="fas fa-copy"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="users_manage.php" class="text-decoration-none">
                <div class="dash-card bg-orange">
                    <h2><?php echo $active_users; ?></h2>
                    <p>Manage User</p>
                    <i class="fas fa-user-edit"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="issued_books.php" class="text-decoration-none">
                <div class="dash-card bg-blue">
                    <h2><?php echo $overdue_count; ?></h2>
                    <p>Status</p>
                    <i class="fas fa-layer-group"></i>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="reservations.php" class="text-decoration-none">
                <div class="dash-card bg-green">
                    <h2><?php echo $requested_books; ?></h2>
                    <p>Requested Books</p>
                    <i class="fas fa-file-alt"></i>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0">Recent Issued Books</h5>
                    <a href="issued_books.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Issued To</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_issues = $pdo->query("
                                SELECT i.*, b.title as book_title, u.name as user_name 
                                FROM issues i 
                                JOIN books b ON i.book_id = b.id 
                                JOIN users u ON i.user_id = u.id 
                                ORDER BY i.issue_date DESC LIMIT 5
                            ")->fetchAll();
                            
                            if ($recent_issues):
                                foreach ($recent_issues as $ri):
                                    $display_status = getIssueDisplayStatus($ri);
                            ?>
                            <tr>
                                <td><span class="fw-bold"><?php echo $ri['book_title']; ?></span></td>
                                <td><?php echo $ri['user_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($ri['issue_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($ri['due_date'])); ?></td>
                                <td>
                                    <span class="badge rounded-pill bg-<?php echo $display_status == 'overdue' ? 'red' : ($display_status == 'returned' ? 'green' : 'blue'); ?> px-3">
                                        <?php echo ucfirst($display_status); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No recent activity.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
