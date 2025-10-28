<?php
require_once "config.php";
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {

        // Check if user is verified
        if ($user["is_verified"] == 0) {
            // Generate a new verification code
            $verification_code = rand(100000, 999999);
            $pdo->prepare("UPDATE users SET verification_code = ? WHERE id = ?")
                ->execute([$verification_code, $user['id']]);

            // Send email with new code
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'yusuf.mahamud@strathmore.edu';   // your sending email
                $mail->Password = 'ukql spey nido jmvh';      // SMTP/app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                $mail->setFrom('yusuf.mahamud@strathmore.edu', 'Task Manager');
                $mail->addAddress($email);
                $mail->Subject = "Verify your email";
                $mail->Body = "Hello {$user['name']},\n\nYour new verification code is: $verification_code";

                $mail->send();
            } catch (Exception $e) {
                // Failed email still allows verification attempt
            }

            // Store user temporarily for verification
            $_SESSION['pre_2fa_user'] = $user['id'];

            // Redirect to verify page
            header("Location: verify.php?email=" . urlencode($email));
            exit();
        }

        // Verified user – log in normally
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["name"];
        header("Location: calendar.php");
        exit();

    } else {
        $error = "❌ Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Task Manager</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-container">
<div class="auth-box">
<h2>Login</h2>
<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<form action="" method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" class="login-btn">Login</button>
</form>
<a href="register.php">Don't have an account? Register</a>
</div>
</div>
</body>
</html>
