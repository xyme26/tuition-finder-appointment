<?php
session_start();
require 'connection.php'; // Include database connection file

header('Content-Type: application/json'); // Set content type to JSON

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch upcoming appointments (not completed)
$sqlUpcoming = "SELECT a.id, a.appointment_datetime, a.reason, a.status, tc.name AS tuition_name
                FROM appointments a
                JOIN tuition_centers tc ON a.tuition_center_id = tc.id
                WHERE a.user_id = ? 
                AND a.appointment_datetime >= NOW()
                AND a.status != 'completed'
                ORDER BY a.appointment_datetime ASC";

$stmtUpcoming = $conn->prepare($sqlUpcoming);
$stmtUpcoming->bind_param("i", $user_id);
$stmtUpcoming->execute();
$resultUpcoming = $stmtUpcoming->get_result();
$upcomingAppointments = $resultUpcoming->fetch_all(MYSQLI_ASSOC);

// Fetch past appointments (including completed ones)
$sqlPast = "SELECT a.id, a.appointment_datetime, a.reason, a.status, tc.name AS tuition_name
            FROM appointments a
            JOIN tuition_centers tc ON a.tuition_center_id = tc.id
            WHERE a.user_id = ? 
            AND (a.appointment_datetime < NOW() OR a.status = 'completed')
            AND a.appointment_datetime >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY a.appointment_datetime DESC";

$stmtPast = $conn->prepare($sqlPast);
$stmtPast->bind_param("i", $user_id);
$stmtPast->execute();
$resultPast = $stmtPast->get_result();
$pastAppointments = $resultPast->fetch_all(MYSQLI_ASSOC);

// Return the data as JSON
echo json_encode([
    'success' => true,
    'upcoming' => $upcomingAppointments,
    'past' => $pastAppointments
]);

// Close the statements and connection
$stmtUpcoming->close();
$stmtPast->close();
$conn->close();
?>
