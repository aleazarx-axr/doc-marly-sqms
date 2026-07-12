<?php

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    public static function destroy() {
        self::start();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            $depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
            $prefix = str_repeat('../', max(0, $depth - 1));
            header("Location: " . $prefix . "index.php"); 
            exit();
        }
    }

    public static function requireRole($role) {
        self::requireLogin();
        
        if (self::get('role') !== $role) {
            // Adjust the redirect path
            $depth = substr_count($_SERVER['PHP_SELF'], '/') - 1;
            $prefix = str_repeat('../', max(0, $depth - 1));
            
            // Redirect to their respective dashboard if they try to access wrong portal
            $userRole = self::get('role');
            if ($userRole === 'admin') {
                header("Location: " . $prefix . "admin/dashboard.php");
            } elseif ($userRole === 'staff') {
                header("Location: " . $prefix . "staff/dashboard.php");
            } else {
                header("Location: " . $prefix . "dashboard.php");
            }
            exit();
        }
    }
}
?>
