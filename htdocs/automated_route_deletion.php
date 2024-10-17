<?php
// Include database connection
include 'db.php';

// Get current date
$current_date = date("Y-m-d");

// Query to fetch routes that have passed their date
$sql = "SELECT * FROM routes WHERE date < ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_date);

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any routes found
if ($result->num_rows > 0) {
    // Loop through each route
    while ($row = $result->fetch_assoc()) {
        // Get route ID
        $route_id = $row['route_id'];

        // Delete the route
        $sql_delete_route = "DELETE FROM routes WHERE route_id = ?";
        $stmt_delete_route = $conn->prepare($sql_delete_route);
        $stmt_delete_route->bind_param("i", $route_id);
        $stmt_delete_route->execute();

        // Output message
        echo "Route with ID " . $route_id . " has been deleted as it has passed its date.<br>";
    }
} else {
    echo "No routes found that have passed their date.";
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
