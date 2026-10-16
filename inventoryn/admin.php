<?php
session_start();
require_once 'config.php';

// ── ADMIN AUTH CHECK ─────────────────────────────
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$msg = '';
$msgType = '';

// ── HANDLE ADD / EDIT PRODUCT ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $name     = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $price    = floatval($_POST['price'] ?? 0);
    $imageData = '';

    // Handle image upload
    if (!empty($_FILES['image']['tmp_name'])) {
        $imgInfo  = getimagesize($_FILES['image']['tmp_name']);
        $mime     = $imgInfo['mime'] ?? 'image/jpeg';
        $imgBin   = file_get_contents($_FILES['image']['tmp_name']);
        $imageData = 'data:' . $mime . ';base64,' . base64_encode($imgBin);
    }

    if ($_POST['action'] === 'add') {
        $stmt = $conn->prepare("INSERT INTO products (name, category, quantity, price, image) VALUES (?,?,?,?,?)");
        $stmt->bind_param("ssids", $name, $category, $quantity, $price, $imageData);
        if ($stmt->execute()) {
            $msg = "✅ Product added successfully!"; $msgType = 'success';
        } else {
            $msg = "❌ Error: " . $stmt->error; $msgType = 'error';
        }
        $stmt->close();

    } elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['edit_id']);
        if (!empty($imageData)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, quantity=?, price=?, image=? WHERE id=?");
            $stmt->bind_param("ssidsi", $name, $category, $quantity, $price, $imageData, $id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, quantity=?, price=? WHERE id=?");
            $stmt->bind_param("ssidi", $name, $category, $quantity, $price, $id);
        }
        if ($stmt->execute()) {
            $msg = "✅ Product updated!"; $msgType = 'success';
        } else {
            $msg = "❌ Error: " . $stmt->error; $msgType = 'error';
        }
        $stmt->close();
    }
}

// ── HANDLE DELETE ────────────────────────────────
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id=$id");
    $msg = "🗑️ Product deleted."; $msgType = 'success';
}

// ── FETCH ALL PRODUCTS ───────────────────────────
$products = [];
$res = $conn->query("SELECT * FROM products ORDER BY id DESC");
while ($row = $res->fetch_assoc()) { $products[] = $row; }

// ── STATS ────────────────────────────────────────
$totalItems = count($products);
$lowStockCount = count(array_filter($products, fn($p) => $p['quantity'] < 10));
$totalValue = array_reduce($products, fn($carry, $p) => $carry + ($p['quantity'] * $p['price']), 0);

// ── ORDERS COUNT ─────────────────────────────────
$orderCount = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c'] ?? 0;

// ── USERS COUNT ──────────────────────────────────
$userCount = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'] ?? 0;

// ── RECENT ORDERS ────────────────────────────────
$recentOrders = [];
$ordRes = $conn->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 10");
while ($row = $ordRes->fetch_assoc()) { $recentOrders[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – MyStore</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;padding:20px;}
        .container{max-width:1300px;margin:0 auto;}

        /* HEADER */
        .header{background:white;border-radius:15px;padding:20px 30px;margin-bottom:25px;box-shadow:0 10px 30px rgba(0,0,0,.1);display:flex;justify-content:space-between;align-items:center;}
        .header h1{color:#333;font-size:24px;}
        .header-right{display:flex;align-items:center;gap:15px;}
        .user-badge{background:#f0f2ff;color:#667eea;padding:8px 16px;border-radius:20px;font-size:14px;font-weight:600;}
        .logout-btn{background:#ff4757;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:500;text-decoration:none;font-size:14px;}
        .home-btn{background:#667eea;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;font-weight:500;text-decoration:none;font-size:14px;}

        /* TABS */
        .tabs{display:flex;gap:8px;margin-bottom:25px;flex-wrap:wrap;}
        .tab-btn{padding:10px 20px;border:none;border-radius:10px;cursor:pointer;font-size:14px;font-weight:600;transition:all .2s;}
        .tab-btn.active{background:white;color:#667eea;box-shadow:0 4px 15px rgba(0,0,0,.15);}
        .tab-btn:not(.active){background:rgba(255,255,255,.25);color:white;}
        .tab-btn:not(.active):hover{background:rgba(255,255,255,.4);}
        .tab-content{display:none;}
        .tab-content.active{display:block;}

        /* STATS */
        .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:25px;}
        .stat-card{background:white;padding:25px;border-radius:15px;box-shadow:0 10px 30px rgba(0,0,0,.1);text-align:center;}
        .stat-number{font-size:36px;font-weight:bold;color:#667eea;}
        .stat-label{color:#666;font-size:14px;margin-top:5px;}

        /* ALERTS */
        .alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-weight:500;font-size:14px;}
        .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
        .alert-error{background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;}

        /* SECTION */
        .section{background:white;border-radius:15px;padding:30px;box-shadow:0 10px 30px rgba(0,0,0,.1);margin-bottom:25px;}
        .section-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;}
        .section-header h3{color:#333;font-size:22px;}
        .add-btn{background:#00b894;color:white;border:none;padding:12px 25px;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;}

        /* TABLE */
        table{width:100%;border-collapse:collapse;}
        th,td{padding:13px 12px;text-align:left;border-bottom:1px solid #eee;font-size:14px;}
        th{background:#f8f9fa;font-weight:700;color:#444;}
        tr:hover td{background:#fafbff;}
        .status-low{color:#e17055;font-weight:bold;}
        .status-high{color:#00b894;font-weight:bold;}
        .action-btn{padding:7px 14px;border:none;border-radius:6px;cursor:pointer;margin-right:4px;font-size:13px;font-weight:500;}
        .edit-btn{background:#ffa502;color:white;}
        .delete-btn{background:#ff4757;color:white;}

        /* MODAL */
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;}
        .modal-content{background:white;margin:5% auto;padding:35px;border-radius:15px;width:90%;max-width:500px;max-height:85vh;overflow-y:auto;}
        .close{float:right;font-size:28px;font-weight:bold;cursor:pointer;color:#aaa;line-height:1;}
        .close:hover{color:#000;}
        .form-group{margin-bottom:18px;}
        .form-group label{display:block;margin-bottom:7px;color:#555;font-weight:500;font-size:14px;}
        .form-group input,.form-group select{width:100%;padding:11px 14px;border:2px solid #e1e5e9;border-radius:8px;font-size:15px;outline:none;transition:border-color .3s;}
        .form-group input:focus,.form-group select:focus{border-color:#667eea;}
        .save-btn{width:100%;padding:13px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;margin-top:8px;}

        /* STATUS BADGE for orders */
        .badge{padding:4px 10px;border-radius:12px;font-size:12px;font-weight:600;}
        .badge-pending{background:#fef3c7;color:#92400e;}
        .badge-confirmed{background:#d1fae5;color:#065f46;}
        .badge-shipped{background:#dbeafe;color:#1e40af;}
        .badge-delivered{background:#d1fae5;color:#065f46;}
        .badge-cancelled{background:#fee2e2;color:#b91c1c;}

        /* Order status select */
        .status-select{padding:6px 10px;border:2px solid #e1e5e9;border-radius:6px;font-size:13px;outline:none;cursor:pointer;}

        @media(max-width:768px){
            .stats-grid{grid-template-columns:1fr 1fr;}
            .header{flex-direction:column;gap:12px;text-align:center;}
            .tabs{justify-content:center;}
            table{font-size:13px;}
            th,td{padding:10px 8px;}
        }
    </style>
</head>
<body>
<div class="container">

    <!-- HEADER -->
    <div class="header">
        <h1>📦 Admin Dashboard</h1>
        <div class="header-right">
            <span class="user-badge">⚙️ <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
            <a href="home.php" class="home-btn">🏠 View Store</a>
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('inventory')">📋 Inventory</button>
        <button class="tab-btn" onclick="switchTab('orders')">🛒 Orders</button>
        <button class="tab-btn" onclick="switchTab('users')">👥 Users</button>
    </div>

    <!-- ════ TAB: INVENTORY ════ -->
    <div class="tab-content active" id="tab-inventory">

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card"><div class="stat-number"><?= $totalItems ?></div><div class="stat-label">📦 Total Products</div></div>
            <div class="stat-card"><div class="stat-number"><?= $lowStockCount ?></div><div class="stat-label">⚠️ Low Stock</div></div>
            <div class="stat-card"><div class="stat-number">₹<?= number_format($totalValue, 0) ?></div><div class="stat-label">💰 Total Value</div></div>
            <div class="stat-card"><div class="stat-number"><?= $orderCount ?></div><div class="stat-label">🛒 Total Orders</div></div>
            <div class="stat-card"><div class="stat-number"><?= $userCount ?></div><div class="stat-label">👥 Registered Users</div></div>
        </div>

        <!-- Inventory Table -->
        <div class="section">
            <div class="section-header">
                <h3>📋 Products</h3>
                <button class="add-btn" onclick="openAddModal()">+ Add Product</button>
            </div>
            <?php if (empty($products)): ?>
                <p style="color:#888;text-align:center;padding:30px">No products yet. Click "Add Product" to start.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr><th>Image</th><th>Name</th><th>Category</th><th>Qty</th><th>Price</th><th>Value</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= !empty($p['image']) ? "<img src='{$p['image']}' style='width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #eee;'>" : '<span style="color:#aaa">No img</span>' ?></td>
                        <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                        <td><?= htmlspecialchars($p['category']) ?></td>
                        <td><?= $p['quantity'] ?></td>
                        <td>₹<?= number_format($p['price'], 2) ?></td>
                        <td>₹<?= number_format($p['quantity'] * $p['price'], 2) ?></td>
                        <td class="<?= $p['quantity'] < 10 ? 'status-low' : 'status-high' ?>"><?= $p['quantity'] < 10 ? '⚠️ Low' : '✅ OK' ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openEditModal(<?= $p['id'] ?>, <?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
                            <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this product?')" class="action-btn delete-btn" style="text-decoration:none">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ════ TAB: ORDERS ════ -->
    <div class="tab-content" id="tab-orders">
        <div class="section">
            <div class="section-header"><h3>🛒 Recent Orders</h3></div>
            <?php if (empty($recentOrders)): ?>
                <p style="color:#888;text-align:center;padding:30px">No orders placed yet.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table>
                <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Qty</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
                <tbody>
                    <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($o['order_code']) ?></strong></td>
                        <td><?= htmlspecialchars($o['full_name']) ?><br><small style="color:#888"><?= htmlspecialchars($o['phone']) ?></small></td>
                        <td><?= htmlspecialchars($o['product_name']) ?></td>
                        <td><?= $o['quantity'] ?></td>
                        <td>₹<?= number_format($o['total_amount'], 2) ?></td>
                        <td style="text-transform:uppercase"><?= htmlspecialchars($o['payment_method']) ?></td>
                        <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                        <td style="font-size:12px;color:#888"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                        <td>
                            <form method="POST" action="admin_update_order.php" style="display:flex;gap:6px;align-items:center">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status" class="status-select">
                                    <?php foreach (['pending','confirmed','shipped','delivered','cancelled'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="action-btn edit-btn" style="padding:6px 10px">Save</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ════ TAB: USERS ════ -->
    <div class="tab-content" id="tab-users">
        <div class="section">
            <div class="section-header"><h3>👥 Registered Users</h3></div>
            <?php
            $usersRes = $conn->query("SELECT id, name, email, created_at FROM users ORDER BY created_at DESC");
            $usersArr = [];
            while ($row = $usersRes->fetch_assoc()) { $usersArr[] = $row; }
            ?>
            <?php if (empty($usersArr)): ?>
                <p style="color:#888;text-align:center;padding:30px">No users registered yet.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
            <table>
                <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Registered</th></tr></thead>
                <tbody>
                    <?php foreach ($usersArr as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($u['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ════ ADD PRODUCT MODAL ════ -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('addModal')">&times;</span>
        <h3>➕ Add New Product</h3><br>
        <form method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="image" accept="image/*" onchange="previewImg(this,'addPreview')">
                <div id="addPreview" style="margin-top:8px"></div>
            </div>
            <div class="form-group"><label>Product Name *</label><input type="text" name="name" required></div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option>Electronics</option><option>Clothing</option><option>Food</option><option>Office</option><option>Other</option>
                </select>
            </div>
            <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" min="0" required></div>
            <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" min="0" step="0.01" required></div>
            <button type="submit" class="save-btn">✅ Add Product</button>
        </form>
    </div>
</div>

<!-- ════ EDIT PRODUCT MODAL ════ -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('editModal')">&times;</span>
        <h3>✏️ Edit Product</h3><br>
        <form method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="edit_id" id="editId">
            <div class="form-group">
                <label>Product Image <small style="color:#888">(leave empty to keep current)</small></label>
                <input type="file" name="image" accept="image/*" onchange="previewImg(this,'editPreview')">
                <div id="editPreview" style="margin-top:8px"></div>
            </div>
            <div class="form-group"><label>Product Name *</label><input type="text" name="name" id="editName" required></div>
            <div class="form-group">
                <label>Category *</label>
                <select name="category" id="editCategory" required>
                    <option value="">Select Category</option>
                    <option>Electronics</option><option>Clothing</option><option>Food</option><option>Office</option><option>Other</option>
                </select>
            </div>
            <div class="form-group"><label>Quantity *</label><input type="number" name="quantity" id="editQty" min="0" required></div>
            <div class="form-group"><label>Price (₹) *</label><input type="number" name="price" id="editPrice" min="0" step="0.01" required></div>
            <button type="submit" class="save-btn">💾 Save Changes</button>
        </form>
    </div>
</div>

<script>
    function switchTab(name) {
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        event.target.classList.add('active');
    }

    function openAddModal() {
        document.getElementById('addModal').style.display = 'block';
    }

    function openEditModal(id, product) {
        document.getElementById('editId').value = id;
        document.getElementById('editName').value = product.name;
        document.getElementById('editCategory').value = product.category;
        document.getElementById('editQty').value = product.quantity;
        document.getElementById('editPrice').value = product.price;
        const preview = document.getElementById('editPreview');
        preview.innerHTML = product.image
            ? `<img src="${product.image}" style="width:60px;height:60px;object-fit:cover;border-radius:6px;">`
            : '';
        document.getElementById('editModal').style.display = 'block';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function previewImg(input, previewId) {
        const file = input.files[0];
        const preview = document.getElementById(previewId);
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                preview.innerHTML = `<img src="${e.target.result}" style="width:60px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #eee;">`;
            };
            reader.readAsDataURL(file);
        }
    }

    window.onclick = e => {
        if (e.target.classList.contains('modal')) e.target.style.display = 'none';
    };
</script>
</body>
</html>
