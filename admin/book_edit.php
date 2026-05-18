<?php
require_once '../config/db.php';
if (!isAdmin()) redirect('../index.php');

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) {
    setFlash('danger', 'Book not found.');
    redirect('books.php');
}

$pageTitle = 'Edit Book';
include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Edit Book</span> <span class="text-muted"><?php echo $book['title']; ?></span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Edit Book</div>
    </div>

    <div class="card-custom" style="max-width: 800px; margin: 0 auto;">
        <form action="../actions/book_actions.php" method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
            
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Book Title</label>
                    <input type="text" name="title" class="form-control" value="<?php echo $book['title']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Author</label>
                    <select name="author_id" class="form-control mb-2">
                        <option value="">-- Select or Add New Below --</option>
                        <?php foreach ($pdo->query("SELECT * FROM authors") as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo $book['author_id'] == $a['id'] ? 'selected' : ''; ?>><?php echo $a['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="new_author" class="form-control form-control-sm" placeholder="Or type new author name...">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-control mb-2">
                        <option value="">-- Select or Add New Below --</option>
                        <?php foreach ($pdo->query("SELECT * FROM categories") as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo $book['category_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo $c['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="new_category" class="form-control form-control-sm" placeholder="Or type new category...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ISBN</label>
                    <input type="text" name="isbn" class="form-control" value="<?php echo $book['isbn']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" value="<?php echo $book['quantity']; ?>" min="1">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Location</label>
                    <select name="location_id" class="form-control mb-2">
                        <option value="">-- Select or Add New Below --</option>
                        <?php foreach ($pdo->query("SELECT * FROM locations") as $l): ?>
                            <option value="<?php echo $l['id']; ?>" <?php echo $book['location_id'] == $l['id'] ? 'selected' : ''; ?>><?php echo $l['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="new_location" class="form-control form-control-sm" placeholder="Or type new location...">
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <a href="books.php" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">Update Book</button>
            </div>
        </form>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
