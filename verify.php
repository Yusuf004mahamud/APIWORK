<?php
require_once "config.php";
session_start();

if (!isset($_GET['email'])) {
    die("No email provided.");
}

$email = $_GET['email'];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST["code"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND verification_code = ?");
    $stmt->execute([$email, $code]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Mark user as verified
        $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?")->execute([$email]);

        // Log user in
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["name"];

        header("Location: calendar.php");
        exit();
    } else {
        $errors[] = "❌ Invalid verification code. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Email - Task Manager</title>
<style>
body { margin:0; font-family: Arial,sans-serif; background:#f4f7f8; height:100vh; display:flex; justify-content:center; align-items:center; }
.container { background:#fff; padding:40px; border-radius:10px; width:350px; box-shadow:0 8px 20px rgba(0,0,0,0.1); text-align:center; }
h2 { margin-bottom:20px; color:#333; }
input[type=text] { width:100%; padding:12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; }
button { width:100%; padding:12px; background:#28a745; color:white; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button:hover { background:#218838; }
.error { color:red; margin-bottom:15px; }
</style>
</head>
<body>
<div class="container">
<h2>Verify Your Email</h2>
<p>We sent a 6-digit code to your email: <?php echo htmlspecialchars($email); ?></p>

<?php if(!empty($errors)) {
    foreach($errors as $e) echo "<div class='error'>$e</div>";
} ?>

<form method="POST">
    <input type="text" name="code" placeholder="Enter 6-digit code" required>
    <button type="submit">Verify</button>
</form>
</div>
</body>
</html>