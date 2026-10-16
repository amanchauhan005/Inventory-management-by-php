<?php
session_start();
require_once 'config.php';

// Fetch all products from database
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Stats
$total     = count($products);
$inStock   = count(array_filter($products, fn($p) => $p['quantity'] >= 10));
$lowStock  = count(array_filter($products, fn($p) => $p['quantity'] > 0 && $p['quantity'] < 10));
$categories = count(array_unique(array_column($products, 'category')));

// Get unique categories for filter
$cats = array_unique(array_column($products, 'category'));
sort($cats);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyStorage – Product Catalog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; background:#f4f6fb; min-height:100vh; display:flex; flex-direction:column; }

        /* HEADER */
        header { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:0 40px; box-shadow:0 4px 15px rgba(102,126,234,.4); position:sticky; top:0; z-index:100; }
        .header-inner { max-width:1200px; margin:0 auto; display:flex; justify-content:space-between; align-items:center; height:70px; }
        .logo { font-size:24px; font-weight:700; letter-spacing:1px; display:flex; align-items:center; gap:10px; }
        nav { display:flex; align-items:center; gap:30px; }
        nav a { color:rgba(255,255,255,.85); text-decoration:none; font-weight:500; font-size:15px; transition:color .2s; }
        nav a:hover, nav a.active { color:white; }
        .auth-buttons { display:flex; align-items:center; gap:10px; }
        .btn-login { background:rgba(255,255,255,.15); border:2px solid rgba(255,255,255,.6); color:white; padding:8px 20px; border-radius:25px; font-size:14px; font-weight:600; text-decoration:none; transition:all .2s; }
        .btn-login:hover { background:rgba(255,255,255,.3); }
        .btn-signup { background:white; color:#667eea; padding:8px 20px; border-radius:25px; font-size:14px; font-weight:700; text-decoration:none; border:2px solid white; transition:all .2s; }
        .btn-signup:hover { background:#f0f0ff; color:#764ba2; }
        .user-welcome { color:rgba(255,255,255,.9); font-size:14px; font-weight:500; }
        .btn-logout { background:rgba(255,80,80,.25); border:2px solid rgba(255,150,150,.6); color:white; padding:8px 18px; border-radius:25px; font-size:14px; font-weight:600; text-decoration:none; transition:all .2s; }
        .btn-logout:hover { background:rgba(255,80,80,.45); }

        /* HERO */
        .hero { background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:white; padding:60px 40px 80px; text-align:center; }
        .hero h2 { font-size:42px; font-weight:700; margin-bottom:12px; }
        .hero p { font-size:18px; opacity:.9; margin-bottom:30px; }
        .live-badge { display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.15); padding:8px 20px; border-radius:20px; font-size:14px; font-weight:600; }
        .live-dot { width:8px; height:8px; background:#4ade80; border-radius:50%; animation:pulse 1.5s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

        /* FILTER */
        .filter-bar { background:white; border-radius:15px; box-shadow:0 10px 40px rgba(0,0,0,.12); padding:20px 25px; max-width:800px; margin:-35px auto 0; display:flex; gap:15px; align-items:center; position:relative; z-index:10; }
        .filter-bar input { flex:1; padding:12px 18px; border:2px solid #e1e5e9; border-radius:8px; font-size:15px; outline:none; transition:border-color .3s; }
        .filter-bar input:focus { border-color:#667eea; }
        .filter-bar select { padding:12px 18px; border:2px solid #e1e5e9; border-radius:8px; font-size:15px; outline:none; cursor:pointer; background:white; }
        .filter-bar select:focus { border-color:#667eea; }

        /* MAIN */
        .main-content { max-width:1200px; margin:50px auto 60px; padding:0 20px; flex:1; width:100%; }
        .section-title { font-size:26px; font-weight:700; color:#333; margin-bottom:8px; }
        .section-subtitle { color:#888; font-size:15px; margin-bottom:30px; }

        /* STATS */
        .stats-bar { display:flex; gap:20px; margin-bottom:35px; flex-wrap:wrap; }
        .stat-pill { background:white; border-radius:12px; padding:15px 22px; display:flex; align-items:center; gap:14px; box-shadow:0 3px 12px rgba(0,0,0,.07); flex:1; min-width:140px; }
        .s-icon { font-size:26px; }
        .s-val { font-size:22px; font-weight:700; color:#333; }
        .s-label { font-size:12px; color:#888; margin-top:2px; }

        /* PRODUCT GRID */
        .product-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:25px; }
        .product-card { background:white; border-radius:15px; box-shadow:0 5px 20px rgba(0,0,0,.07); overflow:hidden; transition:transform .25s,box-shadow .25s; display:flex; flex-direction:column; }
        .product-card:hover { transform:translateY(-5px); box-shadow:0 15px 35px rgba(102,126,234,.18); }
        .product-image { width:100%; height:200px; object-fit:cover; }
        .no-image-placeholder { width:100%; height:200px; background:linear-gradient(135deg,#f0f2ff,#e8eaf6); display:flex; flex-direction:column; align-items:center; justify-content:center; color:#aab; font-size:13px; gap:8px; }
        .no-image-placeholder .icon { font-size:48px; }
        .product-info { padding:18px 20px; flex:1; display:flex; flex-direction:column; }
        .product-category { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#667eea; margin-bottom:6px; }
        .product-name { font-size:17px; font-weight:600; color:#222; margin-bottom:10px; }
        .product-price { font-size:20px; font-weight:700; color:#667eea; margin-bottom:12px; }
        .product-footer { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
        .stock-badge { font-size:12px; font-weight:600; padding:4px 10px; border-radius:12px; }
        .stock-in { background:#d1fae5; color:#065f46; }
        .stock-low { background:#fef3c7; color:#92400e; }
        .qty-text { font-size:13px; color:#888; }
        .order-btn { display:block; text-align:center; background:linear-gradient(135deg,#667eea,#764ba2); color:white; padding:11px; border-radius:10px; text-decoration:none; font-weight:600; font-size:14px; margin-top:auto; border:none; cursor:pointer; transition:opacity .2s,transform .2s; }
        .order-btn:hover { opacity:.9; transform:translateY(-1px); }
        .order-btn-disabled { background:#e5e7eb; color:#9ca3af; cursor:not-allowed; width:100%; }
        .login-prompt { background:#f0f2ff; border:2px dashed #667eea; color:#667eea; display:block; text-align:center; padding:11px; border-radius:10px; font-weight:600; font-size:13px; margin-top:auto; text-decoration:none; }
        .login-prompt:hover { background:#e8eaff; }

        /* EMPTY */
        .empty-state { text-align:center; padding:60px 20px; color:#888; grid-column:1/-1; }
        .empty-state .big-icon { font-size:64px; margin-bottom:16px; }
        .empty-state h3 { font-size:22px; color:#555; margin-bottom:8px; }

        /* FOOTER */
        footer { background:#1a1a2e; color:#ccc; padding:50px 40px 25px; margin-top:auto; }
        .footer-inner { max-width:1200px; margin:0 auto; }
        .footer-grid { display:grid; grid-template-columns:2fr 1fr 1fr; gap:40px; margin-bottom:35px; }
        .logo-text { font-size:22px; font-weight:700; color:white; margin-bottom:14px; }
        .footer-brand p { font-size:14px; line-height:1.7; color:#aaa; }
        .footer-col h4 { color:white; font-size:15px; font-weight:700; margin-bottom:16px; }
        .footer-col ul { list-style:none; }
        .footer-col ul li { margin-bottom:10px; }
        .footer-col ul li a { color:#aaa; text-decoration:none; font-size:14px; transition:color .2s; }
        .footer-col ul li a:hover { color:#667eea; }
        .footer-bottom { border-top:1px solid #333; padding-top:20px; display:flex; justify-content:space-between; flex-wrap:wrap; gap:10px; }
        .footer-bottom p { font-size:13px; color:#888; }
        .footer-bottom a { color:#667eea; text-decoration:none; }

        @media(max-width:768px){
            .hero h2{font-size:28px} .filter-bar{flex-direction:column} .footer-grid{grid-template-columns:1fr;gap:25px}
            nav{gap:15px} header{padding:0 20px} .stats-bar{gap:15px} .auth-buttons{gap:6px}
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="header-inner">
        <div class="logo"><i class="fa-solid fa-store"></i> MyStore</div>
        <nav>
            <a href="home.php" class="active">Home</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
        </nav>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_name'])): ?>
                <span class="user-welcome">👋 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-login">Login</a>
                <a href="signup.php" class="btn-signup">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- HERO -->
<section class="hero">
    <h2><i class="fa-solid fa-cart-flatbed"></i> Welcome to Our Store</h2>
    <p>Browse our latest products — updated live by our team</p>
    <div class="live-badge"><div class="live-dot"></div> Live Inventory</div>
</section>

<!-- FILTER BAR -->
<div class="filter-bar">
    <input type="text" id="searchInput" placeholder="🔍  Search products..." oninput="filterProducts()">
    <select id="categoryFilter" onchange="filterProducts()">
        <option value="">All Categories</option>
        <?php foreach ($cats as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<!-- MAIN -->
<div class="main-content">
    <!-- Stats -->
    <div class="stats-bar">
        <div class="stat-pill"><span class="s-icon">📦</span><div><div class="s-val"><?= $total ?></div><div class="s-label">Total Products</div></div></div>
        <div class="stat-pill"><span class="s-icon">✅</span><div><div class="s-val"><?= $inStock ?></div><div class="s-label">In Stock</div></div></div>
        <div class="stat-pill"><span class="s-icon">⚠️</span><div><div class="s-val"><?= $lowStock ?></div><div class="s-label">Low Stock</div></div></div>
        <div class="stat-pill"><span class="s-icon">🏷️</span><div><div class="s-val"><?= $categories ?></div><div class="s-label">Categories</div></div></div>
    </div>

    <div class="section-title">All Products</div>
    <div class="section-subtitle" id="productCount">Showing <?= $total ?> product<?= $total !== 1 ? 's' : '' ?></div>

    <!-- Product Grid -->
    <div class="product-grid" id="productGrid">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <div class="big-icon">🛒</div>
                <h3>No products available</h3>
                <p>Check back soon — the admin is adding products!</p>
            </div>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
            <div class="product-card"
                 data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
                 data-category="<?= htmlspecialchars($p['category']) ?>">

                <?php if (!empty($p['image'])): ?>
                    <img class="product-image" src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <?php else: ?>
                    <div class="no-image-placeholder"><span class="icon">📷</span><span>No Image</span></div>
                <?php endif; ?>

                <div class="product-info">
                    <div class="product-category"><?= htmlspecialchars($p['category']) ?></div>
                    <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-price">₹<?= number_format($p['price'], 2) ?></div>
                    <div class="product-footer">
                        <span class="stock-badge <?= $p['quantity'] < 10 ? 'stock-low' : 'stock-in' ?>">
                            <?= $p['quantity'] < 10 ? '⚠️ Low Stock' : '✅ In Stock' ?>
                        </span>
                        <span class="qty-text">Qty: <?= $p['quantity'] ?></span>
                    </div>

                    <?php if ($p['quantity'] > 0): ?>
                        <?php if (isset($_SESSION['user_name'])): ?>
                            <a href="order.php?id=<?= (int)$p['id'] ?>" class="order-btn">🛒 Order Now</a>
                        <?php else: ?>
                            <a href="login.php" class="login-prompt">🔐 Login to Order</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <button class="order-btn order-btn-disabled" disabled>Out of Stock</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="logo-text">🛍️ MyStore</div>
                <p>Your one-stop shop for quality products. Inventory managed in real-time.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="home.php">Home</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Categories</h4>
                <ul>
                    <?php foreach ($cats as $cat): ?>
                        <li><a href="home.php"><?= htmlspecialchars($cat) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© 2025 MyStore. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
    // Client-side filtering (instant, no reload)
    function filterProducts() {
        const search   = document.getElementById('searchInput').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value;
        const cards    = document.querySelectorAll('.product-card');
        let visible    = 0;

        cards.forEach(card => {
            const matchSearch = card.dataset.name.includes(search);
            const matchCat    = category === '' || card.dataset.category === category;
            const show        = matchSearch && matchCat;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        document.getElementById('productCount').textContent =
            `Showing ${visible} product${visible !== 1 ? 's' : ''}`;
    }
</script>
</body>
</html>
