<?php
require_once '../config/db.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$pageTitle = 'Manage Book';

// Fetch books with JOINs for human-readable labels
$books = $pdo->query("
    SELECT b.*, a.name as author_name, c.name as category_name, l.name as location_name 
    FROM books b 
    LEFT JOIN authors a ON b.author_id = a.id 
    LEFT JOIN categories c ON b.category_id = c.id 
    LEFT JOIN locations l ON b.location_id = l.id 
    ORDER BY b.created_at DESC
")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<div class="main-content">
    <div class="breadcrumb-section">
        <div><span class="fw-bold">Books</span> <span class="text-muted">Inventory</span></div>
        <div><i class="fas fa-home"></i> Home <i class="fas fa-chevron-right mx-1" style="font-size: 10px;"></i> Manage Book</div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Book Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
            <i class="fas fa-plus me-2"></i> Add New Book
        </button>
    </div>

    <?php displayFlash(); ?>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Stock</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($books): ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <?php if ($book['cover_image'] && $book['cover_image'] != 'default_book.png'): ?>
                                        <img src="../assets/img/<?php echo $book['cover_image']; ?>" alt="Cover" class="rounded" width="40" height="55" style="object-fit: cover;">
                                    <?php else: ?>
                                        <div class="img-placeholder">
                                            <i class="fas fa-book"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="fw-bold"><?php echo $book['title']; ?></span><br><small class="text-muted"><?php echo $book['isbn']; ?></small></td>
                                <td><?php echo $book['author_name'] ?: 'Unknown'; ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $book['category_name'] ?: 'None'; ?></span></td>
                                <td><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?php echo $book['location_name'] ?: 'N/A'; ?></td>
                                <td>
                                    <div class="fw-medium"><?php echo $book['available_quantity']; ?> / <?php echo $book['quantity']; ?></div>
                                    <div class="progress mt-1" style="height: 4px; width: 60px;">
                                        <?php $perc = ($book['available_quantity'] / $book['quantity']) * 100; ?>
                                        <div class="progress-bar bg-green" role="progressbar" style="width: <?php echo $perc; ?>%"></div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="book_edit.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-light rounded-circle me-1" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                                        <a href="../actions/book_actions.php?action=delete&id=<?php echo $book['id']; ?>" class="btn btn-sm btn-light rounded-circle" title="Delete" onclick="return confirm('Delete this book?')"><i class="fas fa-trash text-danger"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-5 text-muted">No books found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Add New Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="../actions/book_actions.php" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Book Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Author</label>
                            <select name="author_id" class="form-control mb-2">
                                <option value="">-- Select or Add New Below --</option>
                                <?php foreach ($pdo->query("SELECT * FROM authors") as $a): ?>
                                    <option value="<?php echo $a['id']; ?>"><?php echo $a['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_author" class="form-control form-control-sm" placeholder="Or type new author name...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-control mb-2">
                                <option value="">-- Select or Add New Below --</option>
                                <?php foreach ($pdo->query("SELECT * FROM categories") as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_category" class="form-control form-control-sm" placeholder="Or type new category...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Location</label>
                            <select name="location_id" class="form-control mb-2">
                                <option value="">-- Select or Add New Below --</option>
                                <?php foreach ($pdo->query("SELECT * FROM locations") as $l): ?>
                                    <option value="<?php echo $l['id']; ?>"><?php echo $l['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="text" name="new_location" class="form-control form-control-sm" placeholder="Or type new location...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>