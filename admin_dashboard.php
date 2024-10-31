<?php
// admin/admin_dashboard.php
session_start();
$current_page = 'dashboard';
// Session timeout after 30 minutes of inactivity
$timeout_duration = 1800;

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: login_admin.php?timeout=1");
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time();

// Check if admin is logged in
if (!isset($_SESSION['admin_username'])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tuition Finder</title>
    
    <!-- Link to Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Link to Bootstrap JS for functionality -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <!-- Load Google Charts API -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <!-- Load jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="style.css">

    <script type="text/javascript">
        // Set the current year in the footer
        $(document).ready(function() {
            $('#currentYear').text(new Date().getFullYear());
        });

        // Load Google Charts
        google.charts.load('current', { packages: ['corechart', 'bar'] });

        // Callback to draw the charts
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            fetchAppointmentsData();
            fetchTuitionCentersData();
            fetchUserAccountsData();
        }

        function fetchAppointmentsData(selectedYear = null, selectedMonth = null) {
            const currentYear = new Date().getFullYear();
            const year = selectedYear || currentYear;
            let url = `fetch_chart.php?type=appointments&year=${year}`;
            if (selectedMonth && selectedMonth !== 'all') {
                url += `&month=${selectedMonth}`;
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    console.log('Appointments data:', data);
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    drawAppointmentsChart(data, year, selectedMonth);
                })
                .catch(error => {
                    console.error('Error fetching appointments data:', error);
                    document.getElementById('appointments_chart').innerHTML = 'Error loading appointments chart: ' + error.message;
                });
        }

        function drawAppointmentsChart(data, year, selectedMonth) {
            const chartData = [['Period', 'Appointments']];
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            if (selectedMonth && selectedMonth !== 'all') {
                // If a specific month is selected, show daily data for that month
                const daysInMonth = new Date(year, selectedMonth, 0).getDate();
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayData = data.find(item => item.day === day);
                    // Ensure the value is never negative, default to 0 if no data
                    chartData.push([day.toString(), Math.max(0, dayData ? parseInt(dayData.appointments) : 0)]);
                }
            } else {
                // Show monthly data for the entire year
                for (let month = 1; month <= 12; month++) {
                    const monthData = data.find(item => item.month === month);
                    // Ensure the value is never negative, default to 0 if no data
                    chartData.push([monthNames[month - 1], Math.max(0, monthData ? parseInt(monthData.appointments) : 0)]);
                }
            }

            const dataTable = google.visualization.arrayToDataTable(chartData);
            const options = {
                title: `Appointments for ${selectedMonth && selectedMonth !== 'all' ? monthNames[selectedMonth - 1] : year}`,
                curveType: 'function',
                legend: { position: 'bottom' },
                hAxis: { 
                    title: selectedMonth && selectedMonth !== 'all' ? 'Day of Month' : 'Month',
                    textPosition: 'out'
                },
                vAxis: { 
                    title: 'Number of Appointments', 
                    minValue: 0,
                    viewWindow: { min: 0 },
                    format: '0',
                    gridlines: {
                        count: -1
                    },
                    baselineColor: '#ccc',
                    baseline: 0
                },
                chartArea: { width: '80%', height: '70%' },
                colors: ['#1a73e8'],
                lineWidth: 3,
                pointSize: 5
            };

            const chart = new google.visualization.LineChart(document.getElementById('appointments_chart'));
            chart.draw(dataTable, options);
        }

        function updateAppointmentsChart() {
            const yearSelect = document.getElementById('year-select');
            const monthSelect = document.getElementById('month-select');
            const selectedYear = yearSelect.value;
            const selectedMonth = monthSelect.value;

            fetchAppointmentsData(selectedYear, selectedMonth);
        }

        function fetchTuitionCentersData() {
            fetch('fetch_chart.php?type=tuition_centers')
                .then(response => response.json())
                .then(data => {
                    console.log('Tuition centers data:', data);
                    drawTuitionCentersChart(data);
                })
                .catch(error => console.error('Error fetching tuition centers data:', error));
        }

        function drawTuitionCentersChart(data) {
            // Chart for top rated tuition centers
            var chartData = [['Tuition Center', 'Rating']];
            
            data.forEach(item => {
                chartData.push([
                    item.center,
                    parseFloat(item.avg_rating)
                ]);
            });

            var dataTable = google.visualization.arrayToDataTable(chartData);
            var options = {
                title: 'Top Rated Tuition Centers',
                bars: 'horizontal',
                height: 300,
                colors: ['#1a73e8'],
                hAxis: {
                    title: 'Rating (0-5)',
                    minValue: 0,
                    maxValue: 5,
                    format: '#.0'
                },
                vAxis: {
                    title: 'Tuition Center'
                },
                legend: { position: 'none' }
            };

            var chart = new google.visualization.BarChart(document.getElementById('tuition_centers_chart'));
            chart.draw(dataTable, options);
        }

        function fetchUserAccountsData() {
            fetch('fetch_chart.php?type=user_accounts')
                .then(response => response.json())
                .then(data => {
                    console.log('User accounts data:', data);
                    drawUserAccountsChart(data);
                })
                .catch(error => console.error('Error fetching user accounts data:', error));
        }

        function drawUserAccountsChart(data) {
            // Chart for monthly user registrations
            var chartData = [['Month', 'New Users']];
            const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                               "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            
            data.forEach(item => {
                chartData.push([
                    monthNames[item.month - 1],
                    item.registrations
                ]);
            });

            var dataTable = google.visualization.arrayToDataTable(chartData);
            var options = {
                title: 'Monthly User Registrations',
                height: 300,
                legend: { position: 'none' },
                bars: 'vertical',
                bar: { groupWidth: '70%' },
                colors: ['#1a73e8'],
                vAxis: {
                    title: 'Number of Registrations',
                    minValue: 0,
                    format: '0'
                }
            };

            var chart = new google.charts.Bar(document.getElementById('user_accounts_chart'));
            chart.draw(dataTable, google.charts.Bar.convertOptions(options));
        }

        function getMonthName(month) {
            const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            return months[month - 1];
        }

        // Initialize year and month selectors
        function initializeSelectors() {
            const yearSelect = document.getElementById('year-select');
            const monthSelect = document.getElementById('month-select');
            const currentYear = new Date().getFullYear();

            // Populate year selector
            for (let year = currentYear; year <= currentYear + 1; year++) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                yearSelect.appendChild(option);
            }

            // Populate month selector
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
            monthNames.forEach((month, index) => {
                const option = document.createElement('option');
                option.value = index + 1;
                option.textContent = month;
                monthSelect.appendChild(option);
            });

            yearSelect.addEventListener('change', updateAppointmentsChart);
            monthSelect.addEventListener('change', updateAppointmentsChart);
        }

        // Call this function when the page loads
        document.addEventListener('DOMContentLoaded', initializeSelectors);
    </script>
</head>
<body>
    <?php include 'admin_navbar.php'; ?>
        <br><br><br><br><br>
    <div class="container-fluid mt-4 admin-dashboard-container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
        <p>This is your admin dashboard. You can use the navigation bar to manage various aspects of the Tuition Finder platform.
            Have a nice day!
        </p>

        <!-- Google Charts Sections -->
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="year-select" class="form-label">Select Year:</label>
                    <select id="year-select" class="form-select">
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="month-select" class="form-label">Select Month:</label>
                    <select id="month-select" class="form-select">
                        <option value="all">All Months</option>
                        <!-- Other options will be populated by JavaScript -->
                    </select>
                </div>
                <div id="appointments_chart" style="height: 400px;"></div>
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-md-6">
                <div id="tuition_centers_chart" style="height: 300px;"></div>
            </div>
            <div class="col-md-6">
                <div id="user_accounts_chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>

    <?php include 'admin_footer.php'; ?>
</body>
</html>
