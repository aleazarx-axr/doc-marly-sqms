<?php

function maskEmail($email) {
    if (empty($email)) return '';
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    
    $name = $parts[0];
    $domain = $parts[1];
    
    $len = strlen($name);
    if ($len <= 2) {
        $maskedName = substr($name, 0, 1) . str_repeat('*', $len > 1 ? $len - 1 : 1);
    } else {
        $maskedName = substr($name, 0, 1) . str_repeat('*', $len - 2) . substr($name, -1);
    }
    
    return $maskedName . '@' . $domain;
}

function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
    return true;
}

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // True if HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
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

    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy() {
        self::start();
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: /login.php"); 
            exit();
        }

        // Session timeout logic (30 minutes = 1800 seconds)
        $timeout_duration = 1800;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            self::destroy();
            header("Location: /login.php?error=timeout");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }

    public static function requireRole($role) {
        self::requireLogin();
        
        if (self::get('role') !== $role) {
            header("Location: /index.php");
            exit();
        }
    }
}
?>
