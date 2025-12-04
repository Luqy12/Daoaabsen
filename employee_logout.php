<?php
session_start();

if (isset($_SESSION['emp_id'])) {
    $emp_id = $_SESSION['emp_id'];

    // Log logout activity
    require_once 'db.php';
    $mysqli = db_connect();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $mysqli->prepare("INSERT INTO activity_logs (user_type, user_id, action, description, ip_addr) VALUES ('employee', ?, 'logout', 'Employee logout', ?)");
    $stmt->bind_param('is', $emp_id, $ip);
    $stmt->execute();
    $mysqli->close();
}

session_destroy();
header('Location: employee_login.php');
exit;
?>