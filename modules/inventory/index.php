<?php
require_once '../../includes/db.php';
requireRole(['Admin', 'Manager']);

// Handle Inventory Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_supplier') {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_info) VALUES (?, ?)");
        $stmt->execute([$_POST['name'], $_POST['contact_info']]);
        header("Location: index.php?msg=Supplier Added");
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] == 'add_ingredient') {
        $stmt = $pdo->prepare("INSERT INTO ingredients (name, unit, stock_level, reorder_level, supplier_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['name'],
            $_POST['unit'],
            $_POST['stock_level'],
            $_POST['reorder_level'],
            $_POST['supplier_id'] ?: null
        ]);
        header("Location: index.php?msg=Ingredient Added");
        exit;
    }
}

// Fetch Data
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();
$ingredients = $pdo->query("
    SELECT i.*, s.name as supplier_name 
    FROM ingredients i 
    LEFT JOIN suppliers s ON i.supplier_id = s.id
    ORDER BY i.name
")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Inventory Management</h2>
    <div>
        <button class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fa-solid fa-truck me-2"></i> Add Supplier
        </button>
        <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
            <i class="fa-solid fa-plus me-2"></i> Add Ingredient
        </button>
    </div>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3">Stock Overview</h5>
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>Ingredient</th>
                        <th>Supplier</th>
                        <th>Current Stock</th>
                        <th>Unit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ingredients as $i): 
                        $is_low = $i['stock_level'] <= $i['reorder_level'];
                    ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($i['name']) ?></td>
                            <td><?= htmlspecialchars($i['supplier_name'] ?? 'None') ?></td>
                            <td class="fw-bold <?= $is_low ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($i['stock_level'], 2) ?>
                            </td>
                            <td><?= htmlspecialchars($i['unit']) ?></td>
                            <td>
                                <?php if($is_low): ?>
                                    <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Low Stock</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Sufficient</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-edit"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($ingredients) === 0): ?>
                        <tr><td colspan="6" class="text-center">No inventory items found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_supplier">
            <div class="mb-3">
                <label class="form-label">Supplier Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Contact Info</label>
                <input type="text" name="contact_info" class="form-control">
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-custom">Save</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Add Ingredient Modal -->
<div class="modal fade" id="addIngredientModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Ingredient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_ingredient">
            <div class="mb-3">
                <label class="form-label">Ingredient Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Unit (e.g. Kg, L, Pcs)</label>
                    <input type="text" name="unit" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php foreach($suppliers as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Initial Stock</label>
                    <input type="number" step="0.01" name="stock_level" class="form-control" value="0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reorder Alert Level</label>
                    <input type="number" step="0.01" name="reorder_level" class="form-control" value="10" required>
                </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-custom">Save</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
