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
    echo json_encode(["error" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

http_response_code(404);
$response = new stdClass();

$jsonbody = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $db->prepare("INSERT INTO consultation (`consultationID`, `patientID`, `consultationDateTime`, `specialistID`, `consultationStatus`)
                              VALUES (:consultationID, :patientID, :consultationDateTime, :specialistID, :consultationStatus)");
        $stmt->execute(array(
            ':consultationID' => $jsonbody->consultationID,
            ':patientID' => $jsonbody->patientID,
            ':consultationDateTime' => $jsonbody->consultationDateTime,
            ':specialistID' => $jsonbody->specialistID,
            ':consultationStatus' => $jsonbody->consultationStatus
        ));
        http_response_code(200);
        $response->message = "Consultation added successfully.";
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['patientID'])) {
    try {
        $patientID = $_GET['patientID'];
        $stmt = $db->prepare("SELECT c.*, s.specialistName
                              FROM consultation c
                              JOIN specialist s ON c.specialistID = s.specialistID
                              WHERE c.patientID = :patientID AND c.consultationDateTime >= CURDATE()
                              ORDER BY c.consultationDateTime ASC");
        $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);
        $stmt->execute();

        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $stmt = $db->prepare("SELECT * FROM consultation WHERE consultationDateTime >= CURDATE()");
        $stmt->execute();
        $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
        http_response_code(200);
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred: " . $ee->getMessage();
    }
}

echo json_encode($response);
exit();
?>
