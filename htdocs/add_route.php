<?php
// Initialize the session
session_start();

// Include database connection
include 'db.php';

// Define variables and initialize with empty values
$origin = $destination = $departure_time = $arrival_time = $date = $vehicles_accepted = $price = $ship_id = "";
$origin_err = $destination_err = $departure_time_err = $arrival_time_err = $date_err = $vehicles_accepted_err = $price_err = $ship_id_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate origin
    if (empty(trim($_POST["origin"]))) {
        $origin_err = "Please enter the origin.";
    } else {
        $origin = trim($_POST["origin"]);
    }

    // Validate destination
    if (empty(trim($_POST["destination"]))) {
        $destination_err = "Please enter the destination.";
    } else {
        $destination = trim($_POST["destination"]);
    }

    // Validate departure time
    if (empty(trim($_POST["departure_time"]))) {
        $departure_time_err = "Please enter the departure time.";
    } else {
        $departure_time = trim($_POST["departure_time"]);
    }

    // Validate arrival time
    if (empty(trim($_POST["arrival_time"]))) {
        $arrival_time_err = "Please enter the arrival time.";
    } else {
        $arrival_time = trim($_POST["arrival_time"]);
    }

    // Validate date
    if (empty(trim($_POST["date"]))) {
        $date_err = "Please enter the date.";
    } else {
        $date = trim($_POST["date"]);
    }

    // Validate vehicles accepted
    if (empty(trim($_POST["vehicles_accepted"]))) {
        $vehicles_accepted_err = "Please enter the vehicles accepted.";
    } else {
        $vehicles_accepted = trim($_POST["vehicles_accepted"]);
    }

    // Validate price
    if (empty(trim($_POST["price"]))) {
        $price_err = "Please enter the price.";
    } else {
        $price = trim($_POST["price"]);
    }

    // Validate ship selection
    if (empty(trim($_POST["ship_id"]))) {
        $ship_id_err = "Please select a ship.";
    } else {
        $ship_id = trim($_POST["ship_id"]);
    }

    // Check input errors before inserting into database
    if (empty($origin_err) && empty($destination_err) && empty($departure_time_err) && empty($arrival_time_err)
        && empty($date_err) && empty($vehicles_accepted_err) && empty($price_err) && empty($ship_id_err)) {

        // Get ship's capacity from ships table
        $sql_ship = "SELECT capacity FROM ships WHERE ship_id = ?";
        if ($stmt_ship = $conn->prepare($sql_ship)) {
            $stmt_ship->bind_param("i", $param_ship_id);
            $param_ship_id = $ship_id;
            if ($stmt_ship->execute()) {
                $stmt_ship->store_result();
                if ($stmt_ship->num_rows == 1) {
                    $stmt_ship->bind_result($ship_capacity);
                    if ($stmt_ship->fetch()) {
                        $available_seats = $ship_capacity;
                    }
                }
            }
            $stmt_ship->close();
        }

        // Prepare an insert statement
        $sql = "INSERT INTO routes (origin, destination, departure_time, arrival_time, date, available_seats, vehicles_accepted, price, ship_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssssssi", $param_origin, $param_destination, $param_departure_time, $param_arrival_time,
                $param_date, $param_available_seats, $param_vehicles_accepted, $param_price, $param_ship_id);

            // Set parameters
            $param_origin = $origin;
            $param_destination = $destination;
            $param_departure_time = $departure_time;
            $param_arrival_time = $arrival_time;
            $param_date = $date;
            $param_available_seats = $available_seats;
            $param_vehicles_accepted = $vehicles_accepted;
            $param_price = $price;
            $param_ship_id = $ship_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to main page
                echo "<script>alert('Route added successfully.'); window.location.href='main_page.php';</script>";
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Route</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <style>
        /* Add your custom styles here */
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


    <!-- Add Route Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Add Route</h2>
                <!-- Form to add a new route -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Origin</label>
                        <input type="text" name="origin" class="form-control <?php echo (!empty($origin_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $origin; ?>">
                        <span class="invalid-feedback"><?php echo $origin_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Destination</label>
                        <input type="text" name="destination" class="form-control <?php echo (!empty($destination_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $destination; ?>">
                        <span class="invalid-feedback"><?php echo $destination_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Departure Time</label>
                        <input type="time" name="departure_time" class="form-control <?php echo (!empty($departure_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $departure_time; ?>">
                        <span class="invalid-feedback"><?php echo $departure_time_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Arrival Time</label>
                        <input type="time" name="arrival_time" class="form-control <?php echo (!empty($arrival_time_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $arrival_time; ?>">
                        <span class="invalid-feedback"><?php echo $arrival_time_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control <?php echo (!empty($date_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $date; ?>">
                        <span class="invalid-feedback"><?php echo $date_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Vehicles Accepted</label>
                        <input type="text" name="vehicles_accepted" class="form-control <?php echo (!empty($vehicles_accepted_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $vehicles_accepted; ?>">
                        <span class="invalid-feedback"><?php echo $vehicles_accepted_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Price</label>
                        <input type="text" name="price" class="form-control <?php echo (!empty($price_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>">
                        <span class="invalid-feedback"><?php echo $price_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Select Ship</label>
                        <select name="ship_id" class="form-control <?php echo (!empty($ship_id_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Ship</option>
                            <?php
                            // Fetch ships from database
                            $sql_ships = "SELECT ship_id, name FROM ships";
                            $result_ships = $conn->query($sql_ships);
                            if ($result_ships->num_rows > 0) {
                                while ($row_ship = $result_ships->fetch_assoc()) {
                                    echo "<option value='" . $row_ship["ship_id"] . "'>" . $row_ship["name"] . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $ship_id_err; ?></span>
                    </div>
                    <div class="form-group" style="margin-top: 10px;">
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="main_page.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
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
</body>

</html>
