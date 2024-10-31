<?php
// get_available_slots.php

// Include the database connection
include 'connection.php';

// Get the selected date from the frontend
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['date'])) {
    echo json_encode(['success' => false, 'message' => 'Date is required.']);
    exit;
}

$date = $input['date'];

// Fetch all available time slots for the selected date (assuming slots table exists)
$sql = "SELECT time FROM available_slots WHERE date = ? AND status = 'available'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$availableSlots = [];
while ($row = $result->fetch_assoc()) {
    $availableSlots[] = $row['time'];
}

// If no available slots found, return a message
if (empty($availableSlots)) {
    echo json_encode(['success' => false, 'message' => 'No available time slots for this date.']);
} else {
    echo json_encode(['success' => true, 'slots' => $availableSlots]);
}

$stmt->close();
$conn->close();
?>
