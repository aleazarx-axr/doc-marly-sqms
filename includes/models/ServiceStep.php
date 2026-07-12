<?php
class ServiceStep {
    private $conn;
    private $table_name = "service_steps";

    public $id;
    public $service_id;
    public $step_order;
    public $step_name;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Save steps for a service (overwrites existing steps)
    public function saveSteps($service_id, $steps_array) {
        // First delete existing steps for this service
        $delQuery = "DELETE FROM " . $this->table_name . " WHERE service_id = :service_id";
        $delStmt = $this->conn->prepare($delQuery);
        $delStmt->bindParam(':service_id', $service_id);
        $delStmt->execute();

        // If no steps provided, return true (cleared)
        if (empty($steps_array)) {
            return true;
        }

        // Insert new steps
        $query = "INSERT INTO " . $this->table_name . " (service_id, step_order, step_name) VALUES (:service_id, :step_order, :step_name)";
        $stmt = $this->conn->prepare($query);

        $order = 1;
        foreach ($steps_array as $step_name) {
            $s_name = htmlspecialchars(strip_tags($step_name));
            
            $stmt->bindValue(':service_id', $service_id);
            $stmt->bindValue(':step_order', $order);
            $stmt->bindValue(':step_name', $s_name);
            
            $stmt->execute();
            $order++;
        }

        return true;
    }

    // Get all steps for a specific service
    public function getStepsByService($service_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE service_id = :service_id ORDER BY step_order ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':service_id', $service_id);
        $stmt->execute();
        
        $steps = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $steps[] = $row['step_name'];
        }
        return $steps;
    }
}
?>
