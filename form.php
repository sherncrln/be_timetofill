<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        if(isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
            $sql = "SELECT f.form_id, f.name_form, f.respondent, f.show_username, f.status_form, f.description, fd.question, fd.qtype, fd.section, fd.section_rule FROM form f LEFT JOIN form_detail fd ON f.form_id = fd.form_id WHERE f.form_id = :id ;";
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
        if (isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
            $form_id = $_GET['form_id'];
            
            $sql = "SELECT * FROM form WHERE form_id = :form_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':form_id', $form_id, PDO::PARAM_INT);
            $stmt->execute();
            $form = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($form) {
                $sql_insert = "INSERT INTO `form` (`name_form`, `status_form`, `respondent`, `show_username`, `description`, `created_at`, `updated_at`) VALUES (:name_form, :status_form, :respondent, :show_username, :description, :created_at, :updated_at)";
                $stmt_insert = $conn->prepare($sql_insert);
                $updated_at = date('Y-m-d H:i:s', strtotime('+7 hour'));
                $created_at = date('Y-m-d H:i:s', strtotime('+7 hour'));
                $stmt_insert->bindParam(':name_form', $form['name_form']);
                $stmt_insert->bindParam(':status_form', $form['status_form']);
                $stmt_insert->bindParam(':respondent', $form['respondent']);
                $stmt_insert->bindParam(':show_username', $form['show_username']);
                $stmt_insert->bindParam(':description', $form['description']);
                $stmt_insert->bindParam(':created_at', $created_at);
                $stmt_insert->bindParam(':updated_at', $updated_at);

                if ($stmt_insert->execute()) {
                    $new_form_id = $conn->lastInsertId();
                    $sql_detail = "SELECT * FROM form_detail WHERE form_id = :form_id";
                    $stmt_detail = $conn->prepare($sql_detail);
                    $stmt_detail->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt_detail->execute();
                    $form_details = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($form_details as $detail) {
                        $sql_insert_detail = "INSERT INTO form_detail (form_id, question, qtype, section, section_rule) VALUES (:form_id, :question, :qtype, :section, :section_rule)";
                        $stmt_insert_detail = $conn->prepare($sql_insert_detail);
                        $stmt_insert_detail->bindParam(':form_id', $new_form_id, PDO::PARAM_INT);
                        $stmt_insert_detail->bindParam(':question', $detail['question']);
                        $stmt_insert_detail->bindParam(':qtype', $detail['qtype']);
                        $stmt_insert_detail->bindParam(':section', $detail['section']);
                        $stmt_insert_detail->bindParam(':section_rule', $detail['section_rule']);
                        $stmt_insert_detail->execute();
                    }

                    $response = ['status' => 1, 'message' => 'Form duplicated successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to duplicate form.'];
                }
            } else {
                $response = ['status' => 0, 'message' => 'Form not found.'];
            }

            echo json_encode($response);
        } else {
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
                        $section = json_encode($form['section']);
                        $section_rule = json_encode($form['section_rule']);
                        $sql_detail = "INSERT INTO `form_detail` (`form_id`, `question`, `qtype`, section, section_rule) VALUES (:form_id, :question, :qtype, :section, :section_rule)";
                        $stmt_detail = $conn->prepare($sql_detail);
                        $stmt_detail->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                        $stmt_detail->bindParam(':question', $question);
                        $stmt_detail->bindParam(':qtype', $qtype);
                        $stmt_detail->bindParam(':section', $section);
                        $stmt_detail->bindParam(':section_rule', $section_rule);

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
                $response = ["error" => "Data is not valid"];
                echo json_encode($response);
            }
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
                    $section = json_encode($form['section']);
                    $section_rule = json_encode($form['section_rule']);
                    $sql_detail = "UPDATE `form_detail` SET `question` = :question, `qtype` = :qtype, `section` = :section, `section_rule` = :section_rule WHERE `form_id` = :form_id";
                    $stmt_detail = $conn->prepare($sql_detail);
                    $stmt_detail->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt_detail->bindParam(':question', $question);
                    $stmt_detail->bindParam(':qtype', $qtype);
                    $stmt_detail->bindParam(':section', $section);
                    $stmt_detail->bindParam(':section_rule', $section_rule);

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
