<?php
// public/reset.php
require_once __DIR__ . '/../src/User.php';

$userModel = new User();
$token = $_GET['token'] ?? null;
$info = null;
$error = '';
$success = '';

if(!$token) {
    $error = 'Token inválido.';
} else {
    $info = $userModel->validateResetToken($token);
    if(!$info) {
        $error = 'Enlace inválido o expirado.';
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && $info) {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if(strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $userModel->updatePassword($info['user_id'], $password);
        $userModel->markTokenUsed($info['id']);
        $success = 'Contraseña actualizada. Ahora puedes iniciar sesión.';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Restablecer contraseña</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center">
  <div class="w-full max-w-md p-6">
    <div class="bg-white p-6 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">Restablecer contraseña</h2>

      <?php if($error): ?>
        <div class="bg-red-50 text-red-700 p-3 rounded mb-4"><?=$error?></div>
      <?php endif; ?>
      <?php if($success): ?>
        <div class="bg-green-50 text-green-700 p-3 rounded mb-4"><?=$success?></div>
        <a href="index.php" class="text-indigo-600 hover:underline">Ir al login</a>
      <?php elseif($info): ?>
        <form method="post" class="space-y-4">
          <div>
            <label class="block text-sm mb-1">Nueva contraseña</label>
            <input name="password" type="password" required class="w-full border px-3 py-2 rounded" />
          </div>
          <div>
            <label class="block text-sm mb-1">Repetir contraseña</label>
            <input name="password2" type="password" required class="w-full border px-3 py-2 rounded" />
          </div>
          <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded">Guardar nueva contraseña</button>
        </form>
      <?php endif; ?>

    </div>
  </div>
</body>
</html>
