<?php
// views/inventario_modals.php
?>
<!-- Backdrop -->
<div id="backdrop-prod" class="fixed inset-0 bg-black/40 hidden z-40"></div>

<!-- =================== MODAL CREAR PRODUCTO =================== -->
<div id="modal-prod-create" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-2xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Nuevo producto</h3>
      <button data-close="modal-prod-create" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <form id="form-prod-create" method="post" class="px-5 py-4 space-y-4">
      <input type="hidden" name="action" value="create">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">SKU *</label>
          <input name="sku" id="c-sku" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Nombre *</label>
          <input name="nombre" id="c-nombre" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Categoría *</label>
          <select name="id_categoria" id="c-id_categoria" required class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Subcategoría *</label>
          <select name="id_subcategoria" id="c-id_subcategoria" required class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-medium">Talla</label>
          <select name="id_talla" id="c-id_talla" class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Color</label>
          <select name="id_color" id="c-id_color" class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Unidades *</label>
          <input name="unidades" id="c-unidades" type="number" min="0" step="1" value="0" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Costo base</label>
          <input name="costo_base" id="c-costo_base" type="number" step="0.01" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div class="flex items-end gap-2">
          <input id="c-activo" name="activo" type="checkbox" class="h-4 w-4" checked>
          <label for="c-activo" class="text-sm">Activo</label>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium">Descripción</label>
        <textarea name="descripcion" id="c-descripcion" rows="3" class="mt-1 w-full rounded-xl border px-3 py-2.5"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-close="modal-prod-create" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- =================== MODAL EDITAR PRODUCTO =================== -->
<div id="modal-prod-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-2xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Editar producto</h3>
      <button data-close="modal-prod-edit" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <form id="form-prod-edit" method="post" class="px-5 py-4 space-y-4">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id_producto" id="e-id_producto">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">SKU *</label>
          <input name="sku" id="e-sku" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Nombre *</label>
          <input name="nombre" id="e-nombre" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Categoría *</label>
          <select name="id_categoria" id="e-id_categoria" required class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Subcategoría *</label>
          <select name="id_subcategoria" id="e-id_subcategoria" required class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-medium">Talla</label>
          <select name="id_talla" id="e-id_talla" class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Color</label>
          <select name="id_color" id="e-id_color" class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Unidades *</label>
          <input name="unidades" id="e-unidades" type="number" min="0" step="1" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Costo base</label>
          <input name="costo_base" id="e-costo_base" type="number" step="0.01" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div class="flex items-end gap-2">
          <input id="e-activo" name="activo" type="checkbox" class="h-4 w-4">
          <label for="e-activo" class="text-sm">Activo</label>
        </div>
      </div>

      <div>
        <label class="text-sm font-medium">Descripción</label>
        <textarea name="descripcion" id="e-descripcion" rows="3" class="mt-1 w-full rounded-xl border px-3 py-2.5"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-close="modal-prod-edit" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-sky-600 text-white">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<!-- =================== MODAL ELIMINAR PRODUCTO =================== -->
<div id="modal-prod-delete" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="px-5 py-4">
      <h3 class="text-lg font-semibold">Eliminar producto</h3>
      <p class="text-sm text-slate-600 mt-1">¿Seguro que deseas eliminar <span id="del-prod-name" class="font-semibold"></span>?</p>
      <form id="form-prod-delete" method="post" class="mt-5 flex justify-end gap-2">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id_producto" id="del-prod-id">
        <button type="button" data-close="modal-prod-delete" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-rose-600 text-white">Eliminar</button>
      </form>
    </div>
  </div>
</div>

<!-- =================== MODAL MOVIMIENTO =================== -->
<div id="modal-mov" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-lg bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Movimiento de inventario</h3>
      <button data-close="modal-mov" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <form id="form-mov" method="post" class="px-5 py-4 space-y-4">
      <input type="hidden" name="action" value="mov">
      <input type="hidden" name="id_producto" id="m-id_producto">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Bodega *</label>
          <select name="id_bodega" id="m-id_bodega" required class="mt-1 w-full rounded-xl border px-3 py-2.5"></select>
        </div>
        <div>
          <label class="text-sm font-medium">Tipo *</label>
          <select name="tipo" id="m-tipo" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
            <option value="IN">Entrada</option>
            <option value="OUT">Salida</option>
            <option value="AJUSTE">Ajuste</option>
          </select>
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Cantidad *</label>
          <input name="cantidad" id="m-cantidad" type="number" step="0.001" min="0" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Costo unitario</label>
          <input name="costo_unitario" id="m-costo" type="number" step="0.0001" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Fecha del evento *</label>
          <input name="fecha_evento" id="m-fecha" type="datetime-local" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Motivo</label>
          <input name="motivo" id="m-motivo" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-close="modal-mov" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-indigo-600 text-white">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- =================== MODAL TALLAS =================== -->
<div id="modal-tallas" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-3xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Tallas</h3>
      <button data-close="modal-tallas" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Izquierda: formulario -->
      <form id="form-talla" method="post" class="space-y-4">
        <input type="hidden" name="action" value="talla_create">
        <input type="hidden" name="id_talla" id="t-id">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-sm font-medium">Código *</label>
            <input id="t-codigo" name="codigo" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
          </div>
          <div>
            <label class="text-sm font-medium">Nombre *</label>
            <input id="t-nombre" name="nombre" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
          </div>
        </div>
        <div class="flex items-center justify-between pt-1">
          <button id="t-refresh" type="button" class="px-3 py-2 rounded-lg border">Actualizar</button>
          <div class="flex gap-2">
            <button id="t-cancel" type="button" class="px-4 py-2 rounded-xl border">Limpiar</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">Guardar</button>
          </div>
        </div>
      </form>

      <!-- Derecha: listado -->
      <div>
        <h4 class="text-sm font-medium text-slate-700 mb-2">Tallas existentes</h4>
        <ul id="t-list" class="divide-y rounded-xl border"></ul>
      </div>
    </div>
  </div>
</div>

<!-- =================== MODAL COLORES =================== -->
<div id="modal-colores" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-3xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Colores</h3>
      <button data-close="modal-colores" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <div class="px-5 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Izquierda: formulario -->
      <form id="form-color" method="post" class="space-y-4">
        <input type="hidden" name="action" value="color_create">
        <input type="hidden" name="id_color" id="col-id">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="text-sm font-medium">Nombre *</label>
            <input id="col-nombre" name="nombre" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
          </div>
          <br>
          <div>
            <label class="text-sm font-medium">Color HEX</label>
            <div class="flex items-center gap-2">
              <input id="col-hex" name="hex" type="color" class="h-10 w-16 rounded border" value="#000000">
              <input id="col-hex-text" class="flex-1 rounded-xl border px-3 py-2.5" placeholder="#000000" value="#000000">
            </div>
          </div>
        </div>
        <div class="flex items-center justify-between pt-1">
          <button id="c-refresh" type="button" class="px-3 py-2 rounded-lg border">Actualizar</button>
          <div class="flex gap-2">
            <button id="c-cancel" type="button" class="px-4 py-2 rounded-xl border">Limpiar</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white">Guardar</button>
          </div>
        </div>
      </form>

      <!-- Derecha: listado -->
      <div>
        <h4 class="text-sm font-medium text-slate-700 mb-2">Colores existentes</h4>
        <ul id="c-list" class="divide-y rounded-xl border"></ul>
      </div>
    </div>
  </div>
</div>
