<?php
session_start();
require_once 'config.php';

// Login check - must be logged in to order
if (!isset($_SESSION['user_name'])) {
    header("Location: login.php"); exit();
}

$success  = false;
$error    = '';
$order_id = '';

$product_id = intval($_GET['id'] ?? ($_POST['product_id'] ?? 0));
$product = null;
$product_name = 'Product';
$product_price = 0;
$product_image = '';

// Product ID required
if ($product_id <= 0) {
    header("Location: home.php");
    exit();
}

// Always fetch product details from DB instead of trusting long GET params
$pstmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$pstmt->bind_param("i", $product_id);
$pstmt->execute();
$product = $pstmt->get_result()->fetch_assoc();
$pstmt->close();

if (!$product || $product['quantity'] <= 0) {
    header("Location: home.php");
    exit();
}

$product_name  = $product['name'];
$product_price = (float) $product['price'];
$product_image = $product['image'] ?? '';

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $state     = trim($_POST['state'] ?? '');
    $pincode   = trim($_POST['pincode'] ?? '');
    $landmark  = trim($_POST['landmark'] ?? '');
    $qty       = max(1, intval($_POST['quantity'] ?? 1));
    $payment   = trim($_POST['payment'] ?? '');
    $upi_id    = trim($_POST['upi_id'] ?? '');
    $card_no   = trim($_POST['card_no'] ?? '');

    if (!$full_name || !$phone || !$address || !$city || !$pincode || !$payment) {
        $error = 'Please fill in all required fields.';
    } elseif ($payment === 'upi' && !$upi_id) {
        $error = 'Please enter your UPI ID.';
    } elseif ($payment === 'card' && !$card_no) {
        $error = 'Please enter your Card Number.';
    } elseif ($qty > (int)$product['quantity']) {
        $error = 'Requested quantity is not available in stock.';
    } else {
        $total = $product_price * $qty;
        $order_code = 'ORD' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $user_id = (int) $_SESSION['user_id'];

        // Reduce stock safely first
        $stockStmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ? AND quantity >= ?");
        $stockStmt->bind_param("iii", $qty, $product_id, $qty);
        $stockStmt->execute();

        if ($stockStmt->affected_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO orders (order_code, user_id, product_id, product_name, product_price, quantity, total_amount, full_name, phone, email, address, city, state, pincode, landmark, payment_method) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("siisdiisssssssss", $order_code, $user_id, $product_id, $product_name, $product_price, $qty, $total, $full_name, $phone, $email, $address, $city, $state, $pincode, $landmark, $payment);

            if ($stmt->execute()) {
                $success  = true;
                $order_id = $order_code;
                $product['quantity'] -= $qty;
            } else {
                // Roll back stock if order insert fails
                $restoreStmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
                $restoreStmt->bind_param("ii", $qty, $product_id);
                $restoreStmt->execute();
                $restoreStmt->close();
                $error = 'Order failed. Please try again.';
            }

            $stmt->close();
        } else {
            $error = 'Requested quantity is not available in stock.';
        }

        $stockStmt->close();
    }
}

function formatINR($amount) { return '₹' . number_format((float)$amount, 2, '.', ','); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order – MyStore</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f4f6fb;min-height:100vh;display:flex;flex-direction:column;}
        header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:0 40px;position:sticky;top:0;z-index:100;}
        .header-inner{max-width:1200px;margin:0 auto;display:flex;justify-content:space-between;align-items:center;height:70px;}
        .logo{font-size:24px;font-weight:700;display:flex;align-items:center;gap:10px;text-decoration:none;color:white;}
        nav{display:flex;align-items:center;gap:25px;}
        nav a{color:rgba(255,255,255,.85);text-decoration:none;font-weight:500;font-size:15px;transition:color .2s;}
        nav a:hover{color:white;}
        .hero{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:45px 40px 70px;text-align:center;}
        .hero h2{font-size:36px;font-weight:700;margin-bottom:8px;}
        .hero p{font-size:16px;opacity:.9;}
        .main-content{max-width:1000px;margin:-30px auto 60px;padding:0 20px;flex:1;width:100%;position:relative;z-index:5;}
        .success-card{background:white;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,.1);padding:60px 40px;text-align:center;}
        .success-icon{font-size:80px;margin-bottom:20px;}
        .success-card h3{font-size:28px;color:#00b894;margin-bottom:10px;}
        .success-card p{font-size:16px;color:#666;margin-bottom:8px;}
        .order-id-box{display:inline-block;background:#f0f2ff;color:#667eea;font-weight:700;font-size:18px;padding:10px 25px;border-radius:8px;margin:15px 0 25px;letter-spacing:1px;}
        .back-btn{display:inline-block;padding:13px 35px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;text-decoration:none;border-radius:8px;font-weight:600;font-size:15px;}
        .order-layout{display:grid;grid-template-columns:1fr 1.6fr;gap:25px;align-items:start;}
        .product-summary{background:white;border-radius:15px;box-shadow:0 5px 20px rgba(0,0,0,.07);overflow:hidden;position:sticky;top:90px;}
        .product-summary img{width:100%;height:200px;object-fit:cover;}
        .no-img-box{width:100%;height:200px;background:linear-gradient(135deg,#f0f2ff,#e8eaf6);display:flex;align-items:center;justify-content:center;font-size:60px;}
        .summary-info{padding:20px;}
        .summary-info h4{font-size:18px;color:#333;margin-bottom:8px;font-weight:700;}
        .summary-price{font-size:24px;font-weight:700;color:#667eea;margin-bottom:14px;}
        .summary-row{display:flex;justify-content:space-between;font-size:14px;color:#888;padding:6px 0;border-bottom:1px solid #f5f5f5;}
        .summary-row.total{font-size:16px;font-weight:700;color:#333;border-bottom:none;margin-top:6px;}
        .qty-control{display:flex;align-items:center;gap:10px;margin:14px 0;}
        .qty-control label{font-size:14px;color:#555;font-weight:500;}
        .qty-control input{width:70px;padding:8px 10px;border:2px solid #e1e5e9;border-radius:8px;font-size:15px;text-align:center;outline:none;}
        .qty-control input:focus{border-color:#667eea;}
        .order-form-card{background:white;border-radius:15px;box-shadow:0 5px 20px rgba(0,0,0,.07);padding:30px 35px;}
        .order-form-card h3{font-size:20px;font-weight:700;color:#333;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f2ff;}
        .section-label{font-size:13px;font-weight:700;color:#667eea;text-transform:uppercase;letter-spacing:1px;margin:20px 0 12px;}
        .form-group{margin-bottom:16px;}
        .form-group label{display:block;margin-bottom:6px;color:#555;font-weight:500;font-size:14px;}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:11px 14px;border:2px solid #e1e5e9;border-radius:8px;font-size:15px;font-family:inherit;outline:none;transition:border-color .3s;background:white;}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#667eea;}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        .payment-options{display:flex;flex-direction:column;gap:12px;margin-bottom:16px;}
        .payment-option{border:2px solid #e1e5e9;border-radius:10px;padding:14px 18px;cursor:pointer;transition:border-color .2s,background .2s;display:flex;align-items:center;gap:14px;}
        .payment-option:hover{border-color:#667eea;background:#f8f9ff;}
        .payment-option.selected{border-color:#667eea;background:#f0f2ff;}
        .payment-option input[type="radio"]{display:none;}
        .pay-icon{font-size:26px;}
        .pay-label{font-weight:600;color:#333;font-size:15px;}
        .pay-sub{font-size:12px;color:#888;}
        .payment-extra{display:none;background:#f8f9ff;border:2px solid #e8eaff;border-radius:10px;padding:16px 18px;margin-top:-8px;margin-bottom:16px;}
        .payment-extra.show{display:block;}
        .submit-btn{width:100%;padding:14px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer;transition:transform .2s;margin-top:10px;}
        .submit-btn:hover{transform:translateY(-2px);}
        .error-msg{background:#fdecea;color:#e17055;border-radius:8px;padding:12px 16px;margin-bottom:18px;font-weight:600;font-size:14px;}
        footer{background:#2d3436;color:#ccc;padding:40px;margin-top:auto;}
        @media(max-width:768px){.order-layout{grid-template-columns:1fr} .product-summary{position:static} .form-row{grid-template-columns:1fr} header{padding:0 20px}}
    </style>
</head>
<body>
<header>
    <div class="header-inner">
        <a href="home.php" class="logo">🛍️ MyStore</a>
        <nav>
            <a href="home.php">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION['user_name'])): ?>
                <span style="color:rgba(255,255,255,.9);font-size:14px">👋 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" style="background:rgba(255,80,80,.25);border:2px solid rgba(255,150,150,.6);padding:7px 16px;border-radius:20px;font-size:14px;">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<section class="hero">
    <h2>🛒 Place Your Order</h2>
    <p>Fill in your details and choose a payment method</p>
</section>

<div class="main-content">
    <?php if ($success): ?>
    <div class="success-card">
        <div class="success-icon">🎉</div>
        <h3>Order Placed Successfully!</h3>
        <p>Thank you <?= htmlspecialchars($_SESSION['user_name']) ?>! We'll contact you soon.</p>
        <div class="order-id-box"><?= htmlspecialchars($order_id) ?></div>
        <p style="color:#888;font-size:14px;margin-bottom:25px;">
            Product: <strong><?= htmlspecialchars($product_name) ?></strong><br>
            Payment: <strong><?= strtoupper(htmlspecialchars($_POST['payment'])) ?></strong>
        </p>
        <a href="home.php" class="back-btn">← Continue Shopping</a>
    </div>
    <?php else: ?>
    <div class="order-layout">
        <!-- Product Summary -->
        <div class="product-summary">
            <?php if ($product_image): ?>
                <img src="<?= htmlspecialchars($product_image) ?>" alt="">
            <?php else: ?>
                <div class="no-img-box">📦</div>
            <?php endif; ?>
            <div class="summary-info">
                <h4><?= htmlspecialchars($product_name) ?></h4>
                <div class="summary-price"><?= formatINR($product_price) ?></div>
                <div class="qty-control">
                    <label>Quantity:</label>
                    <input type="number" id="qtyDisplay" value="1" min="1" max="<?= isset($product) ? $product['quantity'] : 99 ?>" oninput="updateTotal(this.value)">
                </div>
                <div class="summary-row"><span>Price</span><span><?= formatINR($product_price) ?></span></div>
                <div class="summary-row"><span>Qty</span><span id="summaryQty">1</span></div>
                <div class="summary-row"><span>Delivery</span><span style="color:#00b894">FREE</span></div>
                <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?= formatINR($product_price) ?></span></div>
            </div>
        </div>

        <!-- Order Form -->
        <div class="order-form-card">
            <h3>📋 Order Details</h3>
            <?php if ($error): ?><div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>

            <form method="POST">
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                <input type="hidden" name="quantity" id="quantityHidden" value="1">

                <div class="section-label">👤 Personal Information</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($_SESSION['user_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone *</label>
                        <input type="tel" name="phone" placeholder="+91 98765 43210" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>">
                </div>

                <div class="section-label">📍 Delivery Address</div>
                <div class="form-group">
                    <label>Full Address *</label>
                    <textarea name="address" rows="3" placeholder="House No., Street, Area..."><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>State</label>
                        <select name="state">
                            <option value="">Select State</option>
                            <?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Delhi','Jammu & Kashmir'] as $s): ?>
                                <option value="<?= $s ?>" <?= (($_POST['state'] ?? '') === $s) ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Pincode *</label>
                        <input type="text" name="pincode" placeholder="Pincode" maxlength="6" value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Landmark</label>
                        <input type="text" name="landmark" placeholder="Near..." value="<?= htmlspecialchars($_POST['landmark'] ?? '') ?>">
                    </div>
                </div>

                <div class="section-label">💳 Payment Method</div>
                <div class="payment-options">
                    <label class="payment-option <?= (($_POST['payment'] ?? '') === 'cod') ? 'selected' : '' ?>" onclick="selectPayment('cod',this)">
                        <input type="radio" name="payment" value="cod" <?= (($_POST['payment'] ?? '') === 'cod') ? 'checked' : '' ?>>
                        <span class="pay-icon">💵</span><div><div class="pay-label">Cash on Delivery</div><div class="pay-sub">Pay when order arrives</div></div>
                    </label>
                    <label class="payment-option <?= (($_POST['payment'] ?? '') === 'upi') ? 'selected' : '' ?>" onclick="selectPayment('upi',this)">
                        <input type="radio" name="payment" value="upi" <?= (($_POST['payment'] ?? '') === 'upi') ? 'checked' : '' ?>>
                        <span class="pay-icon">📱</span><div><div class="pay-label">UPI Payment</div><div class="pay-sub">PhonePe, GPay, Paytm</div></div>
                    </label>
                    <label class="payment-option <?= (($_POST['payment'] ?? '') === 'card') ? 'selected' : '' ?>" onclick="selectPayment('card',this)">
                        <input type="radio" name="payment" value="card" <?= (($_POST['payment'] ?? '') === 'card') ? 'checked' : '' ?>>
                        <span class="pay-icon">💳</span><div><div class="pay-label">Debit / Credit Card</div><div class="pay-sub">Visa, Mastercard, RuPay</div></div>
                    </label>
                </div>
                <div class="payment-extra <?= (($_POST['payment'] ?? '') === 'upi') ? 'show' : '' ?>" id="upiFields">
                    <div class="form-group" style="margin-bottom:0"><label>UPI ID *</label><input type="text" name="upi_id" placeholder="yourname@upi" value="<?= htmlspecialchars($_POST['upi_id'] ?? '') ?>"></div>
                </div>
                <div class="payment-extra <?= (($_POST['payment'] ?? '') === 'card') ? 'show' : '' ?>" id="cardFields">
                    <div class="form-group"><label>Card Number *</label><input type="text" name="card_no" placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCard(this)" value="<?= htmlspecialchars($_POST['card_no'] ?? '') ?>"></div>
                    <div class="form-row" style="margin-bottom:0">
                        <div class="form-group" style="margin-bottom:0"><label>Expiry</label><input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5" value="<?= htmlspecialchars($_POST['card_expiry'] ?? '') ?>"></div>
                        <div class="form-group" style="margin-bottom:0"><label>CVV</label><input type="password" name="card_cvv" placeholder="•••" maxlength="3"></div>
                    </div>
                </div>
                <button type="submit" class="submit-btn">✅ Confirm Order</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer style="text-align:center;"><p style="font-size:13px;color:#888">© 2025 MyStore. All rights reserved.</p></footer>

<script>
const unitPrice = <?= (float)$product_price ?>;
function updateTotal(qty){qty=parseInt(qty)||1;document.getElementById('summaryQty').textContent=qty;document.getElementById('summaryTotal').textContent='₹'+(unitPrice*qty).toLocaleString('en-IN',{minimumFractionDigits:2});document.getElementById('quantityHidden').value=qty;}
function selectPayment(method,el){document.querySelectorAll('.payment-option').forEach(o=>o.classList.remove('selected'));el.classList.add('selected');el.querySelector('input[type="radio"]').checked=true;document.getElementById('upiFields').classList.remove('show');document.getElementById('cardFields').classList.remove('show');if(method==='upi')document.getElementById('upiFields').classList.add('show');if(method==='card')document.getElementById('cardFields').classList.add('show');}
function formatCard(input){let val=input.value.replace(/\D/g,'').substring(0,16);input.value=val.replace(/(.{4})/g,'$1 ').trim();}
</script>
</body>
</html>
