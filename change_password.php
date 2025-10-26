<?php
require_once 'config.php';
require_once 'utils.php';
session_start();
if (!isset($_SESSION['user_id'])) redirect('login.php');
$db = Database::getInstance()->getConnection();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    if (strlen($new) < 3) $errors[] = 'New password must be at least 3 characters.';
    if (strlen($new) > 60) $errors[] = 'New password too long.';
    if (empty($errors)) {
        $stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($current, $user['password'])) {
            $errors[] = 'Current password incorrect.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $success = 'Password changed.';
        }
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Change Password</title><link rel="stylesheet" href="style.css"></head><body>
<div style="padding:40px;">
  <div class="form-box" style="max-width:420px;margin:80px auto;z-index:2;">
    <h2>Change Password</h2>
    <?php if(!empty($errors)): ?><div style="color:#900"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>
    <?php if(!empty($success)): ?><div style="color:green"><?php echo htmlspecialchars($success);?></div><?php endif; ?>
    <form method="post">
      <input type="password" name="current" placeholder="Current password" required>
      <input type="password" name="new" placeholder="New password (3-60 chars)" required>
      <button type="submit">Change</button>
    </form>
  </div>
</div>
</body></html>
