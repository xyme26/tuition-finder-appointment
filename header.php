<header>
    <nav class="navbar navbar-expand-lg navbar-custom bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Tuition Finder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'home') ? 'active' : ''; ?>" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'appointment') ? 'active' : ''; ?>" href="appointment.php">Appointment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'about') ? 'active' : ''; ?>" href="about.php">About us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page == 'help') ? 'active' : ''; ?>" href="help.php">Help</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <?php if (isset($_SESSION['username'])): ?>
                        <!-- If user is logged in, display the profile button -->
                        <a class="nav-link btn btn-primary" id="profile" href="profile.php"><?php echo $_SESSION['username']; ?>'s Profile</a>
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown" id="notificationList">
                                <!-- Notifications will be dynamically added here -->
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- If not logged in, display login and signup links -->
                        <div class="btn-group auth-button-group" role="group" aria-label="Login and Signup">
                            <a id="signupLink" class="btn btn-outline-primary nav-link <?php echo ($current_page == 'sign_up') ? 'active' : ''; ?>" href="sign_up.php">Sign up</a>
                            <a id="loginLink" class="btn btn-outline-primary nav-link <?php echo ($current_page == 'login') ? 'active' : ''; ?>" href="login.php">Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<script>
function fetchNotifications() {
    fetch('get_notifications.php')
        .then(response => response.json())
        .then(data => {
            const notificationList = document.getElementById('notificationList');
            const notificationCount = document.getElementById('notificationCount');
            notificationList.innerHTML = '';
            let unreadCount = 0;

            if (data.length === 0) {
                notificationList.innerHTML = '<li class="dropdown-item">No notifications</li>';
            } else {
                data.forEach(notification => {
                    const li = document.createElement('li');
                    li.className = notification.is_read ? 'dropdown-item' : 'dropdown-item unread';
                    li.dataset.notificationId = notification.id;
                    li.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="notification-icon bg-primary">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0">${notification.message}</h6>
                                <small class="text-muted">${new Date(notification.created_at).toLocaleString()}</small>
                            </div>
                        </div>
                    `;
                    li.addEventListener('click', markAsRead);
                    notificationList.appendChild(li);

                    if (!notification.is_read) {
                        unreadCount++;
                    }
                });
            }

            notificationCount.textContent = unreadCount > 0 ? unreadCount : '';
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            const notificationList = document.getElementById('notificationList');
            notificationList.innerHTML = '<li class="dropdown-item">Error loading notifications</li>';
        });
}

function markAsRead(event) {
    const notificationId = event.currentTarget.dataset.notificationId;
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
            event.currentTarget.classList.remove('unread');
            fetchNotifications(); // Refresh the notifications
        } else {
            console.error('Failed to mark notification as read');
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

// Fetch notifications every 30 seconds
setInterval(fetchNotifications, 30000);

// Initial fetch
document.addEventListener('DOMContentLoaded', fetchNotifications);
</script>

<style>
.unread {
    background-color: #f0f0f0;
    font-weight: bold;
}
.notification-icon {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
</style>
