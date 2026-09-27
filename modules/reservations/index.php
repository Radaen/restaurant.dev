<?php
require_once '../../includes/db.php';
requireLogin();

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add_reservation') {
        $stmt = $pdo->prepare("INSERT INTO reservations (customer_id, table_id, reservation_date, reservation_time, guests, status) VALUES (?, ?, ?, ?, ?, 'Confirmed')");
        $stmt->execute([
            $_POST['customer_id'],
            $_POST['table_id'],
            $_POST['reservation_date'],
            $_POST['reservation_time'],
            $_POST['guests']
        ]);
        
        // Mark table as reserved
        $pdo->prepare("UPDATE restaurant_tables SET status = 'Reserved' WHERE id = ?")->execute([$_POST['table_id']]);
        
        header("Location: index.php?msg=Reservation Confirmed");
        exit;
    }
}

// Fetch Data
$customers = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name")->fetchAll();
$tables = $pdo->query("SELECT id, table_number FROM restaurant_tables WHERE status = 'Available'")->fetchAll();

$reservations = $pdo->query("
    SELECT r.*, c.name as customer_name, c.phone, t.table_number 
    FROM reservations r
    JOIN customers c ON r.customer_id = c.id
    JOIN restaurant_tables t ON r.table_id = t.id
    ORDER BY r.reservation_date DESC, r.reservation_time DESC
")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Table Reservations</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addReservationModal">
        <i class="fa-solid fa-calendar-plus me-2"></i> New Reservation
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Customer</th>
                        <th>Table</th>
                        <th>Guests</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reservations as $r): ?>
                        <tr>
                            <td class="fw-bold">
                                <i class="fa-regular fa-calendar text-primary me-2"></i> <?= date('M d, Y', strtotime($r['reservation_date'])) ?> <br>
                                <i class="fa-regular fa-clock text-secondary me-2"></i> <?= date('h:i A', strtotime($r['reservation_time'])) ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($r['customer_name']) ?><br>
                                <small class="text-muted"><i class="fa-solid fa-phone"></i> <?= htmlspecialchars($r['phone']) ?></small>
                            </td>
                            <td class="fw-bold fs-5">T<?= htmlspecialchars($r['table_number']) ?></td>
                            <td><i class="fa-solid fa-user-group text-muted me-1"></i> <?= $r['guests'] ?> Pax</td>
                            <td>
                                <?php 
                                    $badge = 'bg-secondary';
                                    if($r['status'] == 'Confirmed') $badge = 'bg-primary';
                                    if($r['status'] == 'Completed') $badge = 'bg-success';
                                    if($r['status'] == 'Cancelled') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?= $badge ?>"><?= $r['status'] ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i></button>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-xmark"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(count($reservations) === 0): ?>
                        <tr><td colspan="6" class="text-center">No reservations found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Reservation Modal -->
<div class="modal fade" id="addReservationModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Reservation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_reservation">
            <div class="mb-3">
                <label class="form-label">Customer</label>
                <select name="customer_id" class="form-select" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text"><a href="../customers/index.php">Register new customer first</a> if they don't exist.</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Table (Available only)</label>
                <select name="table_id" class="form-select" required>
                    <option value="">-- Select Table --</option>
                    <?php foreach($tables as $t): ?>
                        <option value="<?= $t['id'] ?>">Table <?= htmlspecialchars($t['table_number']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="reservation_date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Time</label>
                    <input type="time" name="reservation_time" class="form-control" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" name="guests" class="form-control" value="2" required>
                </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-custom">Confirm Booking</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
