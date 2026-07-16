<?php

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $username;
    public $password;
    public $email;
    public $role;
    public $status;
    public $failed_attempts;
    public $locked_until;
    public $setup_token;
    public $token_expires;
    public $otp_code;
    public $otp_expires;
    public function __construct($db) {
        $this->conn = $db;
    }

    public function findByUsername($username) {
        $query = "SELECT id, name, username, email, password, role, status, failed_attempts, locked_until FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->status = $row['status'];
            $this->failed_attempts = $row['failed_attempts'];
            $this->locked_until = $row['locked_until'];

            return true;
        }

        return false;
    }

    public function findById($id) {
        $query = "SELECT id, name, username, email, password, role, status, failed_attempts, locked_until, setup_token, token_expires, otp_code, otp_expires FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $id = htmlspecialchars(strip_tags($id));
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row['id'] ?? null;
            $this->name = $row['name'] ?? null;
            $this->username = $row['username'] ?? null;
            $this->email = $row['email'] ?? null;
            $this->password = $row['password'] ?? null;
            $this->role = $row['role'] ?? null;
            $this->status = $row['status'] ?? null;
            $this->failed_attempts = $row['failed_attempts'] ?? 0;
            $this->locked_until = $row['locked_until'] ?? null;
            $this->setup_token = $row['setup_token'] ?? null;
            $this->token_expires = $row['token_expires'] ?? null;
            $this->otp_code = $row['otp_code'] ?? null;
            $this->otp_expires = $row['otp_expires'] ?? null;

            return true;
        }

        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, username=:username, email=:email, password=:password, role=:role, setup_token=:setup_token, token_expires=:token_expires";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        // Password should already be hashed before calling create

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":setup_token", $this->setup_token);
        $stmt->bindParam(":token_expires", $this->token_expires);

        return $stmt->execute();
    }

    public function read() {
        $query = "SELECT id, name, username, email, role, status, created_at, setup_token, token_expires FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function update() {
        // We only update name, role, email, and potentially password.
        if (!empty($this->password)) {
            $query = "UPDATE " . $this->table_name . " SET name=:name, username=:username, email=:email, role=:role, password=:password WHERE id=:id";
        } else {
            $query = "UPDATE " . $this->table_name . " SET name=:name, username=:username, email=:email, role=:role WHERE id=:id";
        }

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $this->id);

        if (!empty($this->password)) {
            $stmt->bindParam(":password", $this->password);
        }

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        
        return $stmt->execute();
    }

    public function isLocked() {
        if ($this->locked_until && strtotime($this->locked_until) > time()) {
            return true;
        }
        return false;
    }

    public function incrementFailedAttempts() {
        $query = "UPDATE " . $this->table_name . " SET failed_attempts = failed_attempts + 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        
        $this->failed_attempts++;
    }

    public function lockAccount($minutes = 15) {
        $query = "UPDATE " . $this->table_name . " SET locked_until = DATE_ADD(NOW(), INTERVAL :minutes MINUTE) WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":minutes", $minutes, PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function resetFailedAttempts() {
        $query = "UPDATE " . $this->table_name . " SET failed_attempts = 0, locked_until = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    public function logAuthEvent($event_type) {
        $query = "INSERT INTO auth_logs (user_id, username, ip_address, user_agent, event_type) VALUES (:user_id, :username, :ip_address, :user_agent, :event_type)";
        $stmt = $this->conn->prepare($query);
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        
        $stmt->bindParam(":user_id", $this->id);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":ip_address", $ip_address);
        $stmt->bindParam(":user_agent", $user_agent);
        $stmt->bindParam(":event_type", $event_type);
        
        $stmt->execute();
    }

    public function generateUsername($name) {
        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', str_replace(' ', '.', trim($name))));
        $username = $baseUsername;
        $counter = 1;
        
        while ($this->usernameExists($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    private function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function findByToken($token) {
        $query = "SELECT id, username, email, name FROM " . $this->table_name . " WHERE setup_token = :token AND token_expires > NOW() LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->name = $row['name'];
            return true;
        }
        return false;
    }

    public function updatePasswordAndClearToken($password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password, setup_token = NULL, token_expires = NULL, status = 'active' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $hashed_password);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function archive() {
        $query = "UPDATE " . $this->table_name . " SET status = 'archived' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function restore() {
        $query = "UPDATE " . $this->table_name . " SET status = 'active' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function generateNewSetupToken() {
        $this->setup_token = bin2hex(random_bytes(32));
        $this->token_expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $query = "UPDATE " . $this->table_name . " SET setup_token = :token, token_expires = :expires WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":token", $this->setup_token);
        $stmt->bindParam(":expires", $this->token_expires);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }

    public function generateOTP() {
        // Generate a 6-digit code
        $this->otp_code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->otp_expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        
        $query = "UPDATE " . $this->table_name . " SET otp_code = :code, otp_expires = :expires WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":code", $this->otp_code);
        $stmt->bindParam(":expires", $this->otp_expires);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return $this->otp_code;
        }
        return false;
    }

    public function verifyOTP($code) {
        if (empty($this->otp_code) || empty($this->otp_expires)) {
            return false;
        }
        
        if ($this->otp_code !== $code) {
            return false;
        }
        
        if (strtotime($this->otp_expires) < time()) {
            return false;
        }
        
        return true;
    }

    public function clearOTP() {
        $this->otp_code = null;
        $this->otp_expires = null;
        
        $query = "UPDATE " . $this->table_name . " SET otp_code = NULL, otp_expires = NULL WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>
