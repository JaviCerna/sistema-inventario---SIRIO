/* public/assets/js/inventario.js */

/* ==== Helpers ==== */
const $  = (s, c=document) => c.querySelector(s);
const $$ = (s, c=document) => Array.from(c.querySelectorAll(s));
const escapeHTML = (s='') => (s+'').replaceAll('&','&amp;').replaceAll('<','&lt;')
  .replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'","&#039;");

/* API base */
const BASE = (typeof window !== 'undefined' && window.API_BASE) ? window.API_BASE : '../../src/ProductController.php';

/* Robust fetch JSON */
async function api(url, opts={}) {
  const res  = await fetch(url, opts);
  const text = await res.text();
  let json;
  try { json = JSON.parse(text); }
  catch(e){ console.error('Respuesta no-JSON de', url, '\n----\n', text, '\n----'); throw new Error('Respuesta inválida'); }
  if (!res.ok || json.ok === false) throw new Error(json.error || `Error HTTP ${res.status}`);
  return json.data ?? json;
}

/* Modales */
const BACKDROP = $('#backdrop-prod');
function openModal(id){ const el = document.getElementById(id); if(!el) return; el.classList.remove('hidden'); el.classList.add('flex'); BACKDROP?.classList.remove('hidden'); }
function closeModal(id){ const el = document.getElementById(id); if(!el) return; el.classList.add('hidden'); el.classList.remove('flex'); const any = $$('.fixed.inset-0.z-50').some(m=>!m.classList.contains('hidden')); if(!any) BACKDROP?.classList.add('hidden'); }
document.addEventListener('click', (e)=>{ const b=e.target.closest('[data-close]'); if(!b) return; closeModal(b.getAttribute('data-close')); });
BACKDROP?.addEventListener('click', ()=>{ $$('.fixed.inset-0.z-50').forEach(m=>m.classList.add('hidden')); BACKDROP.classList.add('hidden'); });

/* Cache opciones */
let OPTS = null;
let SUB_BY_CAT = new Map();
const groupBy = (arr,k)=>arr.reduce((m,o)=>((m.get(o[k])||m.set(o[k],[]).get(o[k])).push(o),m), new Map());
function fillSelect(sel,rows,val,text,empty=false){ if(!sel) return; let html = empty?`<option value="">(Opcional)</option>`:''; html += rows.map(r=>`<option value="${r[val]}">${escapeHTML(r[text])}</option>`).join(''); sel.innerHTML=html; }
function buildSubOptions(subs){ SUB_BY_CAT = groupBy(subs,'id_categoria'); }
function onChangeCategoria(selCat, selSub){ const C=$(selCat), S=$(selSub); if(!C||!S) return; C.addEventListener('change',()=>{ const opts = SUB_BY_CAT.get(Number(C.value)) || []; fillSelect(S,opts,'id_subcategoria','nombre'); }); }

/* Cargar opciones */
async function loadOptions(force=false){
  if(OPTS && !force) return OPTS;
  const data = await api(`${BASE}?action=opciones`);
  OPTS = data;
  // llenar selects
  fillSelect($('#c-id_categoria'), data.categorias, 'id_categoria','nombre');
  fillSelect($('#e-id_categoria'), data.categorias, 'id_categoria','nombre');
  fillSelect($('#c-id_talla'), data.tallas, 'id_talla','nombre', true);
  fillSelect($('#e-id_talla'), data.tallas, 'id_talla','nombre', true);
  fillSelect($('#c-id_color'), data.colores, 'id_color','nombre', true);
  fillSelect($('#e-id_color'), data.colores, 'id_color','nombre', true);
  fillSelect($('#m-id_bodega'), data.bodegas, 'id_bodega','nombre');
  buildSubOptions(data.subcategorias);
  onChangeCategoria('#c-id_categoria', '#c-id_subcategoria');
  onChangeCategoria('#e-id_categoria', '#e-id_subcategoria');
  return data;
}

/* Tabla */
const tbody  = $('#tbl-body');
const btnAdd = $('#btn-prod-add');
const search = $('#prod-search');

function emptyRow(){ return `<tr><td colspan="9" class="px-3 py-10 text-center text-slate-500">Sin productos</td></tr>`; }
function renderExistencias(items){ if(!items || !items.length) return `<span class="text-xs text-slate-500">Sin movimientos</span>`; return `<ul class="space-y-1">${items.map(i=>`<li class="text-xs"><span class="font-medium">${escapeHTML(i.bodega)}:</span> ${Number(i.cantidad)}</li>`).join('')}</ul>`; }

function renderRows(rows){
  if(!rows.length){ tbody.innerHTML = emptyRow(); return; }
  tbody.innerHTML = rows.map(r=>`
    <tr class="border-b last:border-0 hover:bg-slate-50/60 align-top">
      <td class="px-3 py-3 text-xs text-slate-500">${r.id_producto}</td>
      <td class="px-3 py-3 font-medium">${escapeHTML(r.sku)}</td>
      <td class="px-3 py-3">
        <div class="font-medium">${escapeHTML(r.nombre)}</div>
        <div class="text-xs text-slate-500">${escapeHTML(r.categoria)} • ${escapeHTML(r.subcategoria)}</div>
        <div class="text-xs text-slate-500">
          ${r.talla?escapeHTML(r.talla)+' • ':''}${r.color?escapeHTML(r.color):''}${(r.talla||r.color)?' • ':''}
          Unidades: ${Number(r.unidades ?? 0)}
        </div>
      </td>
      <td class="px-3 py-3 text-sm">${r.costo_base!=null?Number(r.costo_base).toFixed(2):'-'}</td>
      <td class="px-3 py-3">${renderExistencias(r.existencias)}</td>
      <td class="px-3 py-3"><span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs border ${Number(r.activo)?'bg-emerald-50 text-emerald-700 border-emerald-200':'bg-slate-100 text-slate-600 border-slate-300'}">${Number(r.activo)?'Activo':'Inactivo'}</span></td>
      <td class="px-3 py-3 text-xs text-slate-500">${r.creado_en}</td>
      <td class="px-3 py-3 text-xs text-slate-500">${r.actualizado_en}</td>
      <td class="px-3 py-3">
        <div class="flex flex-wrap gap-2">
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 text-sm hover:bg-indigo-100" data-mov='${r.id_producto}' data-name="${escapeHTML(r.nombre)}" title="Registrar movimiento">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h11M9 21V3m12 11H10m4 8V7"/></svg> Mov.
          </button>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-700 text-sm hover:bg-sky-100" data-edit='${r.id_producto}' title="Editar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/></svg> Editar
          </button>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm hover:bg-rose-100" data-del='${r.id_producto}' data-name="${escapeHTML(r.nombre)}" title="Eliminar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg> Eliminar
          </button>
        </div>
      </td>
    </tr>`).join('');
  bindRowActions();
}

async function load(q=''){ const data = await api(`${BASE}?action=list${q?`&q=${encodeURIComponent(q)}`:''}`); renderRows(data); }
function bindRowActions(){ $$('#tbl-body [data-edit]').forEach(b=>b.onclick=onEdit); $$('#tbl-body [data-del]').forEach(b=>b.onclick=onDelete); $$('#tbl-body [data-mov]').forEach(b=>b.onclick=onMov); }

/* Crear */
$('#btn-prod-add')?.addEventListener('click', async ()=>{
  await loadOptions(); $('#form-prod-create').reset(); $('#c-activo').checked = true; $('#c-unidades').value = 0;
  const cat=$('#c-id_categoria'); if(cat){ cat.selectedIndex=0; cat.dispatchEvent(new Event('change')); }
  openModal('modal-prod-create');
});
$('#form-prod-create')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  fd.set('activo', $('#c-activo').checked ? '1' : '0');
  await api(BASE, {method:'POST', body: fd});
  closeModal('modal-prod-create'); load(search?.value || '');
});

/* Editar */
async function onEdit(e){
  const id = e.currentTarget.getAttribute('data-edit');
  const data = await api(`${BASE}?action=find&id=${id}`);
  await loadOptions();
  $('#e-id_producto').value = data.id_producto;
  $('#e-sku').value = data.sku || '';
  $('#e-nombre').value = data.nombre || '';
  $('#e-descripcion').value = data.descripcion || '';
  $('#e-costo_base').value = data.costo_base ?? '';
  $('#e-id_categoria').value = data.id_categoria; $('#e-id_categoria').dispatchEvent(new Event('change'));
  $('#e-id_subcategoria').value = data.id_subcategoria;
  $('#e-id_talla').value = data.id_talla || '';
  $('#e-id_color').value = data.id_color || '';
  $('#e-unidades').value = data.unidades ?? 0;
  $('#e-activo').checked = !!Number(data.activo);
  openModal('modal-prod-edit');
}
$('#form-prod-edit')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  fd.set('activo', $('#e-activo').checked ? '1' : '0');
  await api(BASE, {method:'POST', body: fd});
  closeModal('modal-prod-edit'); load(search?.value || '');
});

/* Eliminar */
async function onDelete(e){
  const id = e.currentTarget.getAttribute('data-del');
  const name = e.currentTarget.getAttribute('data-name');
  $('#del-prod-id').value = id; $('#del-prod-name').textContent = name || '';
  openModal('modal-prod-delete');
}
$('#form-prod-delete')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  await api(BASE, {method:'POST', body: fd});
  closeModal('modal-prod-delete'); load(search?.value || '');
});

/* Movimiento */
async function onMov(e){
  const id = e.currentTarget.getAttribute('data-mov'); await loadOptions();
  $('#form-mov').reset(); $('#m-id_producto').value = id; $('#m-tipo').value='IN'; openModal('modal-mov');
}
$('#form-mov')?.addEventListener('submit', async (e)=>{
  e.preventDefault(); const fd = new FormData(e.currentTarget);
  await api(BASE, {method:'POST', body: fd});
  closeModal('modal-mov'); load(search?.value || '');
});

/* Búsqueda */
search?.addEventListener('input', (e)=> load(e.currentTarget.value.trim()) );

/* ============ Tallas / Colores ============ */
$('#btn-tallas')?.addEventListener('click', async ()=>{ openModal('modal-tallas'); try{ await loadTallas(); }catch(err){ alert(err.message); }});
$('#btn-colores')?.addEventListener('click', async ()=>{ openModal('modal-colores'); try{ await loadColores(); }catch(err){ alert(err.message); }});

/* ---- Tallas ---- */
async function loadTallas(){
  const res = await api(`${BASE}?action=talla_list`);
  const list = $('#t-list');
  list.innerHTML = res.map(t=>`
    <li class="flex items-center justify-between px-3 py-2">
      <div class="min-w-0"><div class="text-sm font-medium">${escapeHTML(t.nombre)} <span class="text-xs text-slate-500">(${escapeHTML(t.codigo)})</span></div></div>
      <div class="flex gap-2">
        <button class="p-1.5 rounded-lg border text-sky-700 bg-sky-50 hover:bg-sky-100" data-t-edit='${t.id_talla}' title="Editar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/></svg>
        </button>
        <button class="p-1.5 rounded-lg border text-rose-700 bg-rose-50 hover:bg-rose-100" data-t-del='${t.id_talla}' title="Eliminar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
        </button>
      </div>
    </li>`).join('');

  list.querySelectorAll('[data-t-edit]').forEach(b => b.onclick = () => {
    const id = b.getAttribute('data-t-edit'); const item = res.find(x=>x.id_talla==id);
    $('#t-id').value = item.id_talla; $('#t-codigo').value = item.codigo; $('#t-nombre').value = item.nombre;
    $('#form-talla [name=action]').value = 'talla_update';
  });

  list.querySelectorAll('[data-t-del]').forEach(b => b.onclick = async () => {
    if(!confirm('¿Eliminar talla?')) return;
    const fd = new FormData(); fd.append('action','talla_delete'); fd.append('id_talla', b.getAttribute('data-t-del'));
    await api(BASE, {method:'POST', body: fd}); loadTallas(); loadOptions(true);
  });
}
$('#t-refresh')?.addEventListener('click', ()=>loadTallas());
$('#t-cancel')?.addEventListener('click', ()=>{
  $('#form-talla').reset(); $('#t-id').value=''; $('#form-talla [name=action]').value='talla_create';
});
$('#form-talla')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  await api(BASE, {method:'POST', body: fd});
  $('#form-talla').reset(); $('#t-id').value=''; $('#form-talla [name=action]').value='talla_create';
  await Promise.all([loadTallas(), loadOptions(true)]);
});

/* ---- Colores ---- */
async function loadColores(){
  const res = await api(`${BASE}?action=color_list`);
  const list = $('#c-list');
  list.innerHTML = res.map(c=>`
    <li class="flex items-center justify-between px-3 py-2">
      <div class="min-w-0 flex items-center gap-2">
        <span class="h-4 w-4 rounded" style="background:${c.hex||'#ddd'}"></span>
        <div><div class="text-sm font-medium">${escapeHTML(c.nombre)}</div><div class="text-xs text-slate-400">${c.hex||'(sin HEX)'}</div></div>
      </div>
      <div class="flex gap-2">
        <button class="p-1.5 rounded-lg border text-sky-700 bg-sky-50 hover:bg-sky-100" data-c-edit='${c.id_color}' title="Editar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/></svg>
        </button>
        <button class="p-1.5 rounded-lg border text-rose-700 bg-rose-50 hover:bg-rose-100" data-c-del='${c.id_color}' title="Eliminar">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg>
        </button>
      </div>
    </li>`).join('');

  list.querySelectorAll('[data-c-edit]').forEach(b => b.onclick = () => {
    const id = b.getAttribute('data-c-edit'); const item = res.find(x=>x.id_color==id);
    $('#col-id').value      = item.id_color;
    $('#col-nombre').value  = item.nombre || '';
    $('#col-hex').value     = item.hex || '#000000';
    $('#col-hex-text').value= item.hex || '#000000';
    $('#form-color [name=action]').value = 'color_update';
  });

  list.querySelectorAll('[data-c-del]').forEach(b => b.onclick = async ()=>{
    if(!confirm('¿Eliminar color?')) return;
    const fd = new FormData(); fd.append('action','color_delete'); fd.append('id_color', b.getAttribute('data-c-del'));
    await api(BASE, {method:'POST', body: fd}); loadColores(); loadOptions(true);
  });
}

/* sincronizar picker y texto HEX (ambos sentidos) */
(function syncColorHex(){
  const pick = $('#col-hex'); const txt = $('#col-hex-text');
  if(!pick || !txt) return;
  pick.addEventListener('input', ()=> { txt.value = pick.value; });
  txt.addEventListener('input', ()=>{
    const v = txt.value.trim();
    if(/^#[0-9a-fA-F]{6}$/.test(v)) pick.value = v;
  });
})();

$('#c-refresh')?.addEventListener('click', ()=>loadColores());
$('#c-cancel')?.addEventListener('click', ()=>{
  $('#form-color').reset(); $('#col-id').value=''; $('#col-hex').value='#000000'; $('#col-hex-text').value='#000000';
  $('#form-color [name=action]').value='color_create';
});
$('#form-color')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const txt=$('#col-hex-text'), pick=$('#col-hex'); if(txt && pick && /^#[0-9a-fA-F]{6}$/.test(txt.value.trim())) pick.value = txt.value.trim();
  const fd=new FormData(e.currentTarget); await api(BASE,{method:'POST', body: fd});
  $('#form-color').reset(); $('#col-id').value=''; $('#col-hex').value='#000000'; $('#col-hex-text').value='#000000';
  $('#form-color [name=action]').value='color_create'; await Promise.all([loadColores(), loadOptions(true)]);
});

/* Init */
(async function init(){
  try { await loadOptions(); const cat=$('#c-id_categoria'); if(cat){ cat.dispatchEvent(new Event('change')); } load(''); }
  catch(err){ alert('No se pudieron cargar las opciones de inventario.\n' + (err?.message || err)); }
})();
