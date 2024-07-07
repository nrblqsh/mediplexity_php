<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    http_response_code(404); // Set initial response code

    $response = new stdClass();
    $jsonbody = json_decode(file_get_contents('php://input'));

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_GET['consultationID'])) {
            $consultationID = $_GET['consultationID'];
            $consultationTreatment = $jsonbody->consultationTreatment;
            $consultationSymptom = $jsonbody->consultationSymptom;
            $feesConsultation = $jsonbody->feesConsultation;
            $consultationStatus = $jsonbody->consultationStatus;

            try {
                // Update consultation information
                $stmt = $db->prepare("UPDATE consultation SET consultationTreatment = :consultationTreatment, consultationSymptom = :consultationSymptom, feesConsultation = :feesConsultation, consultationStatus = :consultationStatus WHERE consultationID = :consultationID");
                $stmt->execute(array(
                    ':consultationID' => $consultationID,
                    ':consultationTreatment' => $consultationTreatment,
                    ':consultationSymptom' => $consultationSymptom,
                    ':feesConsultation' => $feesConsultation,
                    ':consultationStatus' => $consultationStatus
                ));

                http_response_code(200); // OK
                $response->message = "Consultation information updated successfully.";
            } catch (Exception $e) {
                http_response_code(500);
                $response->error = "Error occurred: " . $e->getMessage();
            }
        } else {
            http_response_code(400); // Bad request
            $response->error = "Missing consultationID in URL.";
        }
    } else {
        http_response_code(405); // Method not allowed
        $response->error = "Invalid request method.";
    }

    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => "Database connection failed: " . $e->getMessage()));
}
