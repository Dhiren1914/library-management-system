<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Issue & Return System';

// Fetch all issues
$issues = $pdo->query("
    SELECT i.*, b.title as book_title, u.name as student_name, u.user_id_code AS student_id 
    FROM issues i 
    JOIN books b ON i.book_id = b.id 
    JOIN users u ON i.user_id = u.id 
    ORDER BY i.status ASC, i.due_date ASC
")->fetchAll();
$fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">Issue & Return Management</h2>
            <p class="text-muted">Track all book circulations, process returns, and manage fines.</p>
        </div>
        <div class="header-actions">
            <button class="btn-university" data-bs-toggle="modal" data-bs-target="#issueBookModal">
                <i class="fas fa-plus"></i> Issue New Book
            </button>
        </div>
    </div>

    <?php displayFlash(); ?>

    <div class="card-custom mt-2">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Book Title</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Fine/Day</th>
                                <th>Fine</th>
                                <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($issues): ?>
                        <?php foreach ($issues as $issue): ?>
                            <?php
                                $display_status = getIssueDisplayStatus($issue);
                                $issue_fine_rate = getIssueFineRate($issue, $fine_rate);
                                $display_fine = $display_status === 'returned'
                                    ? calculateFine($issue['due_date'], $issue_fine_rate, $issue['return_date'])
                                    : calculateFine($issue['due_date'], $issue_fine_rate);
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo $issue['student_name']; ?></div>
                                    <div class="text-muted small"><?php echo $issue['student_id']; ?></div>
                                </td>
                                <td><span class="text-primary fw-medium"><?php echo $issue['book_title']; ?></span></td>
                                <td><?php echo formatDate($issue['issue_date']); ?></td>
                                <td>
                                    <span class="<?php echo ($display_status == 'overdue') ? 'text-danger fw-bold' : ''; ?>">
                                        <?php echo formatDate($issue['due_date']); ?>
                                    </span>
                                </td>
                                <td>Rs <?php echo number_format($issue_fine_rate, 2); ?></td>
                                <td>
                                    <?php if ($display_fine > 0): ?>
                                        <span class="text-danger fw-bold">Rs <?php echo number_format($display_fine, 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?php 
                                        echo $display_status == 'issued' ? 'bg-primary' : 
                                            ($display_status == 'returned' ? 'bg-success' : 'bg-danger'); 
                                    ?>">
                                        <?php echo ucfirst($display_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($issue['status'] != 'returned'): ?>
                                        <form action="../actions/issue_actions.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="return">
                                            <input type="hidden" name="issue_id" value="<?php echo $issue['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">Mark Returned</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="small text-muted"><?php echo formatDate($issue['return_date']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center py-5 text-muted">No circulation records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Issue Book Modal -->
<div class="modal fade" id="issueBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold text-primary">Issue a Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../actions/issue_actions.php" method="POST">
                <input type="hidden" name="action" value="issue">
                <div class="modal-body p-4">
                    <div class="form-group mb-3">
                        <label class="form-label">Search Student (ID or Name)</label>
                        <input type="text" name="student_search" class="form-control form-control-custom" placeholder="e.g. STU-001" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Search Book (Title or ISBN)</label>
                        <input type="text" name="book_search" class="form-control form-control-custom" placeholder="e.g. Clean Code" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Issue Date</label>
                                <input type="date" name="issue_date" class="form-control form-control-custom" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control form-control-custom" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label">Fine Per Day After Due Date</label>
                        <input type="number" name="fine_per_day" class="form-control form-control-custom" step="0.01" min="0" value="<?php echo number_format((float)$fine_rate, 2, '.', ''); ?>" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-university">Confirm Issue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
