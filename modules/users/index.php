<?php
require_once '../../includes/db.php';
requireRole(['Admin', 'Manager']); // Only Admin & Manager can see users

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add_user') {
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name, status) VALUES (?, ?, ?, ?, ?)");
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $_POST['username'],
            $hash,
            $_POST['role'],
            $_POST['full_name'],
            $_POST['status']
        ]);
        header("Location: index.php?msg=User Added");
        exit;
    }
    
    // Handle Edit User
    if (isset($_POST['action']) && $_POST['action'] == 'edit_user') {
        if (!empty($_POST['password'])) {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, role=?, status=?, password_hash=? WHERE id=?");
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt->execute([$_POST['full_name'], $_POST['username'], $_POST['role'], $_POST['status'], $hash, $_POST['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, role=?, status=? WHERE id=?");
            $stmt->execute([$_POST['full_name'], $_POST['username'], $_POST['role'], $_POST['status'], $_POST['id']]);
        }
        header("Location: index.php?msg=User Updated");
        exit;
    }
    
    // Handle Delete User
    if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
        if ($_POST['id'] != 1 && $_POST['id'] != $_SESSION['user_id']) { // Protect main admin and current user
            try {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                header("Location: index.php?msg=User Deleted");
            } catch (PDOException $e) {
                if ($e->getCode() == '23000') {
                    header("Location: index.php?msg=Cannot delete user: They are linked to existing orders. Please deactivate their status instead.");
                } else {
                    header("Location: index.php?msg=An error occurred while deleting the user.");
                }
            }
        } else {
            header("Location: index.php?msg=Cannot delete primary admin or yourself");
        }
        exit;
    }
}

$users = $pdo->query("SELECT id, username, role, full_name, status, created_at FROM users")->fetchAll();

include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">User Management</h2>
    <button class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fa-solid fa-user-plus me-2"></i> Add Staff
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td>
                                <?php
                                    $badge = 'bg-secondary';
                                    if($u['role'] == 'Admin') $badge = 'bg-danger';
                                    if($u['role'] == 'Manager') $badge = 'bg-primary';
                                    if($u['role'] == 'Waitstaff') $badge = 'bg-info';
                                    if($u['role'] == 'Chef') $badge = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?= $badge ?>"><?= $u['role'] ?></span>
                            </td>
                            <td>
                                <?php if($u['status'] == 'Active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>"><i class="fa-solid fa-edit"></i></button>
                                
                                <?php $can_delete = ($u['id'] != 1 && $u['id'] != $_SESSION['user_id']); ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" class="d-inline">
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" <?= $can_delete ? '' : 'disabled title="Cannot delete primary admin or your own account"' ?>><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit User Modal -->
                        <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content rounded-4 border-0 shadow">
                              <div class="modal-header border-0">
                                <h5 class="modal-title fw-bold">Edit Staff Member</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <form method="POST">
                                  <div class="modal-body text-start">
                                    <input type="hidden" name="action" value="edit_user">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($u['full_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($u['username']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password <small class="text-muted">(Leave blank to keep current)</small></label>
                                        <input type="password" name="password" class="form-control">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Role</label>
                                            <select name="role" class="form-select" required>
                                                <option value="Waiter" <?= $u['role']=='Waiter'?'selected':'' ?>>Waiter</option>
                                                <option value="Chef" <?= $u['role']=='Chef'?'selected':'' ?>>Chef</option>
                                                <option value="Cashier" <?= $u['role']=='Cashier'?'selected':'' ?>>Cashier</option>
                                                <option value="Manager" <?= $u['role']=='Manager'?'selected':'' ?>>Manager</option>
                                                <option value="Admin" <?= $u['role']=='Admin'?'selected':'' ?>>Admin</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="Active" <?= $u['status']=='Active'?'selected':'' ?>>Active</option>
                                                <option value="Inactive" <?= $u['status']=='Inactive'?'selected':'' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                  </div>
                                  <div class="modal-footer border-0">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary-custom">Update User</button>
                                  </div>
                              </form>
                            </div>
                          </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">New Staff Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
          <div class="modal-body">
            <input type="hidden" name="action" value="add_user">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select" required>
                        <option value="Waiter">Waiter</option>
                        <option value="Chef">Chef</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Manager">Manager</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-custom">Save User</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include '../../includes/footer.php'; ?>
