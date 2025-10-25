<?php
require_once "db_connect.php";

if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
} else {
    echo "✅ Successfully connected to the database!";
}

$conn->close();
?>
