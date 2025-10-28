<?php
require_once 'config.php';
require_once 'utils.php';
require_once 'Database.php';
session_start();

if (!isset($_SESSION['user_id'])) redirect('login.php');
$db = Database::getInstance()->getConnection();
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = sanitize($_POST['current'] ?? '');
    $new = sanitize($_POST['new'] ?? '');
    
    if (strlen($new) < 3) $errors[] = 'New password must be at least 3 characters.';
    if (strlen($new) > 60) $errors[] = 'New password too long.';
    
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
        if (!$user || !isset($user['password']) || !password_verify($current, $user['password'])) {
            $errors[] = 'Current password incorrect.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $success = 'Password changed successfully!';
        }
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Change Password</title>
<style>
body { font-family:Arial, sans-serif; display:flex; justify-content:center; align-items:center; height:100vh; background:#f4f7f8; }
.form-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.15); max-width:400px; width:100%; }
.form-box h2 { text-align:center; margin-bottom:20px; }
.form-box input, .form-box button { width:100%; padding:12px; margin:8px 0; border-radius:6px; border:1px solid #ccc; font-size:14px; }
.form-box button { background:#2575fc; color:#fff; border:none; cursor:pointer; transition:0.3s; }
.form-box button:hover { background:#6a11cb; }
.message { margin-top:10px; text-align:center; }
</style>
</head>
<body>
<div class="form-box">
    <h2>Change Password</h2>
    <?php if($errors): ?>
        <div class="message" style="color:#900;">
            <?php foreach($errors as $e) echo "<div>".htmlspecialchars($e)."</div>"; ?>
        </div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="message" style="color:green;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="password" name="current" placeholder="Current password" required>
        <input type="password" name="new" placeholder="New password (3-60 chars)" required>
        <button type="submit">Change</button>
    </form>
    <p style="text-align:center; margin-top:10px;">
        <a href="calendar.php">Back to Dashboard</a>
    </p>
</div>
</body>
</html>
