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
            $sql = "SELECT * FROM form";
            // $path = explode('/', $_SERVER['REQUEST_URI']);
            
            if(isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
                $sql .= " WHERE form_id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $_GET['form_id'], PDO::PARAM_INT);
                $stmt->execute();
                $form = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
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
                        foreach ($form['question'] as $key => $question) {
                            
                            $sub_quest = json_encode($question['sub_quest']);
                            $sql_question = "INSERT INTO `form_question` (`form_id`, `header`, `sub_quest`) VALUES (:form_id, :header, :sub_quest )";
                            $stmt_question = $conn->prepare($sql_question);
                            $stmt_question->bindParam(':form_id', $form_id, PDO::PARAM_INT);
                            $stmt_question->bindParam(':header', $question['header']);
                            $stmt_question->bindParam(':sub_quest', $sub_quest);
    
                            if ($stmt_question->execute()) {
                                $question_id = $conn->lastInsertId();
                                $qtype = $form['qtype'][$key];
                                $detail = json_encode($qtype['detail']);
    
                                $sql_qtype = "INSERT INTO `form_qtype` (`question_id`, `type`, `detail`) VALUES (:question_id, :type, :detail)";
                                $stmt_qtype = $conn->prepare($sql_qtype);
                                $stmt_qtype->bindParam(':question_id', $question_id, PDO::PARAM_INT);
                                $stmt_qtype->bindParam(':type', $qtype['type']);
                                $stmt_qtype->bindParam(':detail', $detail);
    
                                if (!$stmt_qtype->execute()) {
                                    $response = ['status' => 0, 'message' => 'Gagal menyimpan detail form.'];
                                    echo json_encode($response);
                                    exit;
                                }
                            } else {
                                $response = ['status' => 0, 'message' => 'Gagal menyimpan pertanyaan.'];
                                echo json_encode($response);
                                exit;
                            }
                        }
                    }
    
                    $response = ['status' => 1, 'message' => 'Rekord berhasil disimpan.'];
                    echo json_encode($response);
                } else {
                    $response = ['status' => 0, 'message' => 'Gagal menyimpan rekord.'];
                    echo json_encode($response);
                }
            } else {
                $response = ["error" => "Data permintaan tidak valid"];
                echo json_encode($response);
            }
            break;
        
            
    }