<?php
// Initialize the session
session_start();

// Include database connection
include 'db.php';

// Check if reservation_id is provided in the POST data
if(isset($_POST['reservation_id'])) {
    $reservation_id = $_POST['reservation_id'];

    // Fetch the reservation details and departure date from the database
    $reservation_query = "SELECT r.route_id, r.amount, rt.date AS route_date,
                                TIMESTAMPDIFF(HOUR, NOW(), rt.date) AS hours_diff
                          FROM reservations r
                          JOIN routes rt ON r.route_id = rt.route_id
                          WHERE r.reservation_id = $reservation_id";

    $reservation_result = mysqli_query($conn, $reservation_query);

    if ($reservation_result && mysqli_num_rows($reservation_result) > 0) {
        $reservation_row = mysqli_fetch_assoc($reservation_result);
        $route_id = $reservation_row['route_id'];
        $total_amount = $reservation_row['amount'];
        $hours_diff = $reservation_row['hours_diff'];

        // Calculate refund percentage based on cancellation timing
        $refund_percentage = ($hours_diff >= 24) ? 100 : 90;
        $refund_amount = ($refund_percentage / 100) * $total_amount;

        // Update the available seats of the route
        $update_route_query = "UPDATE routes SET available_seats = available_seats + 1 WHERE route_id = $route_id";
        if(mysqli_query($conn, $update_route_query)) {
            // Update the total_amount_paid in customers table
            $customer_id = $_SESSION['customer_id'];
            $update_customers_query = "UPDATE customer SET total_amount_paid = total_amount_paid - $refund_amount WHERE customer_id = $customer_id";
            if(mysqli_query($conn, $update_customers_query)) {
                // Delete the reservation from the reservations table
                $delete_reservation_query = "DELETE FROM reservations WHERE reservation_id = $reservation_id";
                if(mysqli_query($conn, $delete_reservation_query)) {
                    echo "Reservation canceled successfully.";
                } else {
                    echo "Error canceling reservation: " . mysqli_error($conn);
                }
            } else {
                echo "Error updating customer total amount paid: " . mysqli_error($conn);
            }
        } else {
            echo "Error updating route available seats: " . mysqli_error($conn);
        }
    } else {
        echo "Reservation not found.";
    }
} else {
    echo "Reservation ID not provided.";
}

// Close connection
mysqli_close($conn);
?>
