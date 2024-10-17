<?php
// Initialize the session
session_start();

// Check if the user is not logged in, then redirect him to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routes</title>
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
    <div class="row">
        <div class="col-md-12">
            <h2>Available Routes</h2>
            <?php
            // Include database connection
            include 'db.php';

            // Check if cancel button is pressed and delete route
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel'])) {
                $route_id = $_POST['route_id'];
                // Delete the route from the database
                $delete_route_query = "DELETE FROM routes WHERE route_id = ?";
                $stmt = $conn->prepare($delete_route_query);
                $stmt->bind_param("i", $route_id);
                $stmt->execute();

                // Check if the route was deleted successfully
                if ($stmt->affected_rows > 0) {
                    echo "<script>alert('Route deleted successfully.');</script>";
                } else {
                    echo "<script>alert('Failed to delete route.');</script>";
                }
            }

            // Query to fetch all routes with ship and company information
            $sql = "SELECT routes.*, ships.name AS ship_name, companies.name AS company_name 
                    FROM routes 
                    INNER JOIN ships ON routes.ship_id = ships.ship_id 
                    INNER JOIN companies ON ships.company_id = companies.company_id";

            // Execute query
            $result = mysqli_query($conn, $sql);

            // Check if there are any routes found
            if(mysqli_num_rows($result) > 0) {
                // Output routes in a table
                echo "<table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>Origin</th>
                                <th>Destination</th>
                                <th>Departure Time</th>
                                <th>Arrival Time</th>
                                <th>Date</th>
                                <th>Available Seats</th>
                                <th>Vehicles Accepted</th>
                                <th>Ship</th> 
                                <th>Company</th> 
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>";
                            while($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>".$row['origin']."</td>";
                                echo "<td>".$row['destination']."</td>";
                                echo "<td>".$row['departure_time']."</td>";
                                echo "<td>".$row['arrival_time']."</td>";
                                echo "<td>".$row['date']."</td>";
                                echo "<td>".$row['available_seats']."</td>";
                                echo "<td>".$row['vehicles_accepted']."</td>";
                                echo "<td>".$row['ship_name']."</td>"; // Display ship name
                                echo "<td>".$row['company_name']."</td>"; // Display company name
                                // Add cancel button with modal trigger
                                echo "<td>
                                    <button type='button' class='btn btn-danger' data-bs-toggle='modal' data-bs-target='#cancelModal".$row['route_id']."'>
                                        Cancel
                                    </button>
                                </td>";
                            
                                echo "</tr>";
                            
                                // Add modal window for route cancellation confirmation
                                echo '<div class="modal fade" id="cancelModal'.$row['route_id'].'" tabindex="-1" aria-labelledby="cancelModalLabel'.$row['route_id'].'" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="cancelModalLabel'.$row['route_id'].'">Cancel Route Confirmation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to cancel the route from '.$row['origin'].' to '.$row['destination'].'?
                                                    <br><br>
                                                    <h6>Customers with reservations on this route:</h6>
                                                    <ul>';
                            
                                // Query to fetch customers with reservations on this route
                                $reservation_query = "SELECT c.full_name, c.email 
                                                FROM route_customer rc
                                                JOIN reservations r ON rc.reservation_id = r.reservation_id
                                                JOIN customer c ON r.customer_id = c.customer_id
                                                WHERE rc.route_id = ".$row['route_id'];
                            
                                // Execute query
                                $reservation_result = mysqli_query($conn, $reservation_query);
                            
                                // Check if there are any customers with reservations
                                if(mysqli_num_rows($reservation_result) > 0) {
                                    while($customer = mysqli_fetch_assoc($reservation_result)) {
                                        echo '<li>'.$customer['full_name'].' - '.$customer['email'].'</li>';
                                    }
                                } else {
                                    echo '<li>No customers have reservations on this route.</li>';
                                }
                            
                                echo '</ul>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <form method="post">
                                                        <input type="hidden" name="route_id" value="'.$row['route_id'].'">
                                                        <button type="submit" name="cancel" class="btn btn-danger">Confirm Cancel</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>';
                            }
                            
                            echo "</tbody></table>";
                            
                            } else {
                            echo "No routes found.";
                            }
                            
                            // Close connection
                            mysqli_close($conn);
                            ?>
                            </div>
                            </div>
                            </div>
                            
                            <!-- Empty space above the footer -->
                            <div style="height: 100px;"></div>
                            
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
  </body>
</html>