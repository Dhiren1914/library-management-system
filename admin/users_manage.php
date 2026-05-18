<?php
require_once '../config/db.php';
if (!isAdmin()) redirect('../index.php');
$pageTitle = 'Manage All Users';
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Users</span> <span class="text-muted">Management</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Manage Users</div>
    </div>
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>ID Code</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><span class="fw-bold"><?php echo $u['name']; ?></span><br><small class="text-muted"><?php echo $u['email']; ?></small></td>
                        <td><code><?php echo $u['user_id_code']; ?></code></td>
                        <td><span class="badge bg-light text-dark border"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><span class="text-success"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> <?php echo ucfirst($u['status']); ?></span></td>
                        <td class="text-end">
                            <a href="../actions/user_actions.php?action=delete&id=<?php echo $u['id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Are you sure you want to delete this user?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
