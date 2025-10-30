<?php
// views/sidebar.php
// Este archivo asume que $user está definido en el archivo que lo incluye 
// (por ejemplo, en public/inventario.php o public/usuarios.php), y tiene la clave 'role'.
?>
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
      
      <?php if ($user['role'] === 'admin'): // <-- INICIO: Solo visible para administradores ?>
      <li><a href="dashboard.php" data-key="inicio"
             class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 12l9-9 9 9M4 10v10h16V10"/></svg>
        <span class="ml-3 truncate">Dashboard</span></a>
      </li>
      <?php endif; // <-- FIN: Solo visible para administradores ?>

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
          <path d="M12 6l-2 2H3v10a2 2 0 002 2h14a2 2 0 002 2V8H12z"/></svg>
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
             class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.45 9a2.25 2.25 0 01-2.25 2.25H6.45A2.25 2.25 0 014.2 16.5l-.45-9M7.5 12h9m-9 4h9M6 6h12"/>
        </svg>
        <span class="ml-3 truncate">Inventario</span></a>
      </li>
      
      <?php if ($user['role'] === 'admin'): // <-- Condición para Usuarios ya estaba correcta ?>
      <li><a href="usuarios.php" data-key="usuarios"
             class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a1.5 1.5 0 10-1.5 1.5m1.5-1.5a1.5 1.5 0 11-1.5-1.5M12 21a9 9 0 100-18 9 9 0 000 18z"/>
        </svg>
        <span class="ml-3 truncate">Usuarios</span></a>
      </li>
      <?php endif; ?>
      
      <li><a href="ayuda.php" data-key="ayuda"
             class="group flex items-center rounded-xl px-3 py-2 text-sm font-medium hover:bg-gray-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5h7.5A.75.75 0 0116.5 5.25v1.5a.75.75 0 01-.75.75H8.25a.75.75 0 01-.75-.75v-1.5a.75.75 0 01.75-.75zm4.5 9h-3a.75.75 0 00-.75.75v3a.75.75 0 00.75.75h3a.75.75 0 00.75-.75v-3a.75.75 0 00-.75-.75z"/>
        </svg>
        <span class="ml-3 truncate">Ayuda</span></a>
      </li>
      </ul>
  </nav>
  <div class="mt-auto p-3 border-t border-gray-100">
    
    <a href="creditos.php" class="group flex items-center justify-center md:justify-start rounded-xl px-3 py-2 text-sm hover:bg-gray-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <span class="ml-3 truncate">Créditos</span>
    </a>
    <a href="logout.php" class="group flex items-center justify-center md:justify-start rounded-xl border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H3m12 0l-4-4m4 4l-4 4m9-7v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h7"/></svg>
      <span class="ml-3 truncate">Cerrar sesión</span>
    </a>
  </div>
</aside>
<script>
    // Toggle sidebar (sin cambios en el script)
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