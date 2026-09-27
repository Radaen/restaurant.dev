<?php
require_once '../../includes/db.php';
requireLogin();

// Handle Table Addition
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_table') {
        $stmt = $pdo->prepare("INSERT INTO restaurant_tables (table_number, capacity, status) VALUES (?, ?, 'Available')");
        $stmt->execute([$_POST['table_number'], $_POST['capacity']]);
        header("Location: index.php?msg=Table Added");
        exit;
    }
    
    // Handle Table Edit
    if (isset($_POST['action']) && $_POST['action'] == 'edit_table') {
        $stmt = $pdo->prepare("UPDATE restaurant_tables SET table_number = ?, capacity = ? WHERE id = ?");
        $stmt->execute([$_POST['table_number'], $_POST['capacity'], $_POST['id']]);
        header("Location: index.php?msg=Table Updated");
        exit;
    }
    
    // Handle Table Deletion
    if (isset($_POST['action']) && $_POST['action'] == 'delete_table') {
        $stmt = $pdo->prepare("DELETE FROM restaurant_tables WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        header("Location: index.php?msg=Table Deleted");
        exit;
    }
}

$tables = $pdo->query("SELECT * FROM restaurant_tables ORDER BY id")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Table Management</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addTableModal">
        <i class="fa-solid fa-plus me-2"></i> Add Table
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php foreach($tables as $t): 
        $colorClass = 'bg-success'; // Available
        if ($t['status'] == 'Occupied') $colorClass = 'bg-danger';
        if ($t['status'] == 'Reserved') $colorClass = 'bg-warning text-dark';
    ?>
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div class="<?= $colorClass ?> text-white rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <i class="fa-solid fa-chair"></i>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($t['table_number']) ?></h4>
                <p class="text-muted small mb-2">Capacity: <?= htmlspecialchars($t['capacity']) ?> Pax</p>
                <span class="badge <?= $colorClass ?>"><?= $t['status'] ?></span>
                
                <div class="mt-3 w-100 d-flex justify-content-center gap-2">
                    <?php if($t['status'] == 'Available'): ?>
                        <a href="/Restaurant/modules/pos/index.php?table_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-success">Take Order</a>
                    <?php else: ?>
                        <a href="/Restaurant/modules/pos/index.php?table_id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">View Order</a>
                    <?php endif; ?>
                    
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editTableModal<?= $t['id'] ?>"><i class="fa-solid fa-edit"></i></button>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this table?');" class="d-inline">
                        <input type="hidden" name="action" value="delete_table">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Table Modal -->
    <div class="modal fade" id="editTableModal<?= $t['id'] ?>" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold">Edit Table</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form method="POST">
              <div class="modal-body text-start">
                <input type="hidden" name="action" value="edit_table">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <div class="mb-3">
                    <label class="form-label">Table Number/Name</label>
                    <input type="text" name="table_number" class="form-control" value="<?= htmlspecialchars($t['table_number']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Capacity (Pax)</label>
                    <input type="number" name="capacity" class="form-control" value="<?= htmlspecialchars($t['capacity']) ?>" required>
                </div>
              </div>
              <div class="modal-footer border-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary-custom">Update Table</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add Table Modal -->
<div class="modal fade" id="addTableModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Table</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_table">
            <div class="mb-3">
                <label class="form-label">Table Number/Name</label>
                <input type="text" name="table_number" class="form-control" placeholder="e.g. T10, Balcony-1" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Capacity (Pax)</label>
                <input type="number" name="capacity" class="form-control" value="4" required>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-custom">Save Table</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
