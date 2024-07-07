<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

$response = new stdClass();

// Read and decode the JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Check if the input is valid and contains the required fields
if ($_SERVER["REQUEST_METHOD"] == "POST" && $input !== null) {
    if (isset($input['specialistID']) && isset($input['oldPassword']) && isset($input['newPassword'])) {
        $specialistID = $input['specialistID'];
        $oldPassword = $input['oldPassword'];
        $newPassword = $input['newPassword'];

        // Log the received data for debugging
        file_put_contents('php://stderr', print_r($input, true));

        try {
            // Create a MySQLi connection
            $conn = new mysqli($hostname, $username, $password, $database);

            // Check the connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Fetch the current password from the database
            $stmtFetch = $conn->prepare("SELECT password FROM specialist WHERE specialistID = ?");
            $stmtFetch->bind_param("i", $specialistID);
            $stmtFetch->execute();
            $stmtFetch->bind_result($currentPassword);
            $stmtFetch->fetch();
            $stmtFetch->close();

            // Verify the old password
            if ($oldPassword === $currentPassword) {
                // Update the password in the database
                $stmtUpdate = $conn->prepare("UPDATE specialist SET password = ? WHERE specialistID = ?");
                $stmtUpdate->bind_param("si", $newPassword, $specialistID);

                if ($stmtUpdate->execute()) {
                    $response->success = true;
                } else {
                    $response->success = false;
                    $response->error = "Error updating password: " . $stmtUpdate->error;
                }

                $stmtUpdate->close();
            } else {
                $response->success = false;
                $response->error = "Old password is incorrect";
            }

            // Close the connection
            $conn->close();
        } catch (Exception $e) {
            http_response_code(500);
            $response->success = false;
            $response->error = "Error occurred: " . $e->getMessage();
        }

        // Echo the JSON-encoded response
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    } else {
        http_response_code(400);
        $response->success = false;
        $response->error = "Password update parameters not provided";
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }
} else {
    http_response_code(405);
    $response->success = false;
    $response->error = "Invalid request method or empty JSON input";
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}
?>
