<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$base_url = '/Restaurant';

// Fetch Restaurant Name
$res_name_query = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'restaurant_name'")->fetchColumn();
$display_restaurant_name = $res_name_query ? $res_name_query : 'Restaurant';

function isActive($dir, $page = null) {
    global $current_dir, $current_page;
    if ($page) {
        return ($current_dir == $dir && $current_page == $page) ? 'active' : '';
    }
    return ($current_dir == $dir) ? 'active' : '';
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fa-solid fa-utensils me-2"></i><?= htmlspecialchars($display_restaurant_name) ?></h3>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="<?= $base_url ?>/index.php" class="<?= isActive('Restaurant', 'index.php') ?>">
                <i class="fa-solid fa-chart-pie"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/pos/index.php" class="<?= isActive('pos') ?>">
                <i class="fa-solid fa-cash-register"></i> POS System
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/kitchen/index.php" class="<?= isActive('kitchen') ?>">
                <i class="fa-solid fa-fire-burner"></i> Kitchen (KOT)
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/tables/index.php" class="<?= isActive('tables') ?>">
                <i class="fa-solid fa-chair"></i> Tables
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/menu/index.php" class="<?= isActive('menu') ?>">
                <i class="fa-solid fa-book-open"></i> Menu Items
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/inventory/index.php" class="<?= isActive('inventory') ?>">
                <i class="fa-solid fa-boxes-stacked"></i> Inventory
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/customers/index.php" class="<?= isActive('customers') ?>">
                <i class="fa-solid fa-address-book"></i> Customers
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/reservations/index.php" class="<?= isActive('reservations') ?>">
                <i class="fa-solid fa-calendar-check"></i> Reservations
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/billing/index.php" class="<?= isActive('billing') ?>">
                <i class="fa-solid fa-file-invoice-dollar"></i> Billing
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/users/index.php" class="<?= isActive('users') ?>">
                <i class="fa-solid fa-users"></i> Users
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/reports/index.php" class="<?= isActive('reports') ?>">
                <i class="fa-solid fa-chart-line"></i> Reports
            </a>
        </li>
        <li>
            <a href="<?= $base_url ?>/modules/settings/index.php" class="<?= isActive('settings') ?>">
                <i class="fa-solid fa-cog"></i> Settings
            </a>
        </li>
    </ul>
</div>
