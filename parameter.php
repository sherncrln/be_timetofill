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
        if(isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            // First, get the class of the user
            $sql = "SELECT u.category, u.username FROM user u WHERE u.user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $_GET['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if($user['category'] == 'Mahasiswa') {
                $sql = "SELECT c.variable_1, c.variable_2, c.variable_3, c.variable_4, c.variable_5, c.variable_6 
                        FROM user u 
                        LEFT JOIN class c ON u.class = c.class 
                        WHERE u.username = :variable 
                        AND (c.valid_from <= CURRENT_DATE() AND c.valid_to >= CURRENT_DATE())";
            } elseif($user['category'] == 'Dosen') {
                $sql = "SELECT GROUP_CONCAT(c.class SEPARATOR ', ') AS classes
                        FROM class c
                        WHERE (c.variable_1 = :variable OR c.variable_2 = :variable OR c.variable_3 = :variable OR c.variable_4 = :variable OR c.variable_5 = :variable OR c.variable_6 = :variable) AND (c.valid_from <= CURRENT_DATE() AND c.valid_to >= CURRENT_DATE())";
            } else {
                $param = "Invalid class";
                echo json_encode($param);
                break;
            }

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':variable', $user['username'], PDO::PARAM_INT);
            $stmt->execute();
            $param = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($param);

        } else {
            if(isset($_GET['p']) && is_numeric($_GET['p'])) {
                $sql = "SELECT name FROM user WHERE username = :p";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':p', $_GET['p'], PDO::PARAM_INT);
                $stmt->execute();
                $pname = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($pname) {
                    echo json_encode($pname);
                } else {
                    echo json_encode(['name' => '']);
                }

            }else{
                $param = "Invalid user_id";
                echo json_encode($param);
            }
        }
        break;
}