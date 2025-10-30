<?php
// public/ayuda.php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Config.php';

$auth = new Auth();
if(!$auth->check()) {
    header('Location: index.php?error=' . urlencode('Por favor inicia sesión.'));
    exit;
}
$user = $auth->user();

// Definir la clave activa del menú lateral para Ayuda
$activeKey = 'ayuda'; 
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ayuda y Recursos - SIRIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Estilo para los iframes de video para mantener la relación de aspecto 16:9 */
    .video-responsive {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
        max-width: 100%;
        border-radius: 0.5rem; /* rounded-lg */
    }
    .video-responsive iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
  </style>
</head>
<body class="bg-slate-100 text-gray-800">
  <div class="min-h-screen flex">
    <?php 
      // Llama al archivo del menú lateral
      require_once __DIR__ . '/../views/sidebar.php'; 
    ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4 justify-between shrink-0">
            <h1 class="font-semibold">Centro de Ayuda y Recursos</h1>
            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
            </div>
        </header>

        <section class="p-4 md:p-6 flex-1 flex min-h-0 overflow-y-auto">
            <div class="w-full flex-1 flex flex-col min-h-0">
                <div class="bg-white p-4 md:p-6 rounded-2xl shadow flex-1 flex flex-col min-h-0 max-w-6xl mx-auto w-full">
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-2">
                        Recursos de Soporte 
                    </h2>

                    <section class="mb-12">
                        <h3 class="text-xl font-semibold text-indigo-700 mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 11l7.5-7.5 7.5 7.5v6c0 1.657-1.343 3-3 3H5c-1.657 0-3-1.343-3-3v-6z" />
                            </svg>
                            Documentación
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <a href="assets/docs/documento.pdf" target="_blank" class="block">
                                <div class="bg-gray-50 p-5 rounded-xl shadow-md hover:shadow-lg transition duration-300 border-l-4 border-indigo-500 h-full">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-indigo-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m-6-8h6m4 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="ml-4">
                                            <p class="text-md font-bold text-gray-900">Documentación General</p>
                                            <p class="text-sm text-gray-500 mt-1">Información general y visión del sistema.</p>
                                            <span class="inline-flex items-center mt-3 text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                                Abrir PDF &rarr;
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <a href="assets/docs/documento.pdf" target="_blank" class="block">
                                <div class="bg-gray-50 p-5 rounded-xl shadow-md hover:shadow-lg transition duration-300 border-l-4 border-green-500 h-full">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <div class="ml-4">
                                            <p class="text-md font-bold text-gray-900">Manual de Usuario</p>
                                            <p class="text-sm text-gray-500 mt-1">Guía para el uso de las funciones básicas.</p>
                                            <span class="inline-flex items-center mt-3 text-sm font-medium text-green-600 hover:text-green-800">
                                                Abrir PDF &rarr;
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>

                            <a href="assets/docs/documento.pdf" target="_blank" class="block">
                                <div class="bg-gray-50 p-5 rounded-xl shadow-md hover:shadow-lg transition duration-300 border-l-4 border-red-500 h-full">
                                    <div class="flex items-start">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.515-.514 1.348-.514 1.863 0l.373.373a1.5 1.5 0 001.06.44h1.996a1.5 1.5 0 011.5 1.5v1.996a1.5 1.5 0 00.44 1.06l.373.373c.514.515.514 1.348 0 1.863l-.373.373a1.5 1.5 0 00-.44 1.06v1.996a1.5 1.5 0 01-1.5 1.5h-1.996a1.5 1.5 0 00-1.06.44l-.373.373c-.515.514-1.348.514-1.863 0l-.373-.373a1.5 1.5 0 00-1.06-.44H6.71a1.5 1.5 0 01-1.5-1.5v-1.996a1.5 1.5 0 00-.44-1.06l-.373-.373c-.514-.515-.514-1.348 0-1.863l.373-.373a1.5 1.5 0 00.44-1.06V6.71a1.5 1.5 0 011.5-1.5h1.996a1.5 1.5 0 001.06-.44l.373-.373z" />
                                        </svg>
                                        <div class="ml-4">
                                            <p class="text-md font-bold text-gray-900">Manual de Administrador</p>
                                            <p class="text-sm text-gray-500 mt-1">Instrucciones para la gestión de usuarios y configuraciones.</p>
                                            <span class="inline-flex items-center mt-3 text-sm font-medium text-red-600 hover:text-red-800">
                                                Abrir PDF &rarr;
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>

                        </div>
                    </section>

                    <div class="h-px bg-gray-200 my-8"></div>
                    
                    <section>
                        <h3 class="text-xl font-semibold text-indigo-700 mb-4 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h4a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Videos de Apoyo
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <div class="bg-gray-50 p-4 rounded-xl shadow-lg border-t-4 border-sky-500">
                                <h4 class="text-lg font-bold text-gray-900 mb-3">Tutorial: Gestión de Inventario</h4>
                                <div class="video-responsive">
                                    <iframe src="https://www.youtube.com/embed/ID_DEL_VIDEO_INVENTARIO" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen></iframe>
                                </div>
                                <p class="text-sm text-gray-500 mt-3">Aprende a registrar y gestionar productos en el sistema.</p>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-xl shadow-lg border-t-4 border-sky-500">
                                <h4 class="text-lg font-bold text-gray-900 mb-3">Tutorial: Creación de Usuarios</h4>
                                <div class="video-responsive">
                                    <iframe src="https://www.youtube.com/embed/ID_DEL_VIDEO_USUARIOS" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen></iframe>
                                </div>
                                <p class="text-sm text-gray-500 mt-3">Video explicativo para administradores sobre la gestión de cuentas.</p>
                            </div>

                            </div>
                        
                        <div class="mt-8 p-4 bg-blue-50 border border-blue-300 text-blue-800 rounded-lg text-sm">
                            Si tienes preguntas adicionales, contacta al administrador del sistema.
                        </div>
                    </section>
                    <div class="mt-12 p-4 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-sm text-center">
                        © 2025 SIRIO S.A. DE C.V. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </section>
    </main>

  </div>
<script>
    // --- Colapsar sidebar (código heredado de categorias.php) ---
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

    // --- Selección activa (Ajustado para la clave 'ayuda') ---
    const menuLinks = document.querySelectorAll('#sidebar nav a, #sidebar div a');
    const activeKeyStored = '<?= $activeKey ?>'; // Establecemos 'ayuda' como clave activa

    const found = Array.from(menuLinks).find(a => a.dataset.key === activeKeyStored);
    if (found) {
        menuLinks.forEach(a => a.removeAttribute('data-active'));
        // Usamos el color de fondo para indicar el link activo
        found.classList.add('bg-gray-100', 'font-semibold');
        found.classList.remove('hover:bg-gray-100');
    }
    // Aseguramos que los futuros clics guarden la clave correcta
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Limpiar estilos de todos los links
            menuLinks.forEach(a => {
                a.classList.remove('bg-gray-100', 'font-semibold');
                a.classList.add('hover:bg-gray-100');
            });
            // Aplicar estilo al link clicado
            link.classList.add('bg-gray-100', 'font-semibold');
            link.classList.remove('hover:bg-gray-100');
            localStorage.setItem('sidebar-active-key', link.dataset.key || '');
        });
    });

    // Código de submenú de categorías eliminado, ya que no aplica aquí.
  </script>
</body>
</html>