<?php
session_start();
require_once 'config.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: receive.php");
    exit();
}

// REMOVED (int) casting - UUIDs are strings
$doc_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

/**
 * Helper function to generate a UUID v4
 */
function generate_uuid() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// 2. Database Logic
// Changed "ii" to "ss" for UUID strings
$stmt = $conn->prepare("SELECT file_path, document_number FROM documents WHERE id = ? AND current_holder = ?");
$stmt->bind_param("ss", $doc_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($doc = $result->fetch_assoc()) {
    $file_path = $doc['file_path'];

    // 3. Update Status and Audit Trail
    $conn->begin_transaction();
    try {
        // Update document status
        $update_stmt = $conn->prepare("UPDATE documents SET current_status = 'Under Review' WHERE id = ?");
        $update_stmt->bind_param("s", $doc_id);
        $update_stmt->execute();

        // Generate a new UUID for this specific log entry
        $log_id = generate_uuid();
        
        // Insert into document_logs (added the 'id' field and quotes for strings)
        $log_stmt = $conn->prepare("INSERT INTO document_logs (id, document_id, user_id, action) VALUES (?, ?, ?, 'Received & Downloaded')");
        $log_stmt->bind_param("sss", $log_id, $doc_id, $user_id);
        $log_stmt->execute();

        // Optional: Update the route status to 'Received'
        $route_stmt = $conn->prepare("UPDATE document_routes SET status = 'Received' WHERE document_id = ? AND to_user = ?");
        $route_stmt->bind_param("ss", $doc_id, $user_id);
        $route_stmt->execute();

        $conn->commit();

        // 4. TRIGGER DOWNLOAD
        if (file_exists($file_path)) {
            // Clear buffer to prevent file corruption
            if (ob_get_level()) ob_end_clean();

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            
            readfile($file_path);
            exit;
        } else {
            die("Error: The file does not exist on the server ($file_path).");
        }
    } catch (Exception $e) {
        $conn->rollback();
        die("Database Error: " . $e->getMessage());
    }
} else {
    die("Error: Document not found or you are not the current holder.");
}
// DO NOT ADD A CLOSING PHP TAG HERE