<?php
session_start();
include 'connection.php'; // Database connection
$current_page = 'manage_tuition';
// Add this function at the top of the file
function geocodeAddress($address) {
    $apiKey = 'AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs';
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($address) . "&key=" . $apiKey;
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if ($data['status'] === 'OK') {
        return [
            'latitude' => $data['results'][0]['geometry']['location']['lat'],
            'longitude' => $data['results'][0]['geometry']['location']['lng']
        ];
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $description = $_POST['description'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Handle multiple course tags
    $course_tags = $_POST['course_tags']; // This will be an array of subjects
    $course_tags_str = implode(',', $course_tags); // Convert the array into a comma-separated string

    // Handle the image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a valid image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Prepare SQL statement to insert tuition center details
        $stmt = $conn->prepare("INSERT INTO tuition_centers (name, address, description, contact, course_tags, price_range, latitude, longitude, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssdds", 
            $name, 
            $address, 
            $description, 
            $_POST['contact'], 
            $course_tags_str, 
            $_POST['price_range'],
            $latitude,
            $longitude, 
            $target_file
        );

        if ($stmt->execute()) {
            echo "Tuition center added successfully!";
            header("Location: manage_tuition.php"); // Redirect back to admin page
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}


$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tuition Center</title>
    
    <!-- Bootstrap CSS -->
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyABMOUhZaFdYKDd_aMISrx4HPmH70OD0gs&libraries=places,geometry"></script>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
    <br><br><br><br>
    
    <div class="container edit-form-container">
        <h2 class="text-center mb-4">Add Tuition Center</h2>
        
        <form action="add_tuition.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Tuition Center Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                <button type="button" onclick="lookupAddress()" class="btn btn-secondary mt-2">
                    Look up coordinates
                </button>
            </div>

            <div class="mb-3">
                <label for="contact" class="form-label">Contact:</label>
                <input type="text" class="form-control" id="contact" name="contact" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Choose Course Tags (Subjects):</label>
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
                    
                    foreach($subjects as $subject) {
                        $displayName = ($subject === 'Malay') ? 'Bahasa Malaysia' : $subject;
                        echo '<div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="course_tags[]" value="'.$subject.'" 
                                           id="'.$subject.'">
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
                <label for="description" class="form-label">Description:</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Upload Image:</label>
                <input type="file" class="form-control" id="image" name="image" 
                       accept="image/*" required>
            </div>

            <div class="mb-3">
                <label for="price_range" class="form-label">Price Range per Subject (in RM):</label>
                <input type="text" class="form-control" id="price_range" name="price_range" 
                       placeholder="e.g., RM20-RM30" required>
            </div>

            <div class="mb-3">
                <label for="latitude" class="form-label">Latitude:</label>
                <input type="number" step="any" class="form-control" id="latitude" name="latitude" readonly>
            </div>

            <div class="mb-3">
                <label for="longitude" class="form-label">Longitude:</label>
                <input type="number" step="any" class="form-control" id="longitude" name="longitude" readonly>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Add Tuition Center</button>
            </div>
        </form>
    </div>

    <script>
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

    <?php include 'admin_footer.php'; ?>
</body>
</html>
