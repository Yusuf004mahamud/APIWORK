<?php
require_once 'config.php';
require_once 'utils.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['error'=>'Unauthorized']); exit; }
$userId = $_SESSION['user_id'];
$title = trim($_POST['title'] ?? '');
$date = $_POST['date'] ?? '';
$time_from = $_POST['time_from'] ?: null;
$time_to = $_POST['time_to'] ?: null;
$color = $_POST['color'] ?? '#ff6a00';
$description = trim($_POST['description'] ?? '');

if (!$title || !$date) { echo json_encode(['error'=>'Title and date required']); exit; }
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('INSERT INTO tasks (user_id,title,description,date,time_from,time_to,color) VALUES (?,?,?,?,?,?,?)');
$stmt->execute([$userId,$title,$description,$date,$time_from,$time_to,$color]);
echo json_encode(['success'=>true]);
