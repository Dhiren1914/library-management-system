<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Student Records';

// Fetch students
$students = $pdo->query("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">Student Records</h2>
            <p class="text-muted">View and manage student registrations and borrowing eligibility.</p>
        </div>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Books Held</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students): ?>
                        <?php foreach ($students as $student):
                            // Count currently issued books for this student
                            $books_held = $pdo->query("SELECT COUNT(*) FROM issues WHERE user_id = {$student['id']} AND status = 'issued'")->fetchColumn();
                            ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; color: var(--navy-sidebar);">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div class="fw-bold"><?php echo $student['name']; ?></div>
                                    </div>
                                </td>
                                <td><code class="small"><?php echo $student['user_id_code']; ?></code></td>
                                <td><?php echo $student['email']; ?></td>
                                <td><?php echo $student['phone'] ?: '-'; ?></td>
                                <td>
                                    <span class="badge <?php echo $books_held > 3 ? 'bg-danger' : 'bg-light text-dark'; ?>">
                                        <?php echo $books_held; ?> Books
                                    </span>
                                </td>
                                <td><span class="badge bg-success rounded-pill">Active</span></td>
                                <td class="small text-muted"><?php echo formatDate($student['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No students registered yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>