
/* ─────────────────────────────────────────────
   HELPERS
───────────────────────────────────────────── */
const fmt     = (n, d=0) => Number(n).toLocaleString('es-AR', {minimumFractionDigits:d, maximumFractionDigits:d});
const fmtPeso = n => '$' + fmt(n, 0);
const MESES   = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

/* ─────────────────────────────────────────────
   DATOS DEMO — reemplazar por fetch() a la API
   que ejecute SELECT * FROM v_recorridos_costo
   WHERE fecha_planificada BETWEEN :desde AND :hasta
───────────────────────────────────────────── */
/*function generarDatos() {
  const repartidores = ['García, M.','López, F.','Romero, J.','Fernández, A.','Torres, L.','Díaz, C.'];
  const turnos = ['Mañana','Tarde'];
  const precios = [900,920,960,1010,1050,1100,1150,1200,1220,1250,1280,1300];
  const rows = []; let id = 1;
  for (let mes = 0; mes < 12; mes++) {
    const precio = precios[mes];
    const n = 4 + Math.floor(Math.random() * 5);
    for (let r = 0; r < n; r++) {
      const km      = 40 + Math.round(Math.random() * 120);
      const litros  = +(km / 9).toFixed(3);
      const costo   = +(litros * precio).toFixed(2);
      const pedidos = 3 + Math.floor(Math.random() * 12);
      const dia     = 1 + Math.floor(Math.random() * 27);
      rows.push({
        id_ruta: id++,
        fecha: `2024-${String(mes+1).padStart(2,'0')}-${String(dia).padStart(2,'0')}`,
        repartidor: repartidores[Math.floor(Math.random() * repartidores.length)],
        turno: turnos[Math.floor(Math.random() * 2)],
        km, litros, precio, costo, pedidos,
        cpp: +(costo / pedidos).toFixed(2)
      });
    }
  }
  return rows;
}

const TODOS = generarDatos();*/


let TODOS = [];

async function cargarDatosServidor(desde, hasta) {
  const resp = await fetch(`/CONTROLADOR/reportes/recorridos.php?desde=${desde}&hasta=${hasta}`);
  if (!resp.ok) throw new Error('Error al obtener recorridos');
  const data = await resp.json();
  // Asegura formatos: fecha en 'YYYY-MM-DD', números como Number
  TODOS = data.map(r => ({
    id_ruta: r.id_ruta,
    fecha: r.fecha,
    repartidor: r.repartidor,
    turno: r.turno,
    km: Number(r.km),
    litros: Number(r.litros),
    precio: Number(r.precio),
    costo: Number(r.costo),
    pedidos: Number(r.pedidos),
    cpp: Number(r.cpp)
  }));
  filtrados = [...TODOS];
  renderTodo();
}


async function cargarDatosMensuales() {
  const resp = await fetch('/CONTROLADOR/reportes/recorridosMensual.php');
  const j = await resp.json();
  // j.gastoMensual y j.evolucion -> procesar para chart
}

/* ─────────────────────────────────────────────
   ESTADO
───────────────────────────────────────────── */
let filtrados = [...TODOS];
let sortKey = 'fecha', sortDir = 1;
let pagina = 1;
const POR_PAG = 12;
let chartM = null, chartP = null;

/* ─────────────────────────────────────────────
   FILTRO
───────────────────────────────────────────── */
/*function aplicarFiltro() {
  const desde = document.getElementById('fechaDesde').value;
  const hasta = document.getElementById('fechaHasta').value;
  filtrados = TODOS.filter(r => r.fecha >= desde && r.fecha <= hasta);
  pagina = 1;
  renderTodo();
}*/

async function aplicarFiltro() {
  const desde = document.getElementById('fechaDesde').value;
  const hasta = document.getElementById('fechaHasta').value;
  try {
    await cargarDatosServidor(desde, hasta);
    pagina = 1;
    renderTodo();
  } catch (e) {
    console.error(e);
    alert('Error al cargar datos');
  }
}


function filtrarTabla() {
  const q    = document.getElementById('searchInput').value.toLowerCase();
  const base = getBase();
  filtrados  = q ? base.filter(r =>
    r.repartidor.toLowerCase().includes(q) ||
    r.fecha.includes(q) ||
    String(r.id_ruta).includes(q)
  ) : base;
  pagina = 1;
  renderTabla(); renderPag();
}

function getBase() {
  const desde = document.getElementById('fechaDesde').value;
  const hasta = document.getElementById('fechaHasta').value;
  return TODOS.filter(r => r.fecha >= desde && r.fecha <= hasta);
}

/* ─────────────────────────────────────────────
   RENDER TODO
───────────────────────────────────────────── */
function renderTodo() {
  renderKPIs(); renderCharts(); renderRanking(); renderTabla(); renderPag();
  document.getElementById('totalInfo').innerHTML =
    `Total de registros en el período: <strong>${filtrados.length}</strong>`;
}

/* ─────────────────────────────────────────────
   KPIs
───────────────────────────────────────────── */
function renderKPIs() {
  const d      = filtrados;
  const gasto  = d.reduce((a,r) => a + r.costo, 0);
  const km     = d.reduce((a,r) => a + r.km, 0);
  const litros = d.reduce((a,r) => a + r.litros, 0);
  const cpp    = d.length ? d.reduce((a,r) => a + r.cpp, 0) / d.length : 0;
  const precio = d.length ? d.reduce((a,r) => a + r.precio, 0) / d.length : 0;

  document.getElementById('kpiGasto').textContent    = fmtPeso(gasto);
  document.getElementById('kpiGastoSub').textContent = `${d.length} rutas completadas`;
  document.getElementById('kpiKm').textContent       = fmt(km);
  document.getElementById('kpiKmSub').textContent    = `${fmt(litros,1)} litros usados`;
  document.getElementById('kpiCPP').textContent      = fmtPeso(cpp);
  document.getElementById('kpiPrecio').textContent   = fmtPeso(precio);
  document.getElementById('totalInfo').innerHTML =
    `Total de registros en el período: <strong>${d.length}</strong>`;
}

/* ─────────────────────────────────────────────
   DATOS MENSUALES
───────────────────────────────────────────── */
function getMeses() {
  const mapa = {};
  filtrados.forEach(r => {
    const m = r.fecha.slice(0,7);
    if (!mapa[m]) mapa[m] = { gasto:0, precios:[] };
    mapa[m].gasto += Number(r.costo) || 0;
    mapa[m].precios.push(Number(r.precio) || 0);
  });
  const keys = Object.keys(mapa).sort();
  return {
    labels:  keys.map(k => { const [y,m] = k.split('-'); return `${MESES[+m-1]} ${y.slice(2)}`; }),
    gastos:  keys.map(k => +mapa[k].gasto.toFixed(0)),
    precios: keys.map(k => +(mapa[k].precios.reduce((a,b)=>a+b,0)/mapa[k].precios.length).toFixed(0))
  };
}

/* ─────────────────────────────────────────────
   CHARTS
───────────────────────────────────────────── */
const chartOpts = {
  responsive: true, maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: { backgroundColor:'#fff', borderColor:'#e3e6f0', borderWidth:1,
               titleColor:'#5a5c69', bodyColor:'#858796',
               titleFont:{family:'Source Sans Pro'}, bodyFont:{family:'Source Sans Pro'} }
  },
  scales: {
    x: { grid:{ color:'rgba(0,0,0,0.05)' }, ticks:{ color:'#858796', font:{size:11} } },
    y: { grid:{ color:'rgba(0,0,0,0.05)' }, ticks:{ color:'#858796', font:{size:11} } }
  }
};

function renderCharts() {
  const { labels, gastos, precios } = getMeses();

  if (chartM) chartM.destroy();
  chartM = new Chart(document.getElementById('chartMensual'), {
    type: 'bar',
    data: { labels, datasets: [{
      label: 'Gasto ($)',
      data: gastos,
      backgroundColor: 'rgba(28,200,138,.25)',
      borderColor: '#1cc88a',
      borderWidth: 1.5,
      borderRadius: 4,
      borderSkipped: false
    }]},
    options: { ...chartOpts,
      plugins: { ...chartOpts.plugins, tooltip: { ...chartOpts.plugins.tooltip,
        callbacks:{ label: ctx => ' ' + fmtPeso(ctx.raw) }
      }},
      scales: { ...chartOpts.scales,
        y: { ...chartOpts.scales.y,
          ticks: { ...chartOpts.scales.y.ticks, callback: v => '$'+fmt(v/1000)+'k' }
        }
      }
    }
  });

  if (chartP) chartP.destroy();
  chartP = new Chart(document.getElementById('chartPrecio'), {
    type: 'line',
    data: { labels, datasets: [{
      label: '$/litro',
      data: precios,
      borderColor: '#4e73df',
      backgroundColor: 'rgba(78,115,223,.08)',
      borderWidth: 2,
      pointBackgroundColor: '#4e73df',
      pointRadius: 4,
      tension: 0.35,
      fill: true
    }]},
    options: { ...chartOpts,
      plugins: { ...chartOpts.plugins, tooltip: { ...chartOpts.plugins.tooltip,
        callbacks:{ label: ctx => ' $'+fmt(ctx.raw,0)+'/litro' }
      }},
      scales: { ...chartOpts.scales,
        y: { ...chartOpts.scales.y,
          ticks: { ...chartOpts.scales.y.ticks, callback: v => '$'+fmt(v) }
        }
      }
    }
  });
}

/* ─────────────────────────────────────────────
   RANKING
───────────────────────────────────────────── */
const COLORES_RANK = ['#1cc88a','#4e73df','#f6c23e','#e74a3b','#858796','#36b9cc'];

function renderRanking() {
  const mapa = {};
  filtrados.forEach(r => {
    if (!mapa[r.repartidor]) mapa[r.repartidor] = { km:0, costo:0, rutas:0 };
    mapa[r.repartidor].km    += r.km;
    mapa[r.repartidor].costo += r.costo;
    mapa[r.repartidor].rutas++;
  });
  const lista = Object.entries(mapa).map(([nombre,v]) => ({nombre,...v}))
    .sort((a,b) => b.km - a.km);
  const maxKm = lista[0]?.km || 1;
  const el = document.getElementById('rankingGrid');

  if (!lista.length) {
    el.innerHTML = '<div class="col-12 text-center text-muted py-4">Sin datos en el período</div>';
    return;
  }

  el.innerHTML = lista.map((r, i) => {
    const color = COLORES_RANK[i] || '#858796';
    const pct   = ((r.km / maxKm) * 100).toFixed(1);
    const medal = i===0 ? '🥇' : i===1 ? '🥈' : i===2 ? '🥉' : `${i+1}.`;
    return `
      <div class="col-sm-6 col-lg-4 mb-3">
        <div class="d-flex align-items-center mb-1">
          <span style="font-size:16px;margin-right:8px;">${medal}</span>
          <strong class="text-sm">${r.nombre}</strong>
          <span class="ml-auto text-muted" style="font-size:12px;">${fmt(r.km)} km</span>
        </div>
        <div class="progress mb-1" style="height:8px;">
          <div class="progress-bar" role="progressbar"
               style="width:${pct}%;background:${color};" aria-valuenow="${pct}"></div>
        </div>
        <small class="text-muted">${r.rutas} ruta${r.rutas>1?'s':''} · ${fmtPeso(r.costo)} total</small>
      </div>`;
  }).join('');
}

/* ─────────────────────────────────────────────
   TABLA
───────────────────────────────────────────── */
document.querySelectorAll('thead th[data-sort]').forEach(th => {
  th.addEventListener('click', () => {
    const key = th.dataset.sort;
    if (sortKey === key) sortDir *= -1; else { sortKey = key; sortDir = 1; }
    document.querySelectorAll('thead th').forEach(t => t.classList.remove('sorted'));
    th.classList.add('sorted');
    const icon = th.querySelector('.sort-icon');
    document.querySelectorAll('.sort-icon').forEach(i => { i.className='sort-icon fas fa-sort'; });
    if (icon) icon.className = `sort-icon fas fa-sort-${sortDir===1?'up':'down'}`;
    pagina = 1;
    renderTabla(); renderPag();
  });
});

function getOrdenados() {
  return [...filtrados].sort((a,b) => {
    const va = a[sortKey], vb = b[sortKey];
    if (typeof va === 'string') return va.localeCompare(vb) * sortDir;
    return (va - vb) * sortDir;
  });
}

function renderTabla() {
  const datos     = getOrdenados();
  const inicio    = (pagina - 1) * POR_PAG;
  const paginados = datos.slice(inicio, inicio + POR_PAG);
  const body      = document.getElementById('tablaBody');
  const foot      = document.getElementById('tablaFoot');

  if (!paginados.length) {
    body.innerHTML = `<tr><td colspan="10" class="text-center text-muted py-4">
      Sin registros para los filtros aplicados</td></tr>`;
    foot.innerHTML = '';
    document.getElementById('tablaInfo').textContent = '0 registros';
    return;
  }

  body.innerHTML = paginados.map(r => `
    <tr>
      <td><strong>#${r.id_ruta}</strong></td>
      <td>${r.fecha}</td>
      <td>${r.repartidor}</td>
      <td>
        <span class="badge badge-pill ${r.turno==='Mañana'?'badge-manana':'badge-tarde'}">
          ${r.turno}
        </span>
      </td>
      <td class="text-center">${fmt(r.km)} km</td>
      <td class="text-center">${fmt(r.litros,1)} L</td>
      <td class="text-center">${fmtPeso(r.precio)}</td>
      <td class="text-center"><strong>${fmtPeso(r.costo)}</strong></td>
      <td class="text-center">${r.pedidos}</td>
      <td class="text-center">${fmtPeso(r.cpp)}</td>
    </tr>
  `).join('');

  // Totales de página visible
  const totGasto = paginados.reduce((a,r)=>a+r.costo,0);
  const totKm    = paginados.reduce((a,r)=>a+r.km,0);
  const totPed   = paginados.reduce((a,r)=>a+r.pedidos,0);
  foot.innerHTML = `
    <tr class="table-secondary font-weight-bold">
      <td colspan="4" class="text-right text-muted" style="font-size:11px;">Subtotal página:</td>
      <td class="text-center">${fmt(totKm)} km</td>
      <td></td><td></td>
      <td class="text-center">${fmtPeso(totGasto)}</td>
      <td class="text-center">${totPed}</td>
      <td></td>
    </tr>`;

  document.getElementById('tablaInfo').textContent =
    `${datos.length} registros · página ${pagina} de ${Math.ceil(datos.length/POR_PAG)}`;
}

/* ─────────────────────────────────────────────
   PAGINACIÓN
───────────────────────────────────────────── */
function renderPag() {
  const total = Math.ceil(filtrados.length / POR_PAG);
  const ul    = document.getElementById('pagBtns');
  if (total <= 1) { ul.innerHTML = ''; return; }

  const btn = (label, page, disabled=false, active=false) =>
    `<li class="page-item ${disabled?'disabled':''} ${active?'active':''}">
       <a class="page-link" href="#" onclick="irPag(${page});return false;">${label}</a>
     </li>`;

  let html = btn('«', pagina-1, pagina===1);
  for (let i=1; i<=total; i++) {
    if (total>7 && i>2 && i<total-1 && Math.abs(i-pagina)>1) {
      if (i===3||i===total-2) html += `<li class="page-item disabled"><a class="page-link">…</a></li>`;
      continue;
    }
    html += btn(i, i, false, i===pagina);
  }
  html += btn('»', pagina+1, pagina===total);
  ul.innerHTML = html;
}

function irPag(n) {
  const total = Math.ceil(filtrados.length / POR_PAG);
  if (n<1||n>total) return;
  pagina = n;
  renderTabla(); renderPag();
}

/* ─────────────────────────────────────────────
   INIT
───────────────────────────────────────────── */
const hoy   = new Date();
const iniAno = new Date(hoy.getFullYear(), 0, 1);
document.getElementById('fechaHasta').value = hoy.toISOString().slice(0,10);
document.getElementById('fechaDesde').value = iniAno.toISOString().slice(0,10);
aplicarFiltro();
