// public/assets/js/categorias.js
(() => {
  const BASE = '../src/CategoryController.php';

  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => Array.from(document.querySelectorAll(sel));

  const tbody = $('#tbl-body');
  const search = $('#search');
  const btnAdd = $('#btn-add');
  const emptyRow = () => `<tr><td colspan="7" class="py-8 text-center text-slate-500">Sin resultados</td></tr>`;

  // CONSTANTES DE PAGINACIÓN
  const limitSelector = $('#limit-selector');
  const paginationControls = $('#pagination-controls');
  const pageInfo = $('#page-info');

  let currentPage = 1;
  let currentLimit = Number(limitSelector?.value) || 10;
  let totalRecords = 0;
  
  /* ---------- SweetAlert2 (Función de notificación/alerta) ---------- */
  /**
   * Muestra una notificación temporal de SweetAlert2 (Toast).
   * @param {string} title Título del toast.
   * @param {string} icon Icono: 'success', 'error', 'warning', 'info', 'question'.
   */
  function showToast(title, icon) {
      Swal.fire({
          toast: true,
          position: 'top-end',
          icon: icon,
          title: title,
          showConfirmButton: false,
          timer: 3000,
          timerProgressBar: true,
          didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer);
              toast.addEventListener('mouseleave', Swal.resumeTimer);
          }
      });
  }

  /* ---------- Helpers de modal ---------- */
  const backdrop = $('#backdrop');
  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    backdrop.classList.remove('hidden');
  }
  function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    backdrop.classList.add('hidden');
  }
  $$('#backdrop, [data-close]').forEach(el => {
    el.addEventListener('click', (e) => {
      const id = el.dataset.close || 'modal-create';
      if (e.target === el || el.dataset.close) closeModal(id);
    });
  });

// --- Exportar PDF (TCPDF vía action=export_pdf) ---
const btnCatPdf = document.getElementById('btn-cat-pdf') || document.getElementById('btn-report');
btnCatPdf?.addEventListener('click', () => {
  const q = (search?.value || '').trim();
  const url = `${BASE}?action=export_pdf${q ? `&q=${encodeURIComponent(q)}` : ''}`;
  const w = window.open(url, '_blank');
  if (!w) { window.location.href = url; }
});


  /* ---------- Listar (MODIFICADO para paginación) ---------- */
  async function fetchList(q = '', page = 1, limit = 10) {
    const url = `${BASE}?action=list&page=${page}&limit=${limit}${q ? `&q=${encodeURIComponent(q)}` : ''}`;
    const res = await fetch(url);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Error al listar.');
    return json; // Devuelve el objeto completo (data y total_records)
  }

  /* ---------- Renderizar Paginación ---------- */
  function renderPaginationControls() {
    if (!paginationControls || !pageInfo) return;

    const totalPages = Math.ceil(totalRecords / currentLimit);
    paginationControls.innerHTML = ''; // Limpiar controles

    const startRecord = (currentPage - 1) * currentLimit + 1;
    const endRecord = Math.min(currentPage * currentLimit, totalRecords);
    
    // Información de página
    if (totalRecords === 0) {
        pageInfo.textContent = 'No hay categorías para mostrar.';
    } else {
        pageInfo.textContent = `Mostrando ${startRecord}-${endRecord} de ${totalRecords} categorías.`;
    }

    // No mostrar paginador si solo hay una página o menos
    if (totalPages <= 1) {
        paginationControls.innerHTML = '';
        return;
    }

    // Función para crear botones
    const createButton = (text, page, isDisabled) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.dataset.page = page;
      button.className = `px-3 py-1.5 text-sm rounded-lg transition-colors ${
        isDisabled 
          ? 'text-slate-400 cursor-not-allowed'
          : page === currentPage 
            ? 'bg-sky-500 text-white font-semibold' 
            : 'text-slate-600 hover:bg-slate-100'
      }`;
      button.innerHTML = text;
      button.disabled = isDisabled;
      return button;
    };

    // Botón Anterior
    const btnPrev = createButton('Anterior', currentPage - 1, currentPage === 1);
    paginationControls.appendChild(btnPrev);

    // Lógica para mostrar solo algunas páginas (ej: 1, 2, 3, 4, 5...)
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
    
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const btn = createButton(String(i), i, false);
        paginationControls.appendChild(btn);
    }

    // Botón Siguiente
    const btnNext = createButton('Siguiente', currentPage + 1, currentPage === totalPages);
    paginationControls.appendChild(btnNext);
    
    // Asignar eventos a los botones de página
    paginationControls.querySelectorAll('button[data-page]').forEach(btn => {
      if (!btn.disabled) {
        btn.addEventListener('click', () => {
          const newPage = Number(btn.dataset.page);
          if (newPage !== currentPage) {
            currentPage = newPage;
            load();
          }
        });
      }
    });
  }


  function renderRows(rows) {
    if (!rows.length) { tbody.innerHTML = emptyRow(); return; }
    tbody.innerHTML = rows.map(r => `
      <tr class="border-b last:border-0">
        <td class="px-3 py-3 text-xs text-slate-500">${r.id_categoria}</td>
        <td class="px-3 py-3 font-medium">${escapeHTML(r.nombre)}</td>
        <td class="px-3 py-3 text-sm text-slate-600">${escapeHTML(r.descripcion ?? '')}</td>
        <td class="px-3 py-3">
          <span class="inline-flex items-center rounded-full px-2 py-1 text-xs ${r.activa ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border'}">
            ${r.activa ? 'Activa' : 'Inactiva'}
          </span>
        </td>
        <td class="px-3 py-3 text-xs text-slate-500">${r.creada_en}</td>
        <td class="px-3 py-3 text-xs text-slate-500">${r.actualizada_en}</td>
        <td class="px-3 py-3">
          <div class="flex gap-2">
  <button
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-sky-200
           bg-sky-50 text-sky-700 text-sm hover:bg-sky-100
           focus:outline-none focus:ring-2 focus:ring-sky-300"
    data-edit='${r.id_categoria}'>
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/>
    </svg>
    Editar
  </button>

  <button
    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200
           bg-rose-50 text-rose-700 text-sm hover:bg-rose-100
           focus:outline-none focus:ring-2 focus:ring-rose-300"
    data-del='${r.id_categoria}' data-name="${escapeHTML(r.nombre)}">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
         stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 
               01-1-1V5a1 1 0 011-1h8a1 1 0 011 1v1m-10 0h10"/>
    </svg>
    Eliminar
  </button>
</div>

        </td>
      </tr>
    `).join('');
    bindRowActions();
    renderPaginationControls();
  }

  function bindRowActions() {
    $$('[data-edit]').forEach(btn => {
      btn.onclick = async () => {
        const id = btn.getAttribute('data-edit');
        const res = await fetch(`${BASE}?action=find&id=${id}`);
        const json = await res.json();
        if (!json.ok) return showToast(json.error || 'No se pudo cargar la categoría.', 'error');
        const c = json.data;
        $('#edit-id').value = c.id_categoria;
        $('#edit-nombre').value = c.nombre || '';
        $('#edit-descripcion').value = c.descripcion || '';
        $('#edit-activa').checked = !!Number(c.activa);
        openModal('modal-edit');
      };
    });

    $$('[data-del]').forEach(btn => {
      btn.onclick = () => {
        $('#del-id').value = btn.getAttribute('data-del');
        $('#del-name').textContent = btn.getAttribute('data-name');
        openModal('modal-delete');
      };
    });
  }

  function escapeHTML(str) {
    return (str || '').replace(/[&<>"']/g, (m) => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  /* ---------- Crear ---------- */
  btnAdd?.addEventListener('click', () => {
    $('#form-create').reset();
    $('#create-activa').checked = true;
    openModal('modal-create');
  });

  $('#form-create')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#create-activa').checked ? '1' : '0');

    const res = await fetch(BASE, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) {
        showToast(json.error || 'Error al crear la categoría.', 'error'); 
        return; 
    }
    closeModal('modal-create');
    showToast('Categoría creada con éxito.', 'success'); 
    currentPage = 1; // Volver a la primera página después de crear
    load();
  });

  /* ---------- Editar ---------- */
  $('#form-edit')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#edit-activa').checked ? '1' : '0');

    const res = await fetch(BASE, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) {
        showToast(json.error || 'Error al actualizar la categoría.', 'error'); 
        return;
    }
    closeModal('modal-edit');
    showToast('Categoría actualizada con éxito.', 'success'); 
    load(); // Recargar la página actual
  });

  /* ---------- Eliminar (CORREGIDO para usar showToast en caso de restricción) ---------- */
  $('#form-delete')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const deletedName = $('#del-name').textContent; 

    const res = await fetch(BASE, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) {
        // Usamos showToast para la alerta de restricción (o cualquier otro error de eliminación)
        showToast(json.error || 'Error al eliminar la categoría.', 'error'); 
        return;
    }
    closeModal('modal-delete');
    showToast(`Categoría "${deletedName}" eliminada con éxito.`, 'success'); 
    
    // Después de eliminar, recargar y asegurar que la página actual sigue siendo válida
    load();
  });

  /* ---------- Búsqueda y Límite (EVENTOS) ---------- */
  let debounce;
  
  search?.addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        currentPage = 1; // Resetear página al buscar
        load();
    }, 250);
  });
  
  limitSelector?.addEventListener('change', () => {
    currentLimit = Number(limitSelector.value);
    currentPage = 1; // Resetear página al cambiar el límite
    load();
  });


  async function load() {
    const q = search?.value?.trim() || '';
    try {
        const result = await fetchList(q, currentPage, currentLimit);
        
        // 1. Actualizar el estado de la paginación global
        totalRecords = result.total_records;
        
        // 2. Ajustar currentPage si después de una operación quedó fuera de rango
        const totalPages = Math.ceil(totalRecords / currentLimit);
        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
            await load(); // Recargar con la última página válida
            return;
        }

        // 3. Renderizar vista
        renderRows(result.data);
    } catch (e) {
        // Usamos Swal.fire (modal central) para errores críticos de carga inicial
        Swal.fire({
            icon: 'error',
            title: 'Error de Carga',
            text: e.message,
        });
        tbody.innerHTML = emptyRow();
        // Limpiar controles de paginación en caso de error
        if (paginationControls) paginationControls.innerHTML = '';
        if (pageInfo) pageInfo.textContent = '';
    }
  }

  // init
  load();
})();