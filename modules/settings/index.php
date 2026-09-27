<?php
require_once '../../includes/db.php';
requireRole('Admin');

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_settings') {
    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
    
    foreach($_POST['settings'] as $key => $value) {
        $stmt->execute([$value, $key]);
    }
    
    header("Location: index.php?msg=Settings Updated Successfully");
    exit;
}

// Fetch Settings
$settings_query = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
$settings = [];
foreach($settings_query as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">System Settings</h2>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_GET['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 pt-4 pb-0">
                <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-store me-2"></i>Restaurant Information</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted fw-bold">Restaurant Name</label>
                        <input type="text" name="settings[restaurant_name]" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['restaurant_name'] ?? '') ?>" required>
                    </div>
                    
                    <h5 class="fw-bold mb-3 mt-5 text-primary"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Tax & Service Charges</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-bold">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="settings[tax_rate]" class="form-control" value="<?= htmlspecialchars($settings['tax_rate'] ?? '0') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted fw-bold">Currency Symbol</label>
                            <input type="text" name="settings[currency_symbol]" class="form-control" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '$') ?>">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary-custom py-3 fs-5">Save Configuration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
