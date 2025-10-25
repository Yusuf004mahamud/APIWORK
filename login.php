<?php
session_start();
require_once "db_connect.php";

// OOP-style database class
class Database {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=localhost;dbname=taskmanager", "root", "");
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getUser($usernameOrEmail) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :input OR email = :input");
        $stmt->execute([':input' => $usernameOrEmail]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$db = new Database();
$message = "";

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usernameOrEmail = trim($_POST['usernameOrEmail']);
    $password = trim($_POST['password']);

    $user = $db->getUser($usernameOrEmail);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Generate a fake 2FA code for demo
        $code = rand(100000, 999999);
        $_SESSION['2fa_code'] = $code;
        $_SESSION['user_temp'] = $user['username'];

        // Normally you’d email this code, but here we’ll just show it
        $message = "✅ Login success! Your 2FA code is <strong>$code</strong>. Enter it below to proceed.";
    } else {
        $message = "❌ Invalid username/email or password.";
    }
}

// Handle 2FA verification
if (isset($_POST['verify_2fa'])) {
    $enteredCode = trim($_POST['two_factor_code']);

    if ($enteredCode == $_SESSION['2fa_code']) {
        $_SESSION['username'] = $_SESSION['user_temp'];
        unset($_SESSION['2fa_code'], $_SESSION['user_temp']);
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "⚠️ Incorrect 2FA code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg p-4">
                <h3 class="text-center mb-4">🔐 Login</h3>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-info text-center"><?= $message ?></div>
                <?php endif; ?>

                <?php if (!isset($_SESSION['2fa_code'])): ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="usernameOrEmail" class="form-label">Username or Email</label>
                            <input type="text" name="usernameOrEmail" id="usernameOrEmail" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="two_factor_code" class="form-label">Enter 2FA Code</label>
                            <input type="text" name="two_factor_code" id="two_factor_code" class="form-control" required>
                        </div>

                        <button type="submit" name="verify_2fa" class="btn btn-success w-100">Verify</button>
                    </form>
                <?php endif; ?>

                <p class="text-center mt-3">Don’t have an account? <a href="register.php">Sign Up</a></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
