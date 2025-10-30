/* public/assets/js/users.js */

/* ==== Helpers ==== */
const $  = (s, c=document) => c.querySelector(s);
const $$ = (s, c=document) => Array.from(c.querySelectorAll(s));
const escapeHTML = (s='') => (s+'')
  .replaceAll('&','&amp;').replaceAll('<','&lt;')
  .replaceAll('>','&gt;').replaceAll('"','&quot;')
  .replaceAll("'","&#039;");

/* API base */
const BASE = (typeof window !== 'undefined' && window.API_BASE)
  ? window.API_BASE
  : '../../src/UserController.php';

// Variables para paginación
const limitSelector = $('#user-limit-selector');
const paginationControls = $('#user-pagination-controls');
const pageInfo = $('#user-page-info');

let currentPage = 1;
let currentLimit = Number(limitSelector?.value) || 10;
let totalRecords = 0;
  
// Obtener el ID del usuario logueado (necesario para el bloqueo de auto-eliminación/edición)
let LOGGED_USER_ID = null;
fetch('../src/Auth.php?action=user').then(r=>r.json()).then(data=>{
  if(data.ok && data.data) LOGGED_USER_ID = Number(data.data.id);
});

/* ---------- SweetAlert2 (Función de notificación/alerta) ---------- */
/** Muestra una notificación temporal de SweetAlert2 (Toast). */
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

/* Robust fetch JSON (INTEGRACIÓN SWEETALERT PARA ERRORES) */
async function api(url, opts={}) {
  const res  = await fetch(url, opts);
  const text = await res.text();
  let json;
  try { json = JSON.parse(text); }
  catch(e){
    console.error('Respuesta no-JSON de', url, '\n----\n', text, '\n----');
    Swal.fire('Error de Conexión', 'Respuesta inválida o incompleta del servidor.', 'error');
    throw new Error('Respuesta inválida del servidor');
  }
  if (!res.ok || json.ok === false) {
    Swal.fire('Error', json.error || `Error HTTP ${res.status}`, 'error');
    throw new Error(json.error || `Error HTTP ${res.status}`);
  }
  return json; // Devuelve el objeto JSON completo (para obtener data y total_records)
}

/* Modales */
const BACKDROP = $('#backdrop-user');
function openModal(id){
  const el = document.getElementById(id); if(!el) return;
  el.classList.remove('hidden'); el.classList.add('flex');
  BACKDROP?.classList.remove('hidden');
}
function closeModal(id){
  const el = document.getElementById(id); if(!el) return;
  el.classList.add('hidden'); el.classList.remove('flex');
  const anyOpen = $$('.fixed.inset-0.z-50').some(m => !m.classList.contains('hidden'));
  if(!anyOpen) BACKDROP?.classList.add('hidden');
}
document.addEventListener('click', (e)=>{
  const b = e.target.closest('[data-close]'); if(!b) return;
  closeModal(b.getAttribute('data-close'));
});
BACKDROP?.addEventListener('click', ()=>{
  $$('.fixed.inset-0.z-50').forEach(m=>m.classList.add('hidden'));
  BACKDROP.classList.add('hidden');
});

/* Elementos UI principales */
const tbody  = $('#tbl-body-user');
const btnAdd = $('#btn-user-add');
const search = $('#user-search');

/* Handlers de reportes */
function goReportGeneral(){
  const q = (search?.value || '').trim();
  const url = `${BASE}?action=report_general${q ? `&q=${encodeURIComponent(q)}` : ''}`;
  window.location.href = url;
}
$('#btn-user-pdf')?.addEventListener('click', goReportGeneral);

function openUserPdf(id){
  if(!id) return;
  const url = `${BASE}?action=report_detalle&id=${encodeURIComponent(id)}`;
  window.location.href = url;
}

/* ---------- Paginación (CORREGIDA) ---------- */
function renderPaginationControls() {
    if (!paginationControls || !pageInfo) return;

    const totalPages = Math.ceil(totalRecords / currentLimit);
    paginationControls.innerHTML = ''; // Limpiar controles

    const startRecord = (currentPage - 1) * currentLimit + 1;
    const endRecord = Math.min(currentPage * currentLimit, totalRecords);
    
    // Información de página
    if (totalRecords === 0) {
        pageInfo.textContent = 'No hay usuarios para mostrar.';
    } else {
        pageInfo.textContent = `Mostrando ${startRecord}-${endRecord} de ${totalRecords} usuarios.`;
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
          ? 'text-slate-400 cursor-not-allowed border bg-slate-100' // Estilo para disabled
          : page === currentPage 
            ? 'bg-sky-500 text-white font-semibold' 
            : 'text-slate-600 hover:bg-slate-100 border' // Estilo para normales
      }`;
      button.innerHTML = text;
      button.disabled = isDisabled;
      return button;
    };

    // Botón Anterior
    const btnPrev = createButton('Anterior', currentPage - 1, currentPage === 1);
    paginationControls.appendChild(btnPrev);

    // Lógica para mostrar un rango de páginas
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

function changeLimit() {
    currentLimit = Number(limitSelector.value);
    currentPage = 1; // Resetear página al cambiar el límite
    load();
}
limitSelector?.addEventListener('change', changeLimit);


/* Tabla */
function emptyRow(){
  return `<tr><td colspan="6" class="px-3 py-10 text-center text-slate-500">Sin usuarios</td></tr>`;
}

function renderRows(rows){
  if(!rows.length){ tbody.innerHTML = emptyRow(); return; }
  tbody.innerHTML = rows.map(r=>{
    const isAdmin = r.role === 'admin';
    const isLoggedUser = Number(r.id) === LOGGED_USER_ID;
    const roleClass = isAdmin ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-sky-50 text-sky-700 border-sky-200';
    const roleText = isAdmin ? 'Administrador' : 'Empleado';
    const deleteDisabled = isLoggedUser ? 'disabled title="No puedes eliminar tu propia cuenta"' : '';
    const deleteClass = isLoggedUser ? 'opacity-50 cursor-not-allowed' : 'hover:bg-rose-100';

    return `
    <tr class="border-b last:border-0 hover:bg-slate-50/60 align-top">
      <td class="px-3 py-3 text-xs text-slate-500">${r.id}</td>
      <td class="px-3 py-3 font-medium">${escapeHTML(r.full_name)}</td>
      <td class="px-3 py-3">${escapeHTML(r.email)}</td>
      <td class="px-3 py-3">
        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs border ${roleClass}">
          ${roleText}
        </span>
      </td>
      <td class="px-3 py-3 text-xs text-slate-500">${r.created_at}</td>
      <td class="px-3 py-3">
        <div class="flex flex-wrap gap-2">
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-sky-200 bg-sky-50 text-sky-700 text-sm hover:bg-sky-100" data-edit='${r.id}' title="Editar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/></svg> Editar
          </button>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm ${deleteClass}" data-del='${r.id}' data-name="${escapeHTML(r.full_name)}" ${deleteDisabled} title="Eliminar">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6"/></svg> Eliminar
          </button>
          <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-violet-200 bg-violet-50 text-violet-700 text-sm hover:bg-violet-100" data-pdf='${r.id}' title="Descargar ficha PDF">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V8m0 8l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V9.5a2 2 0 00-.586-1.414l-4.5-4.5A2 2 0 0013.5 3H6a2 2 0 00-2 2v13a2 2 0 002 2z"/>
            </svg> PDF
          </button>
        </div>
      </td>
    </tr>`;
  }).join('');
  bindRowActions();
  renderPaginationControls();
}

/** Recarga la lista de usuarios con paginación y búsqueda */
async function load() {
  const q = search?.value?.trim() || '';
  try{
    const params = new URLSearchParams({
        action: 'list',
        limit: currentLimit,
        page: currentPage,
        q: q
    });
    
    const result = await api(`${BASE}?${params.toString()}`);
    
    // 1. Actualizar el estado de la paginación global
    totalRecords = result.total_records;

    // 2. Ajustar currentPage si después de una operación quedó fuera de rango
    const totalPages = Math.ceil(totalRecords / currentLimit);
    if (currentPage > totalPages && totalPages > 0) {
        currentPage = totalPages;
        await load(); // Recargar con la última página válida
        return;
    }
    
    renderRows(result.data);

  } catch(err){
    // El error ya se maneja en `api` con SweetAlert (no-JSON o error backend)
    // Usamos Swal.fire (modal central) para errores críticos de carga
    if (!err.message.includes('HTTP')) { 
      Swal.fire({
          icon: 'error',
          title: 'Error de Carga',
          text: 'No se pudo cargar la lista de usuarios. Intente de nuevo más tarde.',
      });
    }
    tbody.innerHTML = emptyRow();
    if (paginationControls) paginationControls.innerHTML = '';
    if (pageInfo) pageInfo.textContent = '';
  }
}

function bindRowActions(){
  $$('#tbl-body-user [data-edit]').forEach(b=>b.onclick=onEdit);
  $$('#tbl-body-user [data-del]:not([disabled])').forEach(b=>b.onclick=onDelete);
  $$('#tbl-body-user [data-pdf]').forEach(b=>b.onclick=()=>openUserPdf(b.getAttribute('data-pdf')));
}

/* Crear (INTEGRACIÓN SWEETALERT) */
$('#btn-user-add')?.addEventListener('click', ()=>{
  $('#form-user-create').reset();
  $('#uc-role').value = 'employee';
  openModal('modal-user-create');
});
$('#form-user-create')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  try {
    await api(BASE, {method:'POST', body: fd});
    closeModal('modal-user-create');
    showToast('Usuario creado con éxito.', 'success'); 
    currentPage = 1; // Volver a la primera página después de crear
    load();
  } catch(err){
    // El error ya se maneja en `api` con SweetAlert.
  }
});

/* Editar (INTEGRACIÓN SWEETALERT) */
async function onEdit(e){
  const id = e.currentTarget.getAttribute('data-edit');
  try {
    const result = await api(`${BASE}?action=find&id=${id}`);
    const data = result.data;
    const isLoggedUser = Number(id) === LOGGED_USER_ID;

    $('#ue-id').value = data.id;
    $('#ue-full_name').value = data.full_name || '';
    $('#ue-email').value = data.email || '';
    $('#ue-password').value = '';
    $('#ue-role').value = data.role;
    
    // Bloquear edición de rol si es el propio usuario logueado
    $('#ue-role').disabled = isLoggedUser;
    if(isLoggedUser){
        $('#user-warning').classList.remove('hidden');
    } else {
        $('#user-warning').classList.add('hidden');
    }

    openModal('modal-user-edit');
  } catch(err) {
    // El error ya se maneja en `api` con SweetAlert.
  }
}
$('#form-user-edit')?.addEventListener('submit', async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const id = Number(fd.get('id'));

  // Asegurar que si el rol está deshabilitado (propio usuario), se envíe el valor actual
  if($('#ue-role').disabled) {
    const oldRole = $('#ue-role').value;
    fd.set('role', oldRole);
  }
  
  try {
    await api(BASE, {method:'POST', body: fd});
    closeModal('modal-user-edit');
    showToast('Usuario actualizado con éxito.', 'success'); 
    load(); // Recargar la página actual
  } catch(err){
    // El error ya se maneja en `api` con SweetAlert.
  }
});

/* Eliminar (USO DE SWEETALERT PARA CONFIRMACIÓN Y RESULTADO) */
async function onDelete(e){
  const id = e.currentTarget.getAttribute('data-del');
  const name = e.currentTarget.getAttribute('data-name');
  
  // 1. Confirmación con SweetAlert
  const result = await Swal.fire({
      title: '¿Estás seguro?',
      text: `Vas a eliminar al usuario: ${name}. ¡Esta acción es irreversible!`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
  });

  if (result.isConfirmed) {
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('id', id);
      try {
          // 2. Ejecutar eliminación
          await api(BASE, {method:'POST', body: fd});
          
          // 3. Notificación de éxito
          showToast(`Usuario "${name}" eliminado con éxito.`, 'success'); 
          load(); // Recargar la página actual
      } catch(err){
          // El error ya se maneja en `api` con SweetAlert (ej. no puedes eliminar tu propia cuenta)
      }
  }
}

/* Búsqueda */
// Al buscar, forzamos ir a la página 1
search?.addEventListener('input', (e)=> {
  clearTimeout(e.currentTarget.debounce);
  e.currentTarget.debounce = setTimeout(() => {
      currentPage = 1;
      load();
  }, 250);
});

/* Init */
(async function init(){
  await load();
})();