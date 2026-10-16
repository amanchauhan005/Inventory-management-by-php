<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MyStorag</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6fb;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER ── */
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0 40px;
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-inner {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
        }
        nav { display: flex; align-items: center; gap: 30px; }
        nav a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: color 0.2s;
        }
        nav a:hover, nav a.active { color: white; }
        .admin-link {
            background: rgba(255,255,255,0.2);
            padding: 8px 18px;
            border-radius: 20px;
            color: white !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            transition: background 0.2s !important;
        }
        .admin-link:hover { background: rgba(255,255,255,0.35) !important; }

        /* ── HERO ── */
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 40px 80px;
            text-align: center;
        }
        .hero h2 { font-size: 42px; font-weight: 700; margin-bottom: 12px; text-shadow: 0 2px 10px rgba(0,0,0,0.15); }
        .hero p { font-size: 18px; opacity: 0.9; }

        /* ── MAIN ── */
        .main-content {
            max-width: 1100px;
            margin: 50px auto 60px;
            padding: 0 20px;
            flex: 1;
            width: 100%;
        }

        /* ── INTRO CARD ── */
        .intro-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 45px 50px;
            display: flex;
            gap: 50px;
            align-items: center;
            margin-bottom: 40px;
        }
        .intro-icon { font-size: 90px; flex-shrink: 0; }
        .intro-card h3 { font-size: 28px; color: #333; margin-bottom: 15px; }
        .intro-card p { font-size: 16px; color: #666; line-height: 1.8; margin-bottom: 12px; }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-box {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            padding: 30px 20px;
            text-align: center;
        }
        .stat-box .num { font-size: 36px; font-weight: 700; color: #667eea; margin-bottom: 6px; }
        .stat-box .lbl { font-size: 14px; color: #888; }

        /* ── VALUES ── */
        .section-title { font-size: 26px; font-weight: 700; color: #333; margin-bottom: 8px; }
        .section-sub { color: #888; font-size: 15px; margin-bottom: 25px; }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .value-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            padding: 30px 25px;
            transition: transform 0.25s, box-shadow 0.25s;
        }
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.18);
        }
        .value-icon { font-size: 40px; margin-bottom: 14px; }
        .value-card h4 { font-size: 18px; color: #333; margin-bottom: 10px; font-weight: 600; }
        .value-card p { font-size: 14px; color: #777; line-height: 1.7; }

        /* ── TEAM ── */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        .team-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            padding: 35px 25px;
            text-align: center;
            transition: transform 0.25s, box-shadow 0.25s;
        }
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.18);
        }
        .team-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 16px;
        }
        .team-card h4 { font-size: 18px; font-weight: 600; color: #333; margin-bottom: 5px; }
        .team-card .role { font-size: 13px; color: #667eea; font-weight: 600; margin-bottom: 12px; }
        .team-card p { font-size: 13px; color: #888; line-height: 1.6; }

        /* ── FOOTER ── */
        footer { background: #2d3436; color: #ccc; padding: 50px 40px 25px; }
        .footer-inner { max-width: 1200px; margin: 0 auto; }
        .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 40px; margin-bottom: 40px; }
        .footer-brand .logo-text { font-size: 22px; font-weight: 700; color: white; margin-bottom: 12px; }
        .footer-brand p { font-size: 14px; line-height: 1.7; color: #aaa; }
        .footer-col h4 { color: white; font-size: 15px; font-weight: 600; margin-bottom: 16px; }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .footer-col ul li a { color: #aaa; text-decoration: none; font-size: 14px; transition: color 0.2s; }
        .footer-col ul li a:hover { color: #667eea; }
        .footer-bottom { border-top: 1px solid #444; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .footer-bottom p { font-size: 13px; color: #888; }
        .footer-bottom a { color: #667eea; text-decoration: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 900px) {
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .values-grid { grid-template-columns: repeat(2, 1fr); }
            .team-grid { grid-template-columns: repeat(2, 1fr); }
            .intro-card { flex-direction: column; text-align: center; gap: 20px; }
            .footer-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .hero h2 { font-size: 28px; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
            .values-grid { grid-template-columns: 1fr; }
            .team-grid { grid-template-columns: 1fr; }
            header { padding: 0 20px; }
            nav { gap: 15px; }
        }
    </style>
</head>
<body>

    <!-- ══ HEADER ══ -->
    <header>
        <div class="header-inner">
            <a href="user.html" class="logo"><span>🛍️</span> MyStore</a>
            <nav>
                 <a href="home.php">Home</a>
                <a href="about.php">About</a>
                <a href="contact.php" class="active">Contact</a>
                <a href="admin.php" class="admin-link">⚙️</a>
            </nav>
        </div>
    </header>

    <!-- ══ HERO ══ -->
    <section class="hero">
        <h2>🏢 About Us</h2>
        <p>Learn more about our story, our mission, and the team behind MyStore</p>
    </section>

    <!-- ══ MAIN CONTENT ══ -->
    <div class="main-content">

        <!-- Intro -->
        <div class="intro-card">
            <div class="intro-icon">🛍️</div>
            <div>
                <h3>Who We Are</h3>
                <p>MyStore is a modern inventory-driven store where every product you see is managed live by our dedicated admin team. We believe in transparency — what you see is always up to date.</p>
                <p>Founded with a simple goal: to make quality products accessible to everyone, with a seamless shopping experience backed by real-time inventory management.</p>
                <p>From electronics to clothing, food to office supplies — we've got everything you need, all in one place.</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="num">5+</div>
                <div class="lbl">Years in Business</div>
            </div>
            <div class="stat-box">
                <div class="num">10K+</div>
                <div class="lbl">Happy Customers</div>
            </div>
            <div class="stat-box">
                <div class="num">500+</div>
                <div class="lbl">Products Listed</div>
            </div>
            <div class="stat-box">
                <div class="num">24/7</div>
                <div class="lbl">Customer Support</div>
            </div>
        </div>

        <!-- Our Values -->
        <div class="section-title">💡 Our Values</div>
        <div class="section-sub">What drives us every single day</div>

        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">🎯</div>
                <h4>Customer First</h4>
                <p>Every decision we make is centered around giving our customers the best possible experience — from browsing to delivery.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🔄</div>
                <h4>Real-Time Updates</h4>
                <p>Our inventory is managed live. You always see accurate stock levels, prices, and product availability — no surprises.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">💎</div>
                <h4>Quality Products</h4>
                <p>We carefully curate every product in our catalog to ensure it meets our high standards of quality and value for money.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🤝</div>
                <h4>Trust & Transparency</h4>
                <p>Honest pricing, clear stock availability, and no hidden fees. We believe trust is the foundation of every great business.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🚀</div>
                <h4>Continuous Growth</h4>
                <p>We're always improving — adding new products, expanding categories, and enhancing the shopping experience.</p>
            </div>
            <div class="value-card">
                <div class="value-icon">🌱</div>
                <h4>Sustainability</h4>
                <p>We are committed to responsible sourcing and reducing waste — because doing business well means doing good by the planet.</p>
            </div>
        </div>

        <!-- Team -->
        <div class="section-title">👥 Our Team</div>
        <div class="section-sub">The people who make it all happen</div>

        <div class="team-grid">
            <div class="team-card">
                <div class="team-avatar">👨‍💼</div>
                <h4>AMAN CHAUHAN</h4>
                <div class="role">Founder & CEO</div>
                <p>Visionary leader with 10+ years in retail and e-commerce. Passionate about building systems that just work.</p>
            </div>
            <div class="team-card">
                <div class="team-avatar">👩‍💻</div>
                <h4>NASEHA CHAUHAN</h4>
                <div class="role">Head of Technology</div>
                <p>Full-stack developer who built our real-time inventory system from the ground up. Loves clean code and fast UIs.</p>
            </div>
            <div class="team-card">
                <div class="team-avatar">👨‍📦</div>
                <h4>IRFAN KHUSHKIVALA</h4>
                <div class="role">Inventory Manager</div>
                <p>Keeps our product catalog accurate and up to date. The person behind every item you see on our store.</p>
            </div>
        </div>

    </div>

    <!-- ══ FOOTER ══ -->
    <footer>
        <div class="footer-inner">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="logo-text">🛍️ MyStore</div>
                    <p>Your one-stop shop for quality products. Our inventory is managed in real-time to ensure you always see the latest availability and pricing.</p>
                </div>
                <div class="footer-col">
                    <h4>Quick Links</h4>
                    <ul>
                       <li><a href="home.php">Home</a></li>
                        <li><a href="home.php">Products</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Categories</h4>
                    <ul>
                        <li><a href="home.php">Electronics</a></li>
                        <li><a href="home.php">Clothing</a></li>
                        <li><a href="home.php">Food</a></li>
                        <li><a href="home.php">Office Supplies</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 MyStore. All rights reserved.</p>
                <p>Powered by <a href="admin.php">Admin Panel</a></p>
            </div>
        </div>
    </footer>

</body>
</html>
