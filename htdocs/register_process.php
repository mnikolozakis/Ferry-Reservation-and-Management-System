<?php
// Include the database connection file
include 'db.php';

// Initialize the session
session_start();

// Define variables and initialize with empty values
$full_name = $phone = $address = $id_number = $username = $email = $password = $confirmPassword = "";
$full_name_err = $phone_err = $address_err = $id_number_err = $username_err = $email_err = $password_err = $confirmPassword_err = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate full name
    if (empty(trim($_POST["full_name"]))) {
        $full_name_err = "Please enter your full name.";
    } else {
        $full_name = trim($_POST["full_name"]);
    }

    // Validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate address
    if (empty(trim($_POST["address"]))) {
        $address_err = "Please enter your address.";
    } else {
        $address = trim($_POST["address"]);
    }

    // Validate ID number
    if (empty(trim($_POST["id_number"]))) {
        $id_number_err = "Please enter your ID number.";
    } else {
        $id_number = trim($_POST["id_number"]);
    }

    // Validate username
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter a username.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);

        // Check if email is already taken
        $sql_email_check = "SELECT * FROM customer WHERE email = ?";
        $stmt_email_check = $conn->prepare($sql_email_check);
        $stmt_email_check->bind_param("s", $email);
        $stmt_email_check->execute();
        $result_email_check = $stmt_email_check->get_result();
        if ($result_email_check->num_rows > 0) {
            $email_err = "This email is already taken.";
        }
        $stmt_email_check->close();
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirmPassword"]))) {
        $confirmPassword_err = "Please confirm password.";
    } else {
        $confirmPassword = trim($_POST["confirmPassword"]);
        if (empty($password_err) && ($password != $confirmPassword)) {
            $confirmPassword_err = "Password did not match.";
        }
    }

    // Check input errors before inserting into database
    if (empty($full_name_err) && empty($phone_err) && empty($address_err) && empty($id_number_err) && empty($username_err) && empty($email_err) && empty($password_err) && empty($confirmPassword_err)) {

        // Prepare an insert statement
        $sql = "INSERT INTO customer (full_name, phone, address, id_number, username, email, password) VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssss", $param_full_name, $param_phone, $param_address, $param_id_number, $param_username, $param_email, $param_password);

            // Set parameters
            $param_full_name = $full_name;
            $param_phone = $phone;
            $param_address = $address;
            $param_id_number = $id_number;
            $param_username = $username;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Redirect to login page
                header("location: login.php");
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
