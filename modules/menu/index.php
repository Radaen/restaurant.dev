<?php
require_once '../../includes/db.php';
requireLogin();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_category') {
        $stmt = $pdo->prepare("INSERT INTO menu_categories (name, color) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['color']]);
        header("Location: index.php?msg=Category Added");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'add_item') {
        $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['category_id'],
            $_POST['name'],
            $_POST['description'],
            $_POST['price'],
            $_POST['status']
        ]);
        header("Location: index.php?msg=Item Added");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'delete_item') {
        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: index.php?msg=Item Deleted");
        exit;
    }
}

// Fetch Data
$categories = $pdo->query("SELECT * FROM menu_categories")->fetchAll();
$items = $pdo->query("
    SELECT m.*, c.name as category_name, c.color as category_color 
    FROM menu_items m 
    JOIN menu_categories c ON m.category_id = c.id
    ORDER BY c.name, m.name
")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Menu Management</h2>
    <div>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa-solid fa-folder-plus me-2"></i> Add Category
        </button>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal" style="border-radius: 10px;">
            <i class="fa-solid fa-plus me-2"></i> Add Menu Item
        </button>
    </div>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-custom table-hover">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($items as $item): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($item['name']) ?></td>
                                    <td>
                                        <span class="badge" style="background-color: <?= htmlspecialchars($item['category_color']) ?>">
                                            <?= htmlspecialchars($item['category_name']) ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= htmlspecialchars($item['description']) ?></td>
                                    <td class="fw-bold text-success">$<?= number_format($item['price'], 2) ?></td>
                                    <td>
                                        <?php if($item['status'] == 'Available'): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Not Available</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-edit"></i></button>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this item?');" class="d-inline">
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(count($items) === 0): ?>
                                <tr><td colspan="6" class="text-center">No menu items found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_category">
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Badge Color</label>
                <input type="color" name="color" class="form-control form-control-color w-100" value="#ff7e5f" required>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-custom">Save Category</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Menu Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_item">
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <?php foreach($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Item Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Price ($)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Available">Available</option>
                        <option value="Not Available">Not Available</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success" style="border-radius: 10px;">Save Item</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
