<?php
session_start();
include 'connection.php';
$current_page = 'home';
// Get user's location from session or set default values
$user_lat = $_SESSION['user_lat'] ?? 0;
$user_lon = $_SESSION['user_lon'] ?? 0;

// Fetch nearest tuition centers
$nearest_query = "
    SELECT id, name, image, 
           (6371 * acos(cos(radians($user_lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($user_lon)) + sin(radians($user_lat)) * sin(radians(latitude)))) AS distance
    FROM tuition_centers
    WHERE latitude IS NOT NULL AND longitude IS NOT NULL
    ORDER BY distance ASC
    LIMIT 5
";
$nearest_result = $conn->query($nearest_query);

if (!$nearest_result) {
    die("Query failed: " . $conn->error);
}

// Fetch top-rated tuition centers
$top_query = "
    SELECT tc.id, tc.name, tc.image, AVG(r.rating) AS avg_rating,
           (6371 * acos(cos(radians($user_lat)) * cos(radians(tc.latitude)) * cos(radians(tc.longitude) - radians($user_lon)) + sin(radians($user_lat)) * sin(radians(tc.latitude)))) AS distance
    FROM tuition_centers tc
    LEFT JOIN reviews r ON tc.id = r.tuition_center_id
    GROUP BY tc.id
    ORDER BY avg_rating DESC
    LIMIT 5
";
$top_result = $conn->query($top_query);

if (!$top_result) {
    die("Query failed: " . $conn->error);
}

// Check if it's the user's first visit
$first_visit = !isset($_SESSION['has_seen_survey']);
if ($first_visit) {
    $_SESSION['has_seen_survey'] = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tuition Finder</title>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
</head>
<body>
<?php include 'header.php'; ?>

    <br><br><br><br>

    <main class="main-content">
        <!-- Search Bar -->
        <div class="search-section">
            <form action="results.php" method="GET">
                <input type="text" name="name" placeholder="Search by name" id="searchName">
                <input type="text" name="location" placeholder="Search by location" id="searchLocation">
                <button type="submit">Search</button>
            </form>
        </div>
        <br>
        <!-- Recommendation Section -->
        <section class="tuition-section">
            <h2 class="section-title">Recommended Tuition Centers</h2>
            <div class="tuition-center-grid" id="recommendation-grid">
                <?php while ($center = $nearest_result->fetch_assoc()): ?>
                    <div class="tuition-center-card">
                        <button class="favorite-btn" data-center-id="<?php echo $center['id']; ?>" aria-label="Add to favorites">
                            <i class="fas fa-heart"></i>
                        </button>
                        <img src="<?php echo htmlspecialchars($center['image']); ?>" alt="<?php echo htmlspecialchars($center['name']); ?>">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($center['name']); ?></h3>
                            <p>Distance: <?php echo number_format($center['distance'], 1); ?> km</p>
                            <a href="tuition_details.php?id=<?php echo $center['id']; ?>" class="btn btn-primary details-btn">Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Top Rated Section -->
        <section class="tuition-section">
            <h2 class="section-title">Top Rated Tuition Centers</h2>
            <div class="tuition-center-grid">
                <?php while ($center = $top_result->fetch_assoc()): ?>
                    <div class="tuition-center-card">
                        <button class="favorite-btn" data-center-id="<?php echo $center['id']; ?>" aria-label="Add to favorites">
                            <i class="fas fa-heart"></i>
                        </button>
                        <img src="<?php echo htmlspecialchars($center['image']); ?>" alt="<?php echo htmlspecialchars($center['name']); ?>">
                        <div class="card-content">
                            <h3><?php echo htmlspecialchars($center['name']); ?></h3>
                            <p>Rating: <?php echo number_format($center['avg_rating'], 1); ?> / 5</p>
                            <a href="tuition_details.php?id=<?php echo $center['id']; ?>" class="btn btn-primary details-btn">Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Lightbox Survey -->
    <div class="modal fade" id="surveyModal" tabindex="-1" aria-labelledby="surveyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="surveyModalLabel">Find Your Perfect Tuition Center</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3" id="surveyProgressBar">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    
                    <div id="helpQuestion">
                        <h2>Do you need help finding a tuition center?</h2>
                        <button class="btn btn-primary" onclick="startQuestions()">Yes, I need help</button>
                        <button class="btn btn-secondary" onclick="showNoHelpMessage()">No, I'm just browsing</button>
                    </div>

                    <div id="questionBox" style="display: none;">
                        <div id="question1">
                            <h3>What subject are you looking for?</h3>
                            <div class="subject-buttons">
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Math')">Math</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Science')">Science</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'English')">English</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Biology')">Biology</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Chemistry')">Chemistry</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Physics')">Physics</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Add Math')">Add Math</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Account')">Account</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'History')">History</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Economy')">Economy</button>
                                <button class="btn btn-outline-primary subject-btn" onclick="toggleSubject(this, 'Malay')">Bahasa Malaysia</button>
                            </div>
                        </div>

                        <div id="question2" style="display: none;">
                            <h3>What is your preferred city?</h3>
                            <input type="text" class="form-control" id="location" placeholder="Enter your city">
                        </div>

                        <div id="question3" style="display: none;">
                            <h3>What is your budget range for per subject?</h3>
                            <button class="btn btn-outline-primary" onclick="finishQuestions('Below RM20')">Below RM20</button>
                            <button class="btn btn-outline-primary" onclick="finishQuestions('RM20 - RM40')">RM20 - RM40</button>
                            <button class="btn btn-outline-primary" onclick="finishQuestions('Above RM40')">Above RM40</button>
                        </div>

                        <div id="question4" style="display: none;">
                            <h3>What is your language preference?</h3>
                            <button class="btn btn-outline-primary" onclick="finishQuestions('Bahasa Malaysia')">Bahasa Malaysia</button>
                            <button class="btn btn-outline-primary" onclick="finishQuestions('English')">English</button>
                        </div>
                    </div>

                    <div id="noHelpMessage" style="display: none;">
                        <p>Okay. If you need help later, you can find assistance in the footer section.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="nextButton" onclick="nextQuestion()">Next</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Show lightbox on first visit
        <?php if ($first_visit): ?>
        setTimeout(function() {
            document.getElementById("helpLightbox").style.display = "flex";
        }, 5000);
        <?php endif; ?>

        // Function to handle adding/removing favorites
        function initializeFavoriteButtons() {
            $('.favorite-btn').click(function(e) {
                e.preventDefault();
                var centerId = $(this).data('center-id');
                var $button = $(this);

                $.ajax({
                    url: 'toggle_favorite.php',
                    type: 'POST',
                    data: { center_id: centerId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $button.toggleClass('active');
                            if (response.action === 'added') {
                                alert('Added to favorites!');
                            } else {
                                alert('Removed from favorites!');
                            }
                        } else {
                            alert('Error: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('Error toggling favorite status.');
                    }
                });
            });
        }

        // Initialize favorite buttons
        initializeFavoriteButtons();

        // Load random recommendations if user chooses not to do the survey
        if (!<?php echo $first_visit ? 'true' : 'false'; ?>) {
            updateRecommendations({random: true});
        }
    });

    // Lightbox functions
    function closeLightbox() {
        document.getElementById("helpLightbox").style.display = "none";
    }

    // Survey functions
let selectedSubjects = [];

function startQuestions() {
    document.getElementById('helpQuestion').style.display = 'none';
    document.getElementById('questionBox').style.display = 'block';
    document.getElementById('question1').style.display = 'block';
    updateProgressBar(25);
}

function showNoHelpMessage() {
    document.getElementById('helpQuestion').style.display = 'none';
    document.getElementById('noHelpMessage').style.display = 'block';
}

function toggleSubject(button, subject) {
    button.classList.toggle('selected');
    if (selectedSubjects.includes(subject)) {
        selectedSubjects = selectedSubjects.filter(s => s !== subject);
    } else {
        selectedSubjects.push(subject);
    }
}

function nextQuestion() {
    const currentQuestion = document.querySelector('#questionBox > div:not([style*="display: none"])');
    const nextQuestion = currentQuestion.nextElementSibling;
    
    if (nextQuestion) {
        currentQuestion.style.display = 'none';
        nextQuestion.style.display = 'block';
        updateProgressBar(parseInt(currentQuestion.id.replace('question', '')) * 25);
    }
}

function finishQuestions(answer) {
    const subjects = selectedSubjects.join(', ');
    const location = document.getElementById('location').value;
    const currentQuestion = document.querySelector('#questionBox > div:not([style*="display: none"])');
    const questionType = currentQuestion.id;

    let surveyAnswers = {
        subjects: subjects,
        location: location
    };

    if (questionType === 'question3') {
        surveyAnswers.priceRange = answer;
    } else if (questionType === 'question4') {
        surveyAnswers.language = answer;
    }

    console.log('Survey completed:', surveyAnswers);
    // Here you can send the surveyAnswers to your server or use them to update recommendations
    closeLightbox();
    updateRecommendations(surveyAnswers);
}

function closeLightbox() {
    document.getElementById('helpLightbox').style.display = 'none';
    resetSurvey();
}

function updateProgressBar(percentage) {
    document.getElementById('progressBar').style.width = percentage + '%';
}

function resetSurvey() {
    document.getElementById('helpQuestion').style.display = 'block';
    document.getElementById('questionBox').style.display = 'none';
    document.getElementById('noHelpMessage').style.display = 'none';
    document.querySelectorAll('#questionBox > div').forEach(div => div.style.display = 'none');
    document.getElementById('progressBar').style.width = '0%';
    document.querySelectorAll('button.selected').forEach(button => button.classList.remove('selected'));
    selectedSubjects = [];
    document.getElementById('location').value = '';
}

// Function to handle survey answers and update recommendations
function updateRecommendations(surveyAnswers) {
            console.log('Updating recommendations based on:', surveyAnswers);

            // Send an AJAX request to get recommendations
            fetch('get_recommendations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(surveyAnswers)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Received recommendations:', data);

                // Get the recommendation grid element
                const recommendationGrid = document.getElementById('recommendation-grid');

                // Clear existing recommendations
                recommendationGrid.innerHTML = '';

                // Check if we received any recommendations
                if (data.length === 0) {
                    recommendationGrid.innerHTML = '<p>No matching tuition centers found.</p>';
                    return;
                }

                // Loop through the recommendations and create cards for each
                data.forEach(center => {
                    const centerCard = `
                        <div class="tuition-center-card">
                            <button class="favorite-btn" data-center-id="${center.id}" aria-label="Add to favorites">
                                <i class="fas fa-heart"></i>
                            </button>
                            <img src="${center.image}" alt="${center.name}">
                            <div class="card-content">
                                <h4>${center.name}</h4>
                                <p>Distance: ${center.distance ? center.distance.toFixed(1) + ' km' : 'N/A'}</p>
                                <p>Subjects: ${center.course_tags}</p>
                                <p>Price Range: ${center.price_range}</p>
                                <a href="tuition_details.php?id=${center.id}" class="btn btn-primary details-btn">Details</a>
                            </div>
                        </div>
                    `;
                    recommendationGrid.innerHTML += centerCard;
                });

                // Reinitialize favorite buttons if needed
                initializeFavoriteButtons();
            })
            .catch(error => {
                console.error('Error fetching recommendations:', error);
                document.getElementById('recommendation-grid').innerHTML = '<p>Error fetching recommendations. Please try again later.</p>';
            });
        }

    function toggleSubject(button, subject) {
        if (selectedSubjects.includes(subject)) {
            selectedSubjects = selectedSubjects.filter(s => s !== subject);
            button.classList.remove('active');
        } else {
            selectedSubjects.push(subject);
            button.classList.add('active');
        }
    }

    function fetchNotifications() {
        fetch('get_notifications.php')
            .then(response => response.json())
            .then(data => {
                const notificationsList = document.getElementById('notificationsList');
                const notificationCount = document.getElementById('notificationCount');
                notificationsList.innerHTML = '';
                let unreadCount = 0;

                if (data.length === 0) {
                    notificationsList.innerHTML = '<li><a class="dropdown-item" href="#">No new notifications</a></li>';
                } else {
                    data.forEach(notification => {
                        const li = document.createElement('li');
                        li.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
                        li.innerHTML = `
                            <a class="dropdown-item" href="#" data-notification-id="${notification.id}">
                                ${notification.message}
                            </a>
                        `;
                        notificationsList.appendChild(li);
                        if (!notification.is_read) {
                            unreadCount++;
                        }
                    });
                }

                if (unreadCount > 0) {
                    notificationCount.textContent = unreadCount;
                    notificationCount.style.display = 'inline';
                } else {
                    notificationCount.style.display = 'none';
                }

                // Add click event listeners to mark notifications as read
                notificationsList.querySelectorAll('.notification-item a').forEach(item => {
                    item.addEventListener('click', markAsRead);
                });
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    function markAsRead(event) {
        event.preventDefault();
        const notificationId = event.target.dataset.notificationId;
        
        fetch('mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `notification_id=${notificationId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                event.target.parentElement.classList.remove('unread');
                fetchNotifications(); // Refresh the notifications
            } else {
                console.error('Error marking notification as read:', data.error);
            }
        })
        .catch(error => console.error('Error marking notification as read:', error));
    }

    // Fetch notifications every 30 seconds
    setInterval(fetchNotifications, 30000);

    // Initial fetch
    document.addEventListener('DOMContentLoaded', fetchNotifications);

    document.addEventListener('DOMContentLoaded', function() {
        // Set the user's location in the search bar
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${position.coords.latitude}&lon=${position.coords.longitude}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.address && data.address.city) {
                            const locationSelect = document.getElementById('searchLocation');
                            const cityOption = Array.from(locationSelect.options).find(option => option.text === data.address.city);
                            if (cityOption) {
                                cityOption.selected = true;
                            }
                        }
                    });
            });
        }

        // Handle search form submission
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent the default form submission
            const searchName = document.getElementById('searchName').value;
            const searchLocation = document.getElementById('searchLocation').value;

            // Fetch search results
            fetch(`search_tuition_centers.php?name=${encodeURIComponent(searchName)}&location=${encodeURIComponent(searchLocation)}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Received data:', data);  // Log the received data
                    const recommendationGrid = document.getElementById('recommendation-grid');
                    recommendationGrid.innerHTML = ''; // Clear existing content

                    if (data.length === 0) {
                        recommendationGrid.innerHTML = '<p>No results found.</p>';
                    } else {
                        data.forEach(center => {
                            const centerCard = `
                                <div class="tuition-center-card">
                                    <button class="favorite-btn" data-center-id="${center.id}" aria-label="Add to favorites">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    <img src="${center.image}" alt="${center.name}">
                                    <div class="card-content">
                                        <h4>${center.name}</h4>
                                        <p>Distance: ${center.distance ? center.distance.toFixed(1) + ' km' : 'N/A'}</p>
                                        <a href="tuition_details.php?id=${center.id}" class="btn btn-primary details-btn">Details</a>
                                    </div>
                                </div>
                            `;
                            recommendationGrid.innerHTML += centerCard;
                        });
                    }
                    initializeFavoriteButtons();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('recommendation-grid').innerHTML = '<p>Error fetching recommendations. Please try again later.</p>';
                });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.getElementById('searchForm');
        const searchButton = document.getElementById('searchButton');

        searchButton.addEventListener('click', performSearch);
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            performSearch();
        });

        function performSearch() {
            const searchName = document.getElementById('searchName').value;
            const searchLocation = document.getElementById('searchLocation').value;

            fetch(`search_tuition_centers.php?name=${encodeURIComponent(searchName)}&location=${encodeURIComponent(searchLocation)}`)
                .then(response => response.json())
                .then(data => {
                    const recommendationGrid = document.getElementById('recommendation-grid');
                    recommendationGrid.innerHTML = '';

                    if (data.length === 0) {
                        recommendationGrid.innerHTML = '<p>No results found.</p>';
                    } else {
                        data.forEach(center => {
                            const centerCard = `
                                <div class="tuition-center-card">
                                    <img src="${center.image}" alt="${center.name}">
                                    <div class="card-content">
                                        <h3>${center.name}</h3>
                                        <p>Distance: ${center.distance || 'N/A'}</p>
                                        <a href="tuition_details.php?id=${center.id}" class="btn btn-primary details-btn">Details</a>
                                    </div>
                                </div>
                            `;
                            recommendationGrid.innerHTML += centerCard;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('recommendation-grid').innerHTML = '<p>Error fetching recommendations. Please try again later.</p>';
                });
        }

        // Load initial recommendations
        performSearch();
    });

    
    document.addEventListener('DOMContentLoaded', function() {
        getLocation();
    });
    </script>
</body>
</html>
