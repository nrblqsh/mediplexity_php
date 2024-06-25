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
        $query = "
            SELECT 
                SUM(CASE WHEN feesConsultationStatus = 'Pending Payment' THEN REPLACE(feesConsultation, 'RM ', '') + 0 ELSE 0 END) AS pendingPayment,
                SUM(CASE WHEN feesConsultationStatus = 'Done Payment' THEN REPLACE(feesConsultation, 'RM ', '') + 0 ELSE 0 END) AS donePayment,
                SUM(REPLACE(feesConsultation, 'RM ', '') + 0) AS totalPayment
            FROM consultation 
            WHERE specialistID = :specialistID 
              AND consultationStatus = 'Done' 
              AND DATE_FORMAT(consultationDateTime, '%Y-%m') = DATE_FORMAT(CURRENT_DATE, '%Y-%m')
        ";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':specialistID', $specialistID, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No data found for the given specialistID"]);
            exit;
        }

        // Return the result as JSON
        echo json_encode(["status" => "success", "data" => $result]);
        exit;

    } catch (PDOException $ex) {
        http_response_code(500);
        $errorDetails = [
            "status" => "error",
            "message" => "Failed to retrieve payment information: " . $ex->getMessage(),
            "trace" => $ex->getTraceAsString(),
        ];
        echo json_encode($errorDetails);
        error_log("Exception in GET request: " . $ex->getMessage() . "\nTrace: " . $ex->getTraceAsString());
    }
}
?>
