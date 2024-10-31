<?php
session_start();
include 'connection.php';
$current_page = 'manage_tuition';

$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_admin.php?timeout=1");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

$success = '';
$error = '';

// Check if ID is provided
if (!isset($_GET['id'])) {
    header("Location: manage_tuition.php");
    exit();
}

$center_id = intval($_GET['id']);

// Fetch existing tuition center data
$stmt = $conn->prepare("SELECT * FROM tuition_centers WHERE id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_tuition.php?error=Tuition centre not found.");
    exit();
}

$center = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $description = trim($_POST['description']);
    $price_range = trim($_POST['price_range']);
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle course tags array
    $course_tags = isset($_POST['course_tags']) ? implode(',', $_POST['course_tags']) : '';

    // Initialize image variable
    $image = null;

    // Check for image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageFileType = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $target_dir = "uploads/";
        $target_file = $target_dir . uniqid() . '.' . $imageFileType;

        // Validate image file type
        $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
        if (!in_array($imageFileType, $allowedTypes)) {
            $error = "Only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            // Attempt to move the uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $target_file; // Set image path for database
            } else {
                $error = "Failed to upload image.";
            }
        }
    } else {
        // If no new image is uploaded, keep the existing one
        $image = $center['image'];
    }

    // Validate required fields
    if (empty($name) || empty($contact)) {
        $error = "Name and contact are required.";
    } else {
        // Update the tuition center in the database
        $stmt = $conn->prepare("UPDATE tuition_centers SET name = ?, address = ?, description = ?, contact = ?, course_tags = ?, price_range = ?, image = ?, latitude = ?, longitude = ? WHERE id = ?");
        $stmt->bind_param("sssssssddi", 
            $name, 
            $address, 
            $description, 
            $contact, 
            $course_tags,  // Now this is a comma-separated string
            $price_range, 
            $image,
            $latitude,
            $longitude, 
            $center_id
        );

        if ($stmt->execute()) {
            $success = "Tuition center updated successfully.";
            // Refresh the data
            $center['name'] = $name;
            $center['address'] = $address;
            $center['contact'] = $contact;
            $center['description'] = $description;
            $center['course_tags'] = $course_tags;
            $center['price_range'] = $price_range;
            $center['image'] = $image;
            $center['latitude'] = $latitude;
            $center['longitude'] = $longitude;
        } else {
            $error = "Error updating tuition center: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tuition Center - Admin</title>
     <!-- Link to Bootstrap CSS for styling -->
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br><br>
    <div class="container edit-form-container">
    <h2>Edit Tuition Center</h2>
    <form action="edit_tuition.php?id=<?php echo $center_id; ?>" method="POST" enctype="multipart/form-data">
        <!-- Display success or error messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="name" class="form-label">Tuition Center Name:</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?php echo htmlspecialchars($center['name']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address:</label>
            <input type="text" class="form-control" id="address" name="address" 
                   value="<?php echo htmlspecialchars($center['address']); ?>" required>
            <button type="button" onclick="lookupAddress()" class="btn btn-secondary mt-2">
                Look up coordinates
            </button>
        </div>

        <div class="mb-3">
            <label for="contact" class="form-label">Contact Information:</label>
            <input type="text" class="form-control" id="contact" name="contact" 
                   value="<?php echo htmlspecialchars($center['contact']); ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea class="form-control" id="description" name="description" 
                      rows="4"><?php echo htmlspecialchars($center['description']); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Subjects Offered:</label>
            <div class="row g-3">
                <?php
                $subjects = [
                    'Math',
                    'Science',
                    'English',
                    'Biology',
                    'Chemistry',
                    'Physics',
                    'Add Math',
                    'Account',
                    'History',
                    'Economy',
                    'Malay'
                ];
                
                // Get current course tags as array
                $current_tags = explode(',', $center['course_tags']);
                
                foreach($subjects as $subject) {
                    $displayName = ($subject === 'Malay') ? 'Bahasa Malaysia' : $subject;
                    $checked = in_array($subject, $current_tags) ? 'checked' : '';
                    
                    echo '<div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="course_tags[]" value="'.$subject.'" 
                                       id="'.$subject.'" '.$checked.'>
                                <label class="form-check-label" for="'.$subject.'">
                                    '.$displayName.'
                                </label>
                            </div>
                        </div>';
                }
                ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="price_range" class="form-label">Price Range per Subject (in RM):</label>
            <input type="text" class="form-control" id="price_range" name="price_range" 
                   value="<?php echo htmlspecialchars($center['price_range']); ?>" 
                   placeholder="e.g., RM20-RM30" required>
        </div>

        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude:</label>
            <input type="number" step="any" class="form-control" id="latitude" name="latitude" 
                   value="<?php echo htmlspecialchars($center['latitude']); ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="longitude" class="form-label">Longitude:</label>
            <input type="number" step="any" class="form-control" id="longitude" name="longitude" 
                   value="<?php echo htmlspecialchars($center['longitude']); ?>" readonly>
        </div>

        <div class="mb-3">
            <label class="form-label">Current Image:</label>
            <?php if (!empty($center['image'])): ?>
                <div class="current-image-preview mb-2">
                    <img src="<?php echo htmlspecialchars($center['image']); ?>" 
                         alt="Current tuition center image" 
                         style="max-width: 200px; height: auto;">
                </div>
            <?php endif; ?>
            <label for="image" class="form-label">Upload New Image:</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <small class="text-muted">Leave empty to keep current image. Accepted formats: JPG, JPEG, PNG, GIF</small>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Update Tuition Center</button>
        </div>
    </form>
</div>

<?php include 'admin_footer.php'; ?>

<script>
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    function lookupAddress() {
        const address = document.getElementById('address').value;
        const geocoder = new google.maps.Geocoder();
        
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === 'OK') {
                const latitude = results[0].geometry.location.lat();
                const longitude = results[0].geometry.location.lng();
                
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
            } else {
                alert('Could not find coordinates for this address. Please check the address or enter coordinates manually.');
            }
        });
    }
</script>
</body>
</html>
