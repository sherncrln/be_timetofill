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

        $email = $dData['email'] ?? '';
        $pass = $dData['pass'] ?? '';
        $result = "";
        
        if($email !== "" and $pass !== ""){
            $sql = "SELECT * FROM user WHERE email='$email' and status_user='Active';";
            $res = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($res) != 0){
                $row = mysqli_fetch_array($res);

                if($pass != $row['password']){
                    $result = "Invalid Password!";
                }
                else{
                    $result = "Log in successed!";
                }
            }
            else{
                $result = "Invalid Email!";
            }
        }
        else{
            $result = "User is Not Active. Please contact administrator!";
        }

        $response[] = array("result" => $result, "email" => $row);
        echo json_encode($response);
    }
?>