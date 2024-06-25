<?php
// Enable error reporting
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

$response = new stdClass();


if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Create a MySQLi connection
        $conn = new mysqli($hostname, $username, $password, $database);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the status update request is present
        if (isset($_GET['updateConsultationStatus'])) {
            $consultationID = $_GET['consultationID'];
            $newStatus = $_GET['updateConsultationStatus'];

            // Prepare and execute the SQL statement to update the status
            $stmtUpdate = $conn->prepare("UPDATE consultation SET consultationStatus = ? WHERE consultationID = ?");
            $stmtUpdate->bind_param("si", $newStatus, $consultationID);

            if ($stmtUpdate->execute()) {
                $response->success = true;
            } else {
                $response->success = false;
                $response->error = "Error updating status: " . $stmtUpdate->error;
            }

            $stmtUpdate->close();
        } else {
            http_response_code(400);
            $response->error = "Status update parameters not provided";
        }

        // Close the connection
        $conn->close();

    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred " . $ee->getMessage();
    }

    // Echo only the JSON-encoded response
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>
