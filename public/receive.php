<?php
session_start();
require_once "../config/config.php";
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$search_result = null;
if (isset($_GET['ref_no'])) {
    $ref = $_GET['ref_no'];
    $sql = "SELECT d.*, dt.document_type_name, ds.status_name 
            FROM document d 
            JOIN document_type dt ON d.doc_type_id = dt.doc_type_id
            JOIN doc_status ds ON d.doc_status_id = ds.doc_status_id
            WHERE d.ref_no = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ref);
    $stmt->execute();
    $search_result = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Receive Document | PRC DTS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Header/Sidebar logic same as index.php -->
    <main class="flex-1 p-10 max-w-5xl mx-auto w-full">
        <h2 class="text-3xl font-extrabold text-blue-900 mb-8 border-b pb-4">RECEIVE DOCUMENT</h2>

        <!-- Search Section -->
        <form method="GET" class="mb-10 flex gap-4">
            <input type="text" name="ref_no" placeholder="Enter Reference Number (e.g. PRC-REF-12345)" 
                   class="flex-1 border-b-2 border-gray-300 p-3 outline-none focus:border-blue-900 text-lg">
            <button type="submit" class="bg-blue-900 text-white px-8 py-3 rounded font-bold hover:bg-blue-800 transition">SEARCH</button>
        </form>

        <?php if ($search_result): ?>
        <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Applicant Name</label>
                    <p class="text-xl font-bold text-blue-900"><?php echo $search_result['applicant_name']; ?></p>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Document Type</label>
                    <p class="text-lg"><?php echo $search_result['document_type_name']; ?></p>
                </div>
            </div>

            <form action="../controllers/DocumentController.php" method="POST" class="space-y-6 border-t pt-8">
                <input type="hidden" name="doc_id" value="<?php echo $search_result['doc_id']; ?>">
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">Acknowledgment Remarks</label>
                    <textarea name="remarks" required class="w-full border p-4 rounded-lg bg-gray-50 h-32 focus:ring-2 focus:ring-blue-900 outline-none" placeholder="Enter notes here..."></textarea>
                </div>
                <button type="submit" name="receive_document" class="w-full py-4 bg-green-600 text-white font-bold rounded-lg hover:bg-green-700 transition">
                    ACKNOWLEDGE & RECEIVE DOCUMENT
                </button>
            </form>
        </div>
        <?php elseif (isset($_GET['ref_no'])): ?>
            <p class="text-red-500 font-bold bg-red-50 p-4 rounded">No document found with that reference number.</p>
        <?php endif; ?>
    </main>
</body>
</html>