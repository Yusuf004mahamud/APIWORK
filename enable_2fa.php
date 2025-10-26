<?php
// Enable TOTP 2FA - generates secret and shows QR code link
require_once 'config.php';
require_once 'utils.php';
session_start();
if (!isset($_SESSION['user_id'])) redirect('login.php');
$db = Database::getInstance()->getConnection();
$userId = $_SESSION['user_id'];
$stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) redirect('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify code entered
    $code = trim($_POST['code'] ?? '');
    if (verify_totp($_POST['secret'] ?? '', $code)) {
        $stmt = $db->prepare('UPDATE users SET totp_secret = ?, totp_enabled = 1 WHERE id = ?');
        $stmt->execute([$_POST['secret'], $userId]);
        redirect('dashboard.php');
    } else {
        $error = 'Invalid code. Try again.';
    }
} else {
    // generate secret
    $secret = generate_totp_secret();
    // provisioning URI for Google Authenticator
    $issuer = urlencode('TaskManager');
    $label = urlencode($user['email']);
    $provisioningUri = "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}&digits=6";
    // QR code via Google Chart API
    $qrUrl = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($provisioningUri);
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Enable 2FA</title><link rel="stylesheet" href="style.css"></head><body>
<div style="padding:40px;">
  <div class="form-box" style="max-width:420px;margin:80px auto;position:relative;z-index:2;text-align:center;">
    <h2>Enable Two-Factor Authentication</h2>
    <?php if(!empty($error)): ?><div style="color:#900;"><?php echo htmlspecialchars($error);?></div><?php endif; ?>
    <p>Scan this QR in Google Authenticator and enter the 6-digit code below to confirm.</p>
    <img src="<?php echo $qrUrl;?>" alt="qr">
    <form method="post">
      <input type="hidden" name="secret" value="<?php echo htmlspecialchars($secret);?>">
      <input type="text" name="code" placeholder="Enter 6-digit code" required>
      <button type="submit">Enable 2FA</button>
    </form>
  </div>
</div>
</body></html>
