<?php

class DocVisibility
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param string $user_role    The role string (e.g., "User", "Admin", "System Administrator")
     * @param int    $user_dept_id The integer ID of the department (e.g., 3)
     */
    public function getVisibleDocuments(string $user_role, int $user_dept_id): array
    {
        // 1. Log the incoming data for debugging
        error_log("DEBUG: Processing Visibility - Role: " . $user_role . " | Dept ID: " . $user_dept_id);

        $params = [];
        $types = "";

        $sql = "SELECT 
                    d.doc_id,
                    d.doc_name,
                    d.doc_description,
                    d.doc_created_at,
                    u_curr.username AS current_handler,
                    u_up.username AS uploader,
                    dept.dept_name AS department,
                    st.status_name AS document_status
                FROM document d
                LEFT JOIN `user` u_curr ON d.current_user_id = u_curr.user_id
                LEFT JOIN `user` u_up ON d.uploaded_by = u_up.user_id
                LEFT JOIN department dept ON d.current_dept_id = dept.dept_id
                LEFT JOIN doc_status st ON d.doc_status_id = st.doc_status_id";

        // 2. Logic: Define who counts as an "Admin" 
        // Based on your login handler, we check for both "Admin" and "System Administrator"
        $isAdmin = in_array($user_role, ["System Administrator", "Admin"]);

        if (!$isAdmin) {
            // 3. Apply the department filter for regular users
            $sql .= " WHERE d.current_dept_id = ?";
            $params[] = $user_dept_id;
            $types .= "i"; // "i" for integer
        }

        $sql .= " ORDER BY d.doc_created_at DESC";

        return $this->executeQuery($sql, $params, $types);
    }

    private function executeQuery($sql, $params, $types): array
    {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL Prepare Error: " . $this->conn->error);
            return [];
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            error_log("SQL Execute Error: " . $stmt->error);
            return [];
        }

        $result = $stmt->get_result();
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}