<?php
require_once 'db.php';

// Override DB_NAME to connect without selecting a DB first, to create it if needed
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($mysqli->connect_errno) {
    die('MySQL Connection Error: ' . $mysqli->connect_error);
}

// Create database if not exists
$dbName = 'absensitest';
$sql = "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($mysqli->query($sql) === TRUE) {
    echo "Database '$dbName' created or already exists.\n";
} else {
    die("Error creating database: " . $mysqli->error);
}

// Select the database
$mysqli->select_db($dbName);

// Read install.sql
$sqlFile = 'install.sql';
if (!file_exists($sqlFile)) {
    die("Error: $sqlFile not found.\n");
}

$sqlContent = file_get_contents($sqlFile);

// Execute multi query
if ($mysqli->multi_query($sqlContent)) {
    do {
        // Store first result set
        if ($result = $mysqli->store_result()) {
            $result->free();
        }
        // Check if there are more result sets
    } while ($mysqli->next_result());
    echo "Database schema imported successfully from $sqlFile.\n";
} else {
    echo "Error importing schema: " . $mysqli->error . "\n";
}

$mysqli->close();
?>