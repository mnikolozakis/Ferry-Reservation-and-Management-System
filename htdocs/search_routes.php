<?php
// Include database connection
include 'db.php';

// Check if origin is set in POST request
if(isset($_POST['origin'])) {
    // Sanitize the input
    $origin = mysqli_real_escape_string($conn, $_POST['origin']);

    // SQL query to search routes based on origin or destination
    $sql = "SELECT * FROM routes WHERE origin LIKE '%$origin%' OR destination LIKE '%$origin%'";
    
    // Execute the query
    $result = mysqli_query($conn, $sql);

    // Check if there are any routes found
    if(mysqli_num_rows($result) > 0) {
        // Output routes in a table with Bootstrap classes
        echo "<div class='table-responsive'>
                <table class='table table-striped'>
                    <thead>
                        <tr>
                            <th>Origin</th>
                            <th>Destination</th>
                            <th>Departure Time</th>
                            <th>Arrival Time</th>
                            <th>Date</th>
                            <th>Available Seats</th>
                            <th>Vehicles Accepted</th>
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
            echo "</tr>";
        }
        echo "</tbody>
            </table>
        </div>";
    } else {
        echo "No routes found with the given origin or destination.";
    }
    
} else {
    echo "Origin not specified.";
}
?>
