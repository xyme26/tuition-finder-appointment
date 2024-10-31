<?php
include 'connection.php';

$tuition_center_id = $_GET['tuition_center_id'];

$stmt = $conn->prepare("SELECT id, appointment_datetime, status FROM appointments WHERE tuition_center_id = ?");
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => 'Booked',
        'start' => $row['appointment_datetime'],
        'color' => ($row['status'] == 'pending') ? 'yellow' : 'green'
    ];
}

echo json_encode($events);

$stmt->close();
$conn->close();