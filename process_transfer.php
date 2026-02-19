<?php
session_start();
include 'config.php';
include 'mailer.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

    $from_user     = $_SESSION['user_id'];
    $to_user       = $_POST['to_user'];
    $document_type = $_POST['doc_type']; // Matches the 'name' attribute in your form
    $title         = $_POST['title'];
    $description   = $_POST['description'];
    $remarks       = $_POST['remarks'];
    
    $doc_number    = "PRC-" . date("Ymd") . "-" . strtoupper(substr(uniqid(), -4));

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $original_name = basename($_FILES["doc_file"]["name"]);
    $file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $original_name);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["doc_file"]["tmp_name"], $target_file)) {
        
        $conn->begin_transaction();
        try {
            // 1. Insert into documents
            $sql_doc = "INSERT INTO documents (document_number, document_type, title, description, file_path, uploaded_by, current_status, current_holder) 
                        VALUES (?, ?, ?, ?, ?, ?, 'Submitted', ?)";
            $stmt1 = $conn->prepare($sql_doc);
            $stmt1->bind_param("ssssssi", $doc_number, $document_type, $title, $description, $target_file, $from_user, $to_user);
            $stmt1->execute();
            $new_doc_id = $conn->insert_id;

            // 2. Insert into document_routes
            $sql_route = "INSERT INTO document_routes (document_id, from_user, to_user, remarks, status) VALUES (?, ?, ?, ?, 'Pending')";
            $stmt2 = $conn->prepare($sql_route);
            $stmt2->bind_param("iiis", $new_doc_id, $from_user, $to_user, $remarks);
            $stmt2->execute();

            // 3. UPDATED: Insert into document_logs with r_id
            $log_msg = "Document uploaded and transferred.";
            $sql_log = "INSERT INTO document_logs (document_id, user_id, r_id, action) VALUES (?, ?, ?, ?)";
            $stmt3 = $conn->prepare($sql_log);
            $stmt3->bind_param("iiis", $new_doc_id, $from_user, $to_user, $log_msg);
            $stmt3->execute();

            // 4. Get Recipient Info for Email
            $stmt_user = $conn->prepare("SELECT email, first_name, last_name FROM users WHERE id = ?");
            $stmt_user->bind_param("i", $to_user);
            $stmt_user->execute();
            $recipient = $stmt_user->get_result()->fetch_assoc();
            
            if ($recipient) {
                $fullName = $recipient['first_name'] . " " . $recipient['last_name'];
                sendNotification($recipient['email'], $fullName, $doc_number, $title, $remarks);
            }

            $conn->commit();
            header("Location: index.php?status=success&recipient=" . urlencode($recipient['first_name']));

        } catch (Exception $e) {
            $conn->rollback();
            if (file_exists($target_file)) unlink($target_file);
            die("Error: " . $e->getMessage());
        }
    }
}