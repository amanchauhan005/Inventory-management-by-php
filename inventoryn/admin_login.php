<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php"); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        #echo password_verify($password, $admin['password']) ?'':''; exit;  
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $admin['username'];
            header("Location: admin.php"); exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – MyStore</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;flex-direction:column;}
        .card-wrapper{flex:1;display:flex;align-items:center;justify-content:center;padding:30px 20px;}
        .card{background:white;border-radius:20px;padding:45px 40px;width:100%;max-width:400px;box-shadow:0 25px 60px rgba(0,0,0,.2);}
        .card-icon{text-align:center;font-size:55px;margin-bottom:10px;}
        .card h2{text-align:center;font-size:26px;font-weight:700;color:#333;margin-bottom:6px;}
        .subtitle{text-align:center;color:#888;font-size:14px;margin-bottom:30px;}
        .alert-error{background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;}
        .form-group{margin-bottom:20px;}
        label{display:block;font-size:14px;font-weight:600;color:#444;margin-bottom:7px;}
        input{width:100%;padding:13px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:15px;outline:none;transition:border-color .3s;}
        input:focus{border-color:#667eea;}
        .submit-btn{width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;margin-top:8px;}
        .submit-btn:hover{opacity:.92;transform:translateY(-1px);}
        .back-link{display:block;text-align:center;margin-top:20px;color:#888;font-size:14px;text-decoration:none;}
        .back-link:hover{color:#667eea;}
        .hint{background:#f0f2ff;border-radius:8px;padding:10px 14px;font-size:13px;color:#667eea;margin-bottom:20px;text-align:center;}
    </style>
</head>
<body>
<div class="card-wrapper">
    <div class="card">
        <div class="card-icon">⚙️</div>
        <h2>Admin Login</h2>
        <p class="subtitle">MyStore Management Panel</p>
        <div class="hint">Default: <strong>admin</strong> / <strong>admin123</strong></div>
        <?php if ($error): ?><div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="admin" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="submit-btn">🔐 Login to Dashboard</button>
        </form>
        <a href="home.php" class="back-link">← Back to Store</a>
    </div>
</div>
</body>
</html>
