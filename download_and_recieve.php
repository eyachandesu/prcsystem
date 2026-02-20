<?php
session_start();
require_once 'config.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: receive.php");
    exit();
}

$doc_id = (int)$_GET['id'];
$user_id = (int)$_SESSION['user_id'];

// 2. Verify that this document is actually assigned to the current user
$stmt = $conn->prepare("SELECT file_path, document_number, title FROM documents WHERE id = ? AND current_holder = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($doc = $result->fetch_assoc()) {
    $file_path = $doc['file_path'];
    $doc_name = $doc['document_number'];

    // 3. Start Transaction to update all tracking tables
    $conn->begin_transaction();

    try {
        // A. Update the main document status
        $updateDoc = $conn->prepare("UPDATE documents SET current_status = 'Under Review' WHERE id = ?");
        $updateDoc->bind_param("i", $doc_id);
        $updateDoc->execute();

        // B. Update the specific route status (marking the transfer as complete)
        $updateRoute = $conn->prepare("UPDATE document_routes SET status = 'Forwarded' WHERE document_id = ? AND to_user = ? AND status = 'Pending'");
        $updateRoute->bind_param("ii", $doc_id, $user_id);
        $updateRoute->execute();

        // C. Add a log entry for the Audit Trail (index.php)
        $log_action = "Received & Downloaded by " . $_SESSION['name'];
        $insertLog = $conn->prepare("INSERT INTO document_logs (document_id, user_id, action) VALUES (?, ?, ?)");
        $insertLog->bind_param("iis", $doc_id, $user_id, $log_action);
        $insertLog->execute();

        $conn->commit();

        // 4. Handle the File Download
        if (file_exists($file_path)) {
            // Clean any previous output to prevent file corruption
            ob_clean();
            flush();

            // Set headers to force download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            
            readfile($file_path);
            exit();
        } else {
            die("Error: The physical file was not found on the server ($file_path).");
        }

    } catch (Exception $e) {
        $conn->rollback();
        die("Database Error: " . $e->getMessage());
    }

} else {
    die("Error: Document not found or you are not authorized to receive this document.");
}