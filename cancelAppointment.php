<?php
header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mediplexity";
$table = "consultation"; // Assuming the table name is 'consultation'

// Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
    return;
}

// Assuming you receive the consultation ID as a parameter
$consultationID = $_GET['consultationID'];


// Delete a specific record based on the consultation ID
$sql = "DELETE FROM $table WHERE consultationID='$consultationID'";

if ($conn->query($sql) === true) {
    echo "Appointment canceled successfully";
} else {
    http_response_code(500); // Internal Server Error
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
return;
?>
