<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST");
    header("Access-Control-Allow-Headers: Content-Type");


    $conn = new mysqli("localhost", "root", "", "timetofill");
    if(mysqli_connect_error()){
        echo mysqli_connect_error();
        exit;
    }
    else{
        $eData = file_get_contents("php://input");
        $dData = json_decode($eData, true);

        $user = $dData['user'] ?? '';
        $pass = $dData['pass'] ?? '';
        $email = $dData['email'] ?? '';
        $result = "";
        
        if($user !== "" and $pass !== ""){
            $sql = "SELECT * FROM user WHERE username='$user';";
            // $sql = "SELECT * FROM user WHERE username='$user';";
            $res = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($res) != 0){
                $row = mysqli_fetch_array($res);

                if($pass != $row['password']){
                    $result = "Invalid Password!";
                }
                else{
                    $result = "Sign up successed!";
                }
            }
            else{
                $result = "Invalid Username!";
            }
        }
        else{
            $result = "";
        }

        $response[] = array("result" => $result, "user" => $row);
        echo json_encode($response);
    }
?>