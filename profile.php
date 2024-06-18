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

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$users = "";

switch($method) {
    case "GET":
        if ($user_id !== null) {
            $sql = "SELECT * FROM user WHERE user_id = :id;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($users);
        } else {
            echo json_encode(["error" => "User ID not provided"]);
        }
        break;

    case "PUT":
        $user = json_decode(file_get_contents('php://input'), true);
        if ($user && is_array($user)) {
            //Update the user data
            $sql = "UPDATE `user` SET `name` = :name, `class` = :class , `category` = :category, `password` = :password, `email` = :email, `updated_at` = :updated_at WHERE `user_id` = :id ;";
            $stmt = $conn->prepare($sql);
            $updated_at = date('Y-m-d');
            $stmt->bindParam(':id', $user['user_id'], PDO::PARAM_INT);
            $stmt->bindParam(':name', $user['name']);
            $stmt->bindParam(':class', $user['class']);
            $stmt->bindParam(':category', $user['category']);
            $stmt->bindParam(':email', $user['email']);
            $stmt->bindParam(':password', $user['password']);
            $stmt->bindParam(':updated_at', $updated_at);
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