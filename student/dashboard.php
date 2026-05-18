<?php
require_once '../config/db.php';

// Check if student
if (!isLoggedIn() || isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Member Dashboard';
$user_id = $_SESSION['user_id'];

// Fetch student stats
$issued_count = $pdo->query("SELECT COUNT(*) FROM issues WHERE user_id = $user_id AND status != 'returned' AND due_date >= CURDATE()")->fetchColumn() ?: 0;
$overdue_count = $pdo->query("SELECT COUNT(*) FROM issues WHERE user_id = $user_id AND status != 'returned' AND due_date < CURDATE()")->fetchColumn() ?: 0;
$res_count = $pdo->query("SELECT COUNT(*) FROM reservations WHERE user_id = $user_id AND status = 'pending'")->fetchColumn() ?: 0;
// Additional student summary
$total_requests = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ?");
$total_requests->execute([$user_id]);
$total_requests = (int)$total_requests->fetchColumn();
$returned_count = $pdo->prepare("SELECT COUNT(*) FROM issues WHERE user_id = ? AND status = 'returned'");
$returned_count->execute([$user_id]);
$returned_count = (int)$returned_count->fetchColumn();

$rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;
$fine_paid_stmt = $pdo->prepare("SELECT IFNULL(SUM(DATEDIFF(return_date, due_date) * COALESCE(fine_per_day, ?)),0) FROM issues WHERE user_id = ? AND status = 'returned' AND return_date > due_date");
$fine_paid_stmt->execute([$rate, $user_id]);
$fine_paid = (float)$fine_paid_stmt->fetchColumn();

// Current accrued fines for overdue items
$overdue_fines_stmt = $pdo->prepare("SELECT due_date, fine_per_day FROM issues WHERE user_id = ? AND status != 'returned' AND due_date < CURDATE()");
$overdue_fines_stmt->execute([$user_id]);
$overdue_accrued = 0.0;
foreach ($overdue_fines_stmt->fetchAll() as $r) {
    $overdue_accrued += calculateFine($r['due_date'], getIssueFineRate($r, $rate));
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">My Dashboard</span> <span class="text-muted">Overview</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Dashboard</div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3">
            <a href="reservations.php" class="text-decoration-none">
                <div class="dash-card bg-blue">
                    <h4><?php echo $total_requests; ?></h4>
                    <p>Books Requested</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="history.php" class="text-decoration-none">
                <div class="dash-card bg-green">
                    <h4><?php echo $returned_count; ?></h4>
                    <p>Books Returned</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="reservations.php" class="text-decoration-none">
                <div class="dash-card bg-cyan">
                    <h4><?php echo $res_count; ?></h4>
                    <p>Pending Requests</p>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="my_books.php" class="text-decoration-none">
                <div class="dash-card bg-red">
                    <h4>Rs <?php echo number_format(($fine_paid + $overdue_accrued),2); ?></h4>
                    <p>Total Fine (paid + accrued)</p>
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Row 1 -->
        <div class="col-md-4">
            <a href="my_books.php" class="text-decoration-none">
                <div class="dash-card bg-cyan">
                    <h2><?php echo $issued_count; ?></h2>
                    <p>Currently Issued</p>
                    <i class="fas fa-book-reader"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="my_books.php" class="text-decoration-none">
                <div class="dash-card bg-red">
                    <h2><?php echo $overdue_count; ?></h2>
                    <p>Overdue Books</p>
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="reservations.php" class="text-decoration-none">
                <div class="dash-card bg-green">
                    <h2><?php echo $res_count; ?></h2>
                    <p>My Reservations</p>
                    <i class="fas fa-bookmark"></i>
                </div>
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-lg-8">
            <div class="card-custom">
                <h5 class="fw-bold mb-4">My Current Books</h5>
                <?php
                $my_books = $pdo->query("
                    SELECT i.*, b.title, a.name as author_name 
                    FROM issues i 
                    JOIN books b ON i.book_id = b.id 
                    LEFT JOIN authors a ON b.author_id = a.id
                    WHERE i.user_id = $user_id AND (i.status = 'issued' OR i.status = 'overdue')
                ")->fetchAll();

                if ($my_books):
                ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Due Date</th>
                                    <th>Fine/Day</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_books as $b): ?>
                                <?php
                                    $display_status = getIssueDisplayStatus($b);
                                    $issue_fine_rate = getIssueFineRate($b, $rate);
                                ?>
                                <tr>
                                    <td><span class="fw-bold"><?php echo $b['title']; ?></span><br><small class="text-muted"><?php echo $b['author_name']; ?></small></td>
                                    <td><?php echo date('M d, Y', strtotime($b['due_date'])); ?></td>
                                    <td>Rs <?php echo number_format($issue_fine_rate, 2); ?></td>
                                    <td><span class="badge rounded-pill bg-<?php echo $display_status == 'overdue' ? 'red' : 'blue'; ?>"><?php echo ucfirst($display_status); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center py-4 text-muted">You have no books issued.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card-custom">
                <h5 class="fw-bold mb-4">Quick Search</h5>
                <form action="search.php" method="GET">
                    <input type="text" name="q" class="form-control mb-3" placeholder="Search by title, author...">
                    <button class="btn btn-primary w-100">Search Catalog</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
