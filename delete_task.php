<?php
require_once 'config.php';
require_once 'utils.php';
session_start();
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
if (!isset($_SESSION['user_id'])) { echo json_encode(['error'=>'Unauthorized']); exit; }
$id = intval($input['id'] ?? 0);
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
echo json_encode(['success'=>true]);
