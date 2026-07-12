<?php
class CounterService {
    private $conn;
    private $table_name = "counter_services";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Save assignments for a counter (overwrites existing)
    public function saveAssignments($counter_id, $service_ids_array) {
        // First delete existing assignments for this counter
        $delQuery = "DELETE FROM " . $this->table_name . " WHERE counter_id = :counter_id";
        $delStmt = $this->conn->prepare($delQuery);
        $delStmt->bindParam(':counter_id', $counter_id);
        $delStmt->execute();

        // If no services checked, return true (cleared)
        if (empty($service_ids_array)) {
            return true;
        }

        // Insert new assignments
        $query = "INSERT INTO " . $this->table_name . " (counter_id, service_id) VALUES (:counter_id, :service_id)";
        $stmt = $this->conn->prepare($query);

        foreach ($service_ids_array as $service_id) {
            $stmt->bindValue(':counter_id', $counter_id);
            $stmt->bindValue(':service_id', $service_id);
            $stmt->execute();
        }

        return true;
    }

    // Get assigned service IDs for a specific counter
    public function getAssignedServices($counter_id) {
        $query = "SELECT service_id FROM " . $this->table_name . " WHERE counter_id = :counter_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':counter_id', $counter_id);
        $stmt->execute();
        
        $assigned = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $assigned[] = $row['service_id'];
        }
        return $assigned;
    }
}
?>
