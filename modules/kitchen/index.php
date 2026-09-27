<?php
require_once '../../includes/db.php';
requireLogin();

// Update Order Status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'mark_ready') {
        $order_id = $_POST['order_id'];
        
        $pdo->beginTransaction();
        try {
            // Update order status
            $pdo->prepare("UPDATE orders SET status = 'Ready' WHERE id = ?")->execute([$order_id]);
            // Update all items to Ready
            $pdo->prepare("UPDATE order_items SET status = 'Ready' WHERE order_id = ?")->execute([$order_id]);
            $pdo->commit();
            header("Location: index.php?msg=Order #$order_id marked as Ready!");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error updating order: " . $e->getMessage();
        }
    }
}

// Fetch Pending Orders
$orders = $pdo->query("
    SELECT o.id, t.table_number, o.created_at, u.full_name as waiter_name 
    FROM orders o
    JOIN restaurant_tables t ON o.table_id = t.id
    JOIN users u ON o.user_id = u.id
    WHERE o.status = 'Kitchen'
    ORDER BY o.created_at ASC
")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Kitchen Display System (KOT)</h2>
    <a href="index.php" class="btn btn-outline-secondary"><i class="fa-solid fa-sync me-2"></i> Refresh</a>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if(count($orders) == 0): ?>
        <div class="col-12 text-center py-5">
            <i class="fa-solid fa-check-circle text-success" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">All caught up! No pending orders.</h4>
        </div>
    <?php else: ?>
        <?php foreach($orders as $o): 
            // Fetch items for this order
            $stmt = $pdo->prepare("
                SELECT oi.quantity, m.name, oi.notes 
                FROM order_items oi
                JOIN menu_items m ON oi.menu_item_id = m.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$o['id']]);
            $items = $stmt->fetchAll();
            
            // Calculate time elapsed
            $created = new DateTime($o['created_at']);
            $now = new DateTime();
            $diff = $now->diff($created);
            $minutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            $cardClass = 'border-primary';
            $headerClass = 'bg-primary text-white';
            if ($minutes > 15) {
                $cardClass = 'border-danger';
                $headerClass = 'bg-danger text-white';
            } else if ($minutes > 10) {
                $cardClass = 'border-warning';
                $headerClass = 'bg-warning text-dark';
            }
        ?>
        <div class="col-md-4 col-sm-6">
            <div class="card shadow-sm h-100 <?= $cardClass ?>" style="border-width: 2px;">
                <div class="card-header <?= $headerClass ?> d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Table <?= htmlspecialchars($o['table_number']) ?></h5>
                    <span class="badge bg-light text-dark">Order #<?= $o['id'] ?></span>
                </div>
                <div class="card-body bg-light">
                    <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                        <small class="text-muted"><i class="fa-regular fa-clock"></i> <?= $minutes ?> mins ago</small>
                        <small class="text-muted"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($o['waiter_name']) ?></small>
                    </div>
                    
                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach($items as $item): ?>
                            <li class="list-group-item bg-transparent px-0 border-light d-flex justify-content-between">
                                <div>
                                    <span class="fw-bold fs-5 me-2"><?= $item['quantity'] ?>x</span>
                                    <span class="fs-5"><?= htmlspecialchars($item['name']) ?></span>
                                    <?php if($item['notes']): ?>
                                        <div class="text-danger small ms-4"><i class="fa-solid fa-note-sticky"></i> <?= htmlspecialchars($item['notes']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-3">
                    <form method="POST">
                        <input type="hidden" name="action" value="mark_ready">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2">
                            <i class="fa-solid fa-bell-concierge me-2"></i> Mark as Ready
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>
