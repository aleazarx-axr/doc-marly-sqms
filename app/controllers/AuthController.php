<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../auth/session.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function login($username, $password) {
        Session::start();

        if (empty($username) || empty($password)) {
            Session::set('error', 'Username and password are required.');
            return false;
        }

        if ($this->user->findByUsername($username)) {
            if (password_verify($password, $this->user->password)) {
                Session::set('user_id', $this->user->id);
                Session::set('username', $this->user->username);
                Session::set('role', $this->user->role);
                return true;
            }
        }
        
        Session::set('error', 'Invalid username or password.');
        return false;
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->login($username, $password)) {
                $role = Session::get('role');
                if ($role === 'admin') {
                    header("Location: /admin/dashboard");
                } elseif ($role === 'staff') {
                    header("Location: /user/dashboard");
                } else {
                    header("Location: /");
                }
                exit();
            } else {
                header("Location: /");
                exit();
            }
        }
        header("Location: /");
        exit();
    }

    public function logout() {
        Session::destroy();
        header("Location: /");
        exit();
    }
}
?>
