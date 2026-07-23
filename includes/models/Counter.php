<?php
class Counter {
    private $conn;
    private $table_name = "counters";

    public $id;
    public $name;
    public $counter_type;
    public $staff_id;
    public $overflow_general;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, counter_type, staff_id, overflow_general) 
                  VALUES (:name, :counter_type, :staff_id, :overflow_general)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->counter_type = htmlspecialchars(strip_tags($this->counter_type));
        $this->staff_id = !empty($this->staff_id) ? htmlspecialchars(strip_tags($this->staff_id)) : null;
        $this->overflow_general = htmlspecialchars(strip_tags($this->overflow_general));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':counter_type', $this->counter_type);
        $stmt->bindParam(':staff_id', $this->staff_id);
        $stmt->bindParam(':overflow_general', $this->overflow_general);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT c.*, u.username as staff_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.staff_id = u.id
                  WHERE c.is_archived = 0
                  ORDER BY c.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readArchived() {
        $query = "SELECT c.*, u.username as staff_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN users u ON c.staff_id = u.id
                  WHERE c.is_archived = 1
                  ORDER BY c.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT c.* FROM " . $this->table_name . " c WHERE c.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->name = $row['name'];
            $this->counter_type = $row['counter_type'];
            $this->staff_id = $row['staff_id'];
            $this->overflow_general = $row['overflow_general'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, counter_type = :counter_type, 
                      staff_id = :staff_id, 
                      overflow_general = :overflow_general
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->counter_type = htmlspecialchars(strip_tags($this->counter_type));
        $this->staff_id = !empty($this->staff_id) ? htmlspecialchars(strip_tags($this->staff_id)) : null;
        $this->overflow_general = htmlspecialchars(strip_tags($this->overflow_general));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':counter_type', $this->counter_type);
        $stmt->bindParam(':staff_id', $this->staff_id);
        $stmt->bindParam(':overflow_general', $this->overflow_general);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function archive() {
        $query = "UPDATE " . $this->table_name . " SET is_archived = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function restore() {
        $query = "UPDATE " . $this->table_name . " SET is_archived = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }

    public function nameExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE name = :name AND id != :id";
        $stmt = $this->conn->prepare($query);
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->id = htmlspecialchars(strip_tags($this->id ?: 0));
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    public function getCountersByStaff($staffId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE staff_id = :staff_id AND is_archived = 0 AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':staff_id', $staffId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCounterServices($counterId) {
        $query = "SELECT service_id FROM counter_services WHERE counter_id = :counter_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':counter_id', $counterId);
        $stmt->execute();
        
        $serviceIds = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $serviceIds[] = $row['service_id'];
        }
        return $serviceIds;
    }
}
?>
