<?php
require_once '../config/db.php';
if (!isLoggedIn() || isAdmin()) redirect('../index.php');
$pageTitle = 'My Profile';
$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$_SESSION['user_id']]);
$u = $user->fetch();
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">My Profile</span> <span class="text-muted">Account Details</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Profile</div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card-custom text-center py-5">
                <div class="profile-icon-placeholder" style="width: 120px; height: 120px; font-size: 50px;">
                    <i class="fas fa-user"></i>
                </div>
                <h4 class="fw-bold"><?php echo $u['name']; ?></h4>
                <p class="text-muted"><?php echo ucfirst($u['role']); ?></p>
                <div class="badge bg-green px-3 py-2">Active Account</div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card-custom">
                <h5 class="fw-bold mb-4">Account Information</h5>
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" value="<?php echo $u['name']; ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" value="<?php echo $u['email']; ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">ID Code</label>
                    <input type="text" class="form-control" value="<?php echo $u['user_id_code']; ?>" readonly>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>