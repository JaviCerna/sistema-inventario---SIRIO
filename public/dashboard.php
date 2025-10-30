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
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 text-gray-800">
  <div class="min-h-screen flex">
    <!-- Sidebar (igual que antes) -->
    <?php 
      // Llama al archivo del menú lateral para no tener muchas líneas de código aquí
      require_once __DIR__ . '/../views/sidebar.php'; 
    ?>

    <!-- Contenido principal -->
    <main class="flex-1">
      <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4 justify-between">
        <h1 class="font-semibold">Sistema Inventario</h1>
        <div class="flex items-center gap-4">
          <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
        </div>
      </header>

      <section class="p-6 space-y-6">
        <!-- Filtros -->
        <form id="filters-form" class="bg-white rounded-2xl shadow p-4 grid grid-cols-1 md:grid-cols-6 gap-4">
  <!-- Desde -->
  <div class="col-span-1">
    <label class="block text-xs text-slate-500 mb-1">Desde</label>
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
        <!-- calendar -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v11a2 2 0 002 2z" />
        </svg>
      </span>
      <input type="date" name="desde"
             value="<?=htmlspecialchars(date('Y-m-01'))?>"
             class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2.5 text-sm
                    focus:outline-none focus:ring-4 focus:ring-slate-100 focus:border-slate-300" />
    </div>
  </div>

  <!-- Hasta -->
  <div class="col-span-1">
    <label class="block text-xs text-slate-500 mb-1">Hasta</label>
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
        <!-- calendar -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2z" />
        </svg>
      </span>
      <input type="date" name="hasta"
             value="<?=htmlspecialchars(date('Y-m-d'))?>"
             class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2.5 text-sm
                    focus:outline-none focus:ring-4 focus:ring-slate-100 focus:border-slate-300" />
    </div>
  </div>

  <!-- Bodega -->
  <div class="col-span-1 md:col-span-2">
    <label class="block text-xs text-slate-500 mb-1">Bodega</label>
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
        <!-- warehouse -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19.5V8.25l9-5.25 9 5.25V19.5a.75.75 0 01-.75.75H3.75A.75.75 0 013 19.5zm3-.75h12m-6-6v6" />
        </svg>
      </span>
      <select name="bodega"
              class="peer w-full appearance-none rounded-xl border border-slate-200 pl-9 pr-9 py-2.5 text-sm bg-white
                     focus:outline-none focus:ring-4 focus:ring-slate-100 focus:border-slate-300">
        <option value="">Todas</option>
        <!-- Se llena por JS -->
      </select>
      <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
        <!-- chevron -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 transition-transform duration-200 peer-focus:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
        </svg>
      </span>
    </div>
    <p class="mt-1 text-[11px] text-slate-400">Elige una bodega para acotar existencias y movimientos.</p>
  </div>

  <!-- Categoría -->
  <div class="col-span-1 md:col-span-2">
    <label class="block text-xs text-slate-500 mb-1">Categoría</label>
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
        <!-- folder -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h5l2 2h11v10a2 2 0 01-2 2H3z"/>
        </svg>
      </span>
      <select name="categoria"
              class="peer w-full appearance-none rounded-xl border border-slate-200 pl-9 pr-9 py-2.5 text-sm bg-white
                     focus:outline-none focus:ring-4 focus:ring-slate-100 focus:border-slate-300">
        <option value="">Todas</option>
        <!-- Se llena por JS -->
      </select>
      <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
        <!-- chevron -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 transition-transform duration-200 peer-focus:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6 6-6"/>
        </svg>
      </span>
    </div>
    <p class="mt-1 text-[11px] text-slate-400">Filtra conteos, gráficos y alertas por categoría.</p>
  </div>

  <!-- Umbral stock bajo -->
  <div class="col-span-1 md:col-span-2">
    <label class="block text-xs text-slate-500 mb-1">Umbral “stock bajo” (si mínimo=0)</label>
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center">
        <!-- gauge -->
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3a9 9 0 00-9 9h3a6 6 0 1112 0h3a9 9 0 00-9-9zm0 9l3 3"/>
        </svg>
      </span>
      <input type="number" step="0.001" name="low" value="10"
             class="w-full rounded-xl border border-slate-200 pl-9 pr-3 py-2.5 text-sm
                    focus:outline-none focus:ring-4 focus:ring-slate-100 focus:border-slate-300" />
    </div>
    <!-- Chips rápidos -->
    <div class="mt-2 flex flex-wrap gap-2">
      <button type="button" data-low="5"  class="chip-low px-2.5 py-1.5 text-xs rounded-full border border-slate-200 hover:bg-slate-50">5</button>
      <button type="button" data-low="10" class="chip-low px-2.5 py-1.5 text-xs rounded-full border border-slate-200 hover:bg-slate-50">10</button>
      <button type="button" data-low="20" class="chip-low px-2.5 py-1.5 text-xs rounded-full border border-slate-200 hover:bg-slate-50">20</button>
      <span class="text-[11px] text-slate-400">Click para autocompletar</span>
    </div>
  </div>

  <!-- Acciones -->
  <div class="col-span-1 md:col-span-4 flex items-end gap-2">
    <button class="px-4 py-2.5 rounded-xl bg-gray-900 text-white hover:bg-gray-800">Aplicar</button>
    <a href="dashboard.php" class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-gray-50">Limpiar</a>
  </div>
</form>


        <!-- KPIs -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-white rounded-2xl shadow p-4">
            <div class="text-xs text-slate-500">Categorías</div>
            <div id="kpi-categorias" class="text-3xl font-semibold mt-1">0</div>
          </div>
          <div class="bg-white rounded-2xl shadow p-4">
            <div class="text-xs text-slate-500">Subcategorías</div>
            <div id="kpi-subcategorias" class="text-3xl font-semibold mt-1">0</div>
          </div>
          <div class="bg-white rounded-2xl shadow p-4">
            <div class="text-xs text-slate-500">Bodegas</div>
            <div id="kpi-bodegas" class="text-3xl font-semibold mt-1">0</div>
          </div>
          <div class="bg-white rounded-2xl shadow p-4">
            <div class="text-xs text-slate-500">Productos activos</div>
            <div id="kpi-productos" class="text-3xl font-semibold mt-1">0</div>
          </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
          <div class="bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-medium text-slate-700">Existencias por bodega</h3>
            </div>
            <div style="height: 16rem;"><canvas id="chartBodega"></canvas></div>
          </div>

          <div class="bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-medium text-slate-700">Productos por categoría</h3>
            </div>
            <div style="height: 16rem;"><canvas id="chartCategoria"></canvas></div>
          </div>

          <div class="bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="font-medium text-slate-700">Ingresos (unidades) por día</h3>
            </div>
            <div style="height: 16rem;"><canvas id="chartSerie"></canvas></div>
          </div>
        </div>

        <!-- Tablas -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-medium text-slate-700">Stock bajo</h3>
              <span id="stock-rule" class="text-xs text-slate-500"></span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                  <tr>
                    <th class="py-2 pr-4">Producto (SKU)</th>
                    <th class="py-2 pr-4">Bodega</th>
                    <th class="py-2 pr-4 text-right">Cantidad</th>
                    <th class="py-2 pr-4 text-right">Mínimo</th>
                    <th class="py-2 pr-4">Estado</th>
                  </tr>
                </thead>
                <tbody id="tb-stock-body" class="divide-y divide-slate-100">
                  <tr><td colspan="5" class="py-3 text-slate-500">Cargando…</td></tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-medium text-slate-700">Últimos ingresos</h3>
              <span id="periodo-ingresos" class="text-xs text-slate-500"></span>
            </div>
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="text-left text-slate-500">
                  <tr>
                    <th class="py-2 pr-4">Fecha</th>
                    <th class="py-2 pr-4">Producto (SKU)</th>
                    <th class="py-2 pr-4">Bodega</th>
                    <th class="py-2 pr-4 text-right">Cant.</th>
                    <th class="py-2 pr-4 text-right">Costo U.</th>
                  </tr>
                </thead>
                <tbody id="tb-ingresos-body" class="divide-y divide-slate-100">
                  <tr><td colspan="5" class="py-3 text-slate-500">Cargando…</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
<div class="mt-12 p-4 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-sm text-center">
                        © 2025 SIRIO S.A. DE C.V. Todos los derechos reservados.
                    </div>
      </section>
      
    </main>
    
  </div>

  <script>
    // Sidebar: mismo comportamiento que tus módulos
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved === 'true') { sidebar.setAttribute('data-collapsed','true'); sidebar.setAttribute('aria-expanded','false'); }
    toggleBtn?.addEventListener('click', () => {
      const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
      sidebar.setAttribute('data-collapsed', String(!isCollapsed));
      sidebar.setAttribute('aria-expanded', String(isCollapsed));
      localStorage.setItem('sidebar-collapsed', String(!isCollapsed));
    });

    // Activo del menú
    const menuLinks = document.querySelectorAll('#sidebar nav a, #sidebar div a');
    const activeKeyStored = localStorage.getItem('sidebar-active-key');
    if (activeKeyStored) {
      const found = Array.from(menuLinks).find(a => a.dataset.key === activeKeyStored);
      if (found) { menuLinks.forEach(a => a.removeAttribute('data-active')); found.setAttribute('data-active','true'); }
    } else if (menuLinks.length) {
      const def = Array.from(menuLinks).find(a => a.dataset.key === 'inicio') || menuLinks[0];
      def.setAttribute('data-active','true'); localStorage.setItem('sidebar-active-key', def.dataset.key || '');
    }
    menuLinks.forEach(link => link.addEventListener('click', () => {
      menuLinks.forEach(a => a.removeAttribute('data-active'));
      link.setAttribute('data-active','true');
      localStorage.setItem('sidebar-active-key', link.dataset.key || '');
    }));

    // Submenú categorías
    const toggleCategorias = document.getElementById('toggle-categorias');
    const submenuCategorias = document.getElementById('submenu-categorias');
    const chevronCategorias = document.getElementById('chevron-categorias');
    const openStored = localStorage.getItem('sidebar-open-categorias');
    if (openStored === 'true') {
      submenuCategorias?.classList.remove('hidden');
      toggleCategorias?.setAttribute('aria-expanded','true');
      if (chevronCategorias) chevronCategorias.style.transform = 'rotate(180deg)';
    }
    toggleCategorias?.addEventListener('click', (e) => {
      e.preventDefault();
      const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true'; if (isCollapsed) return;
      const isOpen = toggleCategorias.getAttribute('aria-expanded') === 'true';
      toggleCategorias.setAttribute('aria-expanded', String(!isOpen));
      submenuCategorias.classList.toggle('hidden');
      if (chevronCategorias) chevronCategorias.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
      localStorage.setItem('sidebar-open-categorias', String(!isOpen));
    });
  </script>

  <script src="assets/js/dashboard.js"></script>
  <script>
  // Chips rápidos para el umbral
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.chip-low').forEach(btn => {
      btn.addEventListener('click', () => {
        const v = btn.getAttribute('data-low');
        const input = document.querySelector('input[name="low"]');
        if (input) input.value = v;
      });
    });
  });
</script>
</body>
</html>
