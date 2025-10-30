// public/assets/js/dashboard.js

// Estado de charts para destruir al refrescar
let chartBodega, chartCategoria, chartSerie;

// Helper fetch
async function api(action, params = {}) {
  const qs = new URLSearchParams({ action, ...params });
  const res = await fetch(`api/dashboard.php?${qs}`, { credentials: 'same-origin' });
  // Lanza error si la respuesta HTTP no es 200/OK
  if (!res.ok) { 
      throw new Error(`Error HTTP: ${res.status}`);
  }
  const json = await res.json();
  if (!json.ok) throw new Error(json.message || 'Error API');
  return json.data || json;
}

// Pintar selects de filtros
async function loadFilters() {
  const data = await api('filters');
  const $bodega = document.querySelector('[name="bodega"]');
  const $categoria = document.querySelector('[name="categoria"]');
  const selBod = $bodega?.value || '';
  const selCat = $categoria?.value || '';

  if ($bodega) {
    $bodega.innerHTML = `<option value="">Todas</option>` +
      data.bodegas.map(b => `<option value="${b.id_bodega}">${b.nombre}</option>`).join('');
    if (selBod) $bodega.value = selBod;
  }
  if ($categoria) {
    $categoria.innerHTML = `<option value="">Todas</option>` +
      data.categorias.map(c => `<option value="${c.id_categoria}">${c.nombre}</option>`).join('');
    if (selCat) $categoria.value = selCat;
  }
}

// KPIs
async function renderKPIs(params) {
  const data = await api('kpis', params);
  document.getElementById('kpi-categorias').textContent = data.categories ?? data.categorias;
  document.getElementById('kpi-subcategorias').textContent = data.subcategorias;
  document.getElementById('kpi-bodegas').textContent = data.bodegas;
  document.getElementById('kpi-productos').textContent = data.productos;
}

// Barras: existencias por bodega
async function renderChartBodega(params) {
  const rows = await api('chart_bodega', params);
  const labels = rows.map(r => r.bodega);
  const values = rows.map(r => Number(r.total || 0));

  const ctx = document.getElementById('chartBodega');
  if (!ctx) return;
  if (chartBodega) chartBodega.destroy();
  chartBodega = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Unidades', data: values, borderWidth: 1 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });
}

// Barras: productos por categoría (antes dona)
async function renderChartCategoria(params) {
  const rows = await api('chart_categoria', params);
  const labels = rows.map(r => r.categoria);
  const values = rows.map(r => Number(r.total || 0));

  const ctx = document.getElementById('chartCategoria');
  if (!ctx) return;
  if (chartCategoria) chartCategoria.destroy();
  chartCategoria = new Chart(ctx, {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Productos', data: values, borderWidth: 1 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });
}

// Línea: ingresos por día
async function renderChartSerie(params) {
  const rows = await api('serie_ingresos', params);
  const labels = rows.map(r => r.fecha);
  const values = rows.map(r => Number(r.total || 0));

  const ctx = document.getElementById('chartSerie');
  if (!ctx) return;
  if (chartSerie) chartSerie.destroy();
  chartSerie = new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{ label: 'Ingresos (unidades)', data: values, fill: false, tension: 0.25, borderWidth: 2 }] },
    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
  });
}

// Tabla: stock bajo
async function renderStockBajo(params) {
  const box = document.getElementById('tb-stock-body');
  const rule = document.getElementById('stock-rule');
  if (!box) return;
  const res = await fetch(`api/dashboard.php?${new URLSearchParams({ action: 'stock_bajo', ...params })}`);
  const json = await res.json();
  if (!json.ok) throw new Error(json.message || 'Error API');

  const rows = json.data || [];
  if (rule) rule.textContent = `Regla: cantidad ≤ mínimo, o mínimo=0 y cantidad ≤ ${params.low || 10}`;

  if (!rows.length) {
    box.innerHTML = `<tr><td colspan="5" class="py-3 text-slate-500">Sin alertas con los filtros actuales.</td></tr>`;
    return;
    }
  box.innerHTML = rows.map(s => {
    // Cantidad a número entero
    const cantidad = Math.floor(Number(s.cantidad || 0));
    // Mínimo a número entero
    const minimo = Math.floor(Number(s.stock_minimo || 0));
    
    const critico = cantidad <= 0;
    const warning = !critico && (cantidad <= minimo || (minimo === 0 && cantidad <= Number(params.low || 10)));
    const trCls = critico ? 'bg-red-50' : (warning ? 'bg-amber-50' : '');
    return `<tr class="${trCls}">
      <td class="py-2 pr-4 font-medium">${escapeHtml(s.producto)} (${escapeHtml(s.sku)})</td>
      <td class="py-2 pr-4">${escapeHtml(s.bodega)}</td>
      <td class="py-2 pr-4 text-right">${cantidad}</td>
      <td class="py-2 pr-4 text-right">${minimo}</td>
      <td class="py-2 pr-4">
        ${critico ? '<span class="px-2 py-1 rounded-full text-xs bg-red-100 text-red-700">Crítico</span>'
                  : '<span class="px-2 py-1 rounded-full text-xs bg-amber-100 text-amber-700">Bajo</span>'}
      </td>
    </tr>`;
  }).join('');
}

// Tabla: últimos ingresos
async function renderUltimosIngresos(params) {
  const box = document.getElementById('tb-ingresos-body');
  const period = document.getElementById('periodo-ingresos');
  if (!box) return;
  const data = await api('ultimos_ingresos', params);
  if (period) period.textContent = `Período: ${params.desde} → ${params.hasta}`;
  if (!data.length) {
    box.innerHTML = `<tr><td colspan="5" class="py-3 text-slate-500">Sin movimientos de ingreso en el período.</td></tr>`;
    return;
  }
  box.innerHTML = data.map(m => {
    // Cantidad a número entero
    const cant = Math.floor(Number(m.cantidad || 0));
    // Costo unitario a dos decimales
    const costo = m.costo_unitario == null ? '-' : Number(m.costo_unitario).toFixed(2);
    return `<tr>
      <td class="py-2 pr-4">${escapeHtml(m.fecha_evento)}</td>
      <td class="py-2 pr-4 font-medium">${escapeHtml(m.producto)} (${escapeHtml(m.sku)})</td>
      <td class="py-2 pr-4">${escapeHtml(m.bodega)}</td>
      <td class="py-2 pr-4 text-right">${cant}</td>
      <td class="py-2 pr-4 text-right">${costo}</td>
    </tr>`;
  }).join('');
}

// Utilidad simple
function escapeHtml(str = '') {
  return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));
}

// Tomar parámetros del formulario
function readParams() {
  const f = document.getElementById('filters-form');
  const params = Object.fromEntries(new FormData(f).entries());
  if (!params.bodega) delete params.bodega;
  if (!params.categoria) delete params.categoria;
  if (!params.low) params.low = 10;
  return params;
}

// Orquestador de render
async function refreshAll(notify = true) {
  const params = readParams();
  await renderKPIs(params);
  // Espera a que todos los gráficos y tablas se rendericen
  await Promise.all([
    renderChartBodega(params),
    renderChartCategoria(params),
    renderChartSerie(params),
    renderStockBajo(params),
    renderUltimosIngresos(params),
  ]);
  
  // Mostrar alerta de aplicación de filtros usando SweetAlert2
  if (notify) {
    Swal.fire({
      icon: 'success',
      title: '¡Filtros aplicados!',
      text: 'Los datos del dashboard se han actualizado con éxito.',
      showConfirmButton: false,
      timer: 1500
    });
  }
}

// Init
document.addEventListener('DOMContentLoaded', async () => {
  try {
      await loadFilters();
      // No notificamos al cargar por primera vez
      await refreshAll(false); 
  } catch(error) {
      console.error("Error inicial al cargar:", error);
      Swal.fire({
          icon: 'error',
          title: 'Error de Carga',
          text: `No se pudieron cargar los datos iniciales. Revisa la consola para más detalles. (${error.message})`,
      });
  }


  const f = document.getElementById('filters-form');
  
  // 1. Manejador para el botón "Aplicar" (submit)
  f?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        // Notificamos al aplicar
        await refreshAll(true); 
    } catch (error) {
        console.error("Error al aplicar filtros:", error);
        // Muestra alerta de error si falla la recarga al aplicar
        Swal.fire({
            icon: 'error',
            title: 'Error al aplicar filtros',
            text: `No se pudo completar la actualización de datos. (${error.message})`,
        });
    }
  });
  
  // 2. Manejador para el enlace/botón "Limpiar"
  const clearBtn = document.querySelector('a[href="dashboard.php"]');
  clearBtn?.addEventListener('click', async (e) => { 
    // ¡CLAVE! Prevenir la navegación del enlace
    e.preventDefault(); 
    
    // Resetea el formulario a sus valores iniciales
    f.reset();
    
    // Ejecuta la carga de datos y ESPERA a que termine
    try {
        await refreshAll(false); 

        // *** CORRECCIÓN CLAVE: Usar setTimeout(0) para garantizar que la alerta se muestre. ***
        setTimeout(() => {
            Swal.fire({
                icon: 'info',
                title: 'Filtros limpiados',
                text: 'Se han restablecido los filtros a sus valores iniciales.',
                showConfirmButton: false,
                timer: 1500
            });
        }, 0);
    } catch (error) {
        console.error("Error al limpiar y recargar:", error);
        // Muestra alerta de error si falla la recarga al limpiar
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: `Ocurrió un error al limpiar los filtros. (${error.message})`,
        });
    }
  });
});