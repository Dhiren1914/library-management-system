<?php
require_once '../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'My Reservations';
$user_id = $_SESSION['user_id'];

$reservations = $pdo->prepare("SELECT r.*, b.title as book_title FROM reservations r JOIN books b ON r.book_id = b.id WHERE r.user_id = ? ORDER BY r.reservation_date DESC");
$reservations->execute([$user_id]);
$res = $reservations->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">My Reservations</span> <span class="text-muted">Requested Books</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Reservations</div>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res): foreach ($res as $r): ?>
                    <tr>
                        <td><span class="fw-bold"><?php echo $r['book_title']; ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($r['reservation_date'])); ?></td>
                        <td><span class="badge rounded-pill bg-<?php echo $r['status']=='pending'?'orange':($r['status']=='fulfilled'?'green':'red'); ?>"><?php echo ucfirst($r['status']); ?></span></td>
                        <td class="text-end">
                            <?php if ($r['status']=='pending'): ?>
                                <form action="../actions/reservation_actions.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="action" value="cancel">
                                    <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this reservation?')">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">No reservations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
