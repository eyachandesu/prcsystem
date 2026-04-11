<?php
require_once __DIR__ . "/../init.php";

$userData = checkAuth();
$processed_by = $userData->user_id; // Your UUID from the session
$sender_dept_id = $userData->dept_id; // Your department ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doc_id = $_POST['documents'] ?? null;
    $target_dept_id = $_POST['departments'] ?? null;
    $receiver_id = $_POST['user'] ?? null; 
    $remarks = $_POST['remarks'] ?? 'Document Forwarded';

    // Status ID for 'Forwarded' (Check your doc_status table, usually 2 or 3)
    $new_status_id = 2; 

    if (!$doc_id || !$target_dept_id || !$receiver_id) {
        $_SESSION['error'] = "Required fields are missing.";
        header("Location: ../public/transfer.php");
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Update 'document' table 
        // Based on your image: current_user_id, current_dept_id, and doc_status_id
        $updateSql = "UPDATE document 
                      SET current_user_id = ?, 
                          current_dept_id = ?, 
                          doc_status_id = ? 
                      WHERE doc_id = ?";
        
        $stmt = $conn->prepare($updateSql);
        // Types: s = UUID, i = INT, i = INT, s = UUID
        $stmt->bind_param("siis", $receiver_id, $target_dept_id, $new_status_id, $doc_id);
        $stmt->execute();

        // 2. Insert into 'transaction_logs' (Plural, as seen in your image)
        $logSql = "INSERT INTO transaction_logs 
                   (doc_id, doc_status_id, current_dept_id, target_dept_id, processed_by, remarks) 
                   VALUES (?, ?, ?, ?, ?, ?)";
        
        $logStmt = $conn->prepare($logSql);
        
        // Log Logic: 
        // current_dept_id = where it came from (sender_dept_id)
        // target_dept_id = where it is going (target_dept_id from POST)
        $logStmt->bind_param("siiiss", 
            $doc_id, 
            $new_status_id, 
            $sender_dept_id, 
            $target_dept_id, 
            $processed_by, 
            $remarks
        );
        $logStmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Document successfully transferred!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    header("Location: ../public/transfer.php");
    exit;
}