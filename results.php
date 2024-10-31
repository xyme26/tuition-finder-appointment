<?php
session_start();
include 'connection.php';

$name = isset($_GET['name']) ? $_GET['name'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';

// Fetch results from the database
$sql = "SELECT tc.id, tc.name, tc.image, tc.address, tc.latitude, tc.longitude, AVG(r.rating) as avg_rating 
        FROM tuition_centers tc 
        LEFT JOIN reviews r ON tc.id = r.tuition_center_id 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($name)) {
    $sql .= " AND tc.name LIKE ?";
    $params[] = "%$name%";
    $types .= "s";
}

if (!empty($location)) {
    $sql .= " AND (tc.city LIKE ? OR tc.address LIKE ?)";
    $params[] = "%$location%";
    $params[] = "%$location%";
    $types .= "ss";
}

$sql .= " GROUP BY tc.id";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$centers = [];
while ($row = $result->fetch_assoc()) {
    $centers[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <br><br><br><br>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12 mb-4">
                <form action="results.php" method="GET" class="d-flex">
                    <input type="text" name="name" placeholder="Search by name" class="form-control me-2" value="<?php echo htmlspecialchars($name); ?>">
                    <input type="text" name="location" placeholder="Search by location" class="form-control me-2" value="<?php echo htmlspecialchars($location); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="filter-section">
                    <h4>Filters</h4>
                    <form id="filterForm">
                        <div class="mb-3">
                            <label for="minRating" class="form-label">Minimum Rating:</label>
                            <input type="number" id="minRating" name="minRating" class="form-control" min="1" max="5" step="0.1">
                        </div>
                        <div class="mb-3">
                            <label for="maxDistance" class="form-label">Maximum Distance:</label>
                            <select id="maxDistance" name="maxDistance" class="form-select">
                                <option value="">Any</option>
                                <option value="5">5 km</option>
                                <option value="10">10 km</option>
                                <option value="20">20 km</option>
                                <option value="50">50 km</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sortBy" class="form-label">Sort By:</label>
                            <select id="sortBy" name="sortBy" class="form-select">
                                <option value="rating">Rating</option>
                                <option value="distance">Distance</option>
                                <option value="name">Name</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </form>
                </div>
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-end mb-3">
                    <button id="gridViewBtn" class="btn btn-outline-primary me-2">Grid</button>
                    <button id="listViewBtn" class="btn btn-outline-primary">List</button>
                </div>
                <div id="resultsContainer" class="results-container grid-view">
                    <?php foreach ($centers as $center): ?>
                        <div class="result-card">
                            <img src="<?php echo htmlspecialchars($center['image']); ?>" alt="<?php echo htmlspecialchars($center['name']); ?>">
                            <div class="result-details">
                                <h5><?php echo htmlspecialchars($center['name']); ?></h5>
                                <div class="star-rating" data-rating="<?php echo number_format($center['avg_rating'], 1); ?>"></div>
                                <p><?php echo htmlspecialchars($center['address']); ?></p>
                                <a href="tuition_details.php?id=<?php echo $center['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>

    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');
            const resultsContainer = document.getElementById('resultsContainer');

            gridViewBtn.addEventListener('click', () => {
                resultsContainer.className = 'results-container grid-view';
            });

            listViewBtn.addEventListener('click', () => {
                resultsContainer.className = 'results-container list-view';
            });

            // Initialize star ratings
            document.querySelectorAll('.star-rating').forEach(function(ratingElement) {
                const rating = parseFloat(ratingElement.dataset.rating);
                let starsHtml = '';
                for (let i = 1; i <= 5; i++) {
                    if (i <= rating) {
                        starsHtml += '<i class="fas fa-star"></i>';
                    } else if (i - 0.5 <= rating) {
                        starsHtml += '<i class="fas fa-star-half-alt"></i>';
                    } else {
                        starsHtml += '<i class="far fa-star"></i>';
                    }
                }
                starsHtml += ` (${rating})`;
                ratingElement.innerHTML = starsHtml;
            });

            // Update the filter form submission handler
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get filter values
                const name = document.querySelector('input[name="name"]').value;
                const location = document.querySelector('input[name="location"]').value;
                const sortBy = document.querySelector('select[name="sortBy"]').value;
                const minRating = document.querySelector('input[name="minRating"]').value;
                
                // Build query string
                const params = new URLSearchParams({
                    name: name,
                    location: location,
                    sortBy: sortBy,
                    minRating: minRating
                });

                // Fetch filtered results
                fetch(`fetch_filtered_results.php?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        const resultsContainer = document.getElementById('resultsContainer');
                        resultsContainer.innerHTML = ''; // Clear existing results

                        if (data.length === 0) {
                            resultsContainer.innerHTML = '<p>No results found.</p>';
                            return;
                        }

                        // Render results
                        data.forEach(center => {
                            const centerCard = `
                                <div class="result-card">
                                    <img src="${center.image}" alt="${center.name}">
                                    <div class="result-details">
                                        <h3>${center.name}</h3>
                                        <div class="star-rating" data-rating="${center.avg_rating}"></div>
                                        <p>${center.address}</p>
                                        <p>Distance: ${center.distance}</p>
                                        <a href="tuition_details.php?id=${center.id}" class="btn btn-primary details-btn">Details</a>
                                    </div>
                                </div>
                            `;
                            resultsContainer.innerHTML += centerCard;
                        });

                        // Reinitialize star ratings
                        initializeStarRatings();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('resultsContainer').innerHTML = 
                            '<p>Error fetching results. Please try again.</p>';
                    });
            });

            // Function to initialize star ratings
            function initializeStarRatings() {
                document.querySelectorAll('.star-rating').forEach(function(ratingElement) {
                    const rating = parseFloat(ratingElement.dataset.rating);
                    let starsHtml = '';
                    for (let i = 1; i <= 5; i++) {
                        if (i <= rating) {
                            starsHtml += '<i class="fas fa-star"></i>';
                        } else if (i - 0.5 <= rating) {
                            starsHtml += '<i class="fas fa-star-half-alt"></i>';
                        } else {
                            starsHtml += '<i class="far fa-star"></i>';
                        }
                    }
                    starsHtml += ` (${rating.toFixed(1)})`;
                    ratingElement.innerHTML = starsHtml;
                });
            }
        });
    </script>
</body>
</html>
