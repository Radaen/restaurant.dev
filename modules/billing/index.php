<?php
require_once '../../includes/db.php';
requireLogin();

// Fetch Settings for taxes and currency
$settings_query = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$settings = [];
foreach($settings_query as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$tax_rate = (float)($settings['tax_rate'] ?? 0);
$currency = $settings['currency_symbol'] ?? '$';

// Handle Payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'process_payment') {
    $order_id = $_POST['order_id'];
    $discount = (float)$_POST['discount'];
    
    $pdo->beginTransaction();
    try {
        // Calculate tax based on current amount minus discount
        $stmt = $pdo->prepare("SELECT total_amount, table_id FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        $tax_amount = ($order['total_amount'] - $discount) * ($tax_rate / 100);
        $final_total = ($order['total_amount'] - $discount) + $tax_amount;
        
        // Update Order
        $pdo->prepare("UPDATE orders SET status = 'Paid', tax_amount = ?, discount_amount = ?, total_amount = ? WHERE id = ?")
            ->execute([$tax_amount, $discount, $final_total, $order_id]);
        
        // Free the table if applicable
        if ($order['table_id']) {
            $pdo->prepare("UPDATE restaurant_tables SET status = 'Available' WHERE id = ?")->execute([$order['table_id']]);
        }
        
        $pdo->commit();
        header("Location: index.php?msg=Payment processed successfully!&print_id=$order_id");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error processing payment: " . $e->getMessage();
    }
}

// Fetch Orders ready for billing (Status: Ready, Served, Kitchen for fast-food)
$orders = $pdo->query("
    SELECT o.id, t.table_number, o.order_type, o.total_amount, o.status, o.created_at, c.name as customer_name
    FROM orders o
    LEFT JOIN restaurant_tables t ON o.table_id = t.id
    LEFT JOIN customers c ON o.customer_id = c.id
    WHERE o.status IN ('Ready', 'Served', 'Kitchen') 
    ORDER BY o.created_at ASC
")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Billing & Payment</h2>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <?php if(isset($_GET['print_id'])): ?>
            <a href="javascript:void(0)" onclick="window.print()" class="btn btn-sm btn-light ms-3"><i class="fa-solid fa-print"></i> Print Receipt #<?= $_GET['print_id'] ?></a>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 d-print-none">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-custom table-hover">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Type / Table</th>
                        <th>Customer</th>
                        <th>Time</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td class="fw-bold">
                                <?php if($o['order_type'] == 'Dine-in'): ?>
                                    <span class="badge bg-primary">Dine-in</span> T<?= htmlspecialchars($o['table_number']) ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $o['order_type'] ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($o['customer_name'] ?? 'Guest') ?></td>
                            <td class="text-muted"><?= date('h:i A', strtotime($o['created_at'])) ?></td>
                            <td class="fw-bold text-success fs-5"><?= $currency ?><?= number_format($o['total_amount'], 2) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary-custom" data-bs-toggle="modal" data-bs-target="#payModal<?= $o['id'] ?>">
                                    <i class="fa-solid fa-money-bill-wave me-1"></i> Checkout
                                </button>
                            </td>
                        </tr>
                        
                        <!-- Payment Modal -->
                        <div class="modal fade" id="payModal<?= $o['id'] ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content rounded-4 border-0 shadow">
                              <div class="modal-header border-0 bg-light">
                                <h5 class="modal-title fw-bold">Process Payment - Order #<?= $o['id'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <form method="POST">
                                  <div class="modal-body p-4">
                                    <input type="hidden" name="action" value="process_payment">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Subtotal</span>
                                        <span class="fw-bold"><?= $currency ?><?= number_format($o['total_amount'], 2) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 text-muted">
                                        <span>Tax (<?= $tax_rate ?>%)</span>
                                        <span>+ <?= $currency ?><?= number_format($o['total_amount'] * ($tax_rate/100), 2) ?></span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Discount Amount (<?= $currency ?>)</label>
                                        <input type="number" step="0.01" name="discount" class="form-control" value="0.00">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label">Payment Method</label>
                                        <select class="form-select" required>
                                            <option value="Cash">Cash</option>
                                            <option value="Credit Card">Credit Card</option>
                                            <option value="Digital Wallet">Digital Wallet</option>
                                        </select>
                                    </div>
                                  </div>
                                  <div class="modal-footer border-0 d-flex">
                                    <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success flex-grow-1 fs-5 py-2" style="border-radius: 10px;">
                                        Confirm Payment
                                    </button>
                                  </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if(count($orders) === 0): ?>
                        <tr><td colspan="6" class="text-center py-4">No active orders to bill.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Simple Print Receipt Styles -->
<style>
@media print {
    body * { visibility: hidden; }
    .alert-success, .alert-success * { visibility: visible; }
    .alert-success { position: absolute; left: 0; top: 0; width: 100%; background: white !important; border: none !important; }
}
</style>

<?php include '../../includes/footer.php'; ?>
