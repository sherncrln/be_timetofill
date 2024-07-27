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
        if(isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
            $sql = "SELECT respondent, updated_at FROM form WHERE form_id = :id ;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_GET['form_id'], PDO::PARAM_INT);
            $stmt->execute();
            $form = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($form['respondent'] == "Mahasiswa") { 
                $sql_mahasiswa = "SELECT GROUP_CONCAT(username SEPARATOR ',') as parameter FROM user WHERE category = 'Dosen' AND status_user = 'Active';";
                $stmt_mahasiswa = $conn->prepare($sql_mahasiswa);
                $stmt_mahasiswa->execute();
                $user_dosen = $stmt_mahasiswa->fetch(PDO::FETCH_ASSOC);

                echo json_encode($user_dosen);

            }elseif ($form['respondent'] == "Dosen") {
                $sql_dosen = "SELECT GROUP_CONCAT(class SEPARATOR ',') as parameter FROM class WHERE category = 'Mahasiswa' AND valid_from <= :updated_at and valid_to >= :updated_at ;";
                $stmt_dosen = $conn->prepare($sql_dosen);
                $stmt_dosen->bindParam(':updated_at', $form['updated_at']);
                $stmt_dosen->execute();
                $class = $stmt_dosen->fetch(PDO::FETCH_ASSOC);

                echo json_encode($class);
            }
        } else {
            $response = ['status' => 0, 'message' => 'Failed to process data.'];
            echo json_encode($response);
        }
        break;
        
    case "PUT":
        // $user = json_decode(file_get_contents('php://input'), true);
        // if ($user && is_array($user)) {
        //     //Update the user data
        //     $sql = "UPDATE `user` SET `name` = :name, `class` = :class , `category` = :category, `status_user` = :status_user, `email` = :email, `updated_at` = :updated_at WHERE `user_id` = :id ;";
        //     $stmt = $conn->prepare($sql);
        //     $updated_at = date('Y-m-d');
        //     $stmt->bindParam(':id', $user['user_id'], PDO::PARAM_INT);
        //     $stmt->bindParam(':name', $user['name']);
        //     $stmt->bindParam(':class', $user['class']);
        //     $stmt->bindParam(':category', $user['category']);
        //     $stmt->bindParam(':email', $user['email']);
        //     $stmt->bindParam(':status_user', $user['status_user']);
        //     $stmt->bindParam(':updated_at', $updated_at);
        //     if($stmt->execute()){
        //         $response = ['status' => 1, 'message' => 'Record updated successfully.'];
        //     }else{
        //         $response = ['status' => 0, 'message' => 'Failed to update record.'];            
        //     }
        //     echo json_encode($response);
        // } else {
        //     // $response = "hello this is else ";
        //     $response = ["error" => "Invalid request data"];
        //     echo json_encode($response);
        // }
        break;
}