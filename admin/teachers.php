<?php
require_once '../config/db.php';
if (!isAdmin()) redirect('../index.php');
$pageTitle = 'Teacher Information';
$teachers = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY name ASC")->fetchAll();
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Teacher</span> <span class="text-muted">Information</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Teachers</div>
    </div>
    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Teacher ID</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $t): ?>
                    <tr>
                        <td><span class="fw-bold"><?php echo $t['name']; ?></span></td>
                        <td><code><?php echo $t['user_id_code']; ?></code></td>
                        <td><?php echo $t['email']; ?></td>
                        <td><?php echo $t['phone']; ?></td>
                        <td class="text-end">
                            <a href="../actions/user_actions.php?action=delete&id=<?php echo $t['id']; ?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Are you sure you want to delete this teacher?')">
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
