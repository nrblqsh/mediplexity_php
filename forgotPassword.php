<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Assuming your database connection details
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

// Retrieve POST data
$postData = json_decode(file_get_contents("php://input"));

if (isset($postData->phone) && isset($postData->password)) {
    $phone = $conn->real_escape_string($postData->phone);
    $password = $conn->real_escape_string($postData->password);

    // Prepare SQL statement for patient table
    $stmt = $conn->prepare("SELECT * FROM patient WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update password for patient
        $updateStmt = $conn->prepare("UPDATE patient SET password = ? WHERE phone = ?");
        $updateStmt->bind_param("ss", $password, $phone);
        if ($updateStmt->execute()) {
            echo json_encode(["message" => "success reset"]);
        } else {
            echo json_encode(["message" => "error", "details" => $conn->error]);
        }
    } else {
        // Check if phone number exists in 'specialist' table
        $stmt = $conn->prepare("SELECT * FROM specialist WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update password for specialist
            $updateStmt = $conn->prepare("UPDATE specialist SET password = ? WHERE phone = ?");
            $updateStmt->bind_param("ss", $password, $phone);
            if ($updateStmt->execute()) {
                echo json_encode(["message" => "success reset"]);
            } else {
                echo json_encode(["message" => "error", "details" => $conn->error]);
            }
        } else {
            echo json_encode(["message" => "error", "details" => "Phone number not found"]);
        }
    }
} else {
    echo json_encode(["message" => "error", "details" => "Invalid or missing parameters"]);
}

$conn->close();
?>
