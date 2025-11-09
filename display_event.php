<?php
require_once "config.php";
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT * FROM calendar_event_master");
    $events = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => $row['event_id'],
            'title' => $row['event_name'],
            'start' => date('Y-m-d', strtotime($row['event_start_date'])),
            'end' => date('Y-m-d', strtotime($row['event_end_date'] . ' +1 day')),
            'color' => '#007bff'
        ];
    }

    echo json_encode(['status' => true, 'data' => $events], JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
?>
