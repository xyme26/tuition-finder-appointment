<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Tuition Center</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
    
</head>
<body>
<header>
    <!-- Admin Navigation bar -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">Tuition Finder - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_tuition_centers.php">Manage Tuition Centers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_appointments.php">Manage Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_reviews.php">Manage Reviews</a>
                    </li>
                </ul>
                <div class="auth-links d-flex">
                    <?php if (isset($_SESSION['admin_username'])): ?>
                        <!-- If admin is logged in, display the profile and logout links -->
                        <a class="nav-link" href="admin_profile.php"><?php echo $_SESSION['admin_username']; ?></a>
                        <a class="nav-link" href="logout.php">Logout</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>


    <h2>Add Tuition Center</h2>
    <form action="add_tuition.php" method="POST" enctype="multipart/form-data">
        <label for="name">Tuition Center Name:</label>
        <input type="text" name="name" required><br>

        <label for="address">Address:</label>
        <textarea name="address" required></textarea><br>

        <label for="subjects">Choose Course Tags (Subjects):</label><br>
        <input type="checkbox" name="course_tags[]" value="Math"> Math<br>
        <input type="checkbox" name="course_tags[]" value="Science"> Science<br>
        <input type="checkbox" name="course_tags[]" value="English"> English<br>
        <input type="checkbox" name="course_tags[]" value="History"> History<br>
        <input type="checkbox" name="course_tags[]" value="Geography"> Geography<br>
        <input type="checkbox" name="course_tags[]" value="Physics"> Physics<br>
        <input type="checkbox" name="course_tags[]" value="Chemistry"> Chemistry<br>
        <input type="checkbox" name="course_tags[]" value="Biology"> Biology<br>
        <input type="checkbox" name="course_tags[]" value="Computer Science"> Computer Science<br>
        <input type="checkbox" name="course_tags[]" value="Art"> Art<br>
        <input type="checkbox" name="course_tags[]" value="Music"> Music<br>
        <input type="checkbox" name="course_tags[]" value="Physical Education"> Physical Education<br>

        <label for="image">Upload Image:</label>
        <input type="file" name="image" accept="image/*" required><br>

        <button type="submit">Add Tuition Center</button>
    </form>
</body>
</html>
