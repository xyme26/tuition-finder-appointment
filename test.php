<?php
session_start();
include 'connection.php';

// Set error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test Page</h2>";

// Test 1: Database Connection
echo "<h3>Testing Database Connection</h3>";
if ($conn) {
    echo "✅ Database connection successful<br>";
} else {
    echo "❌ Database connection failed<br>";
}

// Test 2: Review System
echo "<h3>Testing Review System</h3>";
try {
    $sql = "SELECT COUNT(*) as count FROM reviews";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    echo "✅ Total reviews in database: " . $row['count'] . "<br>";
} catch (Exception $e) {
    echo "❌ Error counting reviews: " . $e->getMessage() . "<br>";
}

// Test 3: Average Ratings
echo "<h3>Testing Average Ratings</h3>";
try {
    $sql = "SELECT tc.name, AVG(r.rating) as avg_rating 
            FROM tuition_centers tc 
            LEFT JOIN reviews r ON tc.id = r.tuition_center_id 
            GROUP BY tc.id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "✅ " . $row['name'] . ": " . number_format($row['avg_rating'], 1) . "<br>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching ratings: " . $e->getMessage() . "<br>";
}

// Test 4: Distance Calculation
echo "<h3>Testing Distance Calculation</h3>";
$test_lat = 3.1390; // Example latitude
$test_lon = 101.6869; // Example longitude
try {
    $sql = "SELECT name, 
            (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * 
            cos(radians(longitude) - radians(?)) + sin(radians(?)) * 
            sin(radians(latitude)))) AS distance 
            FROM tuition_centers 
            ORDER BY distance 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddd", $test_lat, $test_lon, $test_lat);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        echo "✅ " . $row['name'] . ": " . number_format($row['distance'], 2) . " km<br>";
    }
} catch (Exception $e) {
    echo "❌ Error calculating distances: " . $e->getMessage() . "<br>";
}

// Test 5: Session Variables
echo "<h3>Testing Session Variables</h3>";
if (isset($_SESSION['user_lat']) && isset($_SESSION['user_lon'])) {
    echo "✅ User location is set: " . $_SESSION['user_lat'] . ", " . $_SESSION['user_lon'] . "<br>";
} else {
    echo "❌ User location not set<br>";
}

// Test 6: Image Paths
echo "<h3>Testing Image Paths</h3>";
try {
    $sql = "SELECT id, image FROM tuition_centers LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        if (file_exists($row['image'])) {
            echo "✅ Image exists for ID " . $row['id'] . ": " . $row['image'] . "<br>";
        } else {
            echo "❌ Image missing for ID " . $row['id'] . ": " . $row['image'] . "<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error checking images: " . $e->getMessage() . "<br>";
}

// Add styling for better readability
echo "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    h2 {
        color: #1a2238;
    }
    h3 {
        color: #1a2238;
        margin-top: 20px;
    }
    .success {
        color: green;
    }
    .error {
        color: red;
    }
</style>
";

$conn->close();
?>
