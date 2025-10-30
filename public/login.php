<?php
// public/login.php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Config.php';

$auth = new Auth();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST,'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if(!$email) {
        header('Location: index.php?error=' . urlencode('Correo inválido.'));
        exit;
    }

    $user = $auth->attempt($email, $password);
    if($user) {
        // Redirigir según rol
        if($user['role'] === 'admin') {
            header('Location: dashboard.php');
            exit;
        } else {
            header('Location: dashboard.php');
            exit;
        }
    } else {
        header('Location: index.php?error=' . urlencode('Credenciales incorrectas.'));
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
