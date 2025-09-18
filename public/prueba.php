<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Menú lateral desplegable - Tailwind</title>
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
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
            Mi Aplicación
          </span>
        </div>

        <!-- Botón colapsar -->
        <button id="toggleBtn" type="button"
          class="ml-auto inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 hover:bg-gray-50 focus:outline-none"
          aria-label="Contraer/Expandir menú">
          <!-- Icono hamburguesa -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2">
            <path d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>

      <!-- Navegación -->
      <nav class="flex-1 py-3">
        <ul class="px-2 space-y-1">
          <!-- Inicio -->
          <li>
            <a href="#" data-key="inicio"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Inicio">
              <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 12l9-9 9 9M4 10v10h16V10"/>
              </svg>
              <span
                class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                Inicio
              </span>
            </a>
          </li>

          <!-- Tablero -->
          <li>
            <a href="#" data-key="tablero"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Tablero">
              <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>
              </svg>
              <span
                class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                Tablero
              </span>
            </a>
          </li>

          <!-- Proyectos -->
          <li>
            <a href="#" data-key="proyectos"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Proyectos">
              <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M7 7h10v10H7z"/><path d="M3 3h18v4H3zM3 17h18v4H3z"/>
              </svg>
              <span
                class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                Proyectos
              </span>
            </a>
          </li>

          <!-- Reportes -->
          <li>
            <a href="#" data-key="reportes"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Reportes">
              <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M9 17v-6h13M9 7h13"/><path d="M3 3h4v18H3z"/>
              </svg>
              <span
                class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                Reportes
              </span>
            </a>
          </li>

          <!-- Ajustes -->
          <li>
            <a href="#" data-key="ajustes"
               class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100 transition-colors
                      data-[active=true]:bg-gray-900 data-[active=true]:text-white"
               title="Ajustes">
              <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M12 15a3 3 0 100-6 3 3 0 000 6z"/>
                <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V22a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06A2 2 0 013 17.88l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H1a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06A2 2 0 014.12 3l.06.06a1.65 1.65 0 001.82.33h.02A1.65 1.65 0 008 1.88V2a2 2 0 014 0v.09c0 .66.39 1.26 1 1.51h.02a1.65 1.65 0 001.82-.33l.06-.06A2 2 0 0120 6.12l-.06.06c-.46.46-.6 1.15-.33 1.82v.02c.25.61.85 1 1.51 1H22a2 2 0 010 4h-.09c-.66 0-1.26.39-1.51 1z"/>
              </svg>
              <span
                class="ml-3 truncate transition-all duration-300 origin-left
                       group-data-[collapsed=true]/sidebar:opacity-0 group-data-[collapsed=true]/sidebar:scale-x-0 group-data-[collapsed=true]/sidebar:w-0">
                Ajustes
              </span>
            </a>
          </li>
        </ul>
      </nav>

      <!-- Pie del sidebar -->
      <div class="mt-auto p-3 border-t border-gray-100">
        <button class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
          Cerrar sesión
        </button>
      </div>
    </aside>

    <!-- Contenido principal -->
    <main class="flex-1">
      <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4">
        <h1 class="font-semibold">Dashboard</h1>
      </header>

      <section class="p-6">
        <h2 class="text-xl font-semibold mb-2">Contenido</h2>
        <p class="text-gray-600">
          Contrae el menú con el botón de la barra lateral. Haz clic en los ítems para ver el sombreado negro.
        </p>
        <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          <div class="rounded-2xl bg-white p-4 shadow-sm border border-gray-100">Tarjeta 1</div>
          <div class="rounded-2xl bg-white p-4 shadow-sm border border-gray-100">Tarjeta 2</div>
          <div class="rounded-2xl bg-white p-4 shadow-sm border border-gray-100">Tarjeta 3</div>
        </div>
      </section>
    </main>
  </div>

  <script>
    // --- Colapsar sidebar ---
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    // Restaurar estado
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved === 'true') {
      sidebar.setAttribute('data-collapsed', 'true');
      sidebar.setAttribute('aria-expanded', 'false');
    }

    toggleBtn.addEventListener('click', () => {
      const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
      sidebar.setAttribute('data-collapsed', String(!isCollapsed));
      sidebar.setAttribute('aria-expanded', String(isCollapsed));
      localStorage.setItem('sidebar-collapsed', String(!isCollapsed));
    });

    // --- Selección activa ---
    const menuLinks = document.querySelectorAll('#sidebar nav a');
    const activeKeyStored = localStorage.getItem('sidebar-active-key');
    if (activeKeyStored) {
      const found = Array.from(menuLinks).find(a => a.dataset.key === activeKeyStored);
      if (found) {
        menuLinks.forEach(a => a.removeAttribute('data-active'));
        found.setAttribute('data-active', 'true');
      }
    } else if (menuLinks.length) {
      menuLinks[0].setAttribute('data-active', 'true');
      localStorage.setItem('sidebar-active-key', menuLinks[0].dataset.key || '');
    }

    menuLinks.forEach(link => {
      link.addEventListener('click', (e) => {
        menuLinks.forEach(a => a.removeAttribute('data-active'));
        link.setAttribute('data-active', 'true');
        localStorage.setItem('sidebar-active-key', link.dataset.key || '');
      });
    });
  </script>
</body>
</html>
