<?php
class Service {
    private $conn;
    private $table_name = "services";

    public $id;
    public $code;
    public $name;
    public $description;
    public $requirements;

    public $prefix;
    public $starting_number;
    public $is_active;
    public $is_archived;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (code, name, description, requirements, prefix, starting_number) 
                  VALUES (:code, :name, :description, :requirements, :prefix, :starting_number)";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->requirements = htmlspecialchars(strip_tags($this->requirements));

        $this->prefix = htmlspecialchars(strip_tags($this->prefix));
        $this->starting_number = htmlspecialchars(strip_tags($this->starting_number));

        // Bind
        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':requirements', $this->requirements);

        $stmt->bindParam(':prefix', $this->prefix);
        $stmt->bindParam(':starting_number', $this->starting_number);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_archived = 0";
        $query .= " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readArchived() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE is_archived = 1";
        $query .= " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->code = $row['code'];
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->requirements = $row['requirements'];

            $this->prefix = $row['prefix'];
            $this->starting_number = $row['starting_number'];
            $this->is_active = $row['is_active'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET code = :code, name = :name, description = :description, 
                      requirements = :requirements, prefix = :prefix, starting_number = :starting_number
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->code = htmlspecialchars(strip_tags($this->code));
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->requirements = htmlspecialchars(strip_tags($this->requirements));

        $this->prefix = htmlspecialchars(strip_tags($this->prefix));
        $this->starting_number = htmlspecialchars(strip_tags($this->starting_number));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':code', $this->code);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':requirements', $this->requirements);

        $stmt->bindParam(':prefix', $this->prefix);
        $stmt->bindParam(':starting_number', $this->starting_number);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function toggleStatus() {
        $query = "UPDATE " . $this->table_name . " SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    
    public function hasTickets() {
        $query = "SELECT id FROM tickets WHERE service_id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function delete() {
        if ($this->hasTickets()) {
            return false;
        }
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
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
}
?>
