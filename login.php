<?php
require_once "config.php";
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

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
                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'YOUR_EMAIL@gmail.com';   // your sending email
                $mail->Password = 'YOUR_APP_PASSWORD';      // SMTP/app password
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                $mail->setFrom('YOUR_EMAIL@gmail.com', 'Task Manager');
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
<title>Login - Task Manager</title>
<style>
body { font-family: Arial, sans-serif; background:#f4f7f8; display:flex; justify-content:center; align-items:center; height:100vh; }
.container { background:#fff; padding:40px; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.1); width:350px; text-align:center; }
h2 { margin-bottom:30px; color:#333; }
input[type=email], input[type=password] { width:100%; padding:12px; margin:10px 0; border-radius:6px; border:1px solid #ccc; }
button { width:100%; padding:12px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer; font-size:16px; }
button:hover { background:#0056b3; }
.error { color:red; text-align:center; margin-bottom:10px; }
a { display:block; text-align:center; margin-top:15px; text-decoration:none; color:#007bff; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>
<div class="container">
<h2>Login</h2>
<?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
<form action="" method="POST">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
<a href="register.php">Don't have an account? Register</a>
</div>
</body>
</html>
