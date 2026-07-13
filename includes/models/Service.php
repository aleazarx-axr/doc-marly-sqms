<?php
class Service {
    private $conn;
    private $table_name = "services";

    public $id;
    public $name;
    public $requirements;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new service
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, requirements) VALUES (:name, :requirements)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->requirements = htmlspecialchars(strip_tags($this->requirements));

        // Bind
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':requirements', $this->requirements);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all active services
    public function read() {
        $query = "SELECT id, name, requirements, created_at, is_archived FROM " . $this->table_name . " WHERE is_archived = 0 ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a service
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name, requirements = :requirements WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->requirements = htmlspecialchars(strip_tags($this->requirements));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':requirements', $this->requirements);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete (archive) a service
    public function archive() {
        $query = "UPDATE " . $this->table_name . " SET is_archived = 1 WHERE id = :id";
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
