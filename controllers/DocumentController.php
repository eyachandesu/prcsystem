<?php
session_start();
require_once "../config/config.php"; // Adjust path if needed
require_once "../mailer.php";         // For email notifications

if (isset($_POST['upload_document'])) {
    // 1. Generate IDs and Data
    $doc_id = generate_uuid(); // Function from your config
    $uploader_id = $_SESSION['user_id'];
    $current_dept = $_SESSION['dept_id'];
    
    $target_dept_id = $_POST['target_dept_id'];
    $doc_type_id = $_POST['doc_type_id'];
    $applicant = $_POST['applicant_name'];
    $license = $_POST['license_no'];
    $remarks = $_POST['remarks'];
    $ref_no = "PRC-REF-" . date("Ymd") . "-" . rand(100, 999);

    // Default Status: Assuming '1' is 'Forwarded' or 'Pending' in your doc_status table
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
            // 3. Insert into `document` table
            $sql_doc = "INSERT INTO document (doc_id, current_user_id, current_dept_id, doc_status_id, doc_type_id, ref_no, uploaded_by, applicant_name, applicant_license_no, file_path) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt1 = $conn->prepare($sql_doc);
            $stmt1->bind_param("ssiiiissss", $doc_id, $uploader_id, $target_dept_id, $status_id, $doc_type_id, $ref_no, $uploader_id, $applicant, $license, $target_file);
            $stmt1->execute();

            // 4. Insert into `transaction_logs` table
            $sql_log = "INSERT INTO transaction_logs (doc_id, doc_status_id, current_dept_id, target_dept_id, processed_by, remarks) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            $stmt2 = $conn->prepare($sql_log);
            $stmt2->bind_param("siiiis", $doc_id, $status_id, $current_dept, $target_dept_id, $uploader_id, $remarks);
            $stmt2->execute();

            // 5. Send Email to Dept Head (Optional logic)
            // findDeptHeadEmail($target_dept_id); 

            $conn->commit();
            header("Location: ../public/index.php?msg=Document Uploaded Successfully. Ref: $ref_no");

        } catch (Exception $e) {
            $conn->rollback();
            unlink($target_file); // Remove file if DB fails
            header("Location: ../public/transfer.php?error=" . $e->getMessage());
        }
    } else {
        header("Location: ../public/transfer.php?error=File upload failed.");
    }
}