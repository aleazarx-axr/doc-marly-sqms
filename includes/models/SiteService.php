<?php
class SiteService {
    private $conn;
    private $table_name = "site_services";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Save assignments for a site (overwrites existing)
    public function saveAssignments($site_id, $service_ids_array) {
        // First delete existing assignments for this site
        $delQuery = "DELETE FROM " . $this->table_name . " WHERE site_id = :site_id";
        $delStmt = $this->conn->prepare($delQuery);
        $delStmt->bindParam(':site_id', $site_id);
        $delStmt->execute();

        // If no services checked, return true (cleared)
        if (empty($service_ids_array)) {
            return true;
        }

        // Insert new assignments
        $query = "INSERT INTO " . $this->table_name . " (site_id, service_id) VALUES (:site_id, :service_id)";
        $stmt = $this->conn->prepare($query);

        foreach ($service_ids_array as $service_id) {
            $stmt->bindValue(':site_id', $site_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->execute();
        }

        return true;
    }

    // Get assigned service IDs for a specific site
    public function getAssignedServices($site_id) {
        $query = "SELECT service_id FROM " . $this->table_name . " WHERE site_id = :site_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':site_id', $site_id);
        $stmt->execute();
        
        $assigned = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assigned[] = $row['service_id'];
        }
        return $assigned;
    }
}
?>
