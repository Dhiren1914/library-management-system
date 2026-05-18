<?php
require_once '../config/db.php';

if (!isLoggedIn() || isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Library Catalog';

// Search logic
$search = sanitize($_GET['q'] ?? '');
$category = sanitize($_GET['category'] ?? '');

$query = "SELECT b.*, a.name AS author_name, c.name AS category_name, l.name AS location_name
          FROM books b
          LEFT JOIN authors a ON b.author_id = a.id
          LEFT JOIN categories c ON b.category_id = c.id
          LEFT JOIN locations l ON b.location_id = l.id
          WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (b.title LIKE ? OR a.name LIKE ? OR b.isbn LIKE ? )";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($category)) {
    // category filter is based on category name from the dropdown
    $query .= " AND c.name = ?";
    $params[] = $category;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <?php
        // Show alert if student has any pending reservation requests
        $pending_res = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE user_id = ? AND status = 'pending'");
        $pending_res->execute([$_SESSION['user_id']]);
        $pending_count = (int)$pending_res->fetchColumn();
        if ($pending_count > 0):
    ?>
        <div class="alert alert-info">You have <?php echo $pending_count; ?> pending reservation request(s). Check <a href="reservations.php">My Reservations</a>.</div>
    <?php endif; ?>
    <div class="dashboard-header">
        <div>
            <h2 class="page-title">Library Catalog</h2>
            <p class="text-muted">Explore our vast collection of books and resources.</p>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="card-custom mb-4 p-3">
        <form action="search.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Search by title, author, or ISBN..." value="<?php echo $search; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">All Categories</option>
                    <option value="Computer Science" <?php echo $category == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                    <option value="Mathematics" <?php echo $category == 'Mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                    <option value="Physics" <?php echo $category == 'Physics' ? 'selected' : ''; ?>>Physics</option>
                    <option value="Business" <?php echo $category == 'Business' ? 'selected' : ''; ?>>Business</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-university w-100">Search</button>
            </div>
            <div class="col-md-2">
                <a href="search.php" class="btn btn-light rounded-pill w-100">Clear</a>
            </div>
        </form>
    </div>

    <!-- Book Grid -->
    <div class="row g-4">
        <?php if ($books): ?>
            <?php foreach ($books as $book): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="card-custom h-100 p-0 overflow-hidden d-flex flex-column">
                        <div class="position-relative">
                            <?php if (!empty($book['cover_image']) && $book['cover_image'] != 'default_book.png'): ?>
                                <img src="../assets/img/<?php echo $book['cover_image']; ?>" class="w-100" style="height: 250px; object-fit: cover;" alt="<?php echo $book['title'] ?? ''; ?>">
                            <?php else: ?>
                                <div class="d-flex align-items-center justify-content-center bg-light" style="height:250px;">
                                    <i class="fas fa-book fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <?php if (($book['available_quantity'] ?? 0) > 0): ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-3 rounded-pill">Available</span>
                            <?php else: ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-3 rounded-pill">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 flex-grow-1">
                            <div class="text-muted small mb-1"><?php echo $book['category_name'] ?? $book['category'] ?? 'Uncategorized'; ?></div>
                            <h6 class="fw-bold text-primary mb-1 line-clamp-2"><?php echo $book['title'] ?? 'Untitled'; ?></h6>
                            <p class="text-muted small mb-3">by <?php echo $book['author_name'] ?? $book['author'] ?? 'Unknown'; ?></p>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="small fw-medium"><i class="fas fa-barcode me-1"></i> <?php echo $book['isbn'] ?? 'N/A'; ?></span>
                                <span class="small text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $book['location_name'] ?? $book['location'] ?? 'Unknown'; ?></span>
                            </div>
                        </div>
                        <div class="p-3 bg-light border-top mt-auto">
                            <?php if (($book['available_quantity'] ?? 0) > 0): ?>
                                <form action="../actions/reservation_actions.php" method="POST">
                                    <input type="hidden" name="action" value="reserve">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="btn-university btn-sm w-100 justify-content-center">Reserve Book</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary btn-sm w-100 justify-content-center" disabled>Notify Me</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-light mb-3"></i>
                <h5>No books found matching your criteria.</h5>
                <p class="text-muted">Try using different keywords or categories.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php include '../includes/footer.php'; ?>
