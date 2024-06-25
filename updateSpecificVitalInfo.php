<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Initial response code
// Response code will be changed if the request goes into any of the process
http_response_code(404);
$response = new stdClass();

$jsonbody = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET['patientID'])) {
        try {
            $patientID = $_GET['patientID'];
            $attribute = $jsonbody->attribute; // e.g., 'height'
            $value = $jsonbody->value; // e.g., 170
            $latestDate = date('Y-m-d H:i:s'); // Set the latest date to now

            // Fetch the latest record for the given patientID
            $stmt = $db->prepare("SELECT * FROM vital_info WHERE patientID = :patientID ORDER BY latestDate DESC LIMIT 1");
            $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
            $stmt->execute();
            $latestRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            // Initialize new record with existing values or default values
            $newRecord = [
                'patientID' => $patientID,
                'weight' => isset($latestRecord['weight']) ? $latestRecord['weight'] : NULL,
                'height' => isset($latestRecord['height']) ? $latestRecord['height'] : NULL,
                'bmi' => isset($latestRecord['bmi']) ? $latestRecord['bmi'] : NULL,
                'waistCircumference' => isset($latestRecord['waistCircumference']) ? $latestRecord['waistCircumference'] : NULL,
                'bloodPressure' => isset($latestRecord['bloodPressure']) ? $latestRecord['bloodPressure'] : NULL,
                'bloodGlucose' => isset($latestRecord['bloodGlucose']) ? $latestRecord['bloodGlucose'] : NULL,
                'heartRate' => isset($latestRecord['heartRate']) ? $latestRecord['heartRate'] : NULL,
                'latestDate' => $latestDate,
            ];

            // Update the specific attribute
            $newRecord[$attribute] = $value;

            // Insert the new record
            $stmt = $db->prepare("INSERT INTO vital_info (patientID, weight, height, bmi, waistCircumference, bloodPressure, bloodGlucose, heartRate, latestDate) 
                                  VALUES (:patientID, :weight, :height, :bmi, :waistCircumference, :bloodPressure, :bloodGlucose, :heartRate, :latestDate)");
            $stmt->bindParam(':patientID', $newRecord['patientID'], PDO::PARAM_INT);
            $stmt->bindParam(':weight', $newRecord['weight'], PDO::PARAM_INT);
            $stmt->bindParam(':height', $newRecord['height'], PDO::PARAM_INT);
            $stmt->bindParam(':bmi', $newRecord['bmi'], PDO::PARAM_INT);
            $stmt->bindParam(':waistCircumference', $newRecord['waistCircumference'], PDO::PARAM_INT);
            $stmt->bindParam(':bloodPressure', $newRecord['bloodPressure'], PDO::PARAM_INT);
            $stmt->bindParam(':bloodGlucose', $newRecord['bloodGlucose'], PDO::PARAM_INT);
            $stmt->bindParam(':heartRate', $newRecord['heartRate'], PDO::PARAM_INT);
            $stmt->bindParam(':latestDate', $newRecord['latestDate'], PDO::PARAM_STR);
            
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                http_response_code(200);
                $response->message = "Record inserted successfully";
            } else {
                http_response_code(404);
                $response->message = "Failed to insert record";
            }
        } catch (Exception $ee) {
            http_response_code(500);
            $response->error = "Error occurred: " . $ee->getMessage();
        }
    } else {
        http_response_code(400);
        $response->message = "Missing patientID parameter";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['patientID'])) {
    try {
        $patientID = $_GET['patientID'];
        $stmt = $db->prepare("SELECT * FROM vital_info WHERE patientID = :patientID ORDER BY latestDate DESC LIMIT 1");
        $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
        $stmt->execute();
        $response = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(200);
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
}

echo json_encode($response);
exit();
?>
