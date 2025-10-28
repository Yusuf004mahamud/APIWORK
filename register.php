<?php
require_once "config.php";

// Composer autoload for PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

$error = '';
$email_status = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (strlen($password) < 3) {
        $error = "Password must be at least 3 characters long.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email is already registered.";
        } else {
            // Hash password and generate verification code
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_code = rand(100000, 999999);

            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$name, $email, $hashed_password, $verification_code]);
            $user_id = $pdo->lastInsertId();

            // Attempt to send verification email
            $email_sent = false;
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yusuf.mahamud@strathmore.edu';      // Your email
                $mail->Password = 'ukql spey nido jmvh';               // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('yusuf.mahamud@strathmore.edu', 'Task Manager');
                $mail->addAddress($email);
                $mail->Subject = "Verify your email";
                $mail->Body = "Hello $name,\n\nYour verification code is: $verification_code";

                $email_sent = $mail->send();
                $email_status = $email_sent ? 'Verification email sent!' : 'Email could not be sent. You can continue to the calendar.';
            } catch (PHPMailer\PHPMailer\Exception $e) {
                $email_status = 'Email could not be sent. You can continue to the calendar.';
                error_log("PHPMailer Error: " . $e->getMessage());
            }

            // Auto-login
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $name;

            // Redirect with optional email fallback message
            if($email_sent){
                header("Location: verify.php?email=" . urlencode($email));
            } else {
                $_SESSION['email_error'] = $email_status; // store message for calendar page
                header("Location: calendar.php");
            }
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Task Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-container">
<div class="auth-box">
<h2>Create Account</h2>
<?php
if(!empty($error)) echo "<div class='error'>$error</div>";
if(!empty($email_status)) echo "<div class='status'>$email_status</div>";
?>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password (min 3 chars)" required>
    <button type="submit">Register</button>
</form>
<a href="login.php">Already have an account?</a>
</div>
</div>
</body>
</html>
