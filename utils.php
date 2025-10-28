<?php
// utils.php - helper functions (including TOTP)
function redirect($url){
    header('Location: ' . $url);
    exit;
}

function sanitize($s){
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

// TOTP functions (RFC6238 compatible)
function base32_encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bin = '';
    foreach (str_split($data) as $c) {
        $bin .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    }
    $fiveBits = str_split($bin, 5);
    $out = '';
    foreach ($fiveBits as $bits) {
        $out .= $alphabet[bindec(str_pad($bits,5,'0', STR_PAD_RIGHT))];
    }
    while (strlen($out) % 8 != 0) {
        $out .= '=';
    }
    return $out;
}

function base32_decode($b32) {
    $b32 = strtoupper($b32);
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = rtrim($b32, '=');
    $bits = '';
    foreach (str_split($b32) as $c) {
        $val = strpos($alphabet, $c);
        if ($val === false) return false;
        $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
    }
    $bytes = str_split($bits, 8);
    $out = '';
    foreach ($bytes as $byte) {
        if (strlen($byte) < 8) continue;
        $out .= chr(bindec($byte));
    }
    return $out;
}

function generate_totp_secret($length = 16){
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // base32 alphabet
    $secret = '';
    for ($i=0;$i<$length;$i++){
        $secret .= $chars[random_int(0,31)];
    }
    return $secret;
}

function get_totp_token($secret, $timeSlice = null){
    if ($timeSlice === null) $timeSlice = floor(time() / 30);
    $secretKey = base32_decode($secret);
    if ($secretKey === false) return false;
    $time = pack('N*', 0) . pack('N*', $timeSlice);
    $hash = hash_hmac('sha1', $time, $secretKey, true);
    $offset = ord($hash[19]) & 0xf;
    $truncatedHash = substr($hash, $offset, 4);
    $value = unpack('N', $truncatedHash)[1] & 0x7fffffff;
    $modulo = 1000000;
    return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
}

function verify_totp($secret, $code, $discrepancy = 1){
    $currentSlice = floor(time() / 30);
    for ($i = -$discrepancy; $i <= $discrepancy; $i++){
        $calc = get_totp_token($secret, $currentSlice + $i);
        if ($calc === $code) return true;
    }
    return false;
}
?>
