<?php
function fetchDept($conn)
{
    $sql = "SELECT * FROM department";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}
?>