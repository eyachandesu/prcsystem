<?php
require_once __DIR__ . "/../init.php";
ob_start();

$userData = checkAuth();
$current_user_id = $userData->user_id;
$user_role = $userData->role;
$user_dept_id = $userData->dept_id;
$visibility = new DocVisibility($conn);
$documents = $visibility->getVisibleDocuments($user_role, $user_dept_id);
var_dump($userData);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dist/output.css">
    <title>Documents</title>
</head>
<body>
    <h1>Documents</h1>
    <div>
        <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-4">Documents</h2>

        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Document Name</th>
                        <th class="px-4 py-2 text-left">Document Description</th>
                        <th class="px-4 py-2 text-left">Uploaded By</th>
                        <th class="px-4 py-2 text-left">Current Department</th>
                        <th class="px-4 py-2 text-left">Currently Handled by</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $document): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-blue-600 uppercase">
                                <?= $document['doc_name'] ?>
                            </td>
                            <td class="px-4 py-2">
                                <?= htmlspecialchars($document['doc_description']) ?>
                            </td>
                             <td class="px-4 py-2">
                                <?= htmlspecialchars($document['uploader']) ?>
                            </td>
                            <td class="px-4 py-2 text-sm max-w-xs truncate">
                                <?= htmlspecialchars($document['department']) ?>
                            </td>
                            <td class="px-4 py-2 text-sm max-w-xs truncate">
                                <?= htmlspecialchars($document['current_handler']) ?>
                            </td>
                            <td class="px-4 py-2 text-sm max-w-xs truncate">
                                <?= htmlspecialchars($document['document_status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
    </div>
    <div class="">
        <h1>Add New Document</h1>
        <form action="/controllers/add_document.php" method="POST">

            <label for="document_name">Document Name:</label>
            <input type="text" name="document_name" id="document_name" placeholder="Document Name" required>
            <label for="document_desc">Description:</label>
            <input type="text" name="document_desc" id="document_desc" placeholder="Document Description">
            <button type="submit">Add Document</button>
        </form>
    </div>
</body>
</html>