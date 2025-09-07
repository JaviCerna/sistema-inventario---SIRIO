<?php
// public/dashboard.php
require_once __DIR__ . '/../src/Auth.php';
$auth = new Auth();
if(!$auth->check()) {
    header('Location: index.php?error=' . urlencode('Por favor inicia sesión.'));
    exit;
}
$user = $auth->user();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard - Inventario</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
  <nav class="bg-white shadow p-4 flex justify-between items-center">
    <div class="text-lg font-semibold">Sistema Inventario</div>
    <div class="flex items-center gap-4">
      <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
      <a href="logout.php" class="px-3 py-1 bg-red-500 text-white rounded">Cerrar sesión</a>
    </div>
  </nav>

  <main class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded shadow">
        <h3 class="font-medium text-slate-700">Panel</h3>
        <p class="text-sm text-slate-500 mt-2">Rol: <strong><?=htmlspecialchars($user['role'])?></strong></p>
      </div>

      <div class="bg-white p-4 rounded shadow md:col-span-2">
        <h3 class="font-medium text-slate-700">Bienvenido</h3>
      </div>
    </div>
  </main>
</body>
</html>
