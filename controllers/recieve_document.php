<?php
require_once __DIR__ . "/../init.php";

// Ensure user is logged in
$userData = checkAuth();
$processed_by = $userData->user_id; // Your UUID from the session
$current_dept_id = $userData->dept_id; // Your department ID

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Safely get POST data
    $doc_id = trim($_POST['doc_id'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');

    if (empty($remarks)) {
        $remarks = "Document Received and Acknowledged.";
    }

    // New Status: 3 = Received/Action Taken (Based on your doc_status table)
    $new_status_id = 3; 

    // Target Redirect
    $redirect_url = "../public/receive.php";

    if (empty($doc_id)) {
        $_SESSION['error'] = "Missing document identifier.";
        header("Location: " . $redirect_url);
        exit;
    }

    $conn->begin_transaction();

    try {
        // 1. Update 'document' table: Status becomes 3 (Received)
        $updateSql = "UPDATE document 
                      SET doc_status_id = ? 
                      WHERE doc_id = ? AND current_dept_id = ?";
        
        $stmt = $conn->prepare($updateSql);
        if (!$stmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }

        // Types: i(status) s(doc_id) i(current_dept_id to prevent unauthorized receiving)
        $stmt->bind_param("isi", $new_status_id, $doc_id, $current_dept_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception("Document not found or you don't have permission to receive it.");
        }

        // 2. Insert into 'transaction_logs'
        // Keeping current_dept_id and target_dept_id the same, as the document has already arrived.
        $logSql = "INSERT INTO transaction_logs 
                   (doc_id, doc_status_id, current_dept_id, target_dept_id, processed_by, remarks) 
                   VALUES (?, ?, ?, ?, ?, ?)";
        
        $logStmt = $conn->prepare($logSql);
        if (!$logStmt) {
            throw new Exception("Prepare statement failed: " . $conn->error);
        }
        
        // Types: s(doc_id) i(status) i(dept) i(dept) s(user_id) s(remarks)
        $logStmt->bind_param("siiiss", 
            $doc_id, 
            $new_status_id, 
            $current_dept_id, 
            $current_dept_id, 
            $processed_by, 
            $remarks
        );
        $logStmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Document successfully received into your department!";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error receiving document: " . $e->getMessage();
    }

    header("Location: " . $redirect_url);
    exit;

} else {
    // Prevent direct browser access
    header("Location: ../public/receive.php");
    exit;
}
?>