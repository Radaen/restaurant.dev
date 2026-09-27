<?php
require_once '../../includes/db.php';
requireLogin();

// Fetch Tables
$tables = $pdo->query("SELECT id, table_number FROM restaurant_tables WHERE status IN ('Available', 'Occupied', 'Reserved')")->fetchAll();

// Fetch Customers
$customers = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name")->fetchAll();

// Fetch Menu Items
$items = $pdo->query("SELECT id, name, price, category_id FROM menu_items WHERE status = 'Available'")->fetchAll();

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'place_order') {
    $table_id = !empty($_POST['table_id']) ? $_POST['table_id'] : null;
    $customer_id = !empty($_POST['customer_id']) ? $_POST['customer_id'] : null;
    $order_type = $_POST['order_type'] ?? 'Dine-in';
    $cart = json_decode($_POST['cart_data'], true);
    
    if(count($cart) > 0) {
        $pdo->beginTransaction();
        try {
            $order_id = null;
            
            // If Dine-in and Table selected, check for existing order
            if ($order_type == 'Dine-in' && $table_id) {
                $stmt = $pdo->prepare("SELECT id FROM orders WHERE table_id = ? AND status NOT IN ('Paid', 'Cancelled') LIMIT 1");
                $stmt->execute([$table_id]);
                $existing_order = $stmt->fetch();
                if ($existing_order) $order_id = $existing_order['id'];
            }
            
            if (!$order_id) {
                $stmt = $pdo->prepare("INSERT INTO orders (table_id, customer_id, user_id, order_type, status) VALUES (?, ?, ?, ?, 'Kitchen')");
                $stmt->execute([$table_id, $customer_id, $_SESSION['user_id'], $order_type]);
                $order_id = $pdo->lastInsertId();
                
                if ($table_id) {
                    $pdo->prepare("UPDATE restaurant_tables SET status = 'Occupied' WHERE id = ?")->execute([$table_id]);
                }
            }
            
            // Insert Items
            $item_stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price, notes, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
            $total_added = 0;
            
            foreach($cart as $item) {
                $item_stmt->execute([
                    $order_id,
                    $item['id'],
                    $item['qty'],
                    $item['price'],
                    $item['notes'] ?? ''
                ]);
                $total_added += ($item['price'] * $item['qty']);
            }
            
            // Update order total
            $pdo->prepare("UPDATE orders SET total_amount = total_amount + ? WHERE id = ?")->execute([$total_added, $order_id]);
            
            // Loyalty points update
            if ($customer_id) {
                $points = floor($total_added / 10); // $10 = 1 pt
                if ($points > 0) {
                    $pdo->prepare("UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?")->execute([$points, $customer_id]);
                }
            }
            
            $pdo->commit();
            header("Location: index.php?msg=Order sent to kitchen!");
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to place order: " . $e->getMessage();
        }
    }
}

include '../../includes/header.php';
?>
<style>
    .pos-item { cursor: pointer; transition: transform 0.1s; }
    .pos-item:active { transform: scale(0.95); }
    .cart-container { height: calc(100vh - 150px); overflow-y: auto; background: white; border-radius: 15px; }
    .items-container { height: calc(100vh - 150px); overflow-y: auto; }
</style>

<div class="row">
    <!-- Menu Items Section -->
    <div class="col-md-7 items-container pe-4">
        <h3 class="fw-bold mb-3">POS Menu</h3>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>
        
        <div class="row g-3">
            <?php foreach($items as $item): ?>
            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 pos-item" onclick="addToCart(<?= $item['id'] ?>, '<?= addslashes($item['name']) ?>', <?= $item['price'] ?>)">
                    <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
                        <h6 class="fw-bold mb-2"><?= htmlspecialchars($item['name']) ?></h6>
                        <span class="badge bg-primary fs-6">$<?= number_format($item['price'], 2) ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Current Order Section -->
    <div class="col-md-5">
        <div class="cart-container shadow-sm p-4 d-flex flex-column">
            <h4 class="fw-bold mb-3 border-bottom pb-2"><i class="fa-solid fa-receipt me-2"></i>Current Order</h4>
            
            <form method="POST" id="orderForm" class="flex-grow-1 d-flex flex-column">
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="cart_data" id="cartData">
                
                <div class="row mb-3 g-2">
                    <div class="col-6">
                        <label class="form-label fw-bold">Order Type</label>
                        <select name="order_type" class="form-select form-select-sm" onchange="toggleTableReq(this.value)">
                            <option value="Dine-in">Dine-in</option>
                            <option value="Takeaway">Takeaway</option>
                            <option value="Delivery">Delivery</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">Select Table</label>
                        <select name="table_id" id="table_id" class="form-select form-select-sm" required>
                            <option value="">-- Table --</option>
                            <?php foreach($tables as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= (isset($_GET['table_id']) && $_GET['table_id']==$t['id']) ? 'selected' : '' ?>>
                                    Table <?= htmlspecialchars($t['table_number']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="form-label fw-bold">Customer (Optional)</label>
                        <select name="customer_id" class="form-select form-select-sm">
                            <option value="">-- Guest --</option>
                            <?php foreach($customers as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> (<?= htmlspecialchars($c['phone']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div id="cartItems" class="flex-grow-1 overflow-auto mb-3 border rounded p-2 bg-light">
                    <!-- Cart items injected here via JS -->
                    <div class="text-center text-muted mt-5">No items added yet.</div>
                </div>
                
                <div class="border-top pt-3 mt-auto">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-5 text-success" id="cartTotal">$0.00</span>
                    </div>
                    <button type="button" onclick="submitOrder()" class="btn btn-primary-custom w-100 py-3 fs-5">
                        <i class="fa-solid fa-paper-plane me-2"></i> Send to Kitchen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(id, name, price) {
    let existing = cart.find(i => i.id === id);
    if(existing) {
        existing.qty++;
    } else {
        cart.push({id: id, name: name, price: price, qty: 1, notes: ''});
    }
    renderCart();
}

function updateQty(id, delta) {
    let item = cart.find(i => i.id === id);
    if(item) {
        item.qty += delta;
        if(item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        renderCart();
    }
}

function renderCart() {
    let container = document.getElementById('cartItems');
    let totalEl = document.getElementById('cartTotal');
    
    if(cart.length === 0) {
        container.innerHTML = '<div class="text-center text-muted mt-5">No items added yet.</div>';
        totalEl.innerText = '$0.00';
        return;
    }
    
    let html = '';
    let total = 0;
    
    cart.forEach(item => {
        let itemTotal = item.price * item.qty;
        total += itemTotal;
        
        html += `
        <div class="d-flex justify-content-between align-items-center bg-white p-2 mb-2 rounded shadow-sm border">
            <div>
                <div class="fw-bold">${item.name}</div>
                <div class="text-muted small">$${item.price.toFixed(2)} x ${item.qty}</div>
            </div>
            <div class="d-flex align-items-center">
                <button type="button" class="btn btn-sm btn-light" onclick="updateQty(${item.id}, -1)">-</button>
                <span class="mx-2 fw-bold">${item.qty}</span>
                <button type="button" class="btn btn-sm btn-light" onclick="updateQty(${item.id}, 1)">+</button>
            </div>
        </div>`;
    });
    
    container.innerHTML = html;
    totalEl.innerText = '$' + total.toFixed(2);
}

function submitOrder() {
    if(cart.length === 0) {
        alert("Please add items to the order first.");
        return;
    }
    
    let orderType = document.querySelector('select[name="order_type"]').value;
    let tableSelect = document.querySelector('select[name="table_id"]');
    if(orderType === 'Dine-in' && !tableSelect.value) {
        alert("Please select a table for Dine-in orders.");
        return;
    }
    
    document.getElementById('cartData').value = JSON.stringify(cart);
    document.getElementById('orderForm').submit();
}

function toggleTableReq(type) {
    let tableSelect = document.getElementById('table_id');
    if (type === 'Dine-in') {
        tableSelect.setAttribute('required', 'required');
        tableSelect.disabled = false;
    } else {
        tableSelect.removeAttribute('required');
        tableSelect.value = '';
        tableSelect.disabled = true;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
