<?php
require_once 'db.php';

echo "<h2>BPJS Ketenagakerjaan - Database Update</h2>";

$mysqli = db_connect();

// Update existing settings
$updates = [
    "UPDATE settings SET setting_value = 'BPJS Ketenagakerjaan' WHERE setting_key = 'company_name'",
    "UPDATE settings SET setting_value = 'Jl. Jend. Gatot Subroto Kav. 38, Jakarta Selatan' WHERE setting_key = 'company_address'"
];

foreach ($updates as $sql) {
    if ($mysqli->query($sql)) {
        echo "<p style='color:green'>✓ " . htmlspecialchars($sql) . "</p>";
    } else {
        echo "<p style='color:red'>✗ Error: " . $mysqli->error . "</p>";
    }
}

// Add new settings
$newSettings = [
    ['company_tagline', 'Melindungi Pekerja Indonesia', 'string', 'Company tagline'],
    ['company_phone', '1500910', 'string', 'Company phone number'],
    ['company_website', 'www.bpjsketenagakerjaan.go.id', 'string', 'Company website']
];

foreach ($newSettings as $setting) {
    $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->bind_param('ssss', $setting[0], $setting[1], $setting[2], $setting[3]);

    if ($stmt->execute()) {
        echo "<p style='color:green'>✓ Added/Updated: {$setting[0]} = {$setting[1]}</p>";
    } else {
        echo "<p style='color:red'>✗ Error: " . $mysqli->error . "</p>";
    }
}

echo "<hr>";
echo "<h3>Current Settings:</h3>";
$result = $mysqli->query("SELECT * FROM settings WHERE setting_key LIKE 'company%'");
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Key</th><th>Value</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr><td>" . htmlspecialchars($row['setting_key']) . "</td><td>" . htmlspecialchars($row['setting_value']) . "</td></tr>";
}
echo "</table>";

$mysqli->close();

echo "<p><strong>✓ Database successfully updated with BPJS Ketenagakerjaan branding!</strong></p>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='dashboard.php'>Go to Dashboard</a></p>";
?>