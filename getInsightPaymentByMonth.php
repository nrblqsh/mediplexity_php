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
        // Query to get monthly donePayment and totalSales
        $query = "
            SELECT 
                DATE_FORMAT(consultationDateTime, '%Y-%m') AS month,
                SUM(REPLACE(feesConsultation, 'RM ', '') + 0) AS donePayment
            FROM consultation 
            WHERE specialistID = :specialistID 
              AND consultationStatus = 'Done' 
              AND feesConsultationStatus = 'Done Payment'
            GROUP BY DATE_FORMAT(consultationDateTime, '%Y-%m')
            ORDER BY DATE_FORMAT(consultationDateTime, '%Y-%m') DESC
        ";

        // Query to get totalSales (sum of donePayment for all months)
        $queryTotalSales = "
            SELECT 
                SUM(REPLACE(feesConsultation, 'RM ', '') + 0) AS totalSales
            FROM consultation 
            WHERE specialistID = :specialistID 
              AND consultationStatus = 'Done' 
              AND feesConsultationStatus = 'Done Payment'
        ";

        // Prepare and execute the monthly donePayment query
        $stmt = $db->prepare($query);
        $stmt->bindParam(':specialistID', $specialistID, PDO::PARAM_INT);
        $stmt->execute();
        $monthlyResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare and execute the totalSales query
        $stmtTotalSales = $db->prepare($queryTotalSales);
        $stmtTotalSales->bindParam(':specialistID', $specialistID, PDO::PARAM_INT);
        $stmtTotalSales->execute();
        $totalSalesResult = $stmtTotalSales->fetch(PDO::FETCH_ASSOC);

        if (!$monthlyResult) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "No data found for the given specialistID"]);
            exit;
        }

        // Combine results into a single response
        $response = [
            "status" => "success",
            "data" => [
                "monthlyPayments" => $monthlyResult,
                "totalSales" => $totalSalesResult['totalSales'] ?? 0 // Ensure totalSales is initialized even if no data found
            ]
        ];

        // Return the result as JSON
        echo json_encode($response);
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
