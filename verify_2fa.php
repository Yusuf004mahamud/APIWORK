<?php
session_start();

if (!isset($_SESSION['2fa_code'])) {
    header("Location: generate_2fa.php");
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['code']);
    if ($input == $_SESSION['2fa_code']) {
        $_SESSION['verified'] = true;
        unset($_SESSION['2fa_code']);
        header("Location: dashboard.php");
        exit;
    } else {
        $message = "❌ Incorrect code. Try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>2FA Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4">
        <h4 class="text-center mb-3">🔐 Two-Factor Authentication</h4>
        <p class="text-center">Your code: <strong><?= $_SESSION['2fa_code'] ?></strong></p>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <input type="text" name="code" class="form-control" placeholder="Enter 6-digit code" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Verify</button>
        </form>
    </div>
</body>
</html>
