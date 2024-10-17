<?php
// Initialize the session
session_start();

// Include database connection
include 'db.php';

// Check if route_id is provided in the GET data
if(isset($_GET['route_id'])) {
    $route_id = $_GET['route_id'];

    // Get the customer ID from the session
    $customer_id = $_SESSION['customer_id'];

    // Check if the customer has reached the limit of 5 reservations
    $count_reservations_query = "SELECT COUNT(*) AS reservation_count FROM reservations WHERE customer_id = $customer_id";
    $count_reservations_result = mysqli_query($conn, $count_reservations_query);
    $count_reservations_row = mysqli_fetch_assoc($count_reservations_result);
    $reservation_count = $count_reservations_row['reservation_count'];

    if ($reservation_count >= 5) {
        echo '<script>alert("You have reached the maximum limit of reservations (5)."); window.location.href = "main_page.php";</script>';
        exit;
    }

    // Fetch the route details from the database
    $route_query = "SELECT * FROM routes WHERE route_id = $route_id";
    $route_result = mysqli_query($conn, $route_query);

    if ($route_result && mysqli_num_rows($route_result) > 0) {
        $route_row = mysqli_fetch_assoc($route_result);
        $price_per_ticket = $route_row['price'];

        // Increment the number_of_tickets field for the customer
        $update_number_of_tickets_query = "UPDATE reservations SET number_of_tickets = number_of_tickets + 1 WHERE customer_id = $customer_id";
        mysqli_query($conn, $update_number_of_tickets_query);

        // Get the total amount of the reservation
        $total_amount = $price_per_ticket;

        // Insert a new reservation entry
        $insert_reservation_query = "INSERT INTO reservations (reservation_date, number_of_tickets, customer_id, route_id, amount) 
        VALUES (NOW(), 1, $customer_id, $route_id, $total_amount)";

        if(mysqli_query($conn, $insert_reservation_query)) {
            // Get the ID of the last inserted reservation
            $reservation_id = mysqli_insert_id($conn);

            // Insert into route_customer table to keep track of routes booked by the customer
            $insert_route_customer_query = "INSERT INTO route_customer (route_id, reservation_id) VALUES ($route_id, $reservation_id)";
            mysqli_query($conn, $insert_route_customer_query);

            // Update the total amount paid by the customer
            $update_total_amount_paid_query = "UPDATE customer SET total_amount_paid = total_amount_paid + $total_amount WHERE customer_id = $customer_id";
            // Decrease available_seats by 1
            $update_available_seats_query = "UPDATE routes SET available_seats = available_seats - 1 WHERE route_id = $route_id";

            // Begin a transaction
            mysqli_begin_transaction($conn);

            if(mysqli_query($conn, $update_total_amount_paid_query) && mysqli_query($conn, $update_available_seats_query)) {
                // Commit the transaction
                mysqli_commit($conn);

                // Redirect to main_page.php with an alert message
                echo '<script>alert("Booking successful!"); window.location.href = "main_page.php";</script>';
                exit;
            } else {
                // Rollback the transaction if any query fails
                mysqli_rollback($conn);

                // Display error message
                echo "Error updating total amount paid or available seats: " . mysqli_error($conn);
                exit;
            }
        } else {
            echo "Error inserting reservation: " . mysqli_error($conn);
            exit;
        }
    } else {
        echo "Route not found.";
        exit;
    }
} else {
    // If route_id is not provided in the GET data, display an error message
    echo "Route ID not provided.";
    exit;
}

// Close connection
mysqli_close($conn);
?>
