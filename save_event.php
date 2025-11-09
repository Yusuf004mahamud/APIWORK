<?php
require_once "config.php";
header('Content-Type: application/json');

if (
    empty($_POST['event_name']) ||
    empty($_POST['event_start_date']) ||
    empty($_POST['event_end_date'])
) {
    echo json_encode(['status' => false, 'msg' => 'Please fill in all fields.']);
    exit;
}

$event_name = trim($_POST['event_name']);
$event_start_date = $_POST['event_start_date'];
$event_end_date = $_POST['event_end_date'];

try {
    $stmt = $pdo->prepare("
        INSERT INTO calendar_event_master (event_name, event_start_date, event_end_date)
        VALUES (:event_name, :event_start_date, :event_end_date)
    ");
    $stmt->execute([
        'event_name' => $event_name,
        'event_start_date' => $event_start_date,
        'event_end_date' => $event_end_date
    ]);

    echo json_encode(['status' => true, 'msg' => '✅ Event saved successfully!']);
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'msg' => '❌ Database error: ' . $e->getMessage()]);
}
