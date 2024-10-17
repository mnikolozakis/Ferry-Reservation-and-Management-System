<?php
// Initialize the session
session_start();

// Include database connection
include 'db.php';

// Check if reservation_id is provided in the POST data
if(isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];

    // Fetch the reservation details and departure date from the database
    $reservation_query = "SELECT r.route_id, r.amount, r.customer_id, rt.date AS route_date,
                                TIMESTAMPDIFF(HOUR, NOW(), rt.date) AS hours_diff
                          FROM reservations r
                          JOIN routes rt ON r.route_id = rt.route_id
                          WHERE r.reservation_id = ?";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $reservation_query);

    // Bind the reservation ID parameter
    mysqli_stmt_bind_param($stmt, "i", $reservation_id);

    // Execute the statement
    mysqli_stmt_execute($stmt);

    // Get the result
    $reservation_result = mysqli_stmt_get_result($stmt);

    if ($reservation_result && mysqli_num_rows($reservation_result) > 0) {
        $reservation_row = mysqli_fetch_assoc($reservation_result);
        $route_id = $reservation_row['route_id'];
        $total_amount = $reservation_row['amount'];
        $customer_id = $reservation_row['customer_id'];
        $hours_diff = $reservation_row['hours_diff'];

        // Calculate refund percentage based on cancellation timing
        $refund_percentage = ($hours_diff >= 24) ? 100 : 90;
        $refund_amount = ($refund_percentage / 100) * $total_amount;

        // Update the available seats of the route
        $update_route_query = "UPDATE routes SET available_seats = available_seats + 1 WHERE route_id = ?";
        $stmt = mysqli_prepare($conn, $update_route_query);
        mysqli_stmt_bind_param($stmt, "i", $route_id);
        mysqli_stmt_execute($stmt);

        // Update the total_amount_paid in customers table
        $update_customers_query = "UPDATE customer SET total_amount_paid = total_amount_paid - ? WHERE customer_id = ?";
        $stmt = mysqli_prepare($conn, $update_customers_query);
        mysqli_stmt_bind_param($stmt, "di", $refund_amount, $customer_id);
        mysqli_stmt_execute($stmt);

        // Delete the reservation from the reservations table
        $delete_reservation_query = "DELETE FROM reservations WHERE reservation_id = ?";
        $stmt = mysqli_prepare($conn, $delete_reservation_query);
        mysqli_stmt_bind_param($stmt, "i", $reservation_id);
        mysqli_stmt_execute($stmt);

        echo json_encode(array("status" => "success", "message" => "Reservation canceled successfully."));
    } else {
        echo json_encode(array("status" => "error", "message" => "Reservation not found."));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Reservation ID not provided."));
}

// Close connection
mysqli_close($conn);
?>
