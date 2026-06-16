/**
 * prode.js — Lógica del módulo Prode Deportivo
 * Público: sin login. Autenticación por WhatsApp OTP.
 * Token guardado en localStorage como 'prode_token'.
 */

// ---------------------------------------------------------------------------
// Estado global
// ---------------------------------------------------------------------------
const state = {
  token: localStorage.getItem('prode_token') || null,
  alias: localStorage.getItem('prode_alias') || null,
  jornada: null,
  torneo: null,
  temporadaId: null,
  deadline: null,
  bloqueado: false,
  partidos: [],
  prodeMode: 'nuevo',
  rankingTab: 'general',
  publicRankingTab: 'general',
};

// ---------------------------------------------------------------------------
// API helpers
// ---------------------------------------------------------------------------
const API_BASE = '/api/prode';

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

function switchRankingTab(tab) {
  state.rankingTab = tab;
  document.querySelectorAll('.tab-btn[data-rtab]').forEach(b => {
    b.classList.toggle('active', b.dataset.rtab === tab);
    b.classList.toggle('text-on-surface-variant', b.dataset.rtab !== tab);
  });
  cargarRankingTab();
}

function switchPublicRankingTab(tab) {
  state.publicRankingTab = tab;
  document.querySelectorAll('.tab-btn[data-ptab]').forEach(b => {
    b.classList.toggle('active', b.dataset.ptab === tab);
    b.classList.toggle('text-on-surface-variant', b.dataset.ptab !== tab);
  });
  cargarRankingPublico();
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
    btnLabel.textContent = 'Entrar al Prode';
  }
}

// ---------------------------------------------------------------------------
// Noticias recientes (sección pre-footer)
// ---------------------------------------------------------------------------
async function cargarNoticiasProde() {
  try {
    const res = await fetch('/api/noticias?limit=3');
    if (!res.ok) return;
    const noticias = await res.json();
    if (!Array.isArray(noticias) || !noticias.length) return;
    const section = document.getElementById('prodeNoticiasSection');
    const grid = document.getElementById('prodeNoticiasGrid');
    if (!section || !grid) return;
    section.classList.remove('hidden');
    grid.innerHTML = noticias.map(n => {
      const fecha = n.publishedAt || n.createdAt;
      const fechaStr = fecha ? new Date(fecha).toLocaleDateString('es-AR', { day: 'numeric', month: 'long' }) : '';
      const img = n.imagenUrl
        ? `<img src="${n.imagenUrl}" alt="" class="w-full h-full object-cover" loading="lazy"/>`
        : `<div class="w-full h-full bg-primary/10 flex items-center justify-center"><span class="material-symbols-outlined text-primary/30 text-3xl">article</span></div>`;
      const cat = n.categoria ? `<span class="text-[10px] text-primary font-bold uppercase tracking-wide">${n.categoria}</span>` : '';
      const copete = n.copete ? `<p class="text-xs text-on-surface-variant mt-1 line-clamp-1">${n.copete}</p>` : '';
      return `<a href="/noticias/${encodeURIComponent(n.slug)}" class="flex gap-3 bg-surface-container-high border border-outline-variant/20 rounded-xl overflow-hidden hover:border-primary/30 transition-all">
        <div class="w-24 flex-shrink-0 overflow-hidden">${img}</div>
        <div class="p-3 flex-1 min-w-0">
          ${cat}
          <h3 class="font-bold text-sm leading-snug line-clamp-2 mt-0.5">${n.titulo || ''}</h3>
          ${copete}
          <p class="text-xs text-on-surface-variant/50 mt-1">${fechaStr}</p>
        </div>
      </a>`;
    }).join('');
  } catch (_) { /* silently fail */ }
}

// ---------------------------------------------------------------------------
// Formatear deadline
// ---------------------------------------------------------------------------
function formatDeadline(deadlineStr) {
  if (!deadlineStr) return '';
  const d = new Date(deadlineStr.replace(' ', 'T'));
  const now = new Date();
  const diff = d - now;
  if (diff <= 0) return 'Cierre de pronósticos';
  const hs = Math.floor(diff / 3600000);
  const mins = Math.floor((diff % 3600000) / 60000);
  if (hs > 0) return `Cierra en ${hs}h ${mins}m`;
  return `Cierra en ${mins} minutos`;
}

// ---------------------------------------------------------------------------
// Registro / Login con email (un solo paso)
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
    const data = await apiPost('/verificar/solicitar', { email, alias });

    // Email ya registrado: avisar y no auto-loguear
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
    showToast(data.nuevo ? `¡Bienvenido al Prode, ${data.alias}!` : `¡Hola de nuevo, ${data.alias}!`);
    iniciarSesionProde();
  } catch (err) {
    document.getElementById('loadingVerificacion').classList.add('hidden');
    document.getElementById('paso1').classList.remove('hidden');
    btn.disabled = false;
    if (modo === 'relogin' && err.message.includes('alias')) {
      showToast('No encontramos tu cuenta. \u00bfEs tu primera vez participando?', 'error');
    } else {
      showToast(err.message, 'error');
    }
  }
}

// ---------------------------------------------------------------------------
// Iniciar sesión del Prode (post-registro)
// ---------------------------------------------------------------------------
async function iniciarSesionProde() {
  document.getElementById('aliasDisplay').textContent = state.alias;
  document.getElementById('btnLogout').classList.remove('hidden');
  mostrarSeccion('seccionProde');
  await cargarJornadaYPartidos();
}

// ---------------------------------------------------------------------------
// Cargar jornada activa + partidos
// ---------------------------------------------------------------------------
async function cargarJornadaYPartidos() {
  const loading = document.getElementById('loadingPartidos');
  const lista = document.getElementById('listaPartidos');
  const noJornada = document.getElementById('noJornada');
  const btnGuardar = document.getElementById('btnGuardarContainer');

  loading.classList.remove('hidden');
  lista.classList.add('hidden');
  noJornada.classList.add('hidden');
  btnGuardar.classList.add('hidden');

  try {
    const data = await apiGet('/partidos');

    if (!data.jornada || !data.partidos || data.partidos.length === 0) {
      loading.classList.add('hidden');
      noJornada.classList.remove('hidden');
      return;
    }

    state.jornada = data.jornada;
    state.torneo = data.torneo;
    state.deadline = data.deadline;
    state.bloqueado = data.bloqueado;
    state.partidos = data.partidos;

    document.getElementById('headerTorneo').textContent = data.torneo || '';
    document.getElementById('jornadaTitulo').textContent = `Fecha ${data.jornada}`;

    const chip = document.getElementById('deadlineChip');
    const bannerDl = document.getElementById('deadlineBanner');
    if (data.deadline) {
      const dlStr = formatDeadline(data.deadline);
      bannerDl.textContent = `Fecha ${data.jornada} · ${dlStr}`;
      if (data.bloqueado) {
        chip.className = 'text-xs px-3 py-1 rounded-full font-semibold bg-error/20 text-error border border-error/30';
        chip.textContent = 'Pronósticos cerrados';
      } else {
        chip.className = 'text-xs px-3 py-1 rounded-full font-semibold bg-green-500/20 text-green-400 border border-green-500/30 pulsing';
        chip.textContent = dlStr;
      }
    }

    // Aviso de jornada suspendida
    const avisoSuspendida = document.getElementById('avisoSuspendida');
    if (avisoSuspendida) {
      if (data.suspendida && !data.bloqueado && data.deadline) {
        const d = new Date(data.deadline.replace(' ', 'T'));
        const fechaStr = d.toLocaleDateString('es-AR', { weekday: 'long', day: 'numeric', month: 'long' });
        const horaStr  = d.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit' });
        avisoSuspendida.innerHTML = `<span class="material-symbols-outlined text-[18px] shrink-0">pause_circle</span>
          <span>Esta fecha fue <strong>suspendida por lluvia</strong>. Podés cargar o modificar tus pronósticos hasta el <strong>${fechaStr} a las ${horaStr}</strong>.</span>`;
        avisoSuspendida.classList.remove('hidden');
      } else {
        avisoSuspendida.classList.add('hidden');
      }
    }

    renderPartidos(data.partidos, data.bloqueado);
    loading.classList.add('hidden');
    lista.classList.remove('hidden');
    if (!data.bloqueado) btnGuardar.classList.remove('hidden');

  } catch (err) {
    loading.classList.add('hidden');
    showToast(err.message, 'error');
  }
}

// ---------------------------------------------------------------------------
// Renderizar cards de partidos
// ---------------------------------------------------------------------------
function renderPartidos(partidos, bloqueado) {
  const lista = document.getElementById('listaPartidos');
  lista.innerHTML = '';

  partidos.forEach(p => {
    const localEsc  = p.localEscudo  || '/assets/logo.png';
    const visitEsc  = p.visitanteEscudo || '/assets/logo.png';
    const esJugado  = p.estado === 'jugado';

    const card = document.createElement('div');
    card.className = `partido-card bg-surface-container-high border border-outline-variant/20 rounded-xl p-4 ${bloqueado ? 'bloqueado' : ''}`;
    card.dataset.partidoId = p.id;

    const fechaFmt = p.fecha ? new Date(p.fecha + 'T12:00:00').toLocaleDateString('es-AR', { weekday: 'short', day: 'numeric', month: 'short' }) : '';
    const horaFmt  = p.hora ? p.hora.slice(0, 5) : '';

    let resultadoHtml = '';
    if (esJugado) {
      resultadoHtml = `<div class="text-xs text-center text-on-surface-variant mt-1">
        Real: <span class="font-bold text-on-surface">${p.golesLocal} – ${p.golesVisitante}</span>
      </div>`;
    }

    card.innerHTML = `
      <div class="text-xs text-on-surface-variant text-center mb-2">${fechaFmt} ${horaFmt}</div>
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
          <span class="text-sm font-semibold truncate">${p.local}</span>
          <img src="${localEsc}" alt="${p.local}" class="team-logo" onerror="this.src='/assets/logo.png'"/>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <div class="score-stepper">
            <button type="button" class="stepper-btn" data-pid="${p.id}" data-side="local" data-delta="-1" ${bloqueado ? 'disabled' : ''}>−</button>
            <input type="number" min="0" max="20"
              class="score-input prode-gol-local"
              data-partido-id="${p.id}"
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
              ${bloqueado ? 'disabled' : ''}
              placeholder="–"/>
            <button type="button" class="stepper-btn" data-pid="${p.id}" data-side="visitante" data-delta="1" ${bloqueado ? 'disabled' : ''}>+</button>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-1 min-w-0">
          <img src="${visitEsc}" alt="${p.visitante}" class="team-logo" onerror="this.src='/assets/logo.png'"/>
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
    const data = await apiPost('/pronostico', { pronosticos });
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
    const path = state.jornada ? `/mis-pronosticos?jornada=${state.jornada}` : '/mis-pronosticos';
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
    const localEsc  = p.localEscudo  || '/assets/logo.png';
    const visitEsc  = p.visitanteEscudo || '/assets/logo.png';
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

    const card = document.createElement('div');
    card.className = 'bg-surface-container-high border border-outline-variant/20 rounded-xl p-4';
    card.innerHTML = `
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">
          <span class="text-sm font-semibold truncate">${p.local}</span>
          <img src="${localEsc}" alt="${p.local}" class="team-logo" onerror="this.src='/assets/logo.png'"/>
        </div>
        <div class="text-center flex-shrink-0 space-y-0.5">
          <div class="text-base font-bold text-on-surface">${p.goles_local}–${p.goles_visitante}</div>
          ${resultadoReal}
        </div>
        <div class="flex items-center gap-2 flex-1 min-w-0">
          <img src="${visitEsc}" alt="${p.visitante}" class="team-logo" onerror="this.src='/assets/logo.png'"/>
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
    let data;
    if (state.rankingTab === 'general') {
      data = await apiGet('/ranking');
      renderRanking(tabla, data.ranking, false);
    } else {
      const jornada = state.jornada || 1;
      data = await apiGet(`/ranking/jornada/${jornada}`);
      renderRanking(tabla, data.ranking, true);
    }
    loading.classList.add('hidden');
    if (!data.ranking || data.ranking.length === 0) {
      noData.classList.remove('hidden');
    } else {
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
    let data;
    if (state.publicRankingTab === 'general') {
      data = await apiGet('/ranking');
      renderRanking(tabla, data.ranking, false);
    } else {
      const jornadaActiva = await apiGet('/jornada-activa');
      const jornada = jornadaActiva.jornada || 1;
      data = await apiGet(`/ranking/jornada/${jornada}`);
      renderRanking(tabla, data.ranking, true);
    }
    loading.classList.add('hidden');
    tabla.classList.remove('hidden');
  } catch (err) {
    loading.classList.add('hidden');
    showToast(err.message, 'error');
  }
}

function renderRanking(container, ranking, porJornada) {
  if (!ranking || ranking.length === 0) {
    container.innerHTML = '';
    return;
  }

  const puntosKey = porJornada ? 'puntos' : 'puntos_total';
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
          <div class="font-black text-lg text-on-surface">${r[puntosKey] ?? r.puntos ?? 0}<span class="text-xs font-normal text-on-surface-variant ml-0.5">pts</span></div>
          <div class="text-xs text-on-surface-variant">${r.aciertos_exactos} exactos</div>
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
  // Botones
  document.getElementById('btnRegistrar').addEventListener('click', registrarConEmail);
  document.getElementById('btnLogout').addEventListener('click', logout);
  document.getElementById('btnGuardarPronosticos')?.addEventListener('click', guardarPronosticos);

  // Steppers +/- en lista de partidos
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

  // Enter en inputEmail → registrar
  document.getElementById('inputEmail')?.addEventListener('keydown', e => {
    if (e.key === 'Enter') registrarConEmail();
  });

  // Cargar noticias recientes en el pre-footer (siempre)
  cargarNoticiasProde();

  // Detectar si ya tiene sesión activa
  if (state.token && state.alias) {
    document.getElementById('aliasDisplay').textContent = state.alias;
    document.getElementById('btnLogout').classList.remove('hidden');
    mostrarSeccion('seccionProde');
    await cargarJornadaYPartidos();
  } else {
    // Mostrar ranking público mientras no está logueado
    mostrarSeccion('seccionRankingPublico');
    cargarRankingPublico();
  }
});
