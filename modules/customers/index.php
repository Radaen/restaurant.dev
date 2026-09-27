<?php
require_once '../../includes/db.php';
requireLogin();

// Handle Customer Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_customer') {
    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, loyalty_points) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([
            $_POST['name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['loyalty_points'] ?: 0
        ]);
        header("Location: index.php?msg=Customer Added Successfully");
        exit;
    } catch (PDOException $e) {
        $error = "Error adding customer: " . $e->getMessage();
    }
}

// Fetch Data
$customers = $pdo->query("SELECT * FROM customers ORDER BY created_at DESC")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Customer Management</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
        <i class="fa-solid fa-user-plus me-2"></i> Add Customer
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Loyalty Points</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): ?>
                        <tr>
                            <td class="fw-bold">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar shadow-sm" style="width: 35px; height: 35px; font-size: 1rem;">
                                        <?= strtoupper(substr($c['name'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($c['name']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= htmlspecialchars($c['email'] ?? 'N/A') ?></td>
                            <td>
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6 rounded-pill">
                                    <i class="fa-solid fa-star text-danger me-1"></i> <?= $c['loyalty_points'] ?>
                                </span>
                            </td>
                            <td class="text-muted"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-edit"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($customers) === 0): ?>
                        <tr><td colspan="6" class="text-center">No customers registered yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_customer">
            <div class="mb-3">
                <label class="form-label">Customer Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number (Unique)</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email (Optional)</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Initial Loyalty Points</label>
                <input type="number" name="loyalty_points" class="form-control" value="0">
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
