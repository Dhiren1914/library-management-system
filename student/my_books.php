<?php
require_once '../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'My Issued Books';
$user_id = $_SESSION['user_id'];
$books = $pdo->query("
    SELECT i.*, b.title, b.cover_image, a.name AS author_name, l.name AS location_name 
    FROM issues i 
    JOIN books b ON i.book_id = b.id 
    LEFT JOIN authors a ON b.author_id = a.id 
    LEFT JOIN locations l ON b.location_id = l.id 
    WHERE i.user_id = $user_id AND (i.status = 'issued' OR i.status = 'overdue')
    ORDER BY i.due_date ASC
")->fetchAll();
$fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">My Issued Books</h2>
            <p class="text-muted">Manage your currently borrowed books and track return dates.</p>
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
                        <th>Fine/Day</th>
                        <th>Status</th>
                        <th>Fine Accrued</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books): ?>
                        <?php foreach ($books as $book): ?>
                            <?php
                                $display_status = getIssueDisplayStatus($book);
                                $issue_fine_rate = getIssueFineRate($book, $fine_rate);
                                $fine = calculateFine($book['due_date'], $issue_fine_rate);
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                    <?php if (!empty($book['cover_image']) && $book['cover_image'] != 'default_book.png'): ?>
                                        <img src="../assets/img/<?php echo $book['cover_image']; ?>" class="rounded" width="40" height="55" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="img-placeholder" style="width:40px;height:55px;display:flex;align-items:center;justify-content:center;border-radius:4px;">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo $book['title'] ?? 'Untitled'; ?></div>
                                            <div class="text-muted small"><?php echo $book['author_name'] ?: 'Unknown'; ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatDate($book['issue_date']); ?></td>
                                <td>
                                    <span class="<?php echo $display_status == 'overdue' ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo formatDate($book['due_date']); ?>
                                    </span>
                                </td>
                                <td>Rs <?php echo number_format($issue_fine_rate, 2); ?></td>
                                <td>
                                    <span class="badge rounded-pill <?php echo $display_status == 'overdue' ? 'bg-danger' : 'bg-primary'; ?>">
                                        <?php echo ucfirst($display_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $fine > 0 ? "<span class='text-danger fw-bold'>Rs " . number_format($fine, 2) . "</span>" : "-"; ?>
                                </td>
                                <td>
                                    <form action="../actions/reservation_actions.php" method="POST" style="display:inline-block;margin:0;">
                                        <input type="hidden" name="action" value="renew">
                                        <input type="hidden" name="issue_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" class="btn-university btn-sm">Renew</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">You don't have any books issued currently.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
