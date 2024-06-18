<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT");
    header("Access-Control-Allow-Headers: Content-Type");


    $conn = new mysqli("localhost", "root", "", "timetofill");
    if(mysqli_connect_error()){
        echo mysqli_connect_error();
        exit;
    }
    else{
        $user = json_decode(file_get_contents('php://input'), true);
        
        if($user && is_array($user) ){
            $updated_at = date('Y-m-d');
            $email = $user['email'];
            $password = $user['password'];
            $username = $user['username'];

            $sql_user = "SELECT * FROM user WHERE username='$username';";
            $res = mysqli_query($conn, $sql_user);
            
            if(mysqli_num_rows($res) != 0){
                $sql = "UPDATE `user` SET `email` =?, `password` =?, `updated_at` =? WHERE `username` =? ;";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssss', $email, $password, $updated_at, $username);
                if($stmt->execute()){
                    $response = ['status' => 1, 'message' => 'Account has been created.' ];
                }else{
                    $response = ['status' => 0, 'message' => 'Failed to create account.'];            
                }                
            }else{
                $response = ['status' => 0, 'message' => 'User not found!'];
            }

        }
        else{
            $response = ['status' => 0, 'message' => 'Error Data Input!'];
        }
        echo json_encode($response);
    }
?>