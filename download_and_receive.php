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

// 2. Database Logic
$stmt = $conn->prepare("SELECT file_path, document_number FROM documents WHERE id = ? AND current_holder = ?");
$stmt->bind_param("ii", $doc_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($doc = $result->fetch_assoc()) {
    $file_path = $doc['file_path'];

    // 3. Update Status and Audit Trail
    $conn->begin_transaction();
    try {
        $conn->query("UPDATE documents SET current_status = 'Under Review' WHERE id = $doc_id");
        $conn->query("INSERT INTO document_logs (document_id, user_id, action) VALUES ($doc_id, $user_id, 'Received & Downloaded')");
        $conn->commit();

        // 4. TRIGGER DOWNLOAD
        if (file_exists($file_path)) {
            // This clears any text output so only the file data is sent
            if (ob_get_level()) ob_end_clean();

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            header('Pragma: public');
            
            readfile($file_path);
            exit;
        } else {
            die("Physical file not found.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        die("Error processing record.");
    }
} else {
    die("Unauthorized access.");
}
// DO NOT ADD A CLOSING PHP TAG HERE