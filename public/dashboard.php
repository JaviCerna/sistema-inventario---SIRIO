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
<body class="bg-slate-100 text-gray-800">
  <div class="min-h-screen flex">
    <!-- Sidebar -->
    <aside id="sidebar"
      class="group/sidebar flex flex-col bg-white border-r border-gray-200 w-64 transition-all duration-300 data-[collapsed=true]:w-16"
      data-collapsed="false" aria-expanded="true">
      
      <!-- Header del sidebar -->
      <div class="flex items-center h-16 px-4 border-b border-gray-100">
        <div class="flex items-center gap-2 overflow-hidden">
          <!-- Logo -->
          <div class="h-8 w-8 rounded-xl bg-gray-900"></div>
          <span
            class="font-semibold truncate transition-all duration-300 origin-left
                   group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
            SIRIO S.A. DE C.V.
          </span>
        </div>

        <!-- Botón colapsar -->
        <button id="toggleBtn" type="button"
          class="ml-auto inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 hover:bg-gray-50 focus:outline-none"
          aria-label="Contraer/Expandir menú">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>

      <!-- Navegación -->
      <nav class="flex-1 py-3">
        <ul class="px-2 space-y-1">
          <!-- Dashboard -->
          <li>
            <a href="dashboard.php" data-key="inicio"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Dashboard">
              <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 12l9-9 9 9M4 10v10h16V10"/>
              </svg>
              <span class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                Dashboard
              </span>
            </a>
          </li>

          <!-- Categorías (con submenú y navegación) -->
          <li>
            <div class="relative">
              <!-- Enlace principal: navega a categorias.php -->
              <a href="categorias.php" data-key="categorias"
                 class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                        data-[active=true]:bg-gray-900 data-[active=true]:text-white"
                 title="Categorías">
                <!-- Icono carpeta -->
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path d="M3 7h5l2 2h11v10a2 2 0 01-2 2H3z"/>
                </svg>
                <span class="ml-3 truncate transition-all duration-300 origin-left
                             group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                  Categorías
                </span>
              </a>

              <!-- Botón chevron: abre/cierra el submenú sin navegar -->
              <button type="button" id="toggle-categorias"
                      class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded hover:bg-gray-50
                             group-data-[collapsed=true]/sidebar:hidden"
                      aria-controls="submenu-categorias" aria-expanded="false" title="Mostrar submenú">
                <svg id="chevron-categorias" class="h-4 w-4 transition-transform duration-200"
                     xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path d="M6 9l6 6 6-6"/>
                </svg>
              </button>
            </div>

            <!-- Submenú -->
            <ul id="submenu-categorias"
                class="mt-1 ml-9 pr-2 hidden space-y-1
                       group-data-[collapsed=true]/sidebar:hidden">
              <li>
                <a href="categorias.php" data-key="sub-categorias"
                   class="block rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-100
                          data-[active=true]:bg-gray-900 data-[active=true]:text-white">
                  Categorías
                </a>
              </li>
              <li>
                <a href="subcategorias.php" data-key="sub-subcategorias"
                   class="block rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-100
                          data-[active=true]:bg-gray-900 data-[active=true]:text-white">
                  Subcategorías
                </a>
              </li>
            </ul>
          </li>

          <!-- Bodegas -->
          <li>
            <a href="#" data-key="Bodegas"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Bodegas">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                   class="h-5 w-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3 19.5V8.25l9-5.25 9 5.25V19.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 19.5zm3-.75h12m-6-6v6" />
              </svg>
              <span class="ml-3 truncate transition-all duration-300 origin-left
                           group-data-[collapsed=true]/sidebar:opacity-0 
                           group-data-[collapsed=true]/sidebar:scale-x-0 
                           group-data-[collapsed=true]/sidebar:w-0">
                Bodegas
              </span>
            </a>
          </li>

          <!-- Inventario -->
          <li>
            <a href="#" data-key="Inventario"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Inventario">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                   class="h-5 w-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M20.25 7.5l-.45 9a2.25 2.25 0 01-2.25 2.25h-11.1a2.25 2.25 0 01-2.25-2.25l-.45-9m16.5 0H3.75m16.5 0L18 3.75H6L3.75 7.5m4.5 4.5h7.5" />
              </svg>
              <span class="ml-3 truncate transition-all duration-300 origin-left
                           group-data-[collapsed=true]/sidebar:opacity-0 
                           group-data-[collapsed=true]/sidebar:scale-x-0 
                           group-data-[collapsed=true]/sidebar:w-0">
                Inventario
              </span>
            </a>
          </li>
        </ul>
      </nav>

      <!-- Pie del sidebar -->
      <div class="mt-auto p-3 border-t border-gray-100">
        <a href="logout.php"
           class="group flex items-center justify-center md:justify-start rounded-xl border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50"
           data-key="logout" title="Cerrar sesión">
          <!-- Icono logout -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0l-4-4m4 4l-4 4m9-7v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h7"/>
          </svg>
          <!-- Texto (oculto en colapsado) -->
          <span class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
            Cerrar sesión
          </span>
        </a>
      </div>
    </aside>

    <!-- Contenido principal -->
    <main class="flex-1">
      <!-- Topbar -->
      <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4 justify-between">
        <h1 class="font-semibold">Sistema Inventario</h1>
        <div class="flex items-center gap-4">
          <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
        </div>
      </header>

      <!-- Contenido -->
      <section class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-white p-4 rounded shadow">
            <h3 class="font-medium text-slate-700">Panel</h3>
            <p class="text-sm text-slate-500 mt-2">Rol: <strong><?=htmlspecialchars($user['role'])?></strong></p>
          </div>

          <div class="bg-white p-4 rounded shadow md:col-span-2">
            <h3 class="font-medium text-slate-700">Bienvenido</h3>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // --- Colapsar sidebar ---
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved === 'true') {
      sidebar.setAttribute('data-collapsed', 'true');
      sidebar.setAttribute('aria-expanded', 'false');
    }

    toggleBtn?.addEventListener('click', () => {
      const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
      sidebar.setAttribute('data-collapsed', String(!isCollapsed));
      sidebar.setAttribute('aria-expanded', String(isCollapsed));
      localStorage.setItem('sidebar-collapsed', String(!isCollapsed));
    });

    // --- Selección activa ---
    const menuLinks = document.querySelectorAll('#sidebar nav a, #sidebar div a');
    const activeKeyStored = localStorage.getItem('sidebar-active-key');

    if (activeKeyStored) {
      const found = Array.from(menuLinks).find(a => a.dataset.key === activeKeyStored);
      if (found) {
        menuLinks.forEach(a => a.removeAttribute('data-active'));
        found.setAttribute('data-active', 'true');
      }
    } else if (menuLinks.length) {
      const def = Array.from(menuLinks).find(a => a.dataset.key === 'inicio') || menuLinks[0];
      def.setAttribute('data-active', 'true');
      localStorage.setItem('sidebar-active-key', def.dataset.key || '');
    }

    menuLinks.forEach(link => {
      link.addEventListener('click', () => {
        menuLinks.forEach(a => a.removeAttribute('data-active'));
        link.setAttribute('data-active', 'true');
        localStorage.setItem('sidebar-active-key', link.dataset.key || '');
      });
    });

    // --- Submenú Categorías (chevron independiente) ---
    const toggleCategorias = document.getElementById('toggle-categorias');
    const submenuCategorias = document.getElementById('submenu-categorias');
    const chevronCategorias = document.getElementById('chevron-categorias');

    // Restaurar estado abierto/cerrado
    const openStored = localStorage.getItem('sidebar-open-categorias');
    if (openStored === 'true') {
      submenuCategorias?.classList.remove('hidden');
      toggleCategorias?.setAttribute('aria-expanded', 'true');
      if (chevronCategorias) chevronCategorias.style.transform = 'rotate(180deg)';
    }

    toggleCategorias?.addEventListener('click', (e) => {
      e.preventDefault(); // evita navegación (es botón)
      const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
      if (isCollapsed) return;

      const isOpen = toggleCategorias.getAttribute('aria-expanded') === 'true';
      toggleCategorias.setAttribute('aria-expanded', String(!isOpen));
      submenuCategorias.classList.toggle('hidden');
      if (chevronCategorias) chevronCategorias.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
      localStorage.setItem('sidebar-open-categorias', String(!isOpen));
    });
  </script>
</body>
</html>
