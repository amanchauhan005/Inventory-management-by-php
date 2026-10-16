<?php
session_start();
unset($_SESSION['admin_logged_in'], $_SESSION['admin_username']);
header("Location: admin_login.php");
exit();
