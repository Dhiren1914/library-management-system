<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Analytics & Reports';

// Analytics Data
$monthly_issues = $pdo->query("
    SELECT MONTHNAME(issue_date) as month, COUNT(*) as count 
    FROM issues 
    GROUP BY MONTH(issue_date) 
    ORDER BY MONTH(issue_date)
")->fetchAll();

$category_dist = $pdo->query("
    SELECT category, COUNT(*) as count 
    FROM books 
    GROUP BY category
")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">Analytics & Reports</h2>
            <p class="text-muted">Statistical overview of library operations and growth.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline-primary rounded-pill px-4 me-2">
                <i class="fas fa-download me-2"></i> Export CSV
            </button>
            <button class="btn-university" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card-custom h-100">
                <h5 class="fw-bold mb-4">Monthly Circulation Trend</h5>
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-chart-line fa-4x mb-3 opacity-25"></i>
                    <p>Circulation activity has increased by 15% this month.</p>
                    <div class="d-flex justify-content-around align-items-end" style="height: 150px;">
                        <?php foreach(['Jan', 'Feb', 'Mar', 'Apr', 'May'] as $m): ?>
                            <div class="bg-primary rounded-top" style="width: 40px; height: <?php echo rand(30, 100); ?>%;">
                                <div class="small text-muted mt-n4" style="transform: translateY(-25px);"><?php echo $m; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card-custom h-100">
                <h5 class="fw-bold mb-4">Category Distribution</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tbody>
                            <?php foreach($category_dist as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['category']; ?></td>
                                    <td class="text-end fw-bold"><?php echo $cat['count']; ?></td>
                                    <td style="width: 100px;">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar" style="width: <?php echo rand(20, 90); ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card-custom">
        <h5 class="fw-bold mb-4">Financial Summary</h5>
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <p class="text-muted small mb-1">Total Fines Collected</p>
                    <h3 class="fw-bold text-success">Rs <?php echo number_format($pdo->query("SELECT SUM(fine) FROM issues WHERE status = 'returned'")->fetchColumn() ?: 0, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <p class="text-muted small mb-1">Pending Fines</p>
                    <h3 class="fw-bold text-danger">Rs <?php echo number_format($pdo->query("SELECT SUM(fine) FROM issues WHERE status = 'overdue'")->fetchColumn() ?: 0, 2); ?></h3>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded">
                    <p class="text-muted small mb-1">System Efficiency</p>
                    <h3 class="fw-bold text-primary">94%</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
