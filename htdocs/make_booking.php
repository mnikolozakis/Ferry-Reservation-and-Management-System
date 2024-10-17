<?php
// Initialize the session
session_start();

// Include database connection
include 'db.php';

// Define variables and initialize with empty values
$customer_id = $route_id = $number_of_tickets = "";
$customer_id_err = $route_id_err = $number_of_tickets_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate customer_id
    if (empty(trim($_POST["customer_id"]))) {
        $customer_id_err = "Please select a customer.";
    } else {
        $customer_id = trim($_POST["customer_id"]);
    }

    // Validate route_id
    if (empty(trim($_POST["route_id"]))) {
        $route_id_err = "Please select a route.";
    } else {
        $route_id = trim($_POST["route_id"]);
    }

    // Validate number_of_tickets
    if (empty(trim($_POST["number_of_tickets"]))) {
        $number_of_tickets_err = "Please enter the number of tickets.";
    } else {
        $number_of_tickets = trim($_POST["number_of_tickets"]);
        // Check if number_of_tickets is between 1 and 5
        if ($number_of_tickets < 1 || $number_of_tickets > 5) {
            $number_of_tickets_err = "Please select between 1 and 5 tickets.";
        }
    }

    // Check input errors before inserting into database
    if (empty($customer_id_err) && empty($route_id_err) && empty($number_of_tickets_err)) {
        // Check if customer already has 5 bookings
        $sql_check_bookings = "SELECT COUNT(*) as total_bookings FROM reservations WHERE customer_id = ?";
        if ($stmt_check_bookings = $conn->prepare($sql_check_bookings)) {
            // Bind variables to the prepared statement as parameters
            $stmt_check_bookings->bind_param("i", $param_customer_id);
            // Set parameters
            $param_customer_id = $customer_id;
            // Attempt to execute the prepared statement
            if ($stmt_check_bookings->execute()) {
                // Store result
                $stmt_check_bookings->store_result();
                // Check if customer has 5 bookings already
                if ($stmt_check_bookings->num_rows == 1) {
                    $stmt_check_bookings->bind_result($total_bookings);
                    if ($stmt_check_bookings->fetch()) {
                        if ($total_bookings >= 5) {
                            $number_of_tickets_err = "Customer already has 5 bookings.";
                        }
                    }
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }
            // Close statement
            $stmt_check_bookings->close();
        }

        // Prepare an insert statement for reservations
        $sql_insert_reservation = "INSERT INTO reservations (customer_id, route_id, number_of_tickets, amount) VALUES (?, ?, ?, (SELECT price FROM routes WHERE route_id = ? LIMIT 1))";

        if ($stmt_insert_reservation = $conn->prepare($sql_insert_reservation)) {
            // Bind variables to the prepared statement as parameters
            $stmt_insert_reservation->bind_param("iiii", $param_customer_id, $param_route_id, $param_number_of_tickets, $param_route_id);

            // Set parameters
            $param_customer_id = $customer_id;
            $param_route_id = $route_id;
            $param_number_of_tickets = $number_of_tickets;

            // Attempt to execute the prepared statement
            if ($stmt_insert_reservation->execute()) {
                // Get the last inserted reservation ID
                $last_reservation_id = $stmt_insert_reservation->insert_id;

                // Prepare an insert statement for agent_bookings
                $sql_insert_agent_booking = "INSERT INTO agent_bookings (agent_id, customer_id, reservation_id) VALUES (?, ?, ?)";

                if ($stmt_insert_agent_booking = $conn->prepare($sql_insert_agent_booking)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt_insert_agent_booking->bind_param("iii", $param_agent_id, $param_customer_id, $param_reservation_id);

                    // Set parameters
                    $param_agent_id = $_SESSION["customer_id"];
                    $param_customer_id = $customer_id;
                    $param_reservation_id = $last_reservation_id;

                    // Attempt to execute the prepared statement
                    if ($stmt_insert_agent_booking->execute()) {
                        // Insert into route_customer table
                        $sql_insert_route_customer = "INSERT INTO route_customer (route_id, reservation_id) VALUES (?, ?)";
                        if ($stmt_insert_route_customer = $conn->prepare($sql_insert_route_customer)) {
                            // Bind variables to the prepared statement as parameters
                            $stmt_insert_route_customer->bind_param("ii", $param_route_id, $param_reservation_id);

                            // Set parameters
                            $param_route_id = $route_id;
                            $param_reservation_id = $last_reservation_id;

                            // Attempt to execute the prepared statement
                            if (!$stmt_insert_route_customer->execute()) {
                                echo "Something went wrong. Please try again later.";
                            }

                            // Close statement
                            $stmt_insert_route_customer->close();
                        }

                        // Update total_amount_paid for the customer
                        $sql_update_total_amount = "UPDATE customer SET total_amount_paid = total_amount_paid + (SELECT price * ? FROM routes WHERE route_id = ? LIMIT 1) WHERE customer_id = ?";
                        if ($stmt_update_total_amount = $conn->prepare($sql_update_total_amount)) {
                            // Bind variables to the prepared statement as parameters
                            $stmt_update_total_amount->bind_param("iii", $param_number_of_tickets, $param_route_id, $param_customer_id);

                            // Set parameters
                            $param_number_of_tickets = $number_of_tickets;
                            $param_route_id = $route_id;
                            $param_customer_id = $customer_id;

                            // Attempt to execute the prepared statement
                            if (!$stmt_update_total_amount->execute()) {
                                echo "Something went wrong. Please try again later.";
                            }

                            // Close statement
                            $stmt_update_total_amount->close();
                        }

                        // Redirect to main page
                        echo "<script>alert('Booking made successfully.'); window.location.href='main_page.php';</script>";
                    } else {
                        echo "Something went wrong. Please try again later.";
                    }

                    // Close statement
                    $stmt_insert_agent_booking->close();
                }
            } else {
                echo "Something went wrong. Please try again later.";
            }

            // Close statement
            $stmt_insert_reservation->close();
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
    <title>Make Booking</title>
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
                <h2>Make Booking</h2>
                <!-- Form to make a booking -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Select Customer</label>
                        <select name="customer_id" class="form-control <?php echo (!empty($customer_id_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Customer</option>
                            <?php
                            // Fetch customers from database where permission_level is 0 (customer)
                            $sql_customers = "SELECT customer_id, full_name FROM customer WHERE permission_level = 0";
                            $result_customers = $conn->query($sql_customers);
                            if ($result_customers->num_rows > 0) {
                                while ($row_customer = $result_customers->fetch_assoc()) {
                                    echo "<option value='" . $row_customer["customer_id"] . "'>" . $row_customer["full_name"] . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $customer_id_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Select Route</label>
                        <select name="route_id" class="form-control <?php echo (!empty($route_id_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Route</option>
                            <?php
                            // Fetch routes from database
                            $sql_routes = "SELECT route_id, origin, destination FROM routes";
                            $result_routes = $conn->query($sql_routes);
                            if ($result_routes->num_rows > 0) {
                                while ($row_route = $result_routes->fetch_assoc()) {
                                    echo "<option value='" . $row_route["route_id"] . "'>" . $row_route["origin"] . " to " . $row_route["destination"] . "</option>";
                                }
                            }
                            ?>
                        </select>
                        <span class="invalid-feedback"><?php echo $route_id_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Number of Tickets (1-5)</label>
                        <input type="number" name="number_of_tickets" class="form-control <?php echo (!empty($number_of_tickets_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $number_of_tickets; ?>" min="1" max="5">
                        <span class="invalid-feedback"><?php echo $number_of_tickets_err; ?></span>
                    </div>
                    <div class="form-group" style="margin-top:10px">
                        <input type="submit" class="btn btn-primary" value="Make Booking">
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
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</body>

</html>
