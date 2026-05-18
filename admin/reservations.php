<?php
require_once '../config/db.php';
if (!isAdmin()) redirect('../index.php');
$pageTitle = 'Requested Books';
$reservations = $pdo->query("
    SELECT r.*, b.title as book_title, u.name as user_name 
    FROM reservations r 
    JOIN books b ON r.book_id = b.id 
    JOIN users u ON r.user_id = u.id 
    ORDER BY r.reservation_date DESC
")->fetchAll();
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Reservations</span> <span class="text-muted">Requests</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Requested Books</div>
    </div>
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Requested By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reservations): foreach ($reservations as $r): ?>
                    <tr>
                        <td><span class="fw-bold"><?php echo $r['book_title']; ?></span></td>
                        <td><?php echo $r['user_name']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($r['reservation_date'])); ?></td>
                        <td>
                            <span class="badge rounded-pill bg-<?php echo $r['status'] == 'pending' ? 'orange' : ($r['status'] == 'fulfilled' ? 'green' : 'red'); ?>">
                                <?php echo ucfirst($r['status']); ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($r['status'] == 'pending'): ?>
                                <form action="../actions/reservation_actions.php" method="POST" style="display:inline-block;margin:0;">
                                    <input type="hidden" name="action" value="fulfil">
                                    <input type="hidden" name="reservation_id" value="<?php echo $r['id']; ?>">
                                    <button type="submit" class="btn-university btn-sm"><i class="fas fa-check"></i> Fulfil</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">No pending requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
