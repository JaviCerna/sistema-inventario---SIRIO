<?php
// views/categorias_modals.php (versión estética)
?>
<!-- Backdrop con blur -->
<div id="backdrop" class="fixed inset-0 hidden z-40 bg-slate-900/60 backdrop-blur-sm"></div>

<!-- ===== Modal Crear ===== -->
<div id="modal-create" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <div class="flex items-center gap-3">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
          <!-- plus icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12M6 12h12"/>
          </svg>
        </span>
        <div>
          <h3 class="text-base sm:text-lg font-semibold text-slate-800">Nueva categoría</h3>
          <p class="text-xs text-slate-500">Completa los campos y guarda para crearla.</p>
        </div>
      </div>
      <button data-close="modal-create" class="p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-400" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Form -->
    <form id="form-create" class="px-5 py-4 space-y-5">
      <div>
        <label class="text-sm font-medium text-slate-700">Nombre <span class="text-rose-600">*</span></label>
        <input name="nombre" required
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none
                      focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/40" />
        <p class="mt-1 text-xs text-slate-500">Ej.: Hogar, Textil, Electrónica…</p>
      </div>

      <div>
        <label class="text-sm font-medium text-slate-700">Descripción</label>
        <textarea name="descripcion" rows="3"
                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none
                         focus:border-emerald-500 focus:ring-2 focus:ring-emerald-400/40"
                  placeholder="Opcional: breve descripción de la categoría"></textarea>
      </div>

      <!-- Switch Activa -->
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-sm font-medium text-slate-700">Estado</p>
          <p class="text-xs text-slate-500">Activa para usarla inmediatamente.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer select-none">
          <input id="create-activa" name="activa" type="checkbox" class="sr-only peer" checked>
          <span class="h-6 w-11 rounded-full bg-slate-300 peer-checked:bg-emerald-500 transition-colors"></span>
          <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow
                       translate-x-0 peer-checked:translate-x-5 transition-transform"></span>
        </label>
      </div>

      <!-- Footer -->
      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
        <button type="button" data-close="modal-create"
                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
          Cancelar
        </button>
        <button
          class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400">
          Guardar
        </button>
      </div>

      <input type="hidden" name="action" value="create">
    </form>
  </div>
</div>

<!-- ===== Modal Editar (azul) ===== -->
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <div class="flex items-center gap-3">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
          <!-- pencil icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 3.487a2.25 2.25 0 113.182 3.182L7.5 19.313 3 21l1.688-4.5L16.862 3.487z"/>
          </svg>
        </span>
        <div>
          <h3 class="text-base sm:text-lg font-semibold text-slate-800">Editar categoría</h3>
          <p class="text-xs text-slate-500">Actualiza los datos y guarda los cambios.</p>
        </div>
      </div>
      <button data-close="modal-edit" class="p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-400" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Form -->
    <form id="form-edit" class="px-5 py-4 space-y-5">
      <input type="hidden" name="id_categoria" id="edit-id">

      <div>
        <label class="text-sm font-medium text-slate-700">Nombre <span class="text-rose-600">*</span></label>
        <input name="nombre" id="edit-nombre" required
               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none
                      focus:border-sky-500 focus:ring-2 focus:ring-sky-400/40" />
      </div>

      <div>
        <label class="text-sm font-medium text-slate-700">Descripción</label>
        <textarea name="descripcion" id="edit-descripcion" rows="3"
                  class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none
                         focus:border-sky-500 focus:ring-2 focus:ring-sky-400/40"></textarea>
      </div>

      <!-- Switch Activa -->
      <div class="flex items-center justify-between">
        <div class="space-y-0.5">
          <p class="text-sm font-medium text-slate-700">Estado</p>
          <p class="text-xs text-slate-500">Inactiva la ocultará en usos futuros.</p>
        </div>
        <label class="relative inline-flex items-center cursor-pointer select-none">
          <input id="edit-activa" name="activa" type="checkbox" class="sr-only peer">
          <span class="h-6 w-11 rounded-full bg-slate-300 peer-checked:bg-sky-500 transition-colors"></span>
          <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow
                       translate-x-0 peer-checked:translate-x-5 transition-transform"></span>
        </label>
      </div>

      <!-- Footer -->
      <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 pt-2">
        <button type="button" data-close="modal-edit"
                class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
          Cancelar
        </button>
        <button
          class="px-4 py-2 rounded-xl bg-sky-600 text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-400">
          Actualizar
        </button>
      </div>

      <input type="hidden" name="action" value="update">
    </form>
  </div>
</div>


<!-- ===== Modal Eliminar ===== -->
<div id="modal-delete" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <div class="flex items-center gap-3">
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
          <!-- trash icon -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0a1 1 0 01-1-1V5a1 1 0 011-1h8a1 1 0 011 1v1m-10 0h10"/>
          </svg>
        </span>
        <div>
          <h3 class="text-base sm:text-lg font-semibold text-slate-800">Eliminar categoría</h3>
          <p class="text-xs text-slate-500">Esta acción no se puede deshacer.</p>
        </div>
      </div>
      <button data-close="modal-delete" class="p-2 rounded-lg hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-rose-400" aria-label="Cerrar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Body -->
    <div class="px-5 pt-5">
      <p class="text-sm text-slate-600">
        ¿Seguro que deseas eliminar <span id="del-name" class="font-semibold text-slate-800"></span>?
      </p>
    </div>

    <!-- Footer -->
    <form id="form-delete" class="px-5 pb-5 pt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
      <input type="hidden" name="id_categoria" id="del-id">
      <input type="hidden" name="action" value="delete">

      <button type="button" data-close="modal-delete"
              class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">
        Cancelar
      </button>
      <button class="px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-400">
        Eliminar
      </button>
    </form>
  </div>
</div>
