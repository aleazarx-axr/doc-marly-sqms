<?php
class CounterService {
    private $conn;
    private $table_name = "counter_services";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Save assignments for a counter
    public function saveAssignments($counter_id, $service_ids_array) {
        // First deactivate all existing assignments for this counter
        $delQuery = "UPDATE " . $this->table_name . " SET is_active = 0 WHERE counter_id = :counter_id";
        $delStmt = $this->conn->prepare($delQuery);
        $delStmt->bindParam(':counter_id', $counter_id);
        $delStmt->execute();

        if (empty($service_ids_array)) {
            return true;
        }

        // Insert or activate assignments
        foreach ($service_ids_array as $service_id) {
            // Check if exists
            $checkQuery = "SELECT id FROM " . $this->table_name . " WHERE counter_id = :counter_id AND service_id = :service_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindValue(':counter_id', $counter_id);
            $checkStmt->bindValue(':service_id', $service_id);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $updateQuery = "UPDATE " . $this->table_name . " SET is_active = 1 WHERE counter_id = :counter_id AND service_id = :service_id";
                $upStmt = $this->conn->prepare($updateQuery);
                $upStmt->bindValue(':counter_id', $counter_id);
                $upStmt->bindValue(':service_id', $service_id);
                $upStmt->execute();
            } else {
                $query = "INSERT INTO " . $this->table_name . " (counter_id, service_id, is_active) VALUES (:counter_id, :service_id, 1)";
                $stmt = $this->conn->prepare($query);
                $stmt->bindValue(':counter_id', $counter_id);
                $stmt->bindValue(':service_id', $service_id);
                $stmt->execute();
            }
        }

        return true;
    }

    // Get assigned active service IDs for a specific counter
    public function getAssignedServices($counter_id) {
        $query = "SELECT service_id FROM " . $this->table_name . " WHERE counter_id = :counter_id AND is_active = 1";
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
