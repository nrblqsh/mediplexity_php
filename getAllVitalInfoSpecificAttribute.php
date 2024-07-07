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

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['patientID']) && isset($_GET['attribute'])) {
    try {
        $patientID = $_GET['patientID'];
        $attribute = $_GET['attribute'];

        // Fetch the specific attribute for the given patientID, get the latest entry for each date
        $stmt = $db->prepare("
            SELECT vi.$attribute, vi.latestDate
            FROM vital_info vi
            
            WHERE vi.patientID = :patientID
               GROUP BY DATE(latestDate)
        ORDER BY vi.latestDate ASC;
        "
    
     
        );
        $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $response = $result;
            http_response_code(200);
        } else {
            http_response_code(404);
            $response['error'] = "No data found for patientID: $patientID and attribute: $attribute";
        }
    } catch (Exception $e) {
        http_response_code(500);
        $response['error'] = "Error occurred: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    $response['error'] = "Invalid request parameters";
}

echo json_encode($response);
exit();
?>
