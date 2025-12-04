<?php
// Import database schema
require_once 'includes/icons.php';

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'absensitest';

echo "Connecting to database...\n";
$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Connected successfully!\n";
echo "Reading install.sql...\n";

$sql = file_get_contents('install.sql');

if ($sql === false) {
    die("Failed to read install.sql\n");
}

echo "Executing SQL...\n";

// Execute multi-query
if ($mysqli->multi_query($sql)) {
    do {
        if ($result = $mysqli->store_result()) {
            $result->free();
        }

        if ($mysqli->more_results()) {
            echo ".";
        }
    } while ($mysqli->next_result());

    echo "\n";
}

if ($mysqli->error) {
    echo "Error: " . $mysqli->error . "\n";
} else {
    echo "[SUCCESS] Database schema imported successfully!\n";
}

// Check tables
echo "\nTables created:\n";
$result = $mysqli->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    echo "  - " . $row[0] . "\n";
}

$mysqli->close();
echo "\n[SUCCESS] Done!\n";
?>