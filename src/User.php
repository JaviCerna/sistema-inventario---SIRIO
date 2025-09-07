<?php
// src/User.php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($email, $password, $full_name = null, $role = 'employee') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (email, password, full_name, role) VALUES (:email, :password, :full_name, :role)");
        $stmt->execute([
            'email' => $email,
            'password' => $hash,
            'full_name' => $full_name,
            'role' => $role
        ]);
        return $this->db->lastInsertId();
    }

    // password reset token creation
    public function createPasswordResetToken($user_id, $token, $expires_at) {
        $stmt = $this->db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
        $stmt->execute([
            'user_id'=>$user_id,
            'token'=>$token,
            'expires_at'=>$expires_at
        ]);
        return $this->db->lastInsertId();
    }

    public function validateResetToken($token) {
        $stmt = $this->db->prepare("SELECT pr.*, u.email FROM password_resets pr JOIN users u ON u.id = pr.user_id WHERE pr.token = :token AND pr.used = 0 LIMIT 1");
        $stmt->execute(['token'=>$token]);
        $row = $stmt->fetch();
        if(!$row) return false;
        if(strtotime($row['expires_at']) < time()) return false;
        return $row;
    }

    public function markTokenUsed($id) {
        $stmt = $this->db->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
        $stmt->execute(['id'=>$id]);
    }

    public function updatePassword($user_id, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute(['password'=>$hash,'id'=>$user_id]);
    }
}
