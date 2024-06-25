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
http_response_code(404);
$response = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['patientID']) && isset($_GET['attribute'])) {
    try {
        $patientID = $_GET['patientID'];
        $attribute = $_GET['attribute'];

        // Fetch the specific attribute for the given patientID, limit 1
        $stmt = $db->prepare("SELECT $attribute FROM vital_info WHERE patientID = :patientID ORDER BY latestDate DESC LIMIT 1");
        $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response = $result[$attribute]; // Return the specific attribute value
            http_response_code(200);
        } else {
            http_response_code(404);
            $response['error'] = "No data found for patientID: $patientID and attribute: $attribute";
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response['error'] = "Error occurred: " . $ee->getMessage();
    }
} else {
    http_response_code(400);
    $response['error'] = "Invalid request parameters";
}

echo json_encode($response);
exit();
?>
