<?php
class UserVisibility
{
    private $conn;
    const STATUS_DELETED = 4;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getVisibleUsers(?int $limit = null, ?int $offset = null): array
    {
        // Inside your getVisibleUsers method, make sure the SELECT includes the email
        $sql = "SELECT u.user_id, u.username, u.user_role_id, 
               up.user_first_name, up.user_middle_name, up.user_last_name, 
               up.email, up.user_prof, 
               r.role_name
        FROM user u
        INNER JOIN user_profile up ON u.user_id = up.user_id
        INNER JOIN user_role r ON u.user_role_id = r.user_role_id
        WHERE u.user_status_id IN (1, 2, 3)";

        if ($limit !== null) {
            $limit = max(0, (int) $limit);
            $sql .= " LIMIT $limit";
            if ($offset !== null) {
                $offset = max(0, (int) $offset);
                $sql .= " OFFSET $offset";
            }
        }

        $result = $this->conn->query($sql);
        if (!$result) {
            // Log error or return empty array
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>