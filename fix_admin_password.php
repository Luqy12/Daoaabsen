<?php
require_once 'db.php';

// Generate new password hash for 'admin123'
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Fix Script</h2>";
echo "<p><strong>Password:</strong> $password</p>";
echo "<p><strong>New Hash:</strong> $hash</p>";

// Update admin password
$mysqli = db_connect();
$stmt = $mysqli->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->bind_param('s', $hash);

if ($stmt->execute()) {
    echo "<p style='color:green'><strong>✓ Password berhasil diupdate!</strong></p>";

    // Verify
    $verify_stmt = $mysqli->prepare("SELECT id, username, password FROM admins WHERE username = 'admin'");
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "<p><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</p>";

        // Test verification
        if (password_verify($password, $admin['password'])) {
            echo "<p style='color:green'><strong>✓ Password verification test: SUCCESS!</strong></p>";
            echo "<p>Anda sekarang bisa login dengan:</p>";
            echo "<ul>";
            echo "<li><strong>Username:</strong> admin</li>";
            echo "<li><strong>Password:</strong> admin123</li>";
            echo "</ul>";
        } else {
            echo "<p style='color:red'><strong>✗ Password verification test: FAILED!</strong></p>";
        }
    }
} else {
    echo "<p style='color:red'><strong>✗ Error updating password:</strong> " . $mysqli->error . "</p>";
}

$mysqli->close();
?>