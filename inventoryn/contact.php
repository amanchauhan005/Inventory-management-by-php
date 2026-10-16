<?php
// Form submission handle karna
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname   = trim($_POST['fname'] ?? '');
    $lname   = trim($_POST['lname'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($fname) || empty($email) || empty($message)) {
        $error = 'Please fill in Name, Email, and Message fields.';
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MyStore</title>
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

        /* ── TWO COLUMN LAYOUT ── */
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        /* ── INFO CARDS ── */
        .info-col { display: flex; flex-direction: column; gap: 20px; }

        .info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            padding: 25px 28px;
            display: flex;
            align-items: flex-start;
            gap: 18px;
            transition: transform 0.25s, box-shadow 0.25s;
        }
        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 35px rgba(102,126,234,0.18);
        }
        .info-icon {
            font-size: 32px;
            flex-shrink: 0;
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #667eea22, #764ba222);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .info-card h4 { font-size: 15px; font-weight: 600; color: #555; margin-bottom: 5px; }
        .info-card p { font-size: 15px; color: #333; font-weight: 500; line-height: 1.5; }
        .info-card small { font-size: 12px; color: #aaa; }

        /* ── CONTACT FORM ── */
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            padding: 35px 40px;
        }
        .form-card h3 { font-size: 22px; font-weight: 700; color: #333; margin-bottom: 6px; }
        .form-card .sub { font-size: 14px; color: #888; margin-bottom: 28px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #555; font-weight: 500; font-size: 14px; }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.3s;
            outline: none;
            background: white;
        }
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus { border-color: #667eea; }
        .form-group textarea { resize: vertical; min-height: 130px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102,126,234,0.4);
        }

        /* ── SUCCESS / ERROR MESSAGE ── */
        .success-msg {
            background: #d4f5e9;
            color: #00b894;
            border-radius: 8px;
            padding: 15px 18px;
            margin-top: 15px;
            font-weight: 600;
            text-align: center;
            font-size: 15px;
        }
        .error-msg {
            background: #fdecea;
            color: #e17055;
            border-radius: 8px;
            padding: 15px 18px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
            font-size: 15px;
        }

        /* ── MAP PLACEHOLDER ── */
        .map-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.07);
            overflow: hidden;
            margin-bottom: 40px;
        }
        .map-header { padding: 20px 28px 0; }
        .map-header h3 { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 4px; }
        .map-header p { font-size: 14px; color: #888; margin-bottom: 16px; }
        .map-placeholder {
            background: linear-gradient(135deg, #f0f2ff 0%, #e8eaf6 100%);
            height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #667eea;
            gap: 10px;
        }
        .map-placeholder .map-icon { font-size: 52px; }
        .map-placeholder p { font-size: 15px; font-weight: 600; }
        .map-placeholder small { font-size: 13px; color: #888; }

        /* ── FAQ ── */
        .section-title { font-size: 26px; font-weight: 700; color: #333; margin-bottom: 8px; }
        .section-sub { color: #888; font-size: 15px; margin-bottom: 25px; }

        .faq-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 40px; }

        .faq-item {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .faq-question {
            padding: 18px 22px;
            font-size: 15px;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        .faq-question:hover { color: #667eea; }
        .faq-arrow { font-size: 18px; transition: transform 0.3s; color: #667eea; }
        .faq-answer {
            display: none;
            padding: 0 22px 18px;
            font-size: 14px;
            color: #666;
            line-height: 1.8;
            border-top: 1px solid #f0f0f0;
            padding-top: 14px;
        }
        .faq-item.open .faq-arrow { transform: rotate(180deg); }
        .faq-item.open .faq-answer { display: block; }

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
            .contact-layout { grid-template-columns: 1fr; }
            .footer-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .hero h2 { font-size: 28px; }
            .form-row { grid-template-columns: 1fr; }
            header { padding: 0 20px; }
            nav { gap: 15px; }
            .form-card { padding: 25px 20px; }
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
        <h2>📬 Contact Us</h2>
        <p>We'd love to hear from you — reach out anytime!</p>
    </section>

    <!-- ══ MAIN CONTENT ══ -->
    <div class="main-content">

        <!-- Contact Layout -->
        <div class="contact-layout">

            <!-- Left: Info Cards -->
            <div class="info-col">
                <div class="info-card">
                    <div class="info-icon">📍</div>
                    <div>
                        <h4>Our Address</h4>
                        <p>college road,near colloge</p>
                        <p>modasa, Gujarat 383315</p>
                        <small>Visit us Mon–Sat, 10am–7pm</small>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon">📞</div>
                    <div>
                        <h4>Phone Number</h4>
                        <p>+91 98765 43210</p>
                        <small>Available 9am – 8pm, all days</small>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon">📧</div>
                    <div>
                        <h4>Email Address</h4>
                        <p>support@mystore.in</p>
                        <small>We reply within 24 hours</small>
                    </div>
                </div>
                <div class="info-card">
                    <div class="info-icon">⏰</div>
                    <div>
                        <h4>Business Hours</h4>
                        <p>Mon – Sat: 10:00 AM – 7:00 PM</p>
                        <small>Sunday: Closed</small>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Form -->
            <div class="form-card">
                <h3>✉️ Send Us a Message</h3>
                <p class="sub">Fill in the form below and we'll get back to you soon.</p>

                <?php if ($success): ?>
                    <div class="success-msg">✅ Thank you! Your message has been sent. We'll reply within 24 hours.</div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-msg">⚠️ <?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if (!$success): ?>
                <form method="POST" action="contact.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fname">First Name</label>
                            <input type="text" id="fname" name="fname" placeholder="aman"
                                value="<?php echo htmlspecialchars($_POST['fname'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="lname">Last Name</label>
                            <input type="text" id="lname" name="lname" placeholder="chauhan"
                                value="<?php echo htmlspecialchars($_POST['lname'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="aman@example.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" placeholder="+91 98765 43210"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <select id="subject" name="subject">
                            <option value="">Select a subject</option>
                            <option value="order"   <?php echo (($_POST['subject'] ?? '') === 'order')    ? 'selected' : ''; ?>>Order Enquiry</option>
                            <option value="product" <?php echo (($_POST['subject'] ?? '') === 'product')  ? 'selected' : ''; ?>>Product Information</option>
                            <option value="complaint" <?php echo (($_POST['subject'] ?? '') === 'complaint') ? 'selected' : ''; ?>>Complaint</option>
                            <option value="feedback" <?php echo (($_POST['subject'] ?? '') === 'feedback') ? 'selected' : ''; ?>>Feedback</option>
                            <option value="other"   <?php echo (($_POST['subject'] ?? '') === 'other')   ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" placeholder="Write your message here..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">📨 Send Message</button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Map Placeholder -->
        <div class="map-section">
            <div class="map-header">
                <h3>📍 Find Us</h3>
                <p>college road, near college </p>
            </div>
            <div class="map-placeholder">
                <div class="map-icon">🗺️</div>
                <p>MyStore — modasa, Gujarat</p>
                <small>123 Market Street, Sector 5, 383315</small>
            </div>
        </div>

        <!-- FAQ -->
        <div class="section-title">❓ Frequently Asked Questions</div>
        <div class="section-sub">Quick answers to common questions</div>

        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How do I check product availability?
                    <span class="faq-arrow">▼</span>
                </div>
                <div class="faq-answer">
                    All product availability is shown live on our store page. Items marked "In Stock" are ready to purchase. "Low Stock" means limited units remain — act fast!
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How quickly do you respond to messages?
                    <span class="faq-arrow">▼</span>
                </div>
                <div class="faq-answer">
                    We aim to respond to all queries within 24 hours on business days. For urgent matters, please call us directly at +91 98765 43210.
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    Can I visit the store in person?
                    <span class="faq-arrow">▼</span>
                </div>
                <div class="faq-answer">
                    Yes! We are open Monday to Saturday from 10:00 AM to 7:00 PM. Our store is located at college road, near college, modasa, Gujarat.
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFaq(this)">
                    How often is the product catalog updated?
                    <span class="faq-arrow">▼</span>
                </div>
                <div class="faq-answer">
                    Our admin team updates the inventory in real-time. Any new item added, edited, or removed by the admin is reflected on the store page within seconds.
                </div>
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
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="admin.php">Admin Panel</a></li>
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

    <script>
        function toggleFaq(el) {
            const item = el.parentElement;
            item.classList.toggle('open');
        }
    </script>
</body>
</html>
