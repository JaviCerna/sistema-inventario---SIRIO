<?php
// public/creditos.php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Config.php';

$auth = new Auth();
if(!$auth->check()) {
    header('Location: index.php?error=' . urlencode('Por favor inicia sesión.'));
    exit;
}
$user = $auth->user();

// Definir la clave activa del menú lateral para Créditos
$activeKey = 'creditos'; 
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Créditos del Sistema - SIRIO</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-gray-800">
  <div class="min-h-screen flex">
    <?php 
      // Llama al archivo del menú lateral
      require_once __DIR__ . '/../views/sidebar.php'; 
    ?>

    <main class="flex-1 flex flex-col min-w-0">
        <header class="h-16 border-b border-gray-200 bg-white flex items-center px-4 justify-between shrink-0">
            <h1 class="font-semibold">Créditos del Sistema</h1>
            <div class="flex items-center gap-4">
                <div class="text-sm text-slate-600">Hola, <?=htmlspecialchars($user['full_name'] ?? $user['email'])?></div>
            </div>
        </header>

        <section class="p-4 md:p-6 flex-1 flex min-h-0 overflow-y-auto">
            <div class="w-full flex-1 flex flex-col min-h-0">
                <div class="bg-white p-4 md:p-6 rounded-2xl shadow flex-1 flex flex-col min-h-0 max-w-6xl mx-auto w-full">
                    
                    <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                        Desarrolladores del Sistema
                    </h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        
                        <div class="bg-white rounded-xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300">
                            <div class="bg-indigo-600 h-20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="p-6 text-center">
                                <img src="assets/img/persona.jpeg" alt="Foto de Juan Pérez" class="w-24 h-24 rounded-full mx-auto -mt-16 border-4 border-white object-cover shadow-lg">
                                
                                <h3 class="text-xl font-bold text-gray-900 mt-4">Carlos Alvarado</h3>
                                <p class="text-sm text-indigo-600 font-semibold mt-1">Scrum Master</p>
                                <p class="text-sm text-gray-500 mt-3">Breve descripción</p>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <span class="text-xs font-medium text-gray-400 block">Contacto:</span>
                                    <p class="text-sm text-gray-700">correo@gmail.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300">
                            <div class="bg-green-600 h-20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            </div>
                            <div class="p-6 text-center">
                                <img src="assets/img/persona.jpeg" alt="Foto de Ana García" class="w-24 h-24 rounded-full mx-auto -mt-16 border-4 border-white object-cover shadow-lg">
                                
                                <h3 class="text-xl font-bold text-gray-900 mt-4">Stephanie Torres</h3>
                                <p class="text-sm text-green-600 font-semibold mt-1">Team Scrum</p>
                                <p class="text-sm text-gray-500 mt-3">Breve descripción</p>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <span class="text-xs font-medium text-gray-400 block">Contacto:</span>
                                    <p class="text-sm text-gray-700">correo@gmail.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300">
                            <div class="bg-sky-600 h-20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <div class="p-6 text-center">
                                <img src="assets/img/persona.jpeg" alt="Foto de Carlos Ruiz" class="w-24 h-24 rounded-full mx-auto -mt-16 border-4 border-white object-cover shadow-lg">
                                
                                <h3 class="text-xl font-bold text-gray-900 mt-4">Alex Soriano</h3>
                                <p class="text-sm text-sky-600 font-semibold mt-1">Team Scrum</p>
                                <p class="text-sm text-gray-500 mt-3">Breve descripción</p>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <span class="text-xs font-medium text-gray-400 block">Contacto:</span>
                                    <p class="text-sm text-gray-700">correo@gmail.com</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl shadow-xl overflow-hidden hover:shadow-2xl transition duration-300">
                            <div class="bg-yellow-600 h-20 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.146a9.981 9.981 0 00-4.288-4.288M6.335 18.665a9.981 9.981 0 004.288 4.288m-4.664-4.288A9.981 9.981 0 0112 3c2.446 0 4.708.895 6.463 2.417M12 21a9.981 9.981 0 01-4.288-4.288M7.05 7.05L5.636 5.636m12.728 12.728l-1.414-1.414" />
                                </svg>
                            </div>
                            <div class="p-6 text-center">
                                <img src="assets/img/persona.jpeg" alt="Foto de Elena Torres" class="w-24 h-24 rounded-full mx-auto -mt-16 border-4 border-white object-cover shadow-lg">
                                
                                <h3 class="text-xl font-bold text-gray-900 mt-4">Javier Cerna</h3>
                                <p class="text-sm text-yellow-600 font-semibold mt-1">Team Scrum</p>
                                <p class="text-sm text-gray-500 mt-3">Breve descripción</p>
                                
                                <div class="mt-4 pt-4 border-t border-gray-100">
                                    <span class="text-xs font-medium text-gray-400 block">Contacto:</span>
                                    <p class="text-sm text-gray-700">correo@gmail.com</p>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="mt-12 p-4 bg-gray-50 border border-gray-200 text-gray-700 rounded-lg text-sm text-center">
                        © 2023 SIRIO S.A. DE C.V. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </section>
    </main>

  </div>
<script>
    // --- Colapsar sidebar (código heredado) ---
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

    // --- Selección activa (Ajustado para la clave 'creditos') ---
    const menuLinks = document.querySelectorAll('#sidebar nav a, #sidebar div a');
    const activeKeyStored = '<?= $activeKey ?>'; // Establecemos 'creditos' como clave activa

    const found = Array.from(menuLinks).find(a => a.dataset.key === activeKeyStored);
    if (found) {
        menuLinks.forEach(a => a.removeAttribute('data-active'));
        // Usamos el color de fondo para indicar el link activo
        found.classList.add('bg-gray-100', 'font-semibold');
        found.classList.remove('hover:bg-gray-50');
    }
    // Aseguramos que los futuros clics guarden la clave correcta
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Limpiar estilos de todos los links
            menuLinks.forEach(a => {
                a.classList.remove('bg-gray-100', 'font-semibold');
                a.classList.add('hover:bg-gray-50');
            });
            // Aplicar estilo al link clicado
            link.classList.add('bg-gray-100', 'font-semibold');
            link.classList.remove('hover:bg-gray-50');
            localStorage.setItem('sidebar-active-key', link.dataset.key || '');
        });
    });
  </script>
</body>
</html>