<?php
require_once '../config/db.php';
if (!isAdmin()) redirect('../index.php');
$pageTitle = 'Issue Book';
$default_fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Issue</span> <span class="text-muted">Book</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Issue Book</div>
    </div>
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card-custom">
                <h5 class="fw-bold mb-4">New Book Issue</h5>
                <form action="../actions/issue_actions.php" method="POST">
                    <input type="hidden" name="action" value="issue">
                    <div class="mb-3">
                        <label class="form-label">Select User (Student/Teacher)</label>
                        <select name="user_id" class="form-control" required>
                            <?php foreach ($pdo->query("SELECT id, name, user_id_code FROM users WHERE role != 'admin'") as $u): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo $u['name']; ?> (<?php echo $u['user_id_code']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Book</label>
                        <select name="book_id" class="form-control" required>
                            <?php foreach ($pdo->query("SELECT id, title FROM books WHERE available_quantity > 0") as $b): ?>
                                <option value="<?php echo $b['id']; ?>"><?php echo $b['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fine Per Day After Due Date</label>
                        <input type="number" name="fine_per_day" class="form-control" step="0.01" value="<?php echo number_format((float)$default_fine_rate, 2, '.', ''); ?>" min="0" required>
                        <div class="form-text">Student will pay this amount per day only if the due date is exceeded.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Issue Book</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
