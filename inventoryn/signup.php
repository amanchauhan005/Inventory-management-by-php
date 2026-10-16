<?php
session_start();
require_once 'config.php';

if (isset($_SESSION['user_name'])) {
    header("Location: home.php"); exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($pass) || empty($confirm)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($name) < 2) {
        $error = "Name must be at least 2 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } elseif (strlen($pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($pass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "This email is already registered. Please login.";
        } else {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $ins->bind_param("sss", $name, $email, $hashed);
            if ($ins->execute()) {
                header("Location: login.php?registered=1"); exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
            $ins->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up – MyStore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;flex-direction:column;}
        header{padding:18px 40px;display:flex;align-items:center;justify-content:space-between;}
        .logo-link{font-size:22px;font-weight:700;color:white;text-decoration:none;display:flex;align-items:center;gap:10px;}
        .back-home{color:rgba(255,255,255,.8);text-decoration:none;font-size:14px;display:flex;align-items:center;gap:6px;}
        .back-home:hover{color:white;}
        .card-wrapper{flex:1;display:flex;align-items:center;justify-content:center;padding:30px 20px;}
        .card{background:white;border-radius:20px;padding:45px 40px;width:100%;max-width:450px;box-shadow:0 25px 60px rgba(0,0,0,.2);}
        .card-icon{text-align:center;font-size:50px;margin-bottom:10px;}
        .card h2{text-align:center;font-size:26px;font-weight:700;color:#333;margin-bottom:6px;}
        .subtitle{text-align:center;color:#888;font-size:14px;margin-bottom:30px;}
        .alert-error{background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;border-radius:10px;padding:12px 16px;font-size:14px;margin-bottom:20px;}
        .form-group{margin-bottom:18px;}
        label{display:block;font-size:14px;font-weight:600;color:#444;margin-bottom:7px;}
        .input-wrap{position:relative;}
        .input-wrap i.field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#aaa;font-size:15px;}
        input{width:100%;padding:13px 14px 13px 42px;border:2px solid #e5e7eb;border-radius:10px;font-size:15px;outline:none;transition:border-color .3s;color:#333;}
        input:focus{border-color:#667eea;}
        .toggle-password{position:absolute;right:14px;top:50%;transform:translateY(-50%);cursor:pointer;color:#aaa;font-size:15px;}
        .strength-bar{display:flex;gap:4px;margin-top:8px;}
        .strength-bar span{flex:1;height:4px;background:#e5e7eb;border-radius:2px;transition:background .3s;}
        .submit-btn{width:100%;padding:14px;background:linear-gradient(135deg,#667eea,#764ba2);color:white;border:none;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer;transition:opacity .2s,transform .2s;margin-top:8px;}
        .submit-btn:hover{opacity:.92;transform:translateY(-1px);}
        .divider{text-align:center;color:#aaa;font-size:13px;margin:22px 0;position:relative;}
        .divider::before,.divider::after{content:'';position:absolute;top:50%;width:38%;height:1px;background:#e5e7eb;}
        .divider::before{left:0} .divider::after{right:0}
        .login-link{text-align:center;font-size:14px;color:#666;}
        .login-link a{color:#667eea;font-weight:700;text-decoration:none;}
        .login-link a:hover{text-decoration:underline;}
    </style>
</head>
<body>
<header>
    <a href="home.php" class="logo-link"><i class="fa-solid fa-store"></i> MyStore</a>
    <a href="home.php" class="back-home"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
</header>
<div class="card-wrapper">
    <div class="card">
        <div class="card-icon">🛍️</div>
        <h2>Create Account</h2>
        <p class="subtitle">Join MyStore</p>

        <?php if ($error): ?><div class="alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-user field-icon"></i>
                    <input type="text" name="name" placeholder="Your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-envelope field-icon"></i>
                    <input type="email" name="email" placeholder="abc@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-lock field-icon"></i>
                    <input type="password" id="password" name="password" placeholder="At least 6 characters" oninput="checkStrength(this.value)" required>
                    <span class="toggle-password" onclick="togglePass('password','eye1')"><i class="fa-solid fa-eye" id="eye1"></i></span>
                </div>
                <div class="strength-bar"><span id="s1"></span><span id="s2"></span><span id="s3"></span><span id="s4"></span></div>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <div class="input-wrap">
                    <i class="fa-solid fa-shield-halved field-icon"></i>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                    <span class="toggle-password" onclick="togglePass('confirm_password','eye2')"><i class="fa-solid fa-eye" id="eye2"></i></span>
                </div>
            </div>
            <button type="submit" class="submit-btn"><i class="fa-solid fa-user-plus"></i> Create Account</button>
        </form>
        <div class="divider">Or</div>
        <div class="login-link">Already have an account? <a href="login.php">Login</a></div>
    </div>
</div>
<script>
    function togglePass(f,i){const inp=document.getElementById(f),icon=document.getElementById(i);inp.type=inp.type==='password'?'text':'password';icon.className=inp.type==='password'?'fa-solid fa-eye':'fa-solid fa-eye-slash';}
    function checkStrength(val){const bars=[...Array(4)].map((_,i)=>document.getElementById('s'+(i+1)));const colors=['#ef4444','#f97316','#eab308','#22c55e'];let s=0;if(val.length>=6)s++;if(val.length>=10)s++;if(/[A-Z]/.test(val)&&/[0-9]/.test(val))s++;if(/[^A-Za-z0-9]/.test(val))s++;bars.forEach((b,i)=>b.style.background=i<s?colors[s-1]:'#e5e7eb');}
</script>
</body>
</html>
