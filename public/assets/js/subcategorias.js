// public/assets/js/subcategorias.js
(() => {
  const BASE = '../src/SubcategoryController.php';
  const $ = (s) => document.querySelector(s);
  const $$ = (s) => Array.from(document.querySelectorAll(s));

  const tbody = $('#tbl-sub-body');
  const search = $('#sub-search');
  const filterCat = $('#sub-filter-cat');
  const btnAdd = $('#btn-sub-add');
  const backdrop = $('#backdrop-sub');
  
  // CONSTANTES DE PAGINACIÓN
  const limitSelector = $('#limit-selector');
  const paginationControls = $('#pagination-controls');
  const pageInfo = $('#page-info');

  // CONSTANTE DE EXPORTAR PDF (DECLARACIÓN ÚNICA)
  const btnSubPdf = document.getElementById('btn-sub-pdf'); 

  let currentPage = 1;
  let currentLimit = Number(limitSelector?.value) || 10;
  let totalRecords = 0;

  /* ---------- SweetAlert2 (Función de notificación/alerta) ---------- */
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
  function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('hidden'); m.classList.add('flex');
    backdrop.classList.remove('hidden');
  }
  function closeModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add('hidden'); m.classList.remove('flex');
    backdrop.classList.add('hidden');
  }
  $$('#backdrop-sub, [data-close]').forEach(el => {
    el.addEventListener('click', (e) => {
      const id = el.dataset.close || '';
      if (e.target === el || id) {
        if (id) closeModal(id);
        else backdrop.classList.add('hidden');
      }
    });
  });

// --- Exportar PDF (ELIMINADA DECLARACIÓN DUPLICADA) ---
btnSubPdf?.addEventListener('click', () => {
  const q = (search?.value || '').trim();
  const catVal = filterCat?.value || '0';
  const params = [];
  if (q) params.push(`q=${encodeURIComponent(q)}`);
  if (catVal && catVal !== '0') params.push(`cat=${encodeURIComponent(catVal)}`);

  const url = `${BASE}?action=export_pdf${params.length ? `&${params.join('&')}` : ''}`;

  const w = window.open(url, '_blank');
  if (!w) window.location.href = url; // fallback si el popup es bloqueado
});


  /* ---------- Listar (con Paginación) ---------- */
  async function api(url, opts) {
    const res = await fetch(url, opts);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Error');
    return json.data ?? json;
  }
  
  async function fetchList() {
    const q = search?.value?.trim() || '';
    const cat = filterCat?.value && filterCat.value !== '0' ? `&cat=${filterCat.value}` : '';
    const page = `&page=${currentPage}`;
    const limit = `&limit=${currentLimit}`;
    
    // Devuelve el objeto completo incluyendo total_records
    const res = await fetch(`${BASE}?action=list${page}${limit}${q ? `&q=${encodeURIComponent(q)}` : ''}${cat}`);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Error al listar.');
    return json; 
  }

  async function fetchCategories() {
    return api(`${BASE}?action=categories`);
  }

  // ---- render
  const empty = () => `<tr><td colspan="8" class="py-8 text-center text-slate-500">Sin resultados</td></tr>`;

  /* ---------- Renderizar Paginación ---------- */
  function renderPaginationControls() {
    if (!paginationControls || !pageInfo) return;

    const totalPages = Math.ceil(totalRecords / currentLimit);
    paginationControls.innerHTML = ''; // Limpiar controles

    const startRecord = (currentPage - 1) * currentLimit + 1;
    const endRecord = Math.min(currentPage * currentLimit, totalRecords);
    
    // Información de página
    if (totalRecords === 0) {
        pageInfo.textContent = 'No hay subcategorías para mostrar.';
    } else {
        pageInfo.textContent = `Mostrando ${startRecord}-${endRecord} de ${totalRecords} subcategorías.`;
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
    if (!rows.length) { tbody.innerHTML = empty(); return; }
    tbody.innerHTML = rows.map(r => `
      <tr class="border-b last:border-0 hover:bg-slate-50/60">
        <td class="px-3 py-3 text-xs text-slate-500">${r.id_subcategoria}</td>
        <td class="px-3 py-3">
          <span class="inline-flex items-center rounded-full border bg-slate-50 px-2.5 py-1 text-xs text-slate-700 border-slate-200">${escapeHTML(r.categoria)}</span>
        </td>
        <td class="px-3 py-3 font-medium">${escapeHTML(r.nombre)}</td>
        <td class="px-3 py-3 text-sm text-slate-600">${escapeHTML(r.descripcion ?? '')}</td>
        <td class="px-3 py-3">
          <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs border
            ${Number(r.activa) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-300'}">
            ${Number(r.activa) ? 'Activa' : 'Inactiva'}
          </span>
        </td>
        <td class="px-3 py-3 text-xs text-slate-500">${r.creada_en}</td>
        <td class="px-3 py-3 text-xs text-slate-500">${r.actualizada_en}</td>
        <td class="px-3 py-3">
          <div class="flex flex-wrap gap-2">
            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-sky-200
                           bg-sky-50 text-sky-700 text-sm hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-300"
                    data-edit='${r.id_subcategoria}' title="Editar">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/>
              </svg> Editar
            </button>
            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200
                           bg-rose-50 text-rose-700 text-sm hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-300"
                    data-del='${r.id_subcategoria}' data-name="${escapeHTML(r.nombre)}" title="Eliminar">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 01-1-1V5a1 1 0 011-1h8a1 1 0 011 1v1m-10 0h10"/>
              </svg> Eliminar
            </button>
          </div>
        </td>
      </tr>
    `).join('');
    bindRowActions();
    renderPaginationControls();
  }

  function escapeHTML(s) {
    return (s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  // ---- actions
  function bindRowActions() {
    $$('[data-edit]').forEach(b => b.onclick = async () => {
      const id = b.getAttribute('data-edit');
      try {
          const [cats, data] = await Promise.all([fetchCategories(), api(`${BASE}?action=find&id=${id}`)]);
          fillSelect($('#edit-sub-categoria'), cats);
          const c = data.data ?? data;
          $('#edit-sub-id').value = c.id_subcategoria;
          $('#edit-sub-nombre').value = c.nombre || '';
          $('#edit-sub-descripcion').value = c.descripcion || '';
          $('#edit-sub-activa').checked = !!Number(c.activa);
          $('#edit-sub-categoria').value = c.id_categoria;
          openModal('modal-sub-edit');
      } catch (e) {
          showToast(e.message, 'error');
      }
    });

    $$('[data-del]').forEach(b => b.onclick = () => {
      $('#del-sub-id').value = b.getAttribute('data-del');
      $('#del-sub-name').textContent = b.getAttribute('data-name');
      openModal('modal-sub-delete');
    });
  }

  function fillSelect(select, items) {
    select.innerHTML = '<option value="">— Selecciona —</option>' +
      items.map(i => `<option value="${i.id_categoria}">${escapeHTML(i.nombre)}</option>`).join('');
  }

  // ---- create
  btnAdd?.addEventListener('click', async () => {
    try {
        const cats = await fetchCategories();
        fillSelect($('#create-sub-categoria'), cats);
        $('#form-sub-create').reset();
        $('#create-sub-activa').checked = true;
        openModal('modal-sub-create');
    } catch (e) {
        showToast(e.message, 'error');
    }
  });

  $('#form-sub-create')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#create-sub-activa').checked ? '1' : '0');
    const res = await fetch(BASE, { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) {
        showToast(json.error || 'Error al crear', 'error');
        return;
    }
    closeModal('modal-sub-create');
    showToast('Subcategoría creada con éxito.', 'success');
    currentPage = 1; // Volver a la primera página después de crear
    load();
  });

  // ---- update
  $('#form-sub-edit')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#edit-sub-activa').checked ? '1' : '0');
    const res = await fetch(BASE, { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) {
        showToast(json.error || 'Error al actualizar', 'error');
        return;
    }
    closeModal('modal-sub-edit');
    showToast('Subcategoría actualizada con éxito.', 'success');
    load();
  });

  // ---- delete (CORREGIDO para usar showToast en caso de restricción)
  $('#form-sub-delete')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const deletedName = $('#del-sub-name').textContent; 
    
    const res = await fetch(BASE, { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) {
        // CORRECCIÓN: Usamos showToast para la alerta de restricción.
        showToast(json.error || 'Ocurrió un error al eliminar la subcategoría.', 'error');
        return;
    }
    closeModal('modal-sub-delete');
    showToast(`Subcategoría "${deletedName}" eliminada con éxito.`, 'success');
    load();
  });

  // ---- search/filter/limit events
  let deb;
  search?.addEventListener('input', () => { 
    clearTimeout(deb); 
    deb = setTimeout(() => { currentPage = 1; load(); }, 250); 
  });
  
  filterCat?.addEventListener('change', () => { currentPage = 1; load(); });
  
  limitSelector?.addEventListener('change', () => {
    currentLimit = Number(limitSelector.value);
    currentPage = 1; // Resetear página al cambiar el límite
    load();
  });


  /* ---------- Lógica de Carga Principal ---------- */
  async function load() {
    try {
        // cargar filtro categorías si existe y no ha cargado
        if (filterCat && !filterCat.dataset.loaded) {
          const cats = await fetchCategories();
          filterCat.innerHTML = `<option value="0">Todas</option>` +
            cats.map(i => `<option value="${i.id_categoria}">${escapeHTML(i.nombre)}</option>`).join('');
          filterCat.dataset.loaded = '1';
        }
        
        const result = await fetchList();
        
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
        Swal.fire({
            icon: 'error',
            title: 'Error de Carga',
            text: e.message,
        });
        tbody.innerHTML = empty();
        // Limpiar controles de paginación en caso de error
        if (paginationControls) paginationControls.innerHTML = '';
        if (pageInfo) pageInfo.textContent = '';
    }
  }

  // init
  load();
})();