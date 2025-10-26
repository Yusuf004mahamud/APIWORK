<?php
require_once "config.php";
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

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
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yusuf.mahamud@strathmore.edu';      // Replace with your email
                $mail->Password = 'ukql spey nido jmvh';         // Replace with your app password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                // Enable debug
                $mail->SMTPDebug = 2;
                $mail->Debugoutput = 'html';

                $mail->setFrom('YOUR_EMAIL@gmail.com', 'Task Manager');
                $mail->addAddress($email);
                $mail->Subject = "Verify your email";
                $mail->Body = "Hello $name,\n\nYour verification code is: $verification_code";

                if($mail->send()) {
                    $email_sent = true;
                }
            } catch (Exception $e) {
                echo "<p style='color:red;'>❌ Email could not be sent. Error: {$mail->ErrorInfo}</p>";
            }

            // Auto-login (even if email fails)
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = $name;

            // If email sent, redirect to verify page; else go to calendar
            if($email_sent){
                header("Location: verify.php?email=" . urlencode($email));
            } else {
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
<title>Register - Task Manager</title>
<style>
body { margin:0; font-family:Arial,sans-serif; background:#f4f7f8; height:100vh; display:flex; justify-content:center; align-items:center; }
.container { background:#fff; padding:40px; border-radius:10px; width:350px; box-shadow:0 8px 20px rgba(0,0,0,0.1); text-align:center; }
h2 { margin-bottom:30px; color:#333; }
input[type=text], input[type=email], input[type=password] { width:100%; padding:12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; box-sizing:border-box; }
button { width:100%; padding:12px; background:#28a745; color:white; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button:hover { background:#218838; }
a { display:block; margin-top:15px; text-decoration:none; color:#28a745; }
a:hover { text-decoration:underline; }
.error { color:red; margin-bottom:15px; }
</style>
</head>
<body>
<div class="container">
<h2>Create Account</h2>
<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password (min 3 chars)" required>
    <button type="submit">Register</button>
</form>
<a href="login.php">Already have an account?</a>
</div>
</body>
</html>
