<?php
// 1. Start output buffering to prevent "headers already sent" errors
ob_start();
session_start();

// 2. Database connection
require_once "config.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $doc_type = $_POST['doc_type'];
    $description = trim($_POST['description']);
    $recipient_id = (int)$_POST['to_user']; 
    $remarks = trim($_POST['remarks']);

    // 3. Handle File Upload
    $target_dir = "uploads/";
    
    // Create folder if it doesn't exist
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["doc_file"]["name"], PATHINFO_EXTENSION);
    $file_name = time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["doc_file"]["tmp_name"], $target_file)) {
        
        // 4. Generate Unique Document Number
        $doc_number = "PRC-" . date("Y") . "-" . strtoupper(substr(uniqid(), -5));

        // Start Transaction to ensure all tables update or none do
        $conn->begin_transaction();

        try {
            // A. Insert into documents
            $sql = "INSERT INTO documents (document_number, title, description, file_path, uploaded_by, current_holder, current_status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Submitted')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssii", $doc_number, $title, $description, $target_file, $sender_id, $recipient_id);
            $stmt->execute();
            $doc_id = $conn->insert_id;

            // B. Insert into document_routes
            $route_sql = "INSERT INTO document_routes (document_id, from_user, to_user, remarks, status) 
                          VALUES (?, ?, ?, ?, 'Pending')";
            $route_stmt = $conn->prepare($route_sql);
            $route_stmt->bind_param("iiis", $doc_id, $sender_id, $recipient_id, $remarks);
            $route_stmt->execute();

            // C. Insert into document_logs
            $log_sql = "INSERT INTO document_logs (document_id, user_id, action) 
                        VALUES (?, ?, 'Document Transferred')";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ii", $doc_id, $sender_id);
            $log_stmt->execute();

            // D. Fetch Recipient Name for the success message
            $name_q = $conn->query("SELECT first_name, last_name FROM users WHERE id = $recipient_id");
            $user_row = $name_q->fetch_assoc();
            $rec_name = $user_row['first_name'] . " " . $user_row['last_name'];

            // Commit transaction
            $conn->commit();

            // 5. SUCCESSFUL REDIRECT
            // Clean the buffer and redirect
            ob_end_clean();
            header("Location: index.php?status=success&recipient=" . urlencode($rec_name));
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            die("Database Error: " . $e->getMessage());
        }

    } else {
        // This is a common failure point
        die("Error: Failed to move uploaded file. Check if the 'uploads' folder exists and is writable.");
    }
} else {
    header("Location: transfer.php");
    exit();
}