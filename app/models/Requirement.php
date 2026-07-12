<?php
class Requirement {
    private $conn;
    private $table_name = "requirements";

    public $id;
    public $name;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all active requirements
    public function read() {
        $query = "SELECT id, name, created_at FROM " . $this->table_name . " WHERE is_archived = 0 ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Soft delete (archive) a requirement
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

    // Add a new requirement if it doesn't exist
    public function create() {
        $this->name = htmlspecialchars(strip_tags($this->name));
        
        // Prevent empty inserts
        if(empty(trim($this->name))) return false;

        $query = "INSERT IGNORE INTO " . $this->table_name . " (name) VALUES (:name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
