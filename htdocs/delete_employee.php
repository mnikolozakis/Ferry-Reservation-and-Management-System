<?php
// Initialize the session
session_start();

// Include database connection
include 'db.php';

// Function to delete employee
function deleteEmployee($employee_id) {
    global $conn;
    $sql = "DELETE FROM employees WHERE employee_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Check if delete request is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_employee_id"])) {
    $employee_id_to_delete = $_POST["delete_employee_id"];
    deleteEmployee($employee_id_to_delete);
}

// Fetch all employees from the database
$employees_query = "SELECT * FROM employees";
$employees_result = mysqli_query($conn, $employees_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management</title>
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
    <h2>Employee Management</h2>
    <?php
    // Check if there are employees
    if (mysqli_num_rows($employees_result) > 0) {
        echo "<table class='table'>";
        echo "<tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Address</th>
                <th>Specialization</th>
                <th>Phone</th>
                <th>Salary</th>
                <th>ID Number</th>
                <th>Ship ID</th>
                <th>Action</th>
              </tr>";

        // Output data of each row
        while ($row = mysqli_fetch_assoc($employees_result)) {
            echo "<tr>";
            echo "<td>".$row["employee_id"]."</td>";
            echo "<td>".$row["name"]."</td>";
            echo "<td>".$row["address"]."</td>";
            echo "<td>".$row["specialization"]."</td>";
            echo "<td>".$row["phone"]."</td>";
            echo "<td>".$row["salary"]."</td>";
            echo "<td>".$row["id_number"]."</td>";
            echo "<td>".$row["ship_id"]."</td>";
            // Check if the employee's specialization is "Kapetanios" and he is in a route
            if ($row["specialization"] === "Kapetanios" && !empty($row["ship_id"])) {
                echo "<td><button class='btn btn-danger' onclick=\"alert('Cannot delete this employee because they are still assigned to a route.');\">Delete</button></td>";
            } else {
                // Add delete button with form for each employee row
                echo "<td>
                <form method='post' onsubmit=\"return confirm('Are you sure you want to delete this employee?');\">
                    <input type='hidden' name='delete_employee_id' value='".$row["employee_id"]."'>
                    <button type='submit' class='btn btn-danger'>Delete</button>
                </form>
                </td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No employees found.";
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
</body>
</html>

