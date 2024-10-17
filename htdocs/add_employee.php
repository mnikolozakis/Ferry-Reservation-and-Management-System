<?php
// Initialize the session
session_start();

// Include the database connection file
include 'db.php';

// Check if the user is already logged in, if not redirect him to the home page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: home.php");
    exit;
}

// Define variables and initialize with empty values
$employee_id = $name = $address = $specialization = $phone = $salary = $id_number = $ship_id = "";
$name_err = $address_err = $specialization_err = $phone_err = $salary_err = $id_number_err = $ship_id_err = "";
$alert_message = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter employee name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate address
    if (empty(trim($_POST["address"]))) {
        $address_err = "Please enter address.";
    } else {
        $address = trim($_POST["address"]);
    }

    // Validate specialization
    if (empty(trim($_POST["specialization"]))) {
        $specialization_err = "Please enter specialization.";
    } else {
        $specialization = trim($_POST["specialization"]);
    }

    // Validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate salary
    if (empty(trim($_POST["salary"]))) {
        $salary_err = "Please enter salary.";
    } else {
        $salary = trim($_POST["salary"]);
    }

    // Validate ID number
    if (empty(trim($_POST["id_number"]))) {
        $id_number_err = "Please enter ID number.";
    } else {
        $id_number = trim($_POST["id_number"]);
    }

    // Validate ship_id
    if (empty(trim($_POST["ship_id"]))) {
        $ship_id_err = "Please select ship.";
    } else {
        $ship_id = trim($_POST["ship_id"]);
    }

    // Check input errors before inserting into database
    if (empty($name_err) && empty($address_err) && empty($specialization_err) && empty($phone_err) && empty($salary_err) && empty($id_number_err) && empty($ship_id_err)) {
        // Prepare an insert statement
        $sql = "INSERT INTO employees (name, address, specialization, phone, salary, id_number, ship_id) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssss", $param_name, $param_address, $param_specialization, $param_phone, $param_salary, $param_id_number, $param_ship_id);

            // Set parameters
            $param_name = $name;
            $param_address = $address;
            $param_specialization = $specialization;
            $param_phone = $phone;
            $param_salary = $salary;
            $param_id_number = $id_number;
            $param_ship_id = $ship_id;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Show success alert
                $alert_message = "Employee added successfully.";
                echo '<script>alert("' . $alert_message . '"); window.location.href = "main_page.php";</script>';
                exit;
            } else {
                echo "Oops! Something went wrong. Please try again later.";
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
    <title>Add Employee</title>
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


    <!-- Add Employee Form -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2>Add Employee</h2>
                <?php if (!empty($alert_message)) : ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $alert_message; ?>
                </div>
                <?php endif; ?>
                <!-- Form to add a new employee -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                        <span class="invalid-feedback"><?php echo $name_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $address; ?>">
                        <span class="invalid-feedback"><?php echo $address_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Specialization</label>
                        <input type="text" name="specialization" class="form-control <?php echo (!empty($specialization_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $specialization; ?>">
                        <span class="invalid-feedback"><?php echo $specialization_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                        <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Salary</label>
                        <input type="text" name="salary" class="form-control <?php echo (!empty($salary_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $salary; ?>">
                        <span class="invalid-feedback"><?php echo $salary_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>ID Number</label>
                        <input type="text" name="id_number" class="form-control <?php echo (!empty($id_number_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $id_number; ?>">
                        <span class="invalid-feedback"><?php echo $id_number_err; ?></span>
                    </div>
                    <div class="form-group">
                        <label>Ship</label>
                        <select name="ship_id" class="form-control <?php echo (!empty($ship_id_err)) ? 'is-invalid' : ''; ?>">
                            <option value="">Select Ship</option>
                            <?php
                            // Fetch ship names from the database and populate the dropdown
                            $sql = "SELECT ship_id, name FROM ships";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row["ship_id"] . '">' . $row["name"] . '</option>';
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

