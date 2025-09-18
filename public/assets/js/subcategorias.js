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

  // ---- helpers modal
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

  // ---- fetchers
  async function api(url, opts) {
    const res = await fetch(url, opts);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Error');
    return json.data ?? json;
  }

  async function fetchList() {
    const q = search?.value?.trim() || '';
    const cat = filterCat?.value && filterCat.value !== '0' ? `&cat=${filterCat.value}` : '';
    return api(`${BASE}?action=list${q ? `&q=${encodeURIComponent(q)}` : ''}${cat}`);
  }

  async function fetchCategories() {
    return api(`${BASE}?action=categories`);
  }

  // ---- render
  const empty = () => `<tr><td colspan="7" class="py-8 text-center text-slate-500">Sin resultados</td></tr>`;

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
  }

  function escapeHTML(s) {
    return (s || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  // ---- actions
  function bindRowActions() {
    $$('[data-edit]').forEach(b => b.onclick = async () => {
      const id = b.getAttribute('data-edit');
      const [cats, data] = await Promise.all([fetchCategories(), api(`${BASE}?action=find&id=${id}`)]);
      fillSelect($('#edit-sub-categoria'), cats);
      const c = data.data ?? data;
      $('#edit-sub-id').value = c.id_subcategoria;
      $('#edit-sub-nombre').value = c.nombre || '';
      $('#edit-sub-descripcion').value = c.descripcion || '';
      $('#edit-sub-activa').checked = !!Number(c.activa);
      $('#edit-sub-categoria').value = c.id_categoria;
      openModal('modal-sub-edit');
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
    const cats = await fetchCategories();
    fillSelect($('#create-sub-categoria'), cats);
    $('#form-sub-create').reset();
    $('#create-sub-activa').checked = true;
    openModal('modal-sub-create');
  });

  $('#form-sub-create')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#create-sub-activa').checked ? '1' : '0');
    const res = await fetch(BASE, { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) return alert(json.error || 'Error al crear');
    closeModal('modal-sub-create');
    load();
  });

  // ---- update
  $('#form-sub-edit')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    fd.set('activa', $('#edit-sub-activa').checked ? '1' : '0');
    const res = await fetch(BASE, { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) return alert(json.error || 'Error al actualizar');
    closeModal('modal-sub-edit');
    load();
  });

  // ---- delete
  $('#form-sub-delete')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const res = await fetch(BASE, { method:'POST', body: fd });
    const json = await res.json();
    if (!json.ok) return alert(json.error || 'Error al eliminar');
    closeModal('modal-sub-delete');
    load();
  });

  // ---- search/filter
  let deb;
  search?.addEventListener('input', () => { clearTimeout(deb); deb = setTimeout(load, 250); });
  filterCat?.addEventListener('change', load);

  async function load() {
    // cargar filtro categorías si existe
    if (filterCat && !filterCat.dataset.loaded) {
      const cats = await fetchCategories();
      filterCat.innerHTML = `<option value="0">Todas</option>` +
        cats.map(i => `<option value="${i.id_categoria}">${escapeHTML(i.nombre)}</option>`).join('');
      filterCat.dataset.loaded = '1';
    }
    const rows = await fetchList();
    renderRows(rows);
  }

  load();
})();
