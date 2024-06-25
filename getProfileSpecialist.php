<?php
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', '1');
$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection error: " . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $specialistID = isset($_GET['specialistID']) ? $_GET['specialistID'] : null;
    error_log("Received GET request for specialistID: $specialistID");

    if ($specialistID === null) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "specialistID is required"]);
        exit;
    }

    try {
        // Fetch existing data from the database, excluding the image data
        $selectStmt = $db->prepare("SELECT specialistName, phone, specialistTitle FROM specialist WHERE specialistID = :specialistID");
        $selectStmt->bindParam(':specialistID', $specialistID, PDO::PARAM_INT);
        $selectStmt->execute();

        // Fetch the result as an associative array
        $existingData = $selectStmt->fetch(PDO::FETCH_ASSOC);

        // Check if data is available
        if (!$existingData) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Specialist not found"]);
            exit;
        }

        // Set the JSON content type header
        header('Content-Type: application/json');

        // Send the response as JSON with all fields other than the image
        echo json_encode(["status" => "success", "data" => $existingData]);
        exit;

    } catch (PDOException $ex) {
        http_response_code(500);
        $errorDetails = [
            "status" => "error",
            "message" => "Failed to retrieve specialist information: " . $ex->getMessage(),
            "trace" => $ex->getTraceAsString(),
        ];
        echo json_encode($errorDetails);
        error_log("Exception in GET request: " . $ex->getMessage() . "\nTrace: " . $ex->getTraceAsString());
    }
}
?>
