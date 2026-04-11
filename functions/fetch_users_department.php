<?php
function fetchUsersByDepartment($conn, $deptId, $excludeId = null)
{
    // Corrected SQL: Joining department on the actual department ID column
    $sql = "SELECT u.user_id, u.username, up.user_first_name, up.user_last_name
            FROM user u
            LEFT JOIN user_profile up ON u.user_id = up.user_id
            WHERE up.dept_id = ? AND u.user_id != ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL error: " . $conn->error);
    }

    // "
    $stmt->bind_param("is", $deptId, $excludeId);
    $stmt->execute();
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];
    }
}
?>