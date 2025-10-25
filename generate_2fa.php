<?php
session_start();
if (!isset($_SESSION['2fa_code'])) {
    $_SESSION['2fa_code'] = rand(100000, 999999);
}
header("Location: verify_2fa.php");
exit;
?>
