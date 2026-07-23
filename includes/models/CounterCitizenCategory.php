<?php
class CounterCitizenCategory {
    private $conn;
    private $table_name = "counter_citizen_categories";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function saveCategories($counter_id, $categories_array) {
        $delQuery = "DELETE FROM " . $this->table_name . " WHERE counter_id = :counter_id";
        $delStmt = $this->conn->prepare($delQuery);
        $delStmt->bindParam(':counter_id', $counter_id);
        $delStmt->execute();

        if (empty($categories_array)) {
            return true;
        }

        $query = "INSERT INTO " . $this->table_name . " (counter_id, citizen_category) VALUES (:counter_id, :cat)";
        $stmt = $this->conn->prepare($query);

        foreach ($categories_array as $cat) {
            $stmt->bindValue(':counter_id', $counter_id);
            $stmt->bindValue(':cat', $cat);
            $stmt->execute();
        }

        return true;
    }

    public function getCategories($counter_id) {
        $query = "SELECT citizen_category FROM " . $this->table_name . " WHERE counter_id = :counter_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':counter_id', $counter_id);
        $stmt->execute();
        
        $cats = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $cats[] = $row['citizen_category'];
        }
        return $cats;
    }
}
?>
