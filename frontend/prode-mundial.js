/**
 * prode-mundial.js — Lógica del Prode del Mundial FIFA 2026
 * Comparte las tablas prode_participantes / prode_sesiones con el prode de liga.
 * Token guardado en localStorage como 'prode_token' (igual que el prode de liga).
 */

// ---------------------------------------------------------------------------
// Estado global
// ---------------------------------------------------------------------------
const state = {
  token: localStorage.getItem('prode_token') || null,
  alias: localStorage.getItem('prode_alias') || null,
  ronda: null,
  deadline: null,
  bloqueado: false,
  partidos: [],
  prodeMode: 'nuevo',
};

// ---------------------------------------------------------------------------
// API helpers
// ---------------------------------------------------------------------------
const API_BASE = '/api/prode-mundial';

async function apiGet(path) {
  const headers = {};
  if (state.token) headers['X-Prode-Token'] = state.token;
  const res = await fetch(API_BASE + path, { headers });
  if (!res.ok) {
    const data = await res.json().catch(() => ({}));
    throw new Error(data.error || `HTTP ${res.status}`);
  }
  return res.json();
}

async function apiPost(path, body) {
  const headers = { 'Content-Type': 'application/json' };
  if (state.token) headers['X-Prode-Token'] = state.token;
  const res = await fetch(API_BASE + path, {
    method: 'POST',
    headers,
    body: JSON.stringify(body),
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);
  return data;
}

// ---------------------------------------------------------------------------
// Toast
// ---------------------------------------------------------------------------
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = `toast toast-${type} show`;
  setTimeout(() => { t.className = 'toast'; }, 3500);
}

// ---------------------------------------------------------------------------
// Tabs
// ---------------------------------------------------------------------------
function switchTab(tab) {
  document.querySelectorAll('.tab-btn[data-tab]').forEach(b => {
    b.classList.toggle('active', b.dataset.tab === tab);
    b.classList.toggle('text-on-surface-variant', b.dataset.tab !== tab);
  });
  document.getElementById('tabPronosticar').classList.toggle('hidden', tab !== 'pronosticar');
  document.getElementById('tabMisPronosticos').classList.toggle('hidden', tab !== 'misPronosticos');
  document.getElementById('tabRanking').classList.toggle('hidden', tab !== 'ranking');
  if (tab === 'ranking') cargarRankingTab();
  if (tab === 'misPronosticos') cargarMisPronosticos();
}

// ---------------------------------------------------------------------------
// Secciones
// ---------------------------------------------------------------------------
function mostrarSeccion(id) {
  ['seccionVerificacion', 'seccionProde', 'seccionRankingPublico'].forEach(s => {
    document.getElementById(s).classList.add('hidden');
  });
  document.getElementById(id).classList.remove('hidden');
}

function mostrarVerificacion() {
  mostrarSeccion('seccionVerificacion');
  document.getElementById('paso0').classList.remove('hidden');
  document.getElementById('paso1').classList.add('hidden');
  document.getElementById('loadingVerificacion').classList.add('hidden');
}

function mostrarPaso1(modo) {
  state.prodeMode = modo;
  document.getElementById('paso0').classList.add('hidden');
  document.getElementById('paso1').classList.remove('hidden');
  const aliasGroup = document.getElementById('aliasGroup');
  const btnLabel = document.getElementById('btnRegistrarLabel');
  if (modo === 'nuevo') {
    aliasGroup.classList.remove('hidden');
    btnLabel.textContent = 'Registrarme';
  } else {
    aliasGroup.classList.add('hidden');
    btnLabel.textContent = 'Entrar al Prode Mundial';
  }
}

// ---------------------------------------------------------------------------
// Formatear deadline
// ---------------------------------------------------------------------------
function formatDeadline(deadlineStr) {
  if (!deadlineStr) return '';
  const d = new Date(deadlineStr);
  const now = new Date();
  const diff = d - now;
  if (diff <= 0) return 'Cierre de pronósticos';
  const hs = Math.floor(diff / 3600000);
  const mins = Math.floor((diff % 3600000) / 60000);
  if (hs > 0) return `Cierra en ${hs}h ${mins}m`;
  return `Cierra en ${mins} minutos`;
}

// ---------------------------------------------------------------------------
// Flag URL
// ---------------------------------------------------------------------------
function flagUrl(codigoPais) {
  if (!codigoPais) return '';
  return `https://flagcdn.com/w80/${codigoPais}.png`;
}

// ---------------------------------------------------------------------------
// Registro / Login con email (compartido con prode de liga)
// ---------------------------------------------------------------------------
async function registrarConEmail() {
  const modo = state.prodeMode || 'nuevo';
  const alias = modo === 'nuevo' ? document.getElementById('inputAlias').value.trim() : '';
  const email = document.getElementById('inputEmail').value.trim().toLowerCase();

  if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    showToast('Ingresá un email válido', 'error'); return;
  }

  const btn = document.getElementById('btnRegistrar');
  btn.disabled = true;
  document.getElementById('paso1').classList.add('hidden');
  document.getElementById('loadingVerificacion').classList.remove('hidden');

  try {
    // Usamos el endpoint del prode de liga (compartido)
    const headers = { 'Content-Type': 'application/json' };
    const res = await fetch('/api/prode/verificar/solicitar', {
      method: 'POST', headers,
      body: JSON.stringify({ email, alias }),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || `HTTP ${res.status}`);

    if (modo === 'nuevo' && !data.nuevo) {
      document.getElementById('loadingVerificacion').classList.add('hidden');
      document.getElementById('paso1').classList.remove('hidden');
      btn.disabled = false;
      showToast('Ese email ya tiene una cuenta. Usá la opción "Ya jugué" para ingresar.', 'error');
      return;
    }

    localStorage.setItem('prode_token', data.token);
    localStorage.setItem('prode_alias', data.alias);
    state.token = data.token;
    state.alias = data.alias;

    document.getElementById('loadingVerificacion').classList.add('hidden');
    showToast(data.nuevo ? `¡Bienvenido al Prode Mundial, ${data.alias}!` : `¡Hola de nuevo, ${data.alias}!`);
    iniciarSesionProde();
  } catch (err) {
    document.getElementById('loadingVerificacion').classList.add('hidden');
    document.getElementById('paso1').classList.remove('hidden');
    btn.disabled = false;
    showToast(err.message, 'error');
  }
}

// ---------------------------------------------------------------------------
// Iniciar sesión del Prode
// ---------------------------------------------------------------------------
async function iniciarSesionProde() {
  document.getElementById('aliasDisplay').textContent = state.alias;
  document.getElementById('btnLogout').classList.remove('hidden');
  mostrarSeccion('seccionProde');
  await cargarRondaYPartidos();
}

// ---------------------------------------------------------------------------
// Cargar ronda activa + partidos
// ---------------------------------------------------------------------------
async function cargarRondaYPartidos() {
  const loading = document.getElementById('loadingPartidos');
  const lista = document.getElementById('listaPartidos');
  const noRonda = document.getElementById('noRonda');
  const btnGuardar = document.getElementById('btnGuardarContainer');

  loading.classList.remove('hidden');
  lista.classList.add('hidden');
  noRonda.classList.add('hidden');
  btnGuardar.classList.add('hidden');

  try {
    const data = await apiGet('/ronda-activa');

    if (!data.ronda || !data.partidos || data.partidos.length === 0) {
      loading.classList.add('hidden');
      noRonda.classList.remove('hidden');
      return;
    }

    state.ronda = data.ronda;
    state.deadline = data.deadline;
    state.bloqueado = data.bloqueado;
    state.partidos = data.partidos;

    document.getElementById('headerRonda').textContent = data.ronda.nombre || '';
    document.getElementById('rondaTitulo').textContent = data.ronda.nombre || 'Ronda activa';

    const chip = document.getElementById('deadlineChip');
    const bannerDl = document.getElementById('deadlineBanner');
    if (data.deadline) {
      const dlStr = formatDeadline(data.deadline);
      bannerDl.textContent = `${data.ronda.nombre} · ${dlStr}`;
      if (data.bloqueado) {
        chip.className = 'text-xs px-3 py-1 rounded-full font-semibold bg-error/20 text-error border border-error/30';
        chip.textContent = 'Pronósticos cerrados';
      } else {
        chip.className = 'text-xs px-3 py-1 rounded-full font-semibold bg-green-500/20 text-green-400 border border-green-500/30 pulsing';
        chip.textContent = dlStr;
      }
    } else {
      bannerDl.textContent = data.ronda.nombre || '';
    }

    // Cargar pronósticos existentes
    let misPronsMap = {};
    try {
      const prons = await apiGet(`/pronosticos?ronda_id=${data.ronda.id}`);
      if (prons.pronosticos) {
        prons.pronosticos.forEach(p => {
          misPronsMap[p.partido_id] = { gl: p.goles_local, gv: p.goles_visitante };
        });
      }
    } catch (_) { /* silently fail */ }

    renderPartidos(data.partidos, data.bloqueado, misPronsMap);
    loading.classList.add('hidden');
    lista.classList.remove('hidden');
    if (!data.bloqueado) btnGuardar.classList.remove('hidden');

  } catch (err) {
    loading.classList.add('hidden');
    showToast(err.message, 'error');
  }
}

// ---------------------------------------------------------------------------
// Renderizar cards de partidos del Mundial
// ---------------------------------------------------------------------------
function renderPartidos(partidos, bloqueado, misPronsMap = {}) {
  const lista = document.getElementById('listaPartidos');
  lista.innerHTML = '';

  partidos.forEach(p => {
    const esJugado = p.estado === 'jugado';
    const pron = misPronsMap[p.id] || null;

    const card = document.createElement('div');
    card.className = `partido-card bg-surface-container-high border border-outline-variant/20 rounded-xl p-4 ${bloqueado ? 'bloqueado' : ''}`;
    card.dataset.partidoId = p.id;

    const fechaFmt = p.fecha
      ? new Date(p.fecha + 'T12:00:00').toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'short' })
      : '';
    const horaFmt = p.hora ? p.hora.slice(0, 5) : '';

    const grupoHtml = p.grupo
      ? `<span class="grupo-chip">Grupo ${p.grupo}</span>`
      : `<span class="grupo-chip">${(p.fase || '').replace(/_/g, ' ')}</span>`;

    const faseHtml = p.grupo ? '' : `<span class="text-[10px] text-on-surface-variant/60 capitalize">${(p.fase || '').replace(/_/g, ' ')}</span>`;

    let resultadoHtml = '';
    if (esJugado) {
      resultadoHtml = `<div class="text-xs text-center text-on-surface-variant mt-1">
        Real: <span class="font-bold text-on-surface">${p.golesLocal} – ${p.golesVisitante}</span>
      </div>`;
    }

    const glVal = pron !== null ? pron.gl : '';
    const gvVal = pron !== null ? pron.gv : '';

    card.innerHTML = `
      <div class="flex items-center justify-between mb-2">
        <div class="text-xs text-on-surface-variant">${fechaFmt} ${horaFmt}</div>
        ${grupoHtml}
      </div>
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
          <span class="text-sm font-semibold truncate">${p.local}</span>
          <img src="${flagUrl(p.localBandera)}" alt="${p.local}"
               class="team-flag"
               onerror="this.style.display='none'"/>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="score-stepper">
            <button type="button" class="stepper-btn" data-pid="${p.id}" data-side="local" data-delta="-1" ${bloqueado ? 'disabled' : ''}>−</button>
            <input type="number" min="0" max="20"
              class="score-input prode-gol-local"
              data-partido-id="${p.id}"
              value="${glVal}"
              ${bloqueado ? 'disabled' : ''}
              placeholder="–"/>
            <button type="button" class="stepper-btn" data-pid="${p.id}" data-side="local" data-delta="1" ${bloqueado ? 'disabled' : ''}>+</button>
          </div>
          <span class="text-xs text-on-surface-variant/40 font-medium px-0.5">vs</span>
          <div class="score-stepper">
            <button type="button" class="stepper-btn" data-pid="${p.id}" data-side="visitante" data-delta="-1" ${bloqueado ? 'disabled' : ''}>−</button>
            <input type="number" min="0" max="20"
              class="score-input prode-gol-visitante"
              data-partido-id="${p.id}"
              value="${gvVal}"
              ${bloqueado ? 'disabled' : ''}
              placeholder="–"/>
            <button type="button" class="stepper-btn" data-pid="${p.id}" data-side="visitante" data-delta="1" ${bloqueado ? 'disabled' : ''}>+</button>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-1 min-w-0">
          <img src="${flagUrl(p.visitanteBandera)}" alt="${p.visitante}"
               class="team-flag"
               onerror="this.style.display='none'"/>
          <span class="text-sm font-semibold truncate">${p.visitante}</span>
        </div>
      </div>
      ${resultadoHtml}
    `;
    lista.appendChild(card);
  });
}

// ---------------------------------------------------------------------------
// Guardar pronósticos
// ---------------------------------------------------------------------------
async function guardarPronosticos() {
  const inputs = document.querySelectorAll('.prode-gol-local');
  const pronosticos = [];

  let incompleto = false;
  inputs.forEach(inputLocal => {
    const partidoId = parseInt(inputLocal.dataset.partidoId);
    const inputVisitante = document.querySelector(`.prode-gol-visitante[data-partido-id="${partidoId}"]`);
    const gl = inputLocal.value;
    const gv = inputVisitante?.value;
    if (gl === '' || gv === '') { incompleto = true; return; }
    pronosticos.push({ partido_id: partidoId, goles_local: parseInt(gl), goles_visitante: parseInt(gv) });
  });

  if (incompleto) {
    showToast('Completá todos los marcadores antes de guardar', 'error');
    return;
  }
  if (pronosticos.length === 0) {
    showToast('No hay pronósticos para guardar', 'error');
    return;
  }

  const btn = document.getElementById('btnGuardarPronosticos');
  btn.disabled = true;
  btn.textContent = 'Guardando...';

  try {
    const data = await apiPost('/pronosticos', { pronosticos });
    showToast(`¡${data.guardados} pronósticos guardados!`);
    btn.textContent = '✓ Guardado';
    setTimeout(() => {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span> Guardar pronósticos';
    }, 2000);
  } catch (err) {
    showToast(err.message, 'error');
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">save</span> Guardar pronósticos';
  }
}

// ---------------------------------------------------------------------------
// Mis Pronósticos
// ---------------------------------------------------------------------------
async function cargarMisPronosticos() {
  const loading = document.getElementById('loadingMisPronosticos');
  const lista = document.getElementById('listaMisPronosticos');
  const noData = document.getElementById('noMisPronosticos');
  const totalContainer = document.getElementById('totalPuntosContainer');

  loading.classList.remove('hidden');
  lista.classList.add('hidden');
  noData.classList.add('hidden');
  totalContainer.classList.add('hidden');

  try {
    const path = state.ronda ? `/pronosticos?ronda_id=${state.ronda.id}` : '/pronosticos';
    const data = await apiGet(path);

    loading.classList.add('hidden');
    if (!data.pronosticos || data.pronosticos.length === 0) {
      noData.classList.remove('hidden');
      return;
    }

    renderMisPronosticos(data.pronosticos);
    lista.classList.remove('hidden');
    document.getElementById('totalPuntosText').textContent = data.total_puntos;
    totalContainer.classList.remove('hidden');
  } catch (err) {
    loading.classList.add('hidden');
    showToast(err.message, 'error');
  }
}

function renderMisPronosticos(pronosticos) {
  const lista = document.getElementById('listaMisPronosticos');
  lista.innerHTML = '';

  pronosticos.forEach(p => {
    const calculado = p.puntos_obtenidos !== null;
    const puntos    = calculado ? parseInt(p.puntos_obtenidos) : null;

    let puntosHtml = '';
    if (calculado) {
      const cls = puntos >= 4 ? 'puntos-4' : puntos >= 1 ? 'puntos-1' : 'puntos-0';
      const lbl = puntos >= 4 ? `+${puntos} pts ✓` : puntos >= 1 ? `+${puntos} pt` : '0 pts';
      puntosHtml = `<span class="puntos-badge ${cls}">${lbl}</span>`;
    }

    let resultadoReal = '';
    if (p.estado === 'jugado' || calculado) {
      resultadoReal = `<div class="text-xs text-on-surface-variant">Real: <b class="text-on-surface">${p.golesLocalReal}–${p.golesVisitanteReal}</b></div>`;
    }

    const grupoHtml = p.grupo
      ? `<span class="grupo-chip">Grupo ${p.grupo}</span>`
      : `<span class="grupo-chip">${(p.fase || '').replace(/_/g, ' ')}</span>`;

    const card = document.createElement('div');
    card.className = 'bg-surface-container-high border border-outline-variant/20 rounded-xl p-4';
    card.innerHTML = `
      <div class="flex items-center justify-between mb-2">
        <div class="text-xs text-on-surface-variant">${p.fecha || ''}</div>
        ${grupoHtml}
      </div>
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
          <span class="text-sm font-semibold truncate">${p.local}</span>
          <img src="${flagUrl(p.localBandera)}" alt="${p.local}"
               class="team-flag" onerror="this.style.display='none'"/>
        </div>
        <div class="text-center flex-shrink-0 space-y-0.5">
          <div class="text-base font-bold text-on-surface">${p.goles_local}–${p.goles_visitante}</div>
          ${resultadoReal}
        </div>
        <div class="flex items-center gap-2 flex-1 min-w-0">
          <img src="${flagUrl(p.visitanteBandera)}" alt="${p.visitante}"
               class="team-flag" onerror="this.style.display='none'"/>
          <span class="text-sm font-semibold truncate">${p.visitante}</span>
        </div>
      </div>
      ${calculado ? `<div class="text-right mt-2">${puntosHtml}</div>` : ''}
    `;
    lista.appendChild(card);
  });
}

// ---------------------------------------------------------------------------
// Ranking
// ---------------------------------------------------------------------------
async function cargarRankingTab() {
  const loading = document.getElementById('loadingRanking');
  const tabla = document.getElementById('tablaRanking');
  const noData = document.getElementById('noRanking');

  loading.classList.remove('hidden');
  tabla.classList.add('hidden');
  noData.classList.add('hidden');

  try {
    const data = await apiGet('/ranking');
    loading.classList.add('hidden');
    if (!data.ranking || data.ranking.length === 0) {
      noData.classList.remove('hidden');
    } else {
      renderRanking(tabla, data.ranking);
      tabla.classList.remove('hidden');
    }
  } catch (err) {
    loading.classList.add('hidden');
    showToast(err.message, 'error');
  }
}

async function cargarRankingPublico() {
  const loading = document.getElementById('loadingPublicRanking');
  const tabla = document.getElementById('tablaPublicRanking');

  loading.classList.remove('hidden');
  tabla.classList.add('hidden');

  try {
    const data = await apiGet('/ranking/publico');
    loading.classList.add('hidden');
    if (data.ranking && data.ranking.length > 0) {
      renderRanking(tabla, data.ranking);
      tabla.classList.remove('hidden');
    }
  } catch (err) {
    loading.classList.add('hidden');
  }
}

function renderRanking(container, ranking) {
  if (!ranking || ranking.length === 0) {
    container.innerHTML = '';
    return;
  }

  const topIcons = ['🏆', '🥈', '🥉'];
  const userAlias = state.alias;

  let html = `<div class="space-y-2">`;
  ranking.forEach((r, i) => {
    const isMe = userAlias && r.alias === userAlias;
    const icon = topIcons[i] ?? `${r.posicion}.`;
    const isTop = i < 3;

    html += `
      <div class="flex items-center gap-3 px-4 py-3 rounded-xl border transition-all
        ${isMe ? 'bg-primary/15 border-primary/40' : 'bg-surface-container-high border-outline-variant/20'}">
        <span class="text-base font-bold w-6 text-center flex-shrink-0 ${isTop ? 'text-xl' : 'text-on-surface-variant'}">${icon}</span>
        <span class="flex-1 font-semibold truncate ${isMe ? 'text-primary' : ''}">${r.alias}${isMe ? ' (vos)' : ''}</span>
        <div class="text-right flex-shrink-0">
          <div class="font-black text-lg text-on-surface">${r.puntos_total ?? 0}<span class="text-xs font-normal text-on-surface-variant ml-0.5">pts</span></div>
          <div class="text-xs text-on-surface-variant">${r.aciertos_exactos ?? 0} exactos</div>
        </div>
      </div>`;
  });
  html += `</div>`;
  container.innerHTML = html;
}

// ---------------------------------------------------------------------------
// Logout
// ---------------------------------------------------------------------------
function logout() {
  localStorage.removeItem('prode_token');
  localStorage.removeItem('prode_alias');
  state.token = null;
  state.alias = null;
  mostrarSeccion('seccionRankingPublico');
  document.getElementById('btnLogout').classList.add('hidden');
  cargarRankingPublico();
}

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async () => {
  document.getElementById('btnRegistrar').addEventListener('click', registrarConEmail);
  document.getElementById('btnLogout').addEventListener('click', logout);
  document.getElementById('btnGuardarPronosticos')?.addEventListener('click', guardarPronosticos);

  // Steppers +/-
  document.getElementById('listaPartidos')?.addEventListener('click', e => {
    const btn = e.target.closest('.stepper-btn');
    if (!btn || btn.disabled) return;
    const pid   = btn.dataset.pid;
    const side  = btn.dataset.side;
    const delta = parseInt(btn.dataset.delta);
    const sel   = side === 'local'
      ? `.prode-gol-local[data-partido-id="${pid}"]`
      : `.prode-gol-visitante[data-partido-id="${pid}"]`;
    const input = document.querySelector(sel);
    if (!input || input.disabled) return;
    const raw  = parseInt(input.value);
    const curr = isNaN(raw) ? 0 : raw;
    input.value = Math.max(0, Math.min(20, curr + delta));
  });

  // Enter en inputEmail
  document.getElementById('inputEmail')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') registrarConEmail();
  });

  // Si ya tiene sesión activa
  if (state.token && state.alias) {
    document.getElementById('aliasDisplay').textContent = state.alias;
    document.getElementById('btnLogout').classList.remove('hidden');
    mostrarSeccion('seccionProde');
    await cargarRondaYPartidos();
  } else {
    mostrarSeccion('seccionRankingPublico');
    cargarRankingPublico();
  }
});
