<?php
class Ticket {
    private $conn;
    private $table_name = "tickets";

    public $id;
    public $name;
    public $ticket_number;
    public $service_id;
    public $counter_id;

    public $citizen_category;
    public $status;
    public $issued_at;
    public $called_at;
    public $served_at;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAllRecords() {
        $query = "SELECT t.*, s.name as service_name, c.name as counter_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN services s ON t.service_id = s.id
                  LEFT JOIN counters c ON t.counter_id = c.id
                  ORDER BY t.issued_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readRecordsByCounters($counterIds) {
        if (empty($counterIds)) return false;
        $placeholders = str_repeat('?,', count($counterIds) - 1) . '?';
        $query = "SELECT t.*, s.name as service_name, c.name as counter_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN services s ON t.service_id = s.id
                  LEFT JOIN counters c ON t.counter_id = c.id
                  WHERE t.counter_id IN ($placeholders)
                  ORDER BY t.issued_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute($counterIds);
        return $stmt;
    }
    public function getNextInLine($serviceIds) {
        if (empty($serviceIds)) return false;
        
        $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
        $query = "SELECT t.*, s.name as service_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN services s ON t.service_id = s.id
                  WHERE t.status = 'waiting' 
                  AND t.service_id IN ($placeholders)
                  ORDER BY t.issued_at ASC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($serviceIds);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCurrentTicket($counterId) {
        $query = "SELECT t.*, s.name as service_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN services s ON t.service_id = s.id
                  WHERE t.counter_id = ? 
                  AND t.status IN ('called', 'serving')
                  ORDER BY t.issued_at ASC LIMIT 1";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$counterId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($ticketId, $status, $counterId = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status";
        
        if ($status === 'called') {
            $query .= ", called_at = CURRENT_TIMESTAMP, counter_id = :counter_id";
        } elseif ($status === 'serving') {
            $query .= ", served_at = CURRENT_TIMESTAMP, counter_id = :counter_id";
        } elseif ($status === 'done' || $status === 'no-show') {
            $query .= ", counter_id = :counter_id";
        }
        
        $query .= " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $ticketId);
        
        if ($status !== 'waiting' && $counterId !== null) {
            $stmt->bindParam(':counter_id', $counterId);
        }
        
        return $stmt->execute();
    }

    public function getWaitingList($serviceIds) {
        if (empty($serviceIds)) return [];
        
        $placeholders = str_repeat('?,', count($serviceIds) - 1) . '?';
        $query = "SELECT t.*, s.name as service_name 
                  FROM " . $this->table_name . " t
                  LEFT JOIN services s ON t.service_id = s.id
                  WHERE t.status = 'waiting' 
                  AND t.service_id IN ($placeholders)
                  ORDER BY t.issued_at ASC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute($serviceIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createTicket($name, $service_id, $citizen_category) {
        $today = date('Y-m-d');
        
        $queryNum = "SELECT ticket_number FROM " . $this->table_name . " 
                     WHERE service_id = ? AND DATE(created_at) = ? 
                     ORDER BY id DESC LIMIT 1";
        $stmtNum = $this->conn->prepare($queryNum);
        $stmtNum->execute([$service_id, $today]);
        $lastTicket = $stmtNum->fetch(PDO::FETCH_ASSOC);
        
        $nextNum = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket['ticket_number']);
            $nextNum = intval(end($parts)) + 1;
        }
        
        // Get service name for prefix
        $queryService = "SELECT name FROM services WHERE id = ?";
        $stmtService = $this->conn->prepare($queryService);
        $stmtService->execute([$service_id]);
        $service = $stmtService->fetch(PDO::FETCH_ASSOC);
        $prefix = $service && !empty($service['name']) ? substr($service['name'], 0, 1) : 'T';
        
        $ticket_number = strtoupper($prefix) . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, ticket_number, service_id, citizen_category, status, issued_at) 
                  VALUES (?, ?, ?, ?, 'waiting', CURRENT_TIMESTAMP)";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([$name, $ticket_number, $service_id, $citizen_category])) {
            return $ticket_number;
        }
        return false;
    }
}
?>
