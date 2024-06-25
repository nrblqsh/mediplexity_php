<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

$db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
http_response_code(404); // Set initial response code

$response = new stdClass();
$jsonbody = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $consultationID = isset($_GET['consultationID']) ? $_GET['consultationID'] : null;

        if ($consultationID !== null) {
            $stmt = $db->prepare("SELECT consultation.consultationID, consultation.consultationDateTime, specialist.specialistName,
             consultation.consultationSymptom, consultation.consultationTreatment, consultation.feesConsultation, consultation.feesConsultationStatus
              FROM (consultation INNER JOIN specialist ON consultation.specialistID = specialist.specialistID) 
              WHERE consultation.consultationID = :consultationID ");
            $stmt->bindParam(':consultationID', $consultationID, PDO::PARAM_INT);

            // Check if execution is successful before fetching results
            if ($stmt->execute()) {
                $historyConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->data = $historyConsultations;
                $response->success = true;
                http_response_code(200); // Set success response code
            } else {
                $response->success = false;
                $response->error = "Error retrieving history consultations: " . $stmt->errorInfo()[2];
            }
        } else {
            $response->error = "consultationID is missing in the request.";
            $response->success = false;
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred " . $ee->getMessage();
    }
}

echo json_encode($response);
exit();
?>
