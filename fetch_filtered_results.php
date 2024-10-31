<?php
include 'connection.php';
session_start();

// Get filter parameters
$name = isset($_GET['name']) ? $_GET['name'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'name';
$minRating = isset($_GET['minRating']) ? floatval($_GET['minRating']) : 0; // Add this line

// Get user's location
$user_lat = $_SESSION['user_lat'] ?? 0;
$user_lon = $_SESSION['user_lon'] ?? 0;

// Base query
$sql = "SELECT tc.*, 
               AVG(r.rating) as avg_rating,
               (6371 * acos(cos(radians(?)) * cos(radians(tc.latitude)) * 
                cos(radians(tc.longitude) - radians(?)) + sin(radians(?)) * 
                sin(radians(tc.latitude)))) AS distance
        FROM tuition_centers tc 
        LEFT JOIN reviews r ON tc.id = r.tuition_center_id 
        WHERE 1=1";

$params = [$user_lat, $user_lon, $user_lat];
$types = "ddd";

// Add name filter
if (!empty($name)) {
    $sql .= " AND tc.name LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

// Add location filter
if (!empty($location)) {
    $sql .= " AND (tc.address LIKE ? OR tc.city LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $types .= "ss";
}

// Group by to handle the AVG() function
$sql .= " GROUP BY tc.id";

// Add minimum rating filter
if ($minRating > 0) {
    $sql .= " HAVING avg_rating >= ?";
    $params[] = $minRating;
    $types .= "d";
}

// Add sorting
switch ($sortBy) {
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC, distance ASC";
        break;
    case 'distance':
        $sql .= " ORDER BY distance ASC";
        break;
    default:
        $sql .= " ORDER BY tc.name ASC";
}

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $centers = [];
    while ($row = $result->fetch_assoc()) {
        // Format distance and rating
        $row['distance'] = number_format($row['distance'], 2) . ' km';
        $row['avg_rating'] = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : '0.0';
        $centers[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($centers);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
