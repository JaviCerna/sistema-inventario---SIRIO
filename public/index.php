<?php
// public/index.php
require_once __DIR__ . '/../src/Config.php';
session_start();
if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Sistema Inventario</title>
  <!-- Tailwind CDN (rápido para prototipo) -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md p-6">
    <div class="bg-white rounded-2xl shadow-lg p-6">
      <h1 class="text-2xl font-semibold mb-4 text-center">Iniciar sesión</h1>
      <?php if(isset($_GET['error'])): ?>
          <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?=htmlspecialchars($_GET['error'])?></div>
      <?php endif; ?>
      <form action="login.php" method="post" class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Correo</label>
          <input required name="email" type="email" placeholder="tu@correo.com" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Contraseña</label>
          <input required name="password" type="password" placeholder="••••••••" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" />
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">Entrar</button>
      </form>

      <div class="mt-6 text-center text-sm text-slate-500">
        ¿No tienes cuenta? Contacta al administrador.
      </div>
    </div>
  </div>
</body>
</html>
