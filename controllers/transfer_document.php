<?php
require_once __DIR__ . "/../init.php";

$userData = checkAuth();

$processed_by   = $userData->user_id;   // Logged in user UUID
$sender_dept_id = $userData->dept_id;   // Sender department

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../public/documents.php");
    exit;
}

$redirect_url = "../public/documents.php";

/* ---------------------------------
   FETCH FORM VALUES SAFELY
--------------------------------- */
$doc_id         = trim($_POST['documents'] ?? '');
$target_dept_id = trim($_POST['departments'] ?? '');
$receiver_id    = trim($_POST['user'] ?? '');
$remarks        = trim($_POST['remarks'] ?? '');

if ($remarks === '') {
    $remarks = "Document Forwarded";
}

/* ---------------------------------
   VALIDATION
--------------------------------- */
if (empty($doc_id) || empty($target_dept_id) || empty($receiver_id)) {
    $_SESSION['error'] = "Please select document, department, and receiver.";
    header("Location: $redirect_url");
    exit;
}

/* ---------------------------------
   STATUS ID
--------------------------------- */
$new_status_id = 2; // Forwarded

/* ---------------------------------
   START TRANSACTION
--------------------------------- */
$conn->begin_transaction();

try {

    /* ==================================================
       1. VERIFY DOCUMENT EXISTS
    ================================================== */
    $checkSql = "SELECT doc_id FROM document WHERE doc_id = ?";
    $checkStmt = $conn->prepare($checkSql);

    if (!$checkStmt) {
        throw new Exception("Check prepare failed: " . $conn->error);
    }

    $checkStmt->bind_param("s", $doc_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        throw new Exception("Selected document does not exist.");
    }

    /* ==================================================
       2. UPDATE DOCUMENT OWNER / DEPT / STATUS
    ================================================== */
    $updateSql = "
        UPDATE document
        SET
            current_user_id = ?,
            current_dept_id = ?,
            doc_status_id   = ?
        WHERE doc_id = ?
    ";

    $stmt = $conn->prepare($updateSql);

    if (!$stmt) {
        throw new Exception("Update prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "siis",
        $receiver_id,
        $target_dept_id,
        $new_status_id,
        $doc_id
    );

    $stmt->execute();

    if ($stmt->error) {
        throw new Exception("Update failed: " . $stmt->error);
    }

    /* ==================================================
       3. INSERT TRANSACTION LOG
    ================================================== */
    $logSql = "
        INSERT INTO transaction_logs
        (
            doc_id,
            doc_status_id,
            current_dept_id,
            target_dept_id,
            processed_by,
            remarks
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ";

    $logStmt = $conn->prepare($logSql);

    if (!$logStmt) {
        throw new Exception("Log prepare failed: " . $conn->error);
    }

    $logStmt->bind_param(
        "siiiss",
        $doc_id,
        $new_status_id,
        $sender_dept_id,
        $target_dept_id,
        $processed_by,
        $remarks
    );

    $logStmt->execute();

    if ($logStmt->error) {
        throw new Exception("Log insert failed: " . $logStmt->error);
    }

    /* ==================================================
       SUCCESS
    ================================================== */
    $conn->commit();

    $_SESSION['success'] = "Document transferred successfully.";

} catch (Exception $e) {

    $conn->rollback();

    $_SESSION['error'] = "Transfer failed: " . $e->getMessage();
}

/* ---------------------------------
   REDIRECT BACK
--------------------------------- */
header("Location: $redirect_url");
exit;
?>