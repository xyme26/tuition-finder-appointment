<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'connection.php';

header('Content-Type: application/json');

try {
    $name = isset($_GET['name']) ? $_GET['name'] : '';
    $location = isset($_GET['location']) ? $_GET['location'] : '';

    // Get user's location from session (you need to set this when the user allows location access)
    $user_lat = $_SESSION['user_lat'] ?? 0;
    $user_lon = $_SESSION['user_lon'] ?? 0;

    $sql = "SELECT id, name, image, latitude, longitude, 
                   (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance 
            FROM tuition_centers 
            WHERE 1=1";
    $params = [$user_lat, $user_lon, $user_lat];
    $types = "ddd";

    if (!empty($name)) {
        $sql .= " AND name LIKE ?";
        $params[] = "%$name%";
        $types .= "s";
    }

    if (!empty($location)) {
        $sql .= " AND (city LIKE ? OR address LIKE ?)";
        $params[] = "%$location%";
        $params[] = "%$location%";
        $types .= "ss";
    }

    $sql .= " ORDER BY distance";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $centers = array();
    while ($row = $result->fetch_assoc()) {
        $row['distance'] = round($row['distance'], 2) . ' km';
        $centers[] = $row;
    }

    echo json_encode($centers);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
