<?php
// src/Auth.php
require_once __DIR__ . '/User.php';

class Auth {
    private $userModel;

    public function __construct() {
        if(session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    public function attempt($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if(!$user) return false;
        if(password_verify($password, $user['password'])) {
            // Regenerar id de sesión por seguridad
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            return $user;
        }
        return false;
    }

    public function check() {
        return isset($_SESSION['user_id']);
    }

    public function user() {
        if($this->check()) {
            return $this->userModel->findById($_SESSION['user_id']);
        }
        return null;
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
