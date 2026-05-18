<?php
require_once '../config/db.php';
if (!isAdmin()) redirect('../index.php');
$pageTitle = 'Issued Books List';

$issues = $pdo->query("
    SELECT i.*, b.title as book_title, u.name as user_name, u.email as user_email, u.user_id_code, b.isbn
    FROM issues i 
    JOIN books b ON i.book_id = b.id 
    JOIN users u ON i.user_id = u.id 
    ORDER BY i.issue_date DESC
")->fetchAll();
$fine_rate = $pdo->query("SELECT fine_per_day FROM settings LIMIT 1")->fetchColumn() ?: 10;

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Issued</span> <span class="text-muted">Books List</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Issued Books</div>
    </div>
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>User</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($issues as $i): ?>
                    <?php
                        $display_status = getIssueDisplayStatus($i);
                        $issue_fine_rate = getIssueFineRate($i, $fine_rate);
                        $display_fine = $display_status === 'returned'
                            ? calculateFine($i['due_date'], $issue_fine_rate, $i['return_date'])
                            : calculateFine($i['due_date'], $issue_fine_rate);
                    ?>
                    <tr>
                        <td><span class="fw-bold"><?php echo $i['book_title']; ?></span></td>
                        <td><?php echo $i['user_name']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($i['issue_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($i['due_date'])); ?></td>
                        <td>
                            <span class="badge rounded-pill bg-<?php echo $display_status == 'overdue' ? 'red' : ($display_status == 'returned' ? 'green' : 'blue'); ?>">
                                <?php echo ucfirst($display_status); ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#detailModal<?php echo $i['id']; ?>">
                                Details
                            </button>
                        </td>
                    </tr>

                    <!-- Detail Modal for each issue -->
                    <div class="modal fade" id="detailModal<?php echo $i['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header border-0 p-4">
                                    <h5 class="modal-title fw-bold">Issue Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 pt-0">
                                    <div class="mb-4">
                                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Book Information</label>
                                        <div class="p-3 bg-light rounded">
                                            <h6 class="fw-bold mb-1"><?php echo $i['book_title']; ?></h6>
                                            <p class="text-muted small mb-0">ISBN: <?php echo $i['isbn']; ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">User Information</label>
                                        <div class="p-3 bg-light rounded">
                                            <h6 class="fw-bold mb-1"><?php echo $i['user_name']; ?></h6>
                                            <p class="text-muted small mb-1">ID: <?php echo $i['user_id_code']; ?></p>
                                            <p class="text-muted small mb-0">Email: <?php echo $i['user_email']; ?></p>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Issue Date</label>
                                            <p class="fw-bold"><?php echo date('M d, Y', strtotime($i['issue_date'])); ?></p>
                                        </div>
                                        <div class="col-6">
                                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Due Date</label>
                                            <p class="fw-bold"><?php echo date('M d, Y', strtotime($i['due_date'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Fine Per Day After Due Date</label>
                                        <p class="fw-bold">Rs <?php echo number_format($issue_fine_rate, 2); ?></p>
                                    </div>
                                    <div class="mt-3">
                                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Fine Applied</label>
                                        <p class="h4 fw-bold <?php echo $display_fine > 0 ? 'text-danger' : 'text-success'; ?>">Rs <?php echo number_format($display_fine, 2); ?></p>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
