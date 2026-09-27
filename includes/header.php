<?php
// includes/header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$base_url = '/Restaurant';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant - Management System</title>
    <link rel="icon" href="<?= $base_url ?>/assets/img/restaurant.ico" type="image/x-icon">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body>
    
<?php include 'sidebar.php'; ?>

<div class="main-content">
    <div class="top-header">
        <div class="d-flex align-items-center">
            <button class="btn text-white me-3 d-md-none border-0" id="sidebarToggle" style="font-size: 1.5rem;">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h4 class="mb-0 text-dark fw-bold">Dashboard</h4>
        </div>
        
        <div class="user-profile dropdown">
            <div class="d-flex align-items-center dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="text-end me-3">
                    <h6 class="mb-0 fw-bold"><?= htmlspecialchars($_SESSION['full_name'] ?? 'Guest') ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></small>
                </div>
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['full_name'] ?? 'G', 0, 1)) ?>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                <li><a class="dropdown-item" href="<?= $base_url ?>/modules/users/index.php"><i class="fa-solid fa-user me-2 text-primary"></i>Profile</a></li>
                <li><a class="dropdown-item" href="<?= $base_url ?>/modules/settings/index.php"><i class="fa-solid fa-cog me-2 text-secondary"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= $base_url ?>/auth/logout.php"><i class="fa-solid fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </div>
