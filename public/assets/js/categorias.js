// public/assets/js/categorias.js
(() => {
  const BASE = '../src/CategoryController.php';

  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => Array.from(document.querySelectorAll(sel));

  const tbody = $('#tbl-body');
  const search = $('#search');
  const btnAdd = $('#btn-add');
  const emptyRow = () => `<tr><td colspan="7" class="py-8 text-center text-slate-500">Sin resultados</td></tr>`;

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

  /* ---------- Listar ---------- */
  async function fetchList(q = '') {
    const url = `${BASE}?action=list${q ? `&q=${encodeURIComponent(q)}` : ''}`;
    const res = await fetch(url);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Error al listar.');
    return json.data;
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
  <!-- Botón Editar -->
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

  <!-- Botón Eliminar -->
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
  }

  function bindRowActions() {
    $$('[data-edit]').forEach(btn => {
      btn.onclick = async () => {
        const id = btn.getAttribute('data-edit');
        const res = await fetch(`${BASE}?action=find&id=${id}`);
        const json = await res.json();
        if (!json.ok) return alert(json.error || 'No se pudo cargar la categoría.');
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
    if (!json.ok) return alert(json.error || 'Error al crear.');
    closeModal('modal-create');
    load();
  });

  /* ---------- Editar ---------- */
  $('#form-edit')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#edit-activa').checked ? '1' : '0');

    const res = await fetch(BASE, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) return alert(json.error || 'Error al actualizar.');
    closeModal('modal-edit');
    load();
  });

  /* ---------- Eliminar ---------- */
  $('#form-delete')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);

    const res = await fetch(BASE, { method: 'POST', body: fd });
    const json = await res.json();
    if (!json.ok) return alert(json.error || 'Error al eliminar.');
    closeModal('modal-delete');
    load();
  });

  /* ---------- Búsqueda ---------- */
  let debounce;
  search?.addEventListener('input', () => {
    clearTimeout(debounce);
    debounce = setTimeout(() => load(), 250);
  });

  async function load() {
    const q = search?.value?.trim() || '';
    const rows = await fetchList(q);
    renderRows(rows);
  }

  // init
  load();
})();
