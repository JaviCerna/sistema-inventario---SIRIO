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
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md p-6">
    <div class="bg-white rounded-2xl shadow-lg p-6">

      <!-- LOGO -->
      <div class="flex justify-center mb-4">
        <img src="assets/sirio-logo.png" alt="Logo Sirio" class="h-20 w-auto">
      </div>

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
          <div class="relative">
            <input id="password" required name="password" type="password" placeholder="••••••••" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 pr-10" />
            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
              <!-- Icono ojo -->
              <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 
                     4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="w-full bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700 transition">Entrar</button>
      </form>

      <div class="mt-6 text-center text-sm text-slate-500">
        ¿No tienes cuenta? Contacta al administrador.
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const password = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      if (password.type === 'password') {
        password.type = 'text';
        eyeIcon.innerHTML = `
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7
               0.524-1.67 1.42-3.165 2.625-4.362m3.27-2.126A9.956 9.956 0 0112 5c4.477 0 
               8.268 2.943 9.542 7a9.973 9.973 0 01-4.043 5.411M15 12a3 3 0 
               11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 3l18 18" />
        `;
      } else {
        password.type = 'password';
        eyeIcon.innerHTML = `
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 
               8.268 2.943 9.542 7-1.274 
               4.057-5.065 7-9.542 7-4.477 
               0-8.268-2.943-9.542-7z" />
        `;
      }
    }
  </script>
</body>
</html>
