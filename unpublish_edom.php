<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

$data = json_decode(file_get_contents("php://input"));

if ($data && isset($data->form_id)) {
    $form_id = $data->form_id;
    $status_edom = $data->status_edom;

    $sql = "UPDATE edom_result SET status_edom = ? WHERE form_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status_edom, $form_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid input"]);
}

$conn->close();
?>
