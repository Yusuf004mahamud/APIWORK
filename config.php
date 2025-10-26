<?php
// Database Connection Settings
$host = "localhost";
$dbname = "taskmanager"; // your database name
$username = "root";      // default XAMPP username
$password = "";          // leave empty unless you changed it

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
