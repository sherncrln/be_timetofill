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
            $sql = "SELECT f.form_id, f.name_form, f.respondent, f.show_username, f.status_form, f.description, fd.question, fd.qtype FROM form f LEFT JOIN form_detail fd ON f.form_id = fd.form_id WHERE f.form_id = :id ;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_GET['form_id'], PDO::PARAM_INT);
            $stmt->execute();
            $form = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $sql = "SELECT * FROM form";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $form = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($form);
        break;

    case "POST":
        $form = json_decode(file_get_contents('php://input'), true);

        if ($form && is_array($form)) {
            $sql = "INSERT INTO `form` (`name_form`, `status_form`, `respondent`, `show_username`, `description`, `created_at`, `updated_at`) VALUES (:name_form, :status_form, :respondent, :show_username, :description, :created_at, :updated_at)";
            $stmt = $conn->prepare($sql);
            $updated_at = date('Y-m-d H:i:s', strtotime('+7 hour'));
            $created_at = date('Y-m-d H:i:s', strtotime('+7 hour'));
            $stmt->bindParam(':name_form', $form['name_form']);
            $stmt->bindParam(':status_form', $form['status_form']);
            $stmt->bindParam(':respondent', $form['respondent']);
            $stmt->bindParam(':show_username', $form['show_username']);
            $stmt->bindParam(':description', $form['description']);
            $stmt->bindParam(':created_at', $created_at);
            $stmt->bindParam(':updated_at', $updated_at);

            if ($stmt->execute()) {
                $form_id = $conn->lastInsertId();

                if (isset($form['question']) && is_array($form['question'])) {
                    $question = json_encode($form['question']);
                    $qtype = json_encode($form['qtype']);
                    $sql_detail = "INSERT INTO `form_detail` (`form_id`, `question`, `qtype`) VALUES (:form_id, :question, :qtype)";
                    $stmt_detail = $conn->prepare($sql_detail);
                    $stmt_detail->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt_detail->bindParam(':question', $question);
                    $stmt_detail->bindParam(':qtype', $qtype);

                    if ($stmt_detail->execute()) {
                        $response = ['status' => 1, 'message' => 'Record updated successfully.'];
                    } else {
                        $response = ['status' => 0, 'message' => 'Failed to update record.'];
                    }
                    echo json_encode($response);
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to update record.'];
                    echo json_encode($response);
                    exit;
                }
            } else {
                $response = ['status' => 0, 'message' => 'Failed to update record.'];
                echo json_encode($response);
            }
        } else {
            $response = ["error" => "Data permintaan tidak valid"];
            echo json_encode($response);
        }
        break;
        
    case "PUT":
        $form = json_decode(file_get_contents('php://input'), true);
        if (isset($form['form_id']) && is_numeric($form['form_id'])) {
            $form_id = $form['form_id'];

            $sql = "UPDATE `form` SET `name_form` = :name_form, `status_form` = :status_form, `respondent` = :respondent, `show_username` = :show_username, `description` = :description, `updated_at` = :updated_at WHERE `form_id` = :form_id";
            $stmt = $conn->prepare($sql);
            $updated_at = date('Y-m-d H:i:s', strtotime('+7 hour'));
            $stmt->bindParam(':name_form', $form['name_form']);
            $stmt->bindParam(':status_form', $form['status_form']);
            $stmt->bindParam(':respondent', $form['respondent']);
            $stmt->bindParam(':show_username', $form['show_username']);
            $stmt->bindParam(':description', $form['description']);
            $stmt->bindParam(':updated_at', $updated_at);
            $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                if (isset($form['question']) && is_array($form['question'])) {
                    $question = json_encode($form['question']);
                    $qtype = json_encode($form['qtype']);
                    $sql_detail = "UPDATE `form_detail` SET `question` = :question, `qtype` = :qtype WHERE `form_id` = :form_id";
                    $stmt_detail = $conn->prepare($sql_detail);
                    $stmt_detail->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt_detail->bindParam(':question', $question);
                    $stmt_detail->bindParam(':qtype', $qtype);

                    if ($stmt_detail->execute()) {
                        $response = ['status' => 1, 'message' => 'Record updated successfully.'];
                    } else {
                        $response = ['status' => 0, 'message' => 'Failed to update record.'];
                    }
                    echo json_encode($response);
                } else {
                    $response = ['status' => 1, 'message' => 'Record updated successfully.'];
                    echo json_encode($response);
                }
            } else {
                $response = ['status' => 0, 'message' => 'Failed to update record.'];
                echo json_encode($response);
            }
        } else {
            $response = ["error" => "ID formulir tidak valid"];
            echo json_encode($response);
        }
        break;

    case "DELETE":
        if (isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
            $form_id = $_GET['form_id'];
    
            $sql_detail = "DELETE FROM form_detail WHERE form_id = :form_id";
            $stmt_detail = $conn->prepare($sql_detail);
            $stmt_detail->bindParam(':form_id', $form_id, PDO::PARAM_INT);
            
            if ($stmt_detail->execute()) {
                $sql = "DELETE FROM form WHERE form_id = :form_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
    
                if ($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Form deleted successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to delete form.'];
                }
            } else {
                $response = ['status' => 0, 'message' => 'Failed to delete form details.'];
            }
    
            echo json_encode($response);
        } else {
            $response = ["error" => "Invalid form ID"];
            echo json_encode($response);
        }
        break;
}
?>
