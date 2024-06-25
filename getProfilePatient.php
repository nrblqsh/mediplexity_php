<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
    header('Content-Type: application/json');

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

    header('Content-Type: application/json');


http_response_code(404);
$response = new stdClass();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $patientID = isset($_GET['patientID']) ? $_GET['patientID'] : null;
    error_log("Received patientID: $patientID");

    if ($patientID === null) {
        echo json_encode(["status" => "error", "message" => "Patient ID is required"]);
        exit;
    }

    try {
        $selectStmt = $db->prepare("SELECT patientName, icNumber, phone, gender, birthDate FROM patient WHERE patientID = :patientID");
        $selectStmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
        $selectStmt->execute();
        $patientData = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($patientData)) {
            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $patientData]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Patient not found"]);
        }
    } catch (PDOException $ex) {
        http_response_code(500);
        $errorDetails = [
            "status" => "error",
            "message" => "Failed to update patient information: " . $ex->getMessage(),
            "trace" => $ex->getTraceAsString(),
        ];
    header('Content-Type: application/json');

        echo json_encode($errorDetails);
        // Log the exception details to the PHP error log
        error_log("Exception: " . $ex->getMessage() . "\nTrace: " . $ex->getTraceAsString());
    }

    exit;
}

// Function to get the existing value of a field from the database
function getExistingValue($field, $patientID, $db) {
    $stmt = $db->prepare("SELECT $field FROM patient WHERE patientID = :patientID");
    $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
    $stmt->execute();
    $value = $stmt->fetchColumn();
    echo $value;
    return $value;
}
?>
