<?php
// Enable error reporting
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

$response = new stdClass();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Create a MySQLi connection
        $conn = new mysqli($hostname, $username, $password, $database);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve JSON data from POST request
        $post_data = json_decode(file_get_contents("php://input"), true);

        // Check if patientID is provided in URL
        $patientID = isset($_GET['patientID']) ? $_GET['patientID'] : null;

        if ($patientID !== null) {
            // Initialize arrays for fields and parameters
            $fields = array();
            $params = array();
            $types = "";

            // Check and build update query dynamically
            if (isset($post_data['patientName'])) {
                $fields[] = "patientName = ?";
                $params[] = $post_data['patientName'];
                $types .= "s";
            }

            if (isset($post_data['icNumber'])) {
                $fields[] = "icNumber = ?";
                $params[] = $post_data['icNumber'];
                $types .= "s";
            }

            if (isset($post_data['birthDate'])) {
                $fields[] = "birthDate = ?";
                $params[] = $post_data['birthDate'];
                $types .= "s";
            }
            if (isset($post_data['gender'])) {
                $fields[] = "gender = ?";
                $params[] = $post_data['gender'];
                $types .= "s";
            }
            if (isset($post_data['phone'])) {
                $fields[] = "phone = ?";
                $params[] = $post_data['phone'];
                $types .= "s";
            }
            if (isset($post_data['password'])) {
                $fields[] = "password = ?";
                $params[] = $post_data['password'];
                $types .= "s";
            }

            // Prepare the SQL update statement dynamically
            if (!empty($fields)) {
                $sql = "UPDATE patient SET " . implode(", ", $fields) . " WHERE patientID = ?";
                $stmtUpdate = $conn->prepare($sql);

                // Add patientID to parameters
                $params[] = $patientID;
                $types .= "i"; // Assuming patientID is an integer

                // Bind parameters
                $bind_params = array_merge(array($types), $params);
                // Use bind_param directly
                $stmtUpdate->bind_param(...$bind_params);

                // Execute the statement
                if ($stmtUpdate->execute()) {
                    $response->success = true;
                } else {
                    $response->success = false;
                    $response->error = "Error updating profile: " . $stmtUpdate->error;
                }

                $stmtUpdate->close();
            } else {
                $response->success = true; // No fields to update
            }
        } else {
            http_response_code(400);
            $response->error = "Patient ID not provided";
        }

        // Close the connection
        $conn->close();

    } catch (Exception $e) {
        http_response_code(500);
        $response->error = "Error occurred: " . $e->getMessage();
    }

    // Echo the JSON-encoded response
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

// Function to reference values for bind_param
function refValues($arr){
    $refs = array();
    foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
    return $refs;
}
?>
