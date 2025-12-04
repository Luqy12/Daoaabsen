<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];

    // Log logout activity
    require_once 'db.php';
    $mysqli = db_connect();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $mysqli->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_addr) VALUES ('admin', ?, 'logout', 'Admin logout', ?)");
    $stmt->bind_param('is', $admin_id, $ip);
    $stmt->execute();
    $mysqli->close();
}

session_destroy();
header('Location: admin_login.php');
exit;
?>