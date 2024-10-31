<?php
session_start();
include 'connection.php';

// Get survey answers from the POST request
$surveyAnswers = json_decode(file_get_contents('php://input'), true);

// Get user's location from session or set default values
$user_lat = $_SESSION['user_lat'] ?? 0;
$user_lon = $_SESSION['user_lon'] ?? 0;

// Build the SQL query based on survey answers
$sql = "SELECT tc.*, 
               (6371 * acos(cos(radians(?)) * cos(radians(tc.latitude)) * cos(radians(tc.longitude) - radians(?)) + sin(radians(?)) * sin(radians(tc.latitude)))) AS distance
        FROM tuition_centers tc
        WHERE 1=1";
$params = [$user_lat, $user_lon, $user_lat];
$types = "ddd";

if (!empty($surveyAnswers['subjects'])) {
    $subjects = $surveyAnswers['subjects'];
    $sql .= " AND (";
    $subjectConditions = [];
    foreach ($subjects as $subject) {
        $subjectConditions[] = "tc.course_tags LIKE ?";
        $params[] = "%$subject%";
        $types .= "s";
    }
    $sql .= implode(" OR ", $subjectConditions) . ")";
}

if (!empty($surveyAnswers['location'])) {
    $sql .= " AND (tc.city LIKE ? OR tc.address LIKE ?)";
    $params[] = "%{$surveyAnswers['location']}%";
    $params[] = "%{$surveyAnswers['location']}%";
    $types .= "ss";
}

if (!empty($surveyAnswers['budget'])) {
    if ($surveyAnswers['budget'] === 'RM0-20') {
        $sql .= " AND tc.price_range <= 20";
    } elseif ($surveyAnswers['budget'] === 'RM20 - RM40') {
        $sql .= " AND tc.price_range > 20 AND tc.price_range <= 40";
    } elseif ($surveyAnswers['budget'] === 'Above RM40') {
        $sql .= " AND tc.price_range > 40";
    }
}

if (!empty($surveyAnswers['language'])) {
    $sql .= " AND tc.language LIKE ?";
    $params[] = "%{$surveyAnswers['language']}%";
    $types .= "s";
}

// Add distance and rating ordering
$sql .= " ORDER BY distance ASC, (SELECT AVG(rating) FROM reviews WHERE tuition_center_id = tc.id) DESC";

// Limit the results
$sql .= " LIMIT 5";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch the results
$recommendations = [];
while ($row = $result->fetch_assoc()) {
    // Format the distance
    $row['distance'] = number_format($row['distance'], 1);
    $recommendations[] = $row;
}

// Return the recommendations as JSON
header('Content-Type: application/json');
echo json_encode($recommendations);

$stmt->close();
$conn->close();
