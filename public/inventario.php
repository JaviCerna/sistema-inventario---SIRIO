<?php
// public/inventario.php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Config.php';

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
  <title>Inventario</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-gray-800">
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside id="sidebar"
      class="group/sidebar flex flex-col bg-white border-r border-gray-200 w-64 transition-all duration-300 data-[collapsed=true]:w-16"
      data-collapsed="false" aria-expanded="true">
      <div class="flex items-center h-16 px-4 border-b border-gray-100">
        <div class="flex items-center gap-2 overflow-hidden">
        <img src="assets/sirio-logo.png" alt="Logo Sirio" class="h-8 w-auto">
          <span class="font-semibold truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0">
            SIRIO S.A. DE C.V.
          </span>
        </div>
        <button id="toggleBtn" type="button"
          class="ml-auto inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
      </div>
      <nav class="flex-1 py-3">
        <ul class="px-2 space-y-1">
          <li><a href="dashboard.php" data-key="inicio"
                 class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l9-9 9 9M4 10v10h16V10"/></svg>
            <span class="ml-3 truncate">Dashboard</span></a>
          </li>
          <li><a href="categorias.php" data-key="categorias"
                 class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M3 7h5l2 2h11v10a2 2 0 01-2 2H3z"/></svg>
            <span class="ml-3 truncate">Categorías</span></a>
          </li>
          <li><a href="subcategorias.php" data-key="subcategorias"
                 class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
            <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M12 6l-2 2H3v10a2 2 0 002 2h14a2 2 0 002-2V8H12z"/></svg>
            <span class="ml-3 truncate">Subcategorías</span></a>
          </li>
          <li><a href="bodegas.php" data-key="bodegas"
                 class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5V8.25l9-5.25 9 5.25V19.5M6 18.75h12m-6-6v6"/>
            </svg>
            <span class="ml-3 truncate">Bodegas</span></a>
          </li>
          <li><a href="inventario.php" data-key="inventario"
                 class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium bg-gray-900 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.45 9a2.25 2.25 0 01-2.25 2.25H6.45A2.25 2.25 0 014.2 16.5l-.45-9M7.5 12h9m-9 4h9M6 6h12"/>
            </svg>
            <span class="ml-3 truncate">Inventario</span></a>
          </li>
        </ul>
      </nav>
      <div class="mt-auto p-3 border-t border-gray-100">
        <a href="logout.php" class="group flex items-center justify-center md:justify-start rounded-xl border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0l-4-4m4 4l-4 4m9-7v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h7"/></svg>
          <span class="ml-3 truncate">Cerrar sesión</span>
        </a>
      </div>
    </aside>

    <!-- Contenido principal -->
    <main class="flex-1 flex flex-col min-h-0">
      <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4 justify-between">
        <h1 class="font-semibold">Inventario</h1>
        <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
      </header>

      <!-- Contenido -->
      <section class="p-4 md:p-6 flex-1 flex min-h-0">
        <div class="w-full flex-1 flex min-h-0">
          <div class="bg-white p-4 md:p-6 rounded-2xl shadow flex-1 flex flex-col min-h-0">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-800">Productos</h2>
                <p class="text-sm text-slate-500">Crea, edita y gestiona existencias por bodega.</p>
              </div>
              <div class="flex items-center gap-2">
                <button id="btn-tallas" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 21h10M7 3h10M7 3l5 9 5-9"/></svg>
                  Tallas
                </button>
                <button id="btn-colores" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 3a9 9 0 00-9 9 3 3 0 003 3h2a1 1 0 010 2H6a5 5 0 010-10 9 9 0 019-4z"/></svg>
                  Colores
                </button>
                <div class="relative">
                  <input id="prod-search" placeholder="Buscar (SKU o nombre)..." class="w-64 rounded-xl border px-10 py-2 outline-none focus:ring" />
                  <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </div>
                <button id="btn-prod-add" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14m-7-7h14"/></svg>
                  Nuevo
                </button>
              </div>
            </div>

            <div class="mt-4 md:mt-6 overflow-auto flex-1 min-h-0">
              <table class="min-w-full text-sm">
                <thead class="sticky top-0 bg-white z-10">
                  <tr class="text-left text-slate-500 border-b">
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">SKU</th>
                    <th class="px-3 py-2">Producto</th>
                    <th class="px-3 py-2">Costo</th>
                    <th class="px-3 py-2">Existencias</th>
                    <th class="px-3 py-2">Estado</th>
                    <th class="px-3 py-2">Creado</th>
                    <th class="px-3 py-2">Actualizado</th>
                    <th class="px-3 py-2">Acciones</th>
                  </tr>
                </thead>
                <tbody id="tbl-body"></tbody>
              </table>
            </div>
          </div>
        </div>
      </section>
    </main>
  </div>

  <?php require_once __DIR__ . '/../views/inventario_modals.php'; ?>

  <!-- Ruta base del API para el JS -->
  <script>window.API_BASE = '../src/ProductController.php';</script>
  <script src="assets/js/inventario.js"></script>

  <script>
    // Toggle sidebar
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
