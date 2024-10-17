<?php
// Initialize the session
session_start();

// Check if the user is logged in and is an agent
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["permission_level"] !== 2) {
    header("location: login.php");
    exit;
}

// Include database connection
include 'db.php';

// Get the agent's customer ID from the session
$agent_customer_id = $_SESSION['customer_id'];

// Fetch the bookings made by the agent for other customers from the database
$bookings_query = "SELECT ab.agent_booking_id, ab.reservation_id, r.reservation_date, r.number_of_tickets,
                          r.route_id, r.amount, r.customer_id, c.full_name AS customer_name,
                          rt.origin, rt.destination, rt.departure_time, rt.arrival_time, rt.date, rt.available_seats, rt.price
                   FROM agent_bookings ab
                   JOIN reservations r ON ab.reservation_id = r.reservation_id
                   JOIN routes rt ON r.route_id = rt.route_id
                   JOIN customer c ON r.customer_id = c.customer_id
                   WHERE ab.agent_id = $agent_customer_id";
$bookings_result = mysqli_query($conn, $bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Bookings</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100%;
            
        }

        .footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 20px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        .cancel-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .cancel-btn:hover {
            background-color: #c82333;
            color: white;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="">Ferry Booking</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="main_page.php">Home</a>
                </li>
                <?php
                // Check if the user is logged in
                if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
                    // If logged in, display routes
                    echo '<li class="nav-item">
                        <a class="nav-link" href="routes.php">Routes</a>
                    </li>';
                    
                    // Determine navbar links based on permission level
                    if ($_SESSION["permission_level"] === 0) {
                        // Customer with permission level 0
                        echo '<li class="nav-item">
                            <a class="nav-link" href="my_bookings.php">My Bookings</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php">Contact</a>
                        </li>';
                    } elseif ($_SESSION["permission_level"] === 1) {
                        // Admin with permission level 1
                        echo '<li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAdmin" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Admin Actions
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownAdmin">
                                <li><a class="dropdown-item" href="add_company.php">Add Company</a></li>
                                <li><a class="dropdown-item" href="add_route.php">Add Route</a></li>
                                <li><a class="dropdown-item" href="add_employee.php">Add Employee</a></li>
                                <li><a class="dropdown-item" href="delete_employee.php">Delete Employee</a></li>
                                <li><a class="dropdown-item" href="cancel_route.php">Cancel Route</a></li>
                                <li><a class="dropdown-item" href="summary_report.php">Summary Report</a></li>
                                
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_bookings.php">Admin Bookings</a>
                        </li>';
                    } elseif ($_SESSION["permission_level"] === 2) {
                        // Agent with permission level 2
                        echo '<li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAgent" role="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                Agent Actions
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownAgent">
                                <li><a class="dropdown-item" href="make_booking.php">Make Booking</a></li>
                                <li><a class="dropdown-item" href="agent_bookings.php">Agent Bookings</a></li>
                            </ul>
                        </li>';
                    }
                    
                    // Display logout link
                    echo '<li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>';
                } else {
                    // If not logged in, display login and register links
                    echo '<li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Register</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-5">
    <h2>Agent Bookings</h2>
    <?php
    // Check if there are bookings made by the agent
    if (mysqli_num_rows($bookings_result) > 0) {
        echo "<table class='table'>";
        echo "<tr>
                <th>Booking ID</th>
                <th>Reservation Date</th>
                <th>Customer Name</th>
                <th>Route</th>
                <th>Departure Time</th>
                <th>Arrival Time</th>
                <th>Date</th>
                <th>Amount Paid</th>
                <th>Action</th>
              </tr>";

        // Output data of each booking
        while ($row = mysqli_fetch_assoc($bookings_result)) {
            echo "<tr>";
            echo "<td>".$row["agent_booking_id"]."</td>";
            echo "<td>".$row["reservation_date"]."</td>";
            echo "<td>".$row["customer_name"]."</td>";
            echo "<td>".$row["origin"]." to ".$row["destination"]."</td>";
            echo "<td>".$row["departure_time"]."</td>";
            echo "<td>".$row["arrival_time"]."</td>";
            echo "<td>".$row["date"]."</td>";
            echo "<td>".$row["price"]."</td>"; // Display amount paid for the route
            echo "<td><button class='btn btn-danger cancel-btn' data-reservation-id='".$row["reservation_id"]."'>Cancel</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "You have not made any bookings for other customers.";
    }
    ?>
</div>

<!-- Footer -->
<footer class="footer mt-auto">
    <div class="container">
        <p>&copy; 2024 Ferry Booking Site. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

<script>
    // Add event listener to cancel buttons
    document.addEventListener('click', function(event) {

        if (event.target && event.target.classList.contains('cancel-btn')) {

            var reservationId = event.target.getAttribute('data-reservation-id');
            if (confirm("Are you sure you want to cancel this reservation?")) {

                // Send AJAX request to cancel the reservation
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == XMLHttpRequest.DONE) {
                        if (xhr.status == 200) {
                            // Reload the page after successful cancellation
                            window.location.href = 'agent_bookings.php';
                        } else {
                            alert('Error: ' + xhr.responseText);
                        }
                    }
                };
                xhr.open('POST', 'cancel_agent_bookings.php');
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.send('reservation_id=' + reservationId);

            }
        }
    });
</script>
</body>
</html>
