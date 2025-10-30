<?php
// views/usuarios_modals.php
?>
<div id="backdrop-user" class="fixed inset-0 bg-black/40 hidden z-40"></div>

<div id="modal-user-create" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Nuevo Usuario</h3>
      <button data-close="modal-user-create" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <form id="form-user-create" method="post" class="px-5 py-4 space-y-4">
      <input type="hidden" name="action" value="create">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Nombre Completo *</label>
          <input name="full_name" id="uc-full_name" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Email *</label>
          <input name="email" id="uc-email" type="email" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Contraseña * (Mín. 6 caracteres)</label>
          <input name="password" id="uc-password" type="password" required minlength="6" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Rol *</label>
          <select name="role" id="uc-role" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
            <option value="employee">Empleado</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-close="modal-user-create" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-emerald-600 text-white">Guardar</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-user-edit" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-xl bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="flex items-center justify-between px-5 py-4 border-b">
      <h3 class="text-lg font-semibold">Editar Usuario</h3>
      <button data-close="modal-user-edit" type="button" class="p-2 rounded-lg hover:bg-slate-100">✕</button>
    </div>
    <form id="form-user-edit" method="post" class="px-5 py-4 space-y-4">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="ue-id">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Nombre Completo *</label>
          <input name="full_name" id="ue-full_name" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Email *</label>
          <input name="email" id="ue-email" type="email" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Nueva Contraseña (Dejar vacío para no cambiar)</label>
          <input name="password" id="ue-password" type="password" minlength="6" class="mt-1 w-full rounded-xl border px-3 py-2.5">
        </div>
        <div>
          <label class="text-sm font-medium">Rol *</label>
          <select name="role" id="ue-role" required class="mt-1 w-full rounded-xl border px-3 py-2.5">
            <option value="employee">Empleado</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
      </div>
      <div id="user-warning" class="hidden text-sm text-rose-600 p-3 bg-rose-50 rounded-xl border border-rose-200">
        <p class="font-medium">⚠️ Advertencia:</p>
        <p>No puedes cambiar el rol ni eliminar tu propia cuenta.</p>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" data-close="modal-user-edit" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-sky-600 text-white">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-user-delete" class="fixed inset-0 z-50 hidden items-center justify-center p-4 sm:p-6">
  <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-2xl ring-1 ring-black/5">
    <div class="px-5 py-4">
      <h3 class="text-lg font-semibold">Eliminar Usuario</h3>
      <p class="text-sm text-slate-600 mt-1">¿Seguro que deseas eliminar a <span id="del-user-name" class="font-semibold"></span>?</p>
      <form id="form-user-delete" method="post" class="mt-5 flex justify-end gap-2">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="del-user-id">
        <button type="button" data-close="modal-user-delete" class="px-4 py-2 rounded-xl border">Cancelar</button>
        <button class="px-4 py-2 rounded-xl bg-rose-600 text-white">Eliminar</button>
      </form>
    </div>
  </div>
</div>