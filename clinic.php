<?php
header('Content-Type: application/json; charset=utf-8');

// Assuming you have a MySQL database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mediplexity";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the 'q' parameter is set
if (isset($_GET['q'])) {
    $searchQuery = $conn->real_escape_string($_GET['q']);

    $sql = "SELECT * FROM clinic WHERE clinicName LIKE '%$searchQuery%'";
} else {
    // Fetch all clinic information
    $sql = "SELECT * FROM clinic";
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
} else {
    echo "0 results";
}

$conn->close();

?>