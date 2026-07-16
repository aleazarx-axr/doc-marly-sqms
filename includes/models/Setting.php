<?php
class Setting {
    private $conn;
    private $table_name = "settings";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all settings
    public function getAll() {
        $query = "SELECT setting_key, setting_value, description FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    // Get a single setting value
    public function get($key) {
        $query = "SELECT setting_value FROM " . $this->table_name . " WHERE setting_key = :key LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":key", $key);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['setting_value'];
        }
        return null;
    }

    // Update multiple settings at once
    public function updateMultiple($settings_array) {
        try {
            $this->conn->beginTransaction();
            $query = "UPDATE " . $this->table_name . " SET setting_value = :value WHERE setting_key = :key";
            $stmt = $this->conn->prepare($query);

            foreach ($settings_array as $key => $value) {
                $stmt->bindParam(":value", $value);
                $stmt->bindParam(":key", $key);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
