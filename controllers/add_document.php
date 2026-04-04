<?php
require_once __DIR__ . "/../init.php";
ob_start();

$userData = checkAuth();
use Ramsey\Uuid\Uuid;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // 1. Generate unique ID for the document
        $doc_id = Uuid::uuid4()->toString();
    
        // 2. Capture and sanitize form data
        $name = trim($_POST['document_name']);
        $desc = trim($_POST['document_desc']);

        // 3. Capture data from your Token Object ($userData)
        $current_user_id = trim($userData->user_id);
        $uploaded_by     = trim($userData->user_id);
        $current_dept    = $userData->dept_id; 

        $sql = "INSERT INTO document (
                    doc_id, 
                    doc_name, 
                    doc_description, 
                    current_user_id, 
                    current_dept_id, 
                    uploaded_by
                ) VALUES (?, ?, ?, ?, ?, ?)";
        
        // FIX: Assign the prepared statement to $stmt
        $stmt = $conn->prepare($sql);
        
        // 5. Now $stmt is an object, so execute() will work
        $stmt->execute([
            $doc_id, 
            $name, 
            $desc, 
            $current_user_id, 
            $current_dept, 
            $uploaded_by 
        ]);

        // 6. Success Handling & Redirection
        // Using a session message is better for UX than a simple 'echo'
        $_SESSION['success'] = "Document '$name' added successfully!";
        header("Location:../public/documents.php"); // Redirect back to dashboard
        exit();
        
    } catch (Exception $e) {
        // Log the error and show a user-friendly message
        error_log($e->getMessage());
        echo "Error: Could not save document. " . $e->getMessage();
    }
}
ob_end_flush();
?>