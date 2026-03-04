<?php
session_start();
require_once "../config/config.php"; 
require_once "../mailer.php";         

if (isset($_POST['upload_document'])) {
    // 1. Generate IDs and Data
    $doc_id = generate_uuid(); 
    $uploader_id = $_SESSION['user_id'];
    $current_dept = $_SESSION['dept_id'];
    
    $target_dept_id = $_POST['target_dept_id'];
    $doc_type_id = $_POST['doc_type_id'];
    $doc_name = $_POST['document_name']; // Replaced applicant_name
    $remarks = $_POST['remarks'];
    $ref_no = "PRC-REF-" . date("Ymd") . "-" . rand(100, 999);

    // Default Status (e.g., 1 = Forwarded)
    $status_id = 1; 

    // 2. Handle File Upload
    $target_dir = "../public/uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_ext = pathinfo($_FILES["doc_file"]["name"], PATHINFO_EXTENSION);
    $file_name = $doc_id . "." . $file_ext;
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["doc_file"]["tmp_name"], $target_file)) {
        
        $conn->begin_transaction();
        try {
            // 3. Insert into `document` table (Mapped document_name to applicant_name column)
            $sql_doc = "INSERT INTO document (doc_id, current_user_id, current_dept_id, doc_status_id, doc_type_id, ref_no, uploaded_by, applicant_name, file_path) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = $conn->prepare($sql_doc);
            // Updated bind_param: removed one 's' and the license variable
            $stmt1->bind_param("ssiiiisss", $doc_id, $uploader_id, $target_dept_id, $status_id, $doc_type_id, $ref_no, $uploader_id, $doc_name, $target_file);
            $stmt1->execute();

            // 4. Insert into `transaction_logs` table
            $sql_log = "INSERT INTO transaction_logs (doc_id, doc_status_id, current_dept_id, target_dept_id, processed_by, remarks) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql_log);
            $stmt2->bind_param("siiiis", $doc_id, $status_id, $current_dept, $target_dept_id, $uploader_id, $remarks);
            $stmt2->execute();

            $conn->commit();
            header("Location: ../public/index.php?msg=Document Uploaded. Ref: $ref_no");

        } catch (Exception $e) {
            $conn->rollback();
            if(file_exists($target_file)) unlink($target_file); 
            header("Location: ../public/transfer.php?error=" . $e->getMessage());
        }
    } else {
        header("Location: ../public/transfer.php?error=File upload failed.");
    }
}