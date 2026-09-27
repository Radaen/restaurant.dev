<?php
require_once 'includes/db.php';
requireLogin();

// Fetch some basic stats
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'Paid'")->fetchColumn();
$active_tables = $pdo->query("SELECT COUNT(*) FROM restaurant_tables WHERE status = 'Occupied'")->fetchColumn();
$menu_items = $pdo->query("SELECT COUNT(*) FROM menu_items")->fetchColumn();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card bg-gradient-1 text-white">
            <div class="stat-info">
                <h4 class="text-white">Today's Orders</h4>
                <h2><?= number_format((float)$total_orders) ?></h2>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-card bg-gradient-2 text-white">
            <div class="stat-info">
                <h4 class="text-white">Revenue</h4>
                <h2>$<?= number_format((float)$revenue, 2) ?></h2>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-dollar-sign"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-card bg-gradient-3 text-white">
            <div class="stat-info">
                <h4 class="text-white">Active Tables</h4>
                <h2><?= number_format((float)$active_tables) ?></h2>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-chair"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="stat-card bg-gradient-4 text-white">
            <div class="stat-info">
                <h4 class="text-white">Menu Items</h4>
                <h2><?= number_format((float)$menu_items) ?></h2>
            </div>
            <div class="stat-icon">
                <i class="fa-solid fa-burger"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Table</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent_orders = $pdo->query("
                                SELECT o.id, t.table_number, o.total_amount, o.status 
                                FROM orders o
                                JOIN restaurant_tables t ON o.table_id = t.id
                                ORDER BY o.id DESC LIMIT 5
                            ")->fetchAll();
                            
                            if (count($recent_orders) > 0) {
                                foreach ($recent_orders as $order) {
                                    $statusClass = 'bg-secondary';
                                    if ($order['status'] == 'Pending') $statusClass = 'bg-warning';
                                    if ($order['status'] == 'Kitchen') $statusClass = 'bg-info';
                                    if ($order['status'] == 'Served') $statusClass = 'bg-primary';
                                    if ($order['status'] == 'Paid') $statusClass = 'bg-success';
                                    
                                    echo "<tr>
                                        <td>#{$order['id']}</td>
                                        <td>{$order['table_number']}</td>
                                        <td>$" . number_format($order['total_amount'], 2) . "</td>
                                        <td><span class='badge {$statusClass}'>{$order['status']}</span></td>
                                        <td><a href='/Restaurant/modules/pos/index.php?order_id={$order['id']}' class='btn btn-sm btn-outline-primary'>View</a></td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No orders today yet.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Quick Actions</h5>
                <div class="d-grid gap-3">
                    <a href="/Restaurant/modules/pos/index.php" class="btn btn-primary-custom py-3 text-start"><i class="fa-solid fa-plus-circle me-2"></i> New Order (POS)</a>
                    <a href="/Restaurant/modules/tables/index.php" class="btn btn-outline-secondary py-3 text-start"><i class="fa-solid fa-chair me-2"></i> Manage Tables</a>
                    <a href="/Restaurant/modules/kitchen/index.php" class="btn btn-outline-danger py-3 text-start"><i class="fa-solid fa-fire-burner me-2"></i> Kitchen Display</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
