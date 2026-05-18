<?php
require_once '../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Loan History';
$user_id = $_SESSION['user_id'];

// Fetch returned books
$history = $pdo->query("
    SELECT i.*, b.title, b.cover_image, a.name AS author_name, l.name AS location_name 
    FROM issues i 
    JOIN books b ON i.book_id = b.id 
    LEFT JOIN authors a ON b.author_id = a.id 
    LEFT JOIN locations l ON b.location_id = l.id 
    WHERE i.user_id = $user_id AND i.status = 'returned'
    ORDER BY i.return_date DESC
")->fetchAll();
$fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">Loan History</h2>
            <p class="text-muted">A record of all the books you have borrowed and returned.</p>
        </div>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Book</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($history): ?>
                        <?php foreach ($history as $item): ?>
                            <?php $display_fine = calculateFine($item['due_date'], getIssueFineRate($item, $fine_rate), $item['return_date']); ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($item['cover_image']) && $item['cover_image'] != 'default_book.png'): ?>
                                        <img src="../assets/img/<?php echo $item['cover_image']; ?>" class="rounded" width="40" height="55" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="img-placeholder" style="width:40px;height:55px;display:flex;align-items:center;justify-content:center;border-radius:4px;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo $item['title'] ?? 'Untitled'; ?></div>
                                            <div class="text-muted small"><?php echo $item['author_name'] ?: 'Unknown'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatDate($item['issue_date']); ?></td>
                                <td><?php echo formatDate($item['due_date']); ?></td>
                                <td><?php echo formatDate($item['return_date']); ?></td>
                                <td>
                                    <?php echo $display_fine > 0 ? "<span class='text-danger'>Rs " . number_format($display_fine, 2) . "</span>" : "<span class='text-success'>None</span>"; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No past loans found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
