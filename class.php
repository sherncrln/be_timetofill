<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");


include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        $sql = "SELECT * FROM class";
        // $path = explode('/', $_SERVER['REQUEST_URI']);
         
        if(isset($_GET['class_id']) && is_numeric($_GET['class_id'])) {
            $sql .= " WHERE class_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_GET['class_id'], PDO::PARAM_INT);
            $stmt->execute();
            $class = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $class = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($class);
        break;
    case "PUT":
        $class = json_decode(file_get_contents('php://input'), true);
        if ($class && is_array($class)) {
            //Update the class data
            $sql = "UPDATE `class` SET `class` = :class, `category` = :category, `semester` = :semester, `valid_to` = :valid_to, `valid_from` = :valid_from, `variable_1` = :variable_1, `variable_2` = :variable_2, `variable_3` = :variable_3, `variable_4` = :variable_4, `variable_5` = :variable_5, `variable_6` = :variable_6 WHERE `class_id` = :id ;";
            $stmt = $conn->prepare($sql);
            $updated_at = date('Y-m-d');
            $stmt->bindParam(':id', $class['class_id'], PDO::PARAM_INT);
            $stmt->bindParam(':class', $class['class']);
            $stmt->bindParam(':category', $class['category']);
            $stmt->bindParam(':semester', $class['semester'], PDO::PARAM_INT);
            $stmt->bindParam(':valid_to', $class['valid_to']);
            $stmt->bindParam(':valid_from', $class['valid_from']);
            $stmt->bindParam(':variable_1', $class['variable_1']);
            $stmt->bindParam(':variable_2', $class['variable_2']);
            $stmt->bindParam(':variable_3', $class['variable_3']);
            $stmt->bindParam(':variable_4', $class['variable_4']);
            $stmt->bindParam(':variable_5', $class['variable_5']);
            $stmt->bindParam(':variable_6', $class['variable_6']);
            if($stmt->execute()){
                $response = ['status' => 1, 'message' => 'Record updated successfully.'];
            }else{
                $response = ['status' => 0, 'message' => 'Failed to update record.'];            
            }
            echo json_encode($response);
        } else {
            // $response = "hello this is else ";
            $response = ["error" => "Invalid request data"];
            echo json_encode($response);
        }
        break;
}