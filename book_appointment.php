<?php
// book_appointment.php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Enable CORS if needed (modify according to your security requirements)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include the database connection
include 'connection.php';

// Log the received data
$input = json_decode(file_get_contents("php://input"), true);
error_log("Received appointment data: " . print_r($input, true));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$tuition_center_id = $input['tuition_center_id'] ?? null;
$date = $input['date'] ?? null;
$time = $input['time'] ?? null;
$reason = $input['reason'] ?? null;

// Validate input
if (!$tuition_center_id || !$date || !$time || !$reason) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Validate date and time
try {
    $dateTime = new DateTime($date . ' ' . $time);
} catch (Exception $e) {
    error_log("Invalid date/time format: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Invalid date or time format']);
    exit;
}

$dayOfWeek = $dateTime->format('N'); // 1 (Monday) to 7 (Sunday)
$hour = $dateTime->format('G');
$minute = $dateTime->format('i');

if ($dayOfWeek > 5) {
    echo json_encode(['success' => false, 'message' => 'Appointments are only available on weekdays']);
    exit;
}

if ($hour < 11 || $hour >= 17 || ($hour == 16 && $minute > 30)) {
    echo json_encode(['success' => false, 'message' => 'Appointments are only available between 11 AM and 5 PM']);
    exit;
}

if ($minute != '00' && $minute != '30') {
    echo json_encode(['success' => false, 'message' => 'Appointments must be booked on the hour or half-hour']);
    exit;
}

$appointmentDateTime = $dateTime->format('Y-m-d H:i:s');

// Check for existing appointments at the same time
$stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE tuition_center_id = ? AND appointment_datetime = ?");
if ($stmt === false) {
    error_log("Prepare failed for checking existing appointments: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for checking existing appointments']);
    exit;
}

$stmt->bind_param("is", $tuition_center_id, $appointmentDateTime);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    echo json_encode(['success' => false, 'message' => 'This time slot is already booked']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO appointments (user_id, tuition_center_id, appointment_datetime, reason, status) VALUES (?, ?, ?, ?, 'pending')");
if ($stmt === false) {
    error_log("Prepare failed for inserting appointment: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare statement for inserting appointment']);
    exit;
}

$stmt->bind_param("iiss", $userId, $tuition_center_id, $appointmentDateTime, $reason);

error_log("Attempting to insert appointment: User ID: $userId, Tuition Center ID: $tuition_center_id, DateTime: $appointmentDateTime, Reason: $reason");

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Appointment successfully booked.']);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Failed to book appointment: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

?>
