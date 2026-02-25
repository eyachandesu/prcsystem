<?php
ob_start();
session_start();
require_once "config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION['user_id'];
    $title = trim($_POST['title']);
    $doc_type = $_POST['doc_type'];
    $description = trim($_POST['description']);
    $recipient_id = $_POST['to_user']; // This is now a string (UUID)
    $remarks = trim($_POST['remarks']);

    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = pathinfo($_FILES["doc_file"]["name"], PATHINFO_EXTENSION);
    $file_name = time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["doc_file"]["tmp_name"], $target_file)) {
        
        $doc_number = "PRC-" . date("Y") . "-" . strtoupper(substr(uniqid(), -5));

        $conn->begin_transaction();

        try {
            // A. Insert into documents
            $doc_id = generate_uuid(); // Create the UUID manually
            $sql = "INSERT INTO documents (id, document_number, title, document_type, description, file_path, uploaded_by, current_holder, current_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Submitted')";
            $stmt = $conn->prepare($sql);
            // All ID fields are now strings "s"
            $stmt->bind_param("ssssssss", $doc_id, $doc_number, $title, $doc_type, $description, $target_file, $sender_id, $recipient_id);
            $stmt->execute();

            // B. Insert into document_routes
            $route_id = generate_uuid();
            $route_sql = "INSERT INTO document_routes (id, document_id, from_user, to_user, remarks, status) 
                          VALUES (?, ?, ?, ?, ?, 'Pending')";
            $route_stmt = $conn->prepare($route_sql);
            $route_stmt->bind_param("sssss", $route_id, $doc_id, $sender_id, $recipient_id, $remarks);
            $route_stmt->execute();

            // C. Insert into document_logs
            $log_id = generate_uuid();
            $log_sql = "INSERT INTO document_logs (id, document_id, user_id, r_id, action) 
                        VALUES (?, ?, ?, ?, 'Document Transferred')";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("ssss", $log_id, $doc_id, $sender_id, $recipient_id);
            $log_stmt->execute();

            // D. Fetch Recipient Name
            $name_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $name_stmt->bind_param("s", $recipient_id);
            $name_stmt->execute();
            $user_row = $name_stmt->get_result()->fetch_assoc();
            $rec_name = $user_row['first_name'] . " " . $user_row['last_name'];

            $conn->commit();

            ob_end_clean();
            header("Location: index.php?status=success&recipient=" . urlencode($rec_name));
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            die("Database Error: " . $e->getMessage());
        }
    } else {
        die("Error: Failed to move uploaded file.");
    }
}