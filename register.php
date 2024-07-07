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
    if (isset($input['patientName']) && isset($input['phone']) && isset($input['password'])) {
        $patientName = $input['patientName'];
        $phone = $input['phone'];
        $password = $input['password'];

        try {
            // Create a MySQLi connection
            $conn = new mysqli($hostname, $username, $password, $database);

            // Check the connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Check if the user already exists
            $stmtCheck = $conn->prepare("SELECT * FROM patient WHERE phone = ?");
            $stmtCheck->bind_param("s", $phone);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows > 0) {
                $response->success = false;
                $response->error = "User already exists";
            } else {
                // Insert new user into database
              // Insert new user into database
$stmtInsert = $conn->prepare("INSERT INTO patient (patientName, phone, password) VALUES (?, ?, ?)");
$stmtInsert->bind_param("sss", $patientName, $phone, $password);

if ($stmtInsert->execute()) {
    $response->success = true;
    $response->message = "Registration successful";
} else {
    $response->success = false;
    $response->error = "Error inserting user: " . $stmtInsert->error;
}

$stmtInsert->close();

            }

            $stmtCheck->close();
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
        $response->error = "Registration parameters not provided";
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
