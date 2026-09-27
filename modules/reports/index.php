<?php
require_once '../../includes/db.php';
requireRole(['Admin', 'Manager']);

// Fetch Basic Reports Data
$today_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE DATE(created_at) = CURDATE() AND status = 'Paid'")->fetchColumn() ?: 0;
$week_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) AND status = 'Paid'")->fetchColumn() ?: 0;
$month_sales = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'Paid'")->fetchColumn() ?: 0;

// Top selling items
$top_items = $pdo->query("
    SELECT m.name, SUM(oi.quantity) as total_qty, SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN menu_items m ON oi.menu_item_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'Paid'
    GROUP BY m.id
    ORDER BY total_qty DESC
    LIMIT 5
")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">Reports & Analytics</h2>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <h5 class="opacity-75">Today's Sales</h5>
            <h1 class="display-5 fw-bold">$<?= number_format($today_sales, 2) ?></h1>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <h5 class="opacity-75">This Week</h5>
            <h1 class="display-5 fw-bold">$<?= number_format($week_sales, 2) ?></h1>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white;">
            <h5 class="opacity-75">This Month</h5>
            <h1 class="display-5 fw-bold">$<?= number_format($month_sales, 2) ?></h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-4">Top Selling Items</h5>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Quantity Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($top_items as $item): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($item['name']) ?></td>
                                    <td><?= $item['total_qty'] ?></td>
                                    <td class="text-success fw-bold">$<?= number_format($item['total_revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(count($top_items) === 0): ?>
                                <tr><td colspan="3" class="text-center">No sales data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Placeholder for future charts -->
        <div class="card border-0 shadow-sm rounded-4 h-100 d-flex align-items-center justify-content-center bg-light">
            <div class="text-center text-muted">
                <i class="fa-solid fa-chart-bar mb-3" style="font-size: 3rem; color: #ced4da;"></i>
                <h5>Detailed Charts Coming Soon</h5>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
