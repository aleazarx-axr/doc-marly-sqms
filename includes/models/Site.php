<?php
class Site {
    private $conn;
    private $table_name = "sites";

    public $id;
    public $name;
    public $type;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new site
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (name, type) VALUES (:name, :type)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->type = htmlspecialchars(strip_tags($this->type));

        // Bind
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get all active sites
    public function read() {
        $query = "SELECT id, name, type, created_at FROM " . $this->table_name . " WHERE is_archived = 0 ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update a site
    public function update() {
        $query = "UPDATE " . $this->table_name . " SET name = :name, type = :type WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->type = htmlspecialchars(strip_tags($this->type));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Soft delete (archive) a site
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
