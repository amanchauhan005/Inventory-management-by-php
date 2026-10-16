<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_name'])) {
    header("Location: home.php"); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter email and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_id']    = $user['id'];
            header("Location: home.php"); exit();
        } else {
            $error = "Email or password is incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – MyStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;flex-direction:column;}
        header{padding:18px 40px;display:flex;align-items:center;justify-content:space-between;}
        .logo-link{font-size:22px;font-weight:700;color:white;text-decoration:none;display:flex;align-items:center;gap:10px;}
        .back-home{color:rgba(255,255,255,.8);text-decoration:none;font-size:14px;display:flex;align-items:center;gap:6px;transition:color .2s;}
        .back-home:hover{color:white;}
        .card-wrapper{flex:1;display:flex;align-items:center;justify-content:center;padding:30px 20px;}
        .card{background:white;border-radius:20px;padding:45px 40px;width:100%;max-width:420px;box-shadow:0 25px 60px rgba(0,0,0,.2);}
        .card-icon{text-align:center;font-size:50px;margin-bottom:10px;}
        .card h2{text-align:center;font-size:26px;font-weight:700;color:#333;margin-bottom:6px;}
        .subtitle{text-align:center;color:#888;font-size:14px;margin-bottom:30px;}
        .alert-error{background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;}
        .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;}
        .form-group{margin-bottom:20px;}
        label{display:block;font-size:14px;font-weight:600;color:#444;margin-bottom:7px;}
        .input-wrap{position:relative;}
        .input-wrap i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#aaa;font-size:15px;}
        input{width:100%;padding:13px 14px 13px 42px;border:2px solid #e5e7eb;border-radius:10px;font-size:15px;outline:none;transition:border-color .3s;color:#333;}
        input:focus{border-color:#667eea;}
        .toggle-password{position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#aaa;font-size:15px;}
        .toggle-password:hover{color:#667eea;}
        .submit-btn{width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;margin-top:8px;}
        .submit-btn:hover{opacity:.92;transform:translateY(-1px);}
        .divider{text-align:center;color:#aaa;font-size:13px;margin:22px 0;position:relative;}
        .divider::before,.divider::after{content:'';position:absolute;top:50%;width:38%;height:1px;background:#e5e7eb;}
        .divider::before{left:0} .divider::after{right:0}
        .signup-link{text-align:center;font-size:14px;color:#666;}
        .signup-link a{color:#667eea;font-weight:700;text-decoration:none;}
        .signup-link a:hover{text-decoration:underline;}
    </style>
</head>
<body>
<header>
    <a href="home.php" class="logo-link"><i class="fa-solid fa-store"></i> MyStore</a>
    <a href="home.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
</header>
<div class="card-wrapper">
    <div class="card">
        <div class="card-icon">🔐</div>
        <h2>Login</h2>
        <p class="subtitle">Sign in to your account</p>

        <?php if ($error): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if (isset($_GET['registered'])): ?><div class="alert-success"><i class="fa-solid fa-circle-check"></i> Account created! You can now login.</div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="abc@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span class="toggle-password" onclick="togglePass()"><i class="fa-solid fa-eye" id="eyeIcon"></i></span>
                </div>
            </div>
            <button type="submit" class="submit-btn"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        </form>
        <div class="divider">Or</div>
        <div class="signup-link">Don't have an account? <a href="signup.php">Sign Up</a></div>
    </div>
</div>
<script>
    function togglePass(){
        const inp=document.getElementById('password'),icon=document.getElementById('eyeIcon');
        inp.type=inp.type==='password'?'text':'password';
        icon.className=inp.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';
    }
</script>
</body>
</html>
