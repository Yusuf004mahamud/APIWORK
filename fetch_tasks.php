<?php
require_once 'config.php';
require_once 'utils.php';
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode([]); exit; }
$userId = $_SESSION['user_id'];
$year = intval($_GET['year'] ?? date('Y'));
$month = intval($_GET['month'] ?? date('n'));
$start = sprintf('%04d-%02d-01',$year,$month);
$end = date('Y-m-t', strtotime($start));
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('SELECT * FROM tasks WHERE user_id = ? AND date BETWEEN ? AND ? ORDER BY date, time_from');
$stmt->execute([$userId,$start,$end]);
$rows = $stmt->fetchAll();
echo json_encode($rows);
