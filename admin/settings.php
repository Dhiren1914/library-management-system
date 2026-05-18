<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'System Settings';

// Fetch current settings
$settings = $pdo->query("SELECT * FROM settings LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fine_per_day = $_POST['fine_per_day'];
    $max_days = $_POST['max_days_allowed'];
    $max_books = $_POST['max_books_allowed'];

    $stmt = $pdo->prepare("UPDATE settings SET fine_per_day = ?, max_days_allowed = ?, max_books_allowed = ? WHERE id = ?");
    $stmt->execute([$fine_per_day, $max_days, $max_books, $settings['id']]);

    setFlash('success', 'Settings updated successfully!');
    redirect('settings.php');
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">System Settings</h2>
            <p class="text-muted">Configure library rules, fine rates, and borrowing limits.</p>
        </div>
    </div>

    <?php displayFlash(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card-custom">
                <h5 class="fw-bold mb-4">Borrowing Rules</h5>
                <form action="settings.php" method="POST">
                    <div class="form-group mb-3">
                        <label class="form-label">Fine Per Day (Late Return)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">$</span>
                            <input type="number" name="fine_per_day" class="form-control form-control-custom"
                                value="<?php echo $settings['fine_per_day']; ?>" step="0.01">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Maximum Days Allowed</label>
                        <input type="number" name="max_days_allowed" class="form-control form-control-custom"
                            value="<?php echo $settings['max_days_allowed']; ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="form-label">Maximum Books Per Student</label>
                        <input type="number" name="max_books_allowed" class="form-control form-control-custom"
                            value="<?php echo $settings['max_books_allowed']; ?>">
                    </div>
                    <button type="submit" class="btn-university w-100 justify-content-center">Save
                        Configuration</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card-custom h-100">
                <h5 class="fw-bold mb-4">Library Branding</h5>
                <div class="text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-university fa-4x text-primary"></i>
                    </div>
                    <h6>University Digital Library</h6>
                    <p class="text-muted small">v1.0.0 Stable</p>
                    <button class="btn btn-outline-primary btn-sm rounded-pill px-4 mt-2">Change Logo</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>