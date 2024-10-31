<?php
include 'connection.php'; // Database connection
$current_page = 'sign_up';
$showAlert = false;  
$showError = false;  
$exists = false; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST['username']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']); // Capture confirm password

    // Check if username already exists
    $sql = "SELECT * FROM users WHERE username=?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $exists = "Username not available";  
        } else {
            if ($password === $cpassword) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
                
                // Prepare SQL statement for inserting new user
                $sql = "INSERT INTO users (username, email, password, user_role) VALUES (?, ?, ?, 'user')";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("sss", $username, $email, $hashedPassword);
                    
                    if ($stmt->execute()) {
                        $showAlert = true;  // Successfully created account
                    } else {
                        $showError = "Signup failed. Please try again.";
                    }
                } else {
                    $showError = "Error preparing statement.";
                }
            } else {
                $showError = "Passwords do not match";  
            }
        }
    } else {
        $showError = "Error preparing statement.";
    }
    
    // Close statement
    $stmt->close(); 
}

$conn->close(); // Close the connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" 
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css" />

    <!-- Boxicons CSS -->
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet" />

    <style>
        /* Import Google font - Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        /* Container styles */
        .container {
            max-width: 500px;
            margin: 80px auto 50px; /* Adjusted for navbar and footer spacing */
            padding: 20px;
            background-color: #f4db7d; /* Light yellow background */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center; /* Center align text */
        }

        /* Heading */
        .container h2 {
            color: #1a2238; /* Dark blue text */
            margin-bottom: 20px;
        }

        /* Input field styles */
        .input-box {
            position: relative;
            margin-bottom: 15px;
            text-align: left; /* Align text to left for labels */
        }

        /* Labels */
        .input-box label {
            display: block;
            margin-bottom: 5px;
            color: #1a2238; /* Dark blue */
            font-weight: 500;
        }

        /* Input fields */
        .input-box input {
            width: 100%;
            padding: 10px 40px 10px 10px; /* Added padding-right for icon */
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-box input:focus {
            border-color: #1a2238; /* Focus color */
        }

        /* Show/Hide Password Icon */
        .input-box i.show-hide {
            position: absolute;
            top: 38px;
            right: 10px;
            cursor: pointer;
            color: #1a2238; /* Icon color */
            transition: color 0.3s ease;
        }

        .input-box i.show-hide:hover {
            color: #ff6a3d; /* Change icon color on hover */
        }

        /* Error message styles */
        .error {
            display: none;
            margin-top: 5px;
            font-size: 12px;
            color: red;
            text-align: left; /* Align error text to left */
        }

        /* Button styles */
        .button {
            text-align: center;
            margin-top: 20px;
        }


        /* Media Queries for responsiveness */
        @media (max-width: 768px) {
            .navbar-custom .navbar-nav {
                text-align: center;
            }

            .navbar-custom .nav-link {
                margin: 5px 0;
            }

            .container {
                margin: 60px 20px 30px; /* Adjusted margins for smaller screens */
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
<br><br>
        <div class="container">
        <h2 class="text-center mb-4">Sign Up</h2>
        <?php 
    if ($showAlert) { 
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert"> 
                <strong>Success!</strong> Your account is now created and you can login. 
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> 
                    <span aria-hidden="true">x</span>  
                </button>  
              </div>';  
    } 
    
    if ($showError) { 
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">  
                <strong>Error!</strong> ' . $showError . ' 
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> 
                    <span aria-hidden="true">x</span>  
                </button>  
              </div>';  
    } 
    
    if ($exists) { 
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert"> 
                <strong>Error!</strong> ' . $exists . ' 
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">  
                    <span aria-hidden="true">x</span>  
                </button> 
              </div>';  
    } 
    ?>
        <form action="" method="POST">
            <div class="input-box">
                <label for="username">Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="input-box">
                <label for="email">Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-box">
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="input-box">
                <label for="cpassword">Confirm Password</label>
                <input type="password" name="cpassword" required>
                <small id="emailHelp" class="form-text text-muted">Make sure you have type the same password</small>
            </div>
            <div class="button">
                <input type="submit" value="Sign Up">
            </div>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" 
        integrity=" 
    sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" 
        crossorigin="anonymous"> 
    </script> 
        
    <script src=" 
    https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" 
        integrity= 
    "sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" 
        crossorigin="anonymous"> 
    </script> 
        
    <script src=" 
    https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"  
        integrity= 
    "sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"> 
    </script>  

    <?php include 'footer.php'; ?>
        <script>
            
            // Set the current year for the copyright
            document.getElementById('currentYear').textContent = new Date().getFullYear();

            // Email Validation
            function checkEmail() {
                const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
                if (!emailInput.value.match(emailPattern)) {
                    return emailField.classList.add("invalid");
                }
                emailField.classList.remove("invalid");
            }
    
            // Password Validation
            function createPass() {
                const passPattern =
                    /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                if (!passInput.value.match(passPattern)) {
                    return passField.classList.add("invalid");
                }
                passField.classList.remove("invalid");
            }
    
            // Add event listeners to input fields
            emailInput.addEventListener("keyup", checkEmail);
            passInput.addEventListener("keyup", createPass);
            cPassInput.addEventListener("keyup", confirmPass);
    
            // Show/Hide Password Toggle for Create Password
            const togglePassword1 = document.getElementById('togglePassword1');
            togglePassword1.addEventListener('click', function () {
                const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passInput.setAttribute('type', type);
                this.classList.toggle('bx-hide');
                this.classList.toggle('bx-show');
            });
    
            // Show/Hide Password Toggle for Confirm Password
            const togglePassword2 = document.getElementById('togglePassword2');
            togglePassword2.addEventListener('click', function () {
                const type = cPassInput.getAttribute('type') === 'password' ? 'text' : 'password';
                cPassInput.setAttribute('type', type);
                this.classList.toggle('bx-hide');
                this.classList.toggle('bx-show');
            });
        </script>
    </body>
</html>
