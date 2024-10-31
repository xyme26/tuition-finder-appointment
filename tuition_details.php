<?php
session_start(); 
$current_page = 'tuition_details';
include 'connection.php'; // Database connection

// Check if tuition center ID is passed in the URL
if (isset($_GET['id'])) { // Ensure the parameter name matches
    $tuition_center_id = $_GET['id'];

    // Near the top of the file, after getting the user's location
    $user_lat = $_SESSION['user_lat'] ?? 0;
    $user_lon = $_SESSION['user_lon'] ?? 0;

    // Modify the SQL query
    $sql = "SELECT t.*, 
                   (6371 * acos(cos(radians(?)) * cos(radians(t.latitude)) * cos(radians(t.longitude) - radians(?)) + sin(radians(?)) * sin(radians(t.latitude)))) AS distance
            FROM tuition_centers t 
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddi", $user_lat, $user_lon, $user_lat, $tuition_center_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tuition = $result->fetch_assoc();
        $tuition['distance'] = $tuition['distance'] ? number_format($tuition['distance'], 2) . ' km' : 'N/A';
    } else {
        echo "No tuition center found!";
        exit;
    }

    $stmt->close();
} else {
    echo "Invalid request!";
    exit;
}

// Fetch reviews for this tuition center along with the username of the reviewer
$sql = "
    SELECT r.rating, r.comment, r.created_at, u.username, r.liked_by_admin, r.reply
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.tuition_center_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Fetch the average rating
$sql = "SELECT AVG(rating) as avg_rating FROM reviews WHERE tuition_center_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$averageRating = $row['avg_rating'] ? number_format($row['avg_rating'], 1) : 0;

// Fetch unavailable dates
$stmt = $conn->prepare("SELECT unavailable_date FROM tuition_unavailable_dates WHERE tuition_center_id = ?");
$stmt->bind_param("i", $tuition_center_id);
$stmt->execute();
$unavailableDatesResult = $stmt->get_result();
$unavailableDates = [];
while ($row = $unavailableDatesResult->fetch_assoc()) {
    $unavailableDates[] = $row['unavailable_date'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tuition['name']); ?> - Tuition Center Details</title>
     <!-- Link to Bootstrap CSS for styling -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.js'></script>
    <!-- Replace the existing Google Maps script tag with this one -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
    <style>
        #container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        .tuition-image {
            width: 40%;
            float: left;
            padding-right: 15px; /* Reduced padding */
        }

        .tuition-details {
            width: 60%;
            float: right;
            padding-left: 15px; /* Reduced padding */
        }

        .tuition-details h2 {
            font-size: 2.5rem;
            color: #1a2238;
            margin-bottom: 10px;
        }

        .tuition-details p {
            margin-bottom: 10px;
        }

        .tuition-description {
            clear: both;
            padding-top: 20px;
        }

        .booking-btn {
            margin-top: 20px;
            display: inline-block;
            margin-right: 10px;
        }

        /* Clear floats */
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        .reviews-section .card {
            height: 100%;
        }

        .reviews-section .card-body {
            display: flex;
            flex-direction: column;
        }

        .reviews-section .card-text {
            flex-grow: 1;
        }

        .tuition-details p strong {
            width: 100%; /* Adjust this value to align the labels */
            vertical-align: top;
        }

        .tuition-details p span {
            display: inline-block;
            width: calc(100% - 145px); /* Adjust based on the width of the strong tag */
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <br><br><br>
    <div id="container">
        <!-- Tuition Center Details -->
        <div class="clearfix">
            <div class="tuition-image">
                <img src="<?php echo htmlspecialchars($tuition['image']); ?>" alt="Tuition Center Image" class="img-fluid mb-3">
            </div>

            <div class="tuition-details">
                <h2><?php echo htmlspecialchars($tuition['name']); ?></h2>
                <div class="star-rating" data-rating="<?php echo number_format($averageRating, 1); ?>"></div>
                <p><strong>Address:</strong> <span><?php echo htmlspecialchars($tuition['address']); ?></span></p>
                <!--<p><strong>Distance:</strong> <span><?php echo htmlspecialchars($tuition['distance']); ?></span></p>-->
                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($tuition['address']); ?>" 
                    class="btn btn-sm btn-secondary mb-2 google-maps-btn" target="_blank" rel="noopener noreferrer">
                     <i class="fas fa-map-marker-alt"></i> View on Google Maps
                <p><strong>Contact:</strong> 
                    <span>
                        <a href="tel:+60<?php echo htmlspecialchars(ltrim($tuition['contact'], '0')); ?>" class="contact-link">
                            <?php echo htmlspecialchars($tuition['contact']); ?>
                        </a>
                        |
                        <a href="https://wa.me/60<?php echo htmlspecialchars(ltrim($tuition['contact'], '0')); ?>" target="_blank" rel="noopener noreferrer" class="contact-link">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </span>
                </p>
                <p><strong>Subjects Offered:</strong> <span><?php echo htmlspecialchars($tuition['course_tags']); ?></span></p>
                <p><strong>Price Range:</strong> <span><?php echo htmlspecialchars($tuition['price_range']); ?></span></p>
            </div>
        </div>

        <!-- Description -->
        <div class="tuition-description mt-3">
            <h4>Description</h4>
            <p><?php echo htmlspecialchars($tuition['description']); ?></p>
        </div>

        <!-- Book an Appointment -->
        <button type="button" class="btn btn-primary booking-btn" data-bs-toggle="modal" data-bs-target="#appointmentModal">
            Book Appointment
        </button>

        <!-- Appointment Modal -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel">Book Appointment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="appointment-form">
                            <input type="hidden" name="tuition_center_id" value="<?php echo $tuition_center_id; ?>">
                            <div class="mb-3">
                                <label for="appointmentDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="appointmentDate" name="date" required>
                            </div>
                            <div class="mb-3">
                                <label for="appointmentTime" class="form-label">Time</label>
                                <input type="time" class="form-control" id="appointmentTime" name="time" required>
                            </div>
                            <div class="mb-3">
                                <label for="appointmentReason" class="form-label">Reason</label>
                                <textarea class="form-control" id="appointmentReason" name="reason" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Book Appointment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Button -->
<button type="button" class="btn btn-success booking-btn" data-bs-toggle="modal" data-bs-target="#reviewModal">
    Write a Review
</button>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="review-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Submit Your Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="review-rating" class="form-label">Rating:*</label>
                        <select class="form-control" id="review-rating" name="rating" required>
                            <option value="">Select rating</option>
                            <option value="5">5 - Excellent</option>
                            <option value="4">4 - Very Good</option>
                            <option value="3">3 - Good</option>
                            <option value="2">2 - Fair</option>
                            <option value="1">1 - Poor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="review-comment" class="form-label">Comment:*</label>
                        <textarea class="form-control" id="review-comment" name="comment" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Display Reviews -->
<div class="reviews-section mt-4">
    <h4>Reviews</h4>
    <?php if ($reviews->num_rows > 0): ?>
        <div class="row">
            <?php 
            $count = 0;
            while ($review = $reviews->fetch_assoc() and $count < 6): 
            ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($review['username']); ?></h5>
                            <div class="star-rating" data-rating="<?php echo htmlspecialchars($review['rating']); ?>"></div>
                            <p class="card-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                            <p class="card-text"><small class="text-muted">Reviewed on: <?php echo htmlspecialchars($review['created_at']); ?></small></p>
                            <?php if (!empty($review['reply'])): ?>
                                <p class="admin-reply"><strong>Admin Reply:</strong> <?php echo htmlspecialchars($review['reply']); ?></p>
                            <?php endif; ?>
                            <?php if ($review['liked_by_admin']): ?>
                                <p class="admin-like"><i class="fas fa-thumbs-up"></i> Liked by admin</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
            $count++;
            endwhile; 
            ?>
        </div>
        <?php if ($reviews->num_rows > 6): ?>
            <div class="text-center mt-3">
                <a href="#" class="btn btn-primary">View All Reviews</a>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>No reviews yet. Be the first to write one!</p>
    <?php endif; ?>
</div>
</div>

    <?php include 'footer.php'; ?>

    <script>
    // Set the current year for the copyright
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Handle appointment form submission
    document.getElementById('appointment-form').addEventListener('submit', function (event) {
        event.preventDefault();
        
        const date = document.getElementById('appointment-date').value;
        const time = document.getElementById('appointment-time').value;
        const reason = document.getElementById('appointment-reason').value;
        const tuition_center_id = <?php echo json_encode($tuition_center_id); ?>;

        if (!date || !time || !reason) {
            alert("Please select a date, time, and provide a reason.");
            return;
        }

        const appointmentData = {
            date: date,
            time: time,
            reason: reason,
            tuition_center_id: tuition_center_id
        };

        console.log('Appointment data being sent:', appointmentData);
        fetch('book_appointment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(appointmentData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Appointment booked for ${date} at ${time}. Reason: ${reason}`);
                document.getElementById('appointment-form').reset();
                var appointmentModal = bootstrap.Modal.getInstance(document.getElementById('appointmentModal'));
                appointmentModal.hide();
            } else {
                alert(`Error: ${data.message}`);
            }
        })
        .catch((error) => {
            console.error('Fetch error:', error);
            alert('An error occurred while booking the appointment.');
        });
    });

    // Handle review form submission
    document.getElementById('review-form').addEventListener('submit', function (event) {
        event.preventDefault();

        // Get form values
        const rating = document.getElementById('review-rating').value;
        const comment = document.getElementById('review-comment').value;

        // Simple validation
        if (!rating || !comment) {
            alert("Please provide a rating and comment.");
            return;
        }

        // Prepare the data to send
        const reviewData = {
            tuition_center_id: "<?php echo $tuition_center_id; ?>",  // Include the tuition center ID
            rating: rating,
            comment: comment
        };

        // Send data to the backend via AJAX (using Fetch API)
        fetch('submit_reviews.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(reviewData),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Review submitted successfully!');
                // Reset form
                document.getElementById('review-form').reset();
                // Close the modal
                var reviewModal = bootstrap.Modal.getInstance(document.getElementById('reviewModal'));
                reviewModal.hide();
            } else {
                alert('Error: ' + (data.message || 'Failed to submit review'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the review.');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize star ratings
        document.querySelectorAll('.star-rating').forEach(function(ratingElement) {
            const rating = parseFloat(ratingElement.dataset.rating);
            console.log('Raw rating:', ratingElement.dataset.rating);
            console.log('Parsed rating:', rating);
            
            if (isNaN(rating)) {
                console.error('Invalid rating:', ratingElement.dataset.rating);
                return;
            }

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
    });

    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('appointmentDate');
        const timeInput = document.getElementById('appointmentTime');

        dateInput.addEventListener('change', updateTimeSlots);
        timeInput.addEventListener('change', validateTimeSlot);

        function updateTimeSlots() {
            const selectedDate = new Date(dateInput.value);
            const dayOfWeek = selectedDate.getDay();

            // Clear existing time
            timeInput.value = '';

            // Disable time input on weekends
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                timeInput.disabled = true;
                alert('Appointments are only available on weekdays');
            } else {
                timeInput.disabled = false;
            }
        }

        function validateTimeSlot() {
            const selectedTime = timeInput.value;
            const [hours, minutes] = selectedTime.split(':').map(Number);

            if (hours < 11 || hours >= 17 || (hours === 16 && minutes > 30)) {
                alert('Appointments are only available between 11 AM and 5 PM');
                timeInput.value = '';
                return;
            }

            if (minutes !== 0 && minutes !== 30) {
                alert('Appointments must be booked on the hour or half-hour');
                timeInput.value = '';
                return;
            }
        }

        document.getElementById('appointment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var appointmentData = {
                tuition_center_id: formData.get('tuition_center_id'),
                date: formData.get('date'),
                time: formData.get('time'),
                reason: formData.get('reason')
            };

            fetch('book_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(appointmentData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Appointment booked successfully!');
                    $('#appointmentModal').modal('hide');
                    this.reset();
                } else {
                    alert('Failed to book appointment: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while booking the appointment');
            });
        });
    });
</script>
</body>
</html>
