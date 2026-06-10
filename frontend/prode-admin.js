// =====================================================================
// Prode Admin — frontend/prode-admin.js
// =====================================================================

const PRODE = '/prode';

// ── Escape HTML ───────────────────────────────────────────────────────
function esc(str) {
  return String(str ?? '').replace(/[&<>"']/g, (m) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m])
  );
}

// ── Formatear fecha/hora ─────────────────────────────────────────────
function normalizeIso(str) {
  if (str.includes('T')) return str;          // ya es ISO
  if (str.includes(' ')) return str.replace(' ', 'T'); // datetime con espacio
  return str + 'T00:00:00';                   // solo fecha
}
function fmtDatetime(str) {
  if (!str) return '—';
  const d = new Date(normalizeIso(str));
  return d.toLocaleString('es-AR', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' });
}
function fmtDate(str) {
  if (!str) return '—';
  const d = new Date(normalizeIso(str));
  return d.toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// ── Tab Management ────────────────────────────────────────────────────
const tabBtns = document.querySelectorAll('.prode-tab');
const tabContents = document.querySelectorAll('.prode-tab-content');
let activeTab = 'fechas';

function setTab(name) {
  activeTab = name;
  tabBtns.forEach((t) => {
    const on = t.dataset.tab === name;
    t.classList.toggle('active', on);
    t.classList.toggle('text-on-surface-variant', !on);
  });
  tabContents.forEach((c) => {
    c.classList.toggle('hidden', c.id !== 'tab' + name.charAt(0).toUpperCase() + name.slice(1));
  });
  if (name === 'ranking') loadRanking(document.getElementById('rankingJornadaSelect').value);
  if (name === 'participantes') loadParticipantes();
}

tabBtns.forEach((t) => t.addEventListener('click', () => setTab(t.dataset.tab)));
setTab('fechas');

// ── Prode ON/OFF ──────────────────────────────────────────────────────
let prodeEnabled = false;

function applyProdeUI(enabled) {
  prodeEnabled = enabled;
  const badge = document.getElementById('prodeGlobalStatusBadge');
  const dot   = document.getElementById('prodeGlobalStatusDot');
  const text  = document.getElementById('prodeGlobalStatusText');
  const btn   = document.getElementById('btnToggleProde');
  if (enabled) {
    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border bg-green-500/15 text-green-400 border-green-500/30';
    dot.className   = 'w-2 h-2 rounded-full inline-block bg-green-400';
    text.textContent = 'Activo';
    btn.className   = 'h-9 rounded-lg px-4 text-sm font-semibold border transition-colors bg-red-500/15 border-red-500/30 text-red-400 hover:bg-red-500/25';
    btn.textContent = 'Desactivar';
  } else {
    badge.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border bg-white/5 text-gray-400 border-white/10';
    dot.className   = 'w-2 h-2 rounded-full inline-block bg-gray-500';
    text.textContent = 'Inactivo';
    btn.className   = 'h-9 rounded-lg px-4 text-sm font-semibold border transition-colors bg-green-500/15 border-green-500/30 text-green-400 hover:bg-green-500/25';
    btn.textContent = 'Activar';
  }
}

document.getElementById('btnToggleProde').addEventListener('click', async () => {
  try {
    const data = await api.put(`${PRODE}/config`, { enabled: !prodeEnabled });
    if (data && data.enabled !== undefined) applyProdeUI(data.enabled !== false);
  } catch (e) { console.error(e); }
});

// ── Stats cards ───────────────────────────────────────────────────────
async function loadStats() {
  try {
    const [jornada, stats] = await Promise.all([
      api.get(`${PRODE}/jornada-activa`).catch(() => ({})),
      api.get(`${PRODE}/admin/stats`).catch(() => ({})),
    ]);
    document.getElementById('statParticipantes').textContent = stats.participantes ?? '—';
    document.getElementById('statPronosticos').textContent   = stats.pronosticos   ?? '—';
    if (jornada && jornada.jornada) {
      document.getElementById('statJornada').textContent  = `J${jornada.jornada} · ${jornada.torneo}`;
      document.getElementById('statDeadline').textContent = jornada.deadline
        ? fmtDatetime(jornada.deadline)
        : 'Sin deadline';
    } else {
      document.getElementById('statJornada').textContent  = 'Ninguna';
      document.getElementById('statDeadline').textContent = '—';
    }
  } catch (e) { console.error(e); }
}

// ── Fechas ────────────────────────────────────────────────────────────
let fechasCache = [];

async function loadFechas() {
  const tbody = document.getElementById('tablaFechas');
  tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant text-sm">Cargando...</td></tr>';
  try {
    const data = await api.get(`${PRODE}/admin/fechas`);
    fechasCache = data.fechas || [];

    // Llenar selector de ranking
    const sel = document.getElementById('rankingJornadaSelect');
    sel.innerHTML = '<option value="">General (acumulado)</option>';
    fechasCache.forEach((f) => {
      const opt = document.createElement('option');
      opt.value = f.jornada;
      opt.textContent = `J${f.jornada} — ${f.torneo}`;
      sel.appendChild(opt);
    });

    if (!fechasCache.length) {
      tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-on-surface-variant text-sm">No hay jornadas con pronósticos aún.</td></tr>';
      return;
    }

    tbody.innerHTML = fechasCache.map((f) => {
      const cerrada    = f.estado === 'cerrada';
      const estadoBadge = cerrada
        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-500/15 text-green-400"><span class="material-symbols-outlined text-[12px]">check_circle</span>Cerrada</span>'
        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-500/15 text-yellow-400"><span class="material-symbols-outlined text-[12px]">pending</span>Abierta</span>';
      const deadlineStr = fmtDatetime(f.deadline);
      const accion = cerrada
        ? '<span class="text-xs text-on-surface-variant flex items-center gap-1"><span class="material-symbols-outlined text-[14px] text-green-400">task_alt</span>Puntos calculados</span>'
        : `<button data-jornada="${f.jornada}" data-torneo="${esc(f.torneo)}" data-temporada="${f.temporada_id}"
               class="btnCerrar h-8 rounded-lg bg-primary px-3 text-xs font-semibold text-white hover:bg-primary/80 transition-colors flex items-center gap-1">
             <span class="material-symbols-outlined text-[14px]">calculate</span>Calcular puntos
           </button>`;
      return `<tr class="hover:bg-surface-container/50 transition-colors">
        <td class="px-4 py-3 font-bold font-headline">J${f.jornada}</td>
        <td class="px-4 py-3 text-on-surface-variant text-sm">${esc(f.torneo)}</td>
        <td class="px-4 py-3">${estadoBadge}</td>
        <td class="px-4 py-3 text-sm">${deadlineStr}</td>
        <td class="px-4 py-3 text-right font-mono">${f.participantes}</td>
        <td class="px-4 py-3 text-right font-mono">${f.pronosticos_total}</td>
        <td class="px-4 py-3">${accion}</td>
      </tr>`;
    }).join('');

    tbody.querySelectorAll('.btnCerrar').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const { jornada, torneo, temporada } = btn.dataset;
        if (!confirm(`¿Calcular puntos para J${jornada} — ${torneo}?\n\nSe asignarán puntos a todos los participantes según los resultados cargados. Esta acción no se puede deshacer.`)) return;
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[14px] animate-spin">refresh</span>Calculando...';
        try {
          await api.post(`${PRODE}/admin/fecha/${jornada}/cerrar`, {
            torneo,
            temporada_id: Number(temporada),
          });
          await Promise.all([loadFechas(), loadStats()]);
        } catch (e) {
          alert('Error al calcular puntos: ' + (e?.message || 'Intentá de nuevo.'));
          btn.disabled = false;
          btn.innerHTML = '<span class="material-symbols-outlined text-[14px]">calculate</span>Calcular puntos';
        }
      });
    });
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-red-400 text-sm">Error cargando fechas.</td></tr>';
  }
}

document.getElementById('btnRefreshFechas').addEventListener('click', () => {
  loadFechas();
  loadStats();
});

// ── Ranking ───────────────────────────────────────────────────────────
async function loadRanking(jornada = '') {
  const tbody = document.getElementById('tablaRanking');
  const colResultados = document.getElementById('rankingColResultados');
  tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant text-sm">Cargando...</td></tr>';
  try {
    let rows;
    if (jornada) {
      const data = await api.get(`${PRODE}/ranking/jornada/${jornada}`);
      rows = (data.ranking || []).map((r) => ({ ...r, puntos_total: r.puntos, aciertos_resultado: null }));
      colResultados.classList.add('hidden');
    } else {
      const data = await api.get(`${PRODE}/ranking?limit=100`);
      rows = data.ranking || [];
      colResultados.classList.remove('hidden');
    }

    if (!rows.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant text-sm">Sin datos aún.</td></tr>';
      return;
    }

    const medals = ['🥇', '🥈', '🥉'];
    tbody.innerHTML = rows.map((r, i) => {
      const medal  = i < 3 ? `<span class="mr-1 text-base">${medals[i]}</span>` : '';
      const pts    = r.puntos_total ?? r.puntos ?? 0;
      const resCol = jornada ? '' : `<td class="px-4 py-3 text-right font-mono text-sm">${r.aciertos_resultado ?? 0}</td>`;
      const ptsClass = pts > 0 ? 'text-primary font-bold' : 'text-on-surface-variant';
      return `<tr class="hover:bg-surface-container/50 transition-colors">
        <td class="px-4 py-3 font-bold text-on-surface-variant">${medal}${r.posicion}</td>
        <td class="px-4 py-3 font-semibold">${esc(r.alias)}</td>
        <td class="px-4 py-3 text-right text-lg ${ptsClass}">${pts}</td>
        <td class="px-4 py-3 text-right font-mono text-sm">${r.aciertos_exactos ?? 0}</td>
        ${resCol}
        <td class="px-4 py-3 text-right font-mono text-sm text-on-surface-variant">${r.pronosticos_count ?? 0}</td>
      </tr>`;
    }).join('');
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-red-400 text-sm">Error cargando ranking.</td></tr>';
  }
}

document.getElementById('rankingJornadaSelect').addEventListener('change', (e) => {
  loadRanking(e.target.value);
});

// ── Participantes ─────────────────────────────────────────────────────
async function loadParticipantes() {
  const tbody = document.getElementById('tablaParticipantes');
  tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant text-sm">Cargando...</td></tr>';
  try {
    const data = await api.get(`${PRODE}/admin/participantes`);
    const list = data.participantes || [];
    document.getElementById('totalParticipantesLabel').textContent =
      list.length === 1 ? '1 participante registrado' : `${list.length} participantes registrados`;

    if (!list.length) {
      tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant text-sm">No hay participantes registrados aún.</td></tr>';
      return;
    }

    tbody.innerHTML = list.map((p, i) => `
      <tr class="hover:bg-surface-container/50 transition-colors">
        <td class="px-4 py-3 text-on-surface-variant font-mono text-xs">${i + 1}</td>
        <td class="px-4 py-3 font-semibold">${esc(p.alias)}</td>
        <td class="px-4 py-3 text-sm text-on-surface-variant">${esc(p.email)}</td>
        <td class="px-4 py-3 text-right font-bold ${p.puntos_total > 0 ? 'text-primary' : 'text-on-surface-variant'}">${p.puntos_total}</td>
        <td class="px-4 py-3 text-right font-mono text-sm">${p.aciertos_exactos}</td>
        <td class="px-4 py-3 text-right font-mono text-sm text-on-surface-variant">${p.pronosticos_count}</td>
        <td class="px-4 py-3 text-sm text-on-surface-variant">${fmtDate(p.created_at)}</td>
        <td class="px-4 py-3">
          <button data-id="${p.id}" data-alias="${esc(p.alias)}"
            class="btnEliminar h-7 rounded-lg border border-error/30 px-2.5 text-xs font-semibold text-error hover:bg-error/10 transition-colors flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">delete</span>Eliminar
          </button>
        </td>
      </tr>
    `).join('');

    tbody.querySelectorAll('.btnEliminar').forEach((btn) => {
      btn.addEventListener('click', async () => {
        const { id, alias } = btn.dataset;
        if (!confirm(`¿Eliminar a "${alias}"?\n\nSe borrarán todos sus pronósticos y sesiones.`)) return;
        btn.disabled = true;
        try {
          await api.del(`${PRODE}/admin/participantes/${id}`);
          await Promise.all([loadParticipantes(), loadStats()]);
        } catch (e) {
          alert('Error al eliminar participante.');
          btn.disabled = false;
        }
      });
    });
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="8" class="px-4 py-10 text-center text-red-400 text-sm">Error cargando participantes.</td></tr>';
  }
}

// ── Init ──────────────────────────────────────────────────────────────
(async () => {
  try {
    const cfg = await api.get(`${PRODE}/config`);
    applyProdeUI(cfg.enabled !== false);
  } catch (e) {}

  await loadStats();
  loadFechas();
})();
