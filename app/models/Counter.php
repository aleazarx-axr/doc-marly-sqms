<?php
class Counter {
    private $conn;
    private $table_name = "counters";

    public $id;
    public $site_id;
    public $name;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new counter
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (site_id, name) VALUES (:site_id, :name)";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->site_id = htmlspecialchars(strip_tags($this->site_id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':site_id', $this->site_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all active counters with site names
    public function read() {
        $query = "SELECT c.id, c.site_id, c.name, s.name as site_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN sites s ON c.site_id = s.id
                  WHERE c.status = 'active'
                  ORDER BY s.name ASC, c.name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a counter
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name, site_id = :site_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->site_id = htmlspecialchars(strip_tags($this->site_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':site_id', $this->site_id);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete (archive/inactive)
    public function archive() {
        $query = "UPDATE " . $this->table_name . " SET status = 'inactive' WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
