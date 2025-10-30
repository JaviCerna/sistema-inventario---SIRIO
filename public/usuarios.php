<?php
// public/usuarios.php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Config.php';

$auth = new Auth();
if(!$auth->check()) {
  header('Location: index.php?error=' . urlencode('Por favor inicia sesión.'));
  exit;
}
$user = $auth->user(); // $user debe estar disponible para 'sidebar.php'
// Seguridad: solo administradores pueden acceder a esta vista
if ($user['role'] !== 'admin') {
    header('Location: inventario.php?error=' . urlencode('Acceso denegado. Se requiere rol de administrador.'));
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Usuarios</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 text-gray-800">
  <div class="min-h-screen flex">
    
    <?php 
      // Llama al archivo del menú lateral
      require_once __DIR__ . '/../views/sidebar.php'; 
    ?>

    <main class="flex-1 flex flex-col min-h-0">
      <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4 justify-between">
        <h1 class="font-semibold">Usuarios</h1>
        <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
      </header>

      <section class="p-4 md:p-6 flex-1 flex min-h-0">
        <div class="w-full flex-1 flex min-h-0">
          <div class="bg-white p-4 md:p-6 rounded-2xl shadow flex-1 flex flex-col min-h-0">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-800">Gestión de Usuarios</h2>
                <p class="text-sm text-slate-500">Crea, edita y gestiona accesos al sistema.</p>
              </div>
              <div class="flex items-center gap-2">
                <div class="relative">
                  <input id="user-search" placeholder="Buscar (Nombre o email)..." class="w-64 rounded-xl border px-10 py-2 outline-none focus:ring" />
                  <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </div>
                <button id="btn-user-add" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                  Nuevo Usuario
                </button>
                <button id="btn-user-pdf"
  class="inline-flex items-center gap-2 rounded-xl border border-slate-300
         px-4 py-2 bg-white text-slate-700 hover:bg-slate-50
         active:translate-y-px focus:outline-none focus:ring-2 focus:ring-slate-300"
  title="Exportar PDF del listado general">
  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7m3-4l4-4 4 4m-4-4v12"/>
  </svg>
  PDF
</button>
              </div>
            </div>

            <div class="mt-4 md:mt-6 overflow-auto flex-1 min-h-0">
              <table class="min-w-full text-sm">
                <thead class="sticky top-0 bg-white z-10">
                  <tr class="text-left text-slate-500 border-b">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">Nombre Completo</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Rol</th>
                    <th class="px-3 py-2">Creado</th>
                    <th class="px-3 py-2">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tbl-body-user"></tbody>
              </table>
            </div>

            <div id="user-pagination-container" class="mt-4 pt-4 border-t flex flex-col sm:flex-row items-center justify-between gap-3">
              <div id="user-page-info" class="text-sm text-slate-600"></div>
              <div class="flex items-center gap-4">
                 <label for="user-limit-selector" class="text-sm text-slate-600">Mostrar:</label>
                 <select id="user-limit-selector" class="rounded-lg border border-slate-300 py-1.5 px-3 text-sm focus:ring-sky-500 focus:border-sky-500">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                 </select>
                 
                 <div id="user-pagination-controls" class="inline-flex items-center space-x-1">
                    </div>
              </div>
            </div>
            <div class="mt-12 p-4 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-sm text-center">
                        © 2025 SIRIO S.A. DE C.V. Todos los derechos reservados.
                    </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <?php require_once __DIR__ . '/../views/usuarios_modals.php'; ?>

  <script>window.API_BASE = '../src/UserController.php';</script>
  <script src="assets/js/users.js"></script>

  <script>
    // Toggle sidebar (mantener la lógica de la barra lateral)
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved === 'true') { sidebar.setAttribute('data-collapsed', 'true'); sidebar.setAttribute('aria-expanded', 'false'); }
    toggleBtn?.addEventListener('click', () => {
      const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
      sidebar.setAttribute('data-collapsed', String(!isCollapsed));
      sidebar.setAttribute('aria-expanded', String(isCollapsed));
      localStorage.setItem('sidebar-collapsed', String(!isCollapsed));
    });
  </script>
</body>
</html>