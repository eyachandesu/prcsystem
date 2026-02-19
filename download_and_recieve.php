<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: receive.php");
    exit();
}

$doc_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify holder
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ? AND current_holder = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

if (!$document) { die("Access Denied."); }

$conn->begin_transaction();
try {
    // 1. Update Document Status
    $updateDoc = $conn->prepare("UPDATE documents SET current_status = 'Under Review', updated_at = NOW() WHERE id = ?");
    $updateDoc->bind_param("i", $doc_id);
    $updateDoc->execute();

    // 2. UPDATED: Record Action in Logs with r_id
    $action_msg = "Document Received and Downloaded.";
    // Here user_id is the receiver, and r_id is also the receiver
    $logStmt = $conn->prepare("INSERT INTO document_logs (document_id, user_id, r_id, action) VALUES (?, ?, ?, ?)");
    $logStmt->bind_param("iiis", $doc_id, $user_id, $user_id, $action_msg);
    $logStmt->execute();

    // 3. Update Route Status
    $routeStmt = $conn->prepare("UPDATE document_routes SET status = 'Approved', action_date = NOW() WHERE document_id = ? AND to_user = ? AND status = 'Pending'");
    $routeStmt->bind_param("ii", $doc_id, $user_id);
    $routeStmt->execute();

    $conn->commit();

    // 4. Download
    $file_path = $document['file_path'];
    if (file_exists($file_path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        readfile($file_path);
        exit();
    }
} catch (Exception $e) {
    $conn->rollback();
    die("Transaction failed.");
}