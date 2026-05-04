(function () {
// === Auth & API ===
const ACCOUNT_STORAGE_KEY = "accountProfile";

const getAuthHeaders = () => {
  const headers = { "Content-Type": "application/json" };
  const token = localStorage.getItem("token");
  if (token) {
    headers.Authorization = `Bearer ${token}`;
    headers["X-Auth-Token"] = token;
  }
  return headers;
};

function clearSession() {
  localStorage.removeItem("token");
  localStorage.removeItem(ACCOUNT_STORAGE_KEY);
}

function redirectToLogin() {
  window.location.href = "/login";
}

async function request(path, options = {}) {
  let response;
  const url = path.startsWith("http") ? path : "/api" + path;
  try {
    response = await fetch(url, {
      ...options,
      headers: {
        ...getAuthHeaders(),
        ...(options.headers || {}),
      },
    });
  } catch {
    throw new Error("No se pudo conectar con el servidor");
  }

  const contentType = response.headers.get("content-type") || "";
  let payload = null;

  if (contentType.includes("application/json")) {
    payload = await response.json();
  } else {
    const text = await response.text();
    payload = text ? { message: text } : null;
  }

  if (response.status === 401) {
    clearSession();
    redirectToLogin();
    throw new Error(payload?.error || payload?.message || "Sesión inválida");
  }

  if (!response.ok) {
    throw new Error(payload?.error || payload?.message || `Error ${response.status}`);
  }

  return payload;
}

const api = {
  get: (path) => request(path),
  post: (path, body) => request(path, { method: "POST", body: JSON.stringify(body) }),
  put: (path, body) => request(path, { method: "PUT", body: JSON.stringify(body) }),
  del: (path) => request(path, { method: "DELETE" }),
};

// === State ===
let currentJornada = null;
let equiposCache = []; // [{id, nombre, escudo, ...}]
let eventosTemp = []; // [{jugador_id, equipo_id, tipo, jugador_nombre, minuto}]
let resultModalState = { localEquipoId: null, visitanteEquipoId: null };
let fixturePartidosCache = [];

// === Helpers ===
const $ = (id) => document.getElementById(id);
const getCategoria = () => $("categoriaFiltro")?.value || "";
const getTorneo = () => $("torneoFiltro")?.value || "";
const DEFAULT_ESCUDO = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='%23666'%3E%3Crect width='32' height='32' rx='16' fill='%23334155'/%3E%3Ctext x='16' y='20' text-anchor='middle' font-size='14' fill='%23cbd5e1'%3E?%3C/text%3E%3C/svg%3E";

function showToast(message, type = "success") {
  const toast = $("toast");
  toast.textContent = message;
  toast.className = `toast ${type === "success" ? "toast-success" : "toast-error"} show`;
  setTimeout(() => toast.classList.remove("show"), 3000);
}

function getEscudo(equipoId) {
  const eq = equiposCache.find((e) => e.id == equipoId);
  return eq?.escudo || DEFAULT_ESCUDO;
}

function getEquipoNombre(equipoId) {
  const eq = equiposCache.find((e) => e.id == equipoId);
  return eq?.nombre || "—";
}

function getEquipoById(equipoId) {
  return equiposCache.find((e) => String(e.id) === String(equipoId)) || null;
}

function applyEquipoLocalEstadio(targetInput, equipoId) {
  if (!targetInput) return;
  const equipo = getEquipoById(equipoId);
  if (!equipo?.estadio) return;
  if (targetInput.value.trim() !== "") return;
  targetInput.value = equipo.estadio;
}

// === Auth check ===
function checkAuth() {
  if (!localStorage.getItem("token")) {
    window.location.href = "/login";
    return false;
  }
  return true;
}

// === Load equipos for current category ===
async function loadEquipos() {
  const cat = getCategoria();
  if (!cat) return;
  try {
    equiposCache = await api.get(`/equipos?categoria=${encodeURIComponent(cat)}`);
  } catch (error) {
    equiposCache = [];
    showToast(error.message, "error");
  }
}

// === Jornadas ===
async function loadJornadas() {
  const cat = getCategoria();
  const tor = getTorneo();
  if (!cat || !tor) return;

  await loadEquipos();
  const container = $("jornadasContainer");
  let jornadas = [];

  try {
    jornadas = await api.get(`/partidos/jornadas?categoria=${encodeURIComponent(cat)}&torneo=${encodeURIComponent(tor)}`);
  } catch (error) {
    container.innerHTML = "<p class='text-sm text-red-300'>No se pudieron cargar las jornadas.</p>";
    $("jornadaDetail").classList.add("hidden");
    showToast(error.message, "error");
    return;
  }

  container.innerHTML = "";

  if (!jornadas.length) {
    container.innerHTML = "<p class='text-sm text-slate-400'>No hay jornadas. Creá la primera con el botón de arriba.</p>";
    $("jornadaDetail").classList.add("hidden");
    return;
  }

  jornadas.forEach((j) => {
    if (j.jornada == null) return; // skip null jornadas
    const total = Number(j.total);
    const jugados = Number(j.jugados);
    let statusClass = "jornada-vacia";
    if (jugados === total && total > 0) statusClass = "jornada-completa";
    else if (jugados > 0) statusClass = "jornada-parcial";

    const btn = document.createElement("button");
    btn.className = `jornada-btn px-4 py-2 rounded-lg text-sm font-bold ${statusClass} ${currentJornada === j.jornada ? "active" : ""}`;
    btn.textContent = `Fecha ${j.jornada}`;
    btn.addEventListener("click", () => selectJornada(j.jornada));
    container.appendChild(btn);
  });
}

async function selectJornada(num) {
  currentJornada = num;
  document.querySelectorAll(".jornada-btn").forEach((b) => b.classList.toggle("active", b.textContent === `Fecha ${num}`));
  $("jornadaTitle").textContent = `Fecha ${num}`;
  $("jornadaDetail").classList.remove("hidden");
  hideFixtureBuilder();
  await loadPartidosJornada(num);
}

async function crearJornada() {
  const cat = getCategoria();
  const tor = getTorneo();
  if (!cat || !tor) { showToast("Seleccioná categoría y torneo", "error"); return; }

  const jornadas = await api.get(`/partidos/jornadas?categoria=${encodeURIComponent(cat)}&torneo=${encodeURIComponent(tor)}`).catch(() => []);
  const validJornadas = jornadas.filter((j) => j.jornada != null).map((j) => j.jornada);
  const nextNum = validJornadas.length ? Math.max(...validJornadas) + 1 : 1;
  currentJornada = nextNum;
  await loadJornadas();
  await selectJornada(nextNum);
  // Auto-show fixture builder for new empty jornada
  showFixtureBuilder();
}

// === Partidos de la jornada ===
async function loadPartidosJornada(jornada) {
  const cat = getCategoria();
  const tor = getTorneo();
  const container = $("partidosContainer");
  const noPartidos = $("noPartidos");
  let partidos = [];

  try {
    partidos = await api.get(`/partidos?categoria=${encodeURIComponent(cat)}&torneo=${encodeURIComponent(tor)}&jornada=${jornada}`);
  } catch (error) {
    container.innerHTML = "<p class='text-sm text-red-300'>No se pudieron cargar los partidos de esta fecha.</p>";
    noPartidos.classList.add("hidden");
    showToast(error.message, "error");
    return;
  }

  fixturePartidosCache = partidos;

  container.innerHTML = "";

  if (!partidos.length) {
    noPartidos.classList.remove("hidden");
    toggleFlyerButtons(false, false);
    return;
  }
  noPartidos.classList.add("hidden");

  // Show flyer export buttons based on match states
  const allJugados = partidos.length > 0 && partidos.every((p) => p.estado === "jugado");
  const anyPendiente = partidos.length > 0 && partidos.some((p) => p.estado !== "jugado");
  toggleFlyerButtons(allJugados, anyPendiente);

  partidos.forEach((p) => {
    const isJugado = p.estado === "jugado";
    const localEscudo = p.localEscudo || DEFAULT_ESCUDO;
    const visitanteEscudo = p.visitanteEscudo || DEFAULT_ESCUDO;

    // Badges de estado
    let estadoBadge = "";
    let estadoClass = "";
    switch (p.estado) {
      case "jugado":
        estadoBadge = "🟢 Finalizado";
        estadoClass = "bg-emerald-500/20 text-emerald-300";
        break;
      case "en_curso":
        estadoBadge = "🟡 En progreso";
        estadoClass = "bg-amber-500/20 text-amber-300";
        break;
      case "postergado":
        estadoBadge = "🔴 Postergado";
        estadoClass = "bg-red-500/20 text-red-300";
        break;
      default:
        estadoBadge = "🔵 Pendiente";
        estadoClass = "bg-slate-500/20 text-slate-300";
    }

    const card = document.createElement("div");
    card.className = "bg-white/5 border border-white/10 rounded-lg p-3 sm:p-4 flex flex-col gap-2 sm:gap-3";
    card.innerHTML = `
      <div class="flex items-center gap-2 sm:gap-3 flex-1 justify-center">
        <img src="${localEscudo}" alt="" class="logo-team-sm" onerror="this.src='${DEFAULT_ESCUDO}'" />
        <span class="font-bold text-right flex-1 text-xs sm:text-sm truncate">${p.local}</span>
        ${isJugado || p.estado === "en_curso"
          ? `<span class="bg-primary/20 text-primary font-bold px-2 sm:px-3 py-1 rounded-lg text-sm sm:text-lg min-w-[60px] sm:min-w-[80px] text-center">${p.golesLocal || 0} - ${p.golesVisitante || 0}</span>`
          : `<span class="bg-slate-700 text-slate-400 font-bold px-2 sm:px-3 py-1 rounded-lg text-xs sm:text-sm">vs</span>`
        }
        <span class="font-bold flex-1 text-xs sm:text-sm truncate">${p.visitante}</span>
        <img src="${visitanteEscudo}" alt="" class="logo-team-sm" onerror="this.src='${DEFAULT_ESCUDO}'" />
      </div>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-xs text-slate-400 flex-wrap">
          <span>${p.fecha || ""}</span>
          <span>${p.hora || ""}</span>
          <span class="hidden sm:inline">·</span>
          <span class="hidden sm:inline">${p.estadio || ""}</span>
          <span class="hidden sm:inline">·</span>
          <span class="px-2 py-0.5 rounded-full text-xs font-bold ${estadoClass}">${estadoBadge}</span>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
          <button data-id="${p.id}" class="btnProgramar flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-colors bg-slate-700 hover:bg-slate-600 text-white">
            <span class="material-symbols-outlined text-[16px] align-middle mr-0.5">schedule</span>
            Programar
          </button>
          ${p.estado === "pendiente" ? `<button data-id="${p.id}" class="btnIniciar flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-colors bg-amber-500 hover:bg-amber-400 text-black">
            <span class="material-symbols-outlined text-[16px] align-middle mr-0.5">play_arrow</span>
            Iniciar
          </button>` : ""}
          <button data-id="${p.id}" class="btnCargar flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-colors ${isJugado ? "bg-blue-600 hover:bg-blue-700 text-white" : "bg-primary hover:bg-primary/80 text-white"}">
            <span class="material-symbols-outlined text-[16px] align-middle mr-0.5">${isJugado ? "edit" : "scoreboard"}</span>
            ${isJugado ? "Editar" : "Cargar"}
          </button>
          <button data-id="${p.id}" class="btnEliminar px-2 py-1.5 rounded-lg text-xs sm:text-sm bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
            <span class="material-symbols-outlined text-[16px]">delete</span>
          </button>
        </div>
      </div>
    `;
    container.appendChild(card);
  });

  container.querySelectorAll(".btnIniciar").forEach((btn) => {
    btn.addEventListener("click", async () => {
      try {
        await api.put(`/partidos/${btn.dataset.id}`, { estado: "en_curso" });
        showToast("Partido iniciado");
        await loadPartidosJornada(currentJornada);
      } catch (error) {
        showToast(error.message, "error");
      }
    });
  });

  container.querySelectorAll(".btnCargar").forEach((btn) => {
    btn.addEventListener("click", () => {
      const p = partidos.find((x) => x.id == btn.dataset.id);
      if (p) openResultModal(p);
    });
  });

  container.querySelectorAll(".btnProgramar").forEach((btn) => {
    btn.addEventListener("click", () => {
      const p = fixturePartidosCache.find((x) => x.id == btn.dataset.id);
      if (p) openScheduleModal(p);
    });
  });

  container.querySelectorAll(".btnEliminar").forEach((btn) => {
    btn.addEventListener("click", async () => {
      if (!confirm("¿Eliminar este partido?")) return;
      try {
        await api.del(`/partidos/${btn.dataset.id}`);
        showToast("Partido eliminado");
        await loadPartidosJornada(currentJornada);
        await loadJornadas();
      } catch (error) {
        showToast(error.message, "error");
      }
    });
  });
}

// === Fixture Builder ===
function showFixtureBuilder() {
  const builder = $("fixtureBuilder");
  builder.classList.remove("hidden");
  const rows = $("fixtureRows");
  rows.innerHTML = "";
  addFixtureRow();
}

function hideFixtureBuilder() {
  $("fixtureBuilder").classList.add("hidden");
  $("fixtureRows").innerHTML = "";
}

function addFixtureRow() {
  const rows = $("fixtureRows");
  const idx = rows.children.length;
  const row = document.createElement("div");
  row.className = "fixture-row bg-white/5 border border-white/10 rounded-lg p-3";
  row.dataset.idx = idx;

  const equipoOptions = equiposCache.map((e) => `<option value="${e.id}" data-escudo="${e.escudo || ''}" data-estadio="${e.estadio || ''}">${e.nombre}</option>`).join("");

  row.innerHTML = `
    <div class="flex flex-col gap-2 sm:gap-3">
      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-stretch sm:items-center">
        <!-- Local -->
        <div class="flex items-center gap-2 flex-1">
          <img class="logo-team-sm fixtureLocalLogo" src="${DEFAULT_ESCUDO}" alt="" />
          <select class="fixtureLocal border rounded p-1.5 text-sm flex-1 min-w-0">
            <option value="" disabled selected>Local</option>
            ${equipoOptions}
          </select>
        </div>
        <span class="text-slate-500 font-bold text-sm text-center">vs</span>
        <!-- Visitante -->
        <div class="flex items-center gap-2 flex-1">
          <select class="fixtureVisitante border rounded p-1.5 text-sm flex-1 min-w-0">
            <option value="" disabled selected>Visitante</option>
            ${equipoOptions}
          </select>
          <img class="logo-team-sm fixtureVisitanteLogo" src="${DEFAULT_ESCUDO}" alt="" />
        </div>
      </div>
      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 items-stretch sm:items-center">
        <input type="date" class="fixtureFecha border border-white/20 rounded p-1.5 text-sm w-full sm:w-[130px]" />
        <input type="time" class="fixtureHora border border-white/20 rounded p-1.5 text-sm w-full sm:w-[100px]" />
        <input type="text" class="fixtureEstadio border border-white/20 rounded p-1.5 text-sm w-full sm:w-[140px] flex-1" placeholder="Estadio del local" />
        <input type="url" class="fixtureBroadcast border border-white/20 rounded p-1.5 text-sm w-full sm:w-[160px]" placeholder="Link YouTube (opcional)" />
        <button class="removeFixtureRow text-red-400 hover:text-red-300 transition-colors self-end sm:self-center" title="Quitar">
          <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
      </div>
    </div>
  `;

  // Logo update on select change
  row.querySelector(".fixtureLocal").addEventListener("change", (e) => {
    const opt = e.target.selectedOptions[0];
    const escudo = opt?.dataset.escudo || "";
    row.querySelector(".fixtureLocalLogo").src = escudo || DEFAULT_ESCUDO;
    applyEquipoLocalEstadio(row.querySelector(".fixtureEstadio"), e.target.value);
  });
  row.querySelector(".fixtureVisitante").addEventListener("change", (e) => {
    const opt = e.target.selectedOptions[0];
    const escudo = opt?.dataset.escudo || "";
    row.querySelector(".fixtureVisitanteLogo").src = escudo || DEFAULT_ESCUDO;
  });

  // Remove row
  row.querySelector(".removeFixtureRow").addEventListener("click", () => {
    row.remove();
    if ($("fixtureRows").children.length === 0) addFixtureRow();
  });

  rows.appendChild(row);
}

async function saveFixture() {
  const rows = $("fixtureRows").children;
  if (!rows.length) return;
  if (!currentJornada) { showToast("No hay jornada seleccionada", "error"); return; }

  const partidos = [];
  for (const row of rows) {
    const local = row.querySelector(".fixtureLocal").value;
    const visitante = row.querySelector(".fixtureVisitante").value;
    const fecha = row.querySelector(".fixtureFecha").value;
    const hora = row.querySelector(".fixtureHora").value;
    const estadio = row.querySelector(".fixtureEstadio").value.trim();
    const broadcast = row.querySelector(".fixtureBroadcast").value.trim() || null;

    if (!local || !visitante) { showToast("Seleccioná local y visitante en cada partido", "error"); return; }
    if (local === visitante) { showToast("Local y visitante deben ser distintos", "error"); return; }
    if (!fecha || !hora) { showToast("Completá fecha y hora de cada partido", "error"); return; }
    partidos.push({ local, visitante, fecha, hora, estadio, broadcast });
  }

  // Check for duplicate teams
  const allTeams = partidos.flatMap((p) => [p.local, p.visitante]);
  const uniqueTeams = new Set(allTeams);
  if (uniqueTeams.size !== allTeams.length) {
    showToast("Hay equipos repetidos en el fixture", "error");
    return;
  }

  try {
    const result = await api.post("/partidos/bulk", {
      partidos: partidos.map((partido) => ({
        ...partido,
        estado: "pendiente",
        categoria: getCategoria(),
        torneo: getTorneo(),
        jornada: currentJornada,
      })),
    });

    hideFixtureBuilder();
    showToast(`${result.total} partido${result.total > 1 ? "s" : ""} guardado${result.total > 1 ? "s" : ""}`);
    await loadPartidosJornada(currentJornada);
    await loadJornadas();
  } catch (error) {
    showToast(error.message, "error");
  }
}

function openScheduleModal(partido) {
  const modal = $("scheduleModal");
  if (!modal) {
    showToast("No se encontró el modal para programar partidos", "error");
    return;
  }

  const partidoIdInput = $("schedulePartidoId");
  const label = $("schedulePartidoLabel");
  const fechaInput = $("scheduleFecha");
  const horaInput = $("scheduleHora");
  const estadioInput = $("scheduleEstadio");
  const estadoInput = $("scheduleEstado");
  const broadcastInput = $("scheduleBroadcast");

  if (!partidoIdInput || !label || !fechaInput || !horaInput || !estadioInput || !estadoInput || !broadcastInput) {
    showToast("Faltan campos del modal de programación", "error");
    return;
  }

  partidoIdInput.value = partido.id;
  label.textContent = `${partido.local} vs ${partido.visitante}`;
  fechaInput.value = partido.fecha || "";
  horaInput.value = partido.hora || "";
  estadioInput.value = partido.estadio || "";
  estadoInput.value = partido.estado === "jugado" ? "pendiente" : (partido.estado || "pendiente");
  broadcastInput.value = partido.broadcast || "";
  modal.classList.remove("hidden");
}

function closeScheduleModal() {
  const modal = $("scheduleModal");
  if (modal) {
    modal.classList.add("hidden");
  }
}

async function saveScheduleModal() {
  const id = $("schedulePartidoId")?.value;
  if (!id) {
    showToast("No se encontró el partido a programar", "error");
    return;
  }

  const fecha = $("scheduleFecha")?.value || "";
  const hora = $("scheduleHora")?.value || "";
  const estadio = $("scheduleEstadio")?.value?.trim() || "";
  const estado = $("scheduleEstado")?.value || "pendiente";
  const broadcast = $("scheduleBroadcast")?.value?.trim() || "";

  const payload = {
    fecha,
    hora,
    estadio,
    estado,
    broadcast,
  };

  if (!payload.fecha || !payload.hora) {
    showToast("Completá fecha y hora del partido", "error");
    return;
  }

  try {
    await api.put(`/partidos/${id}`, payload);
    closeScheduleModal();
    showToast("Programación actualizada");
    await loadPartidosJornada(currentJornada);
    await loadJornadas();
  } catch (error) {
    showToast(error.message, "error");
  }
}

// === Modal: Cargar resultado ===
async function openResultModal(partido) {
  resultModalState.localEquipoId = partido.localId;
  resultModalState.visitanteEquipoId = partido.visitanteId;

  $("resultPartidoId").value = partido.id;
  $("resultLocalName").textContent = partido.local;
  $("resultVisitanteName").textContent = partido.visitante;
  $("resultGolesLocal").value = partido.golesLocal || 0;
  $("resultGolesVisitante").value = partido.golesVisitante || 0;
  $("golesLocalTitle").textContent = partido.local;
  $("golesVisitanteTitle").textContent = partido.visitante;
  $("tarjetasLocalTitle").textContent = partido.local;
  $("tarjetasVisitanteTitle").textContent = partido.visitante;

  // Escudos
  $("resultLocalEscudo").src = partido.localEscudo || getEscudo(partido.localId);
  $("resultVisitanteEscudo").src = partido.visitanteEscudo || getEscudo(partido.visitanteId);

  // Load existing eventos for this match
  let existingEventos = [];
  let jugLocal = [];
  let jugVisitante = [];

  try {
    [existingEventos, jugLocal, jugVisitante] = await Promise.all([
      api.get(`/eventos?partido_id=${partido.id}&_=${Date.now()}`),
      api.get(`/jugadores?equipo=${partido.localId}`),
      api.get(`/jugadores?equipo=${partido.visitanteId}`),
    ]);
  } catch (error) {
    showToast(error.message, "error");
    return;
  }

  eventosTemp = existingEventos.map((ev) => ({
    jugador_id: ev.jugador_id,
    equipo_id: ev.equipo_id,
    tipo: ev.tipo,
    minuto: ev.minuto == null || ev.minuto === "" ? null : Number(ev.minuto),
    jugador_nombre: ev.jugador_nombre || "?",
  }));

  renderPlayerList("golesLocalList", jugLocal, partido.localId, "gol");
  renderPlayerList("golesVisitanteList", jugVisitante, partido.visitanteId, "gol");
  renderPlayerList("tarjetasLocalList", jugLocal, partido.localId, "tarjeta");
  renderPlayerList("tarjetasVisitanteList", jugVisitante, partido.visitanteId, "tarjeta");
  renderEventosTags();

  // Inicializar radio buttons según estado del partido
  const radioEnCurso = document.querySelector('input[name="resultEstado"][value="en_curso"]');
  const radioJugado = document.querySelector('input[name="resultEstado"][value="jugado"]');
  if (radioEnCurso && radioJugado) {
    // Si el partido ya está "en_curso", mantener ese estado
    // Si está "jugado", mantener "jugado" (modo edición)
    // Si está "pendiente" u otro, default a "en_curso" (para ir cargando sin finalizar)
    if (partido.estado === "jugado") {
      radioJugado.checked = true;
      radioEnCurso.checked = false;
    } else {
      // Default: "en_curso" para cualquier otro estado (pendiente, en_curso, postergado)
      radioEnCurso.checked = true;
      radioJugado.checked = false;
    }
  }

  switchTab("goles");
  $("resultModal").classList.remove("hidden");
}

function renderPlayerList(containerId, jugadores, equipoId, mode) {
  const container = $(containerId);
  container.innerHTML = "";

  jugadores.forEach((j) => {
    const div = document.createElement("div");
    div.className = "flex items-center gap-2";

    if (mode === "gol") {
      div.className = "flex items-center gap-2";
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "flex-1 text-left text-sm px-2 py-1 rounded hover:bg-primary/20 transition-colors truncate";
      btn.textContent = j.nombre;
      btn.addEventListener("click", () => {
        eventosTemp.push({ jugador_id: j.id, equipo_id: equipoId, tipo: "gol", jugador_nombre: j.nombre });
        renderEventosTags();
        autoUpdateScore();
      });
      div.appendChild(btn);

      const ownGoalBtn = document.createElement("button");
      ownGoalBtn.type = "button";
      ownGoalBtn.className = "text-[10px] px-2 py-1 rounded bg-orange-500/20 text-orange-200 hover:bg-orange-500/30 transition-colors";
      ownGoalBtn.textContent = "EC";
      ownGoalBtn.title = "Registrar gol en contra";
      ownGoalBtn.addEventListener("click", () => {
        eventosTemp.push({ jugador_id: j.id, equipo_id: equipoId, tipo: "autogol", jugador_nombre: j.nombre });
        renderEventosTags();
        autoUpdateScore();
      });
      div.appendChild(ownGoalBtn);
    } else {
      const nameSpan = document.createElement("span");
      nameSpan.className = "flex-1 text-sm truncate";
      nameSpan.textContent = j.nombre;
      div.appendChild(nameSpan);

      const btnA = document.createElement("button");
      btnA.type = "button";
      btnA.className = "text-xs px-1.5 py-0.5 rounded bg-yellow-500/20 text-yellow-300 hover:bg-yellow-500/40";
      btnA.textContent = "🟨";
      btnA.addEventListener("click", () => {
        eventosTemp.push({ jugador_id: j.id, equipo_id: equipoId, tipo: "amarilla", minuto: null, jugador_nombre: j.nombre });
        renderEventosTags();
      });
      div.appendChild(btnA);

      const btnR = document.createElement("button");
      btnR.type = "button";
      btnR.className = "text-xs px-1.5 py-0.5 rounded bg-red-500/20 text-red-300 hover:bg-red-500/40";
      btnR.textContent = "🟥";
      btnR.addEventListener("click", () => {
        eventosTemp.push({ jugador_id: j.id, equipo_id: equipoId, tipo: "roja", minuto: null, jugador_nombre: j.nombre });
        renderEventosTags();
      });
      div.appendChild(btnR);
    }

    container.appendChild(div);
  });

  if (!jugadores.length) {
    container.innerHTML = "<p class='text-xs text-slate-500'>No hay jugadores cargados para este equipo</p>";
  }
}

function getEventoEquipoBeneficiado(evento) {
  if (evento.tipo === "autogol") {
    return Number(evento.equipo_id) === Number(resultModalState.localEquipoId)
      ? Number(resultModalState.visitanteEquipoId)
      : Number(resultModalState.localEquipoId);
  }

  return Number(evento.equipo_id);
}

function autoUpdateScore() {
  const golesLocal = eventosTemp.filter((e) => ["gol", "autogol"].includes(e.tipo) && getEventoEquipoBeneficiado(e) === Number(resultModalState.localEquipoId)).length;
  const golesVisitante = eventosTemp.filter((e) => ["gol", "autogol"].includes(e.tipo) && getEventoEquipoBeneficiado(e) === Number(resultModalState.visitanteEquipoId)).length;
  $("resultGolesLocal").value = golesLocal;
  $("resultGolesVisitante").value = golesVisitante;
}

function renderEventosTags() {
  const golesContainer = $("golesRegistrados");
  const tarjetasContainer = $("tarjetasRegistradas");
  golesContainer.innerHTML = "";
  tarjetasContainer.innerHTML = "";

  eventosTemp.forEach((ev, idx) => {
    const tag = document.createElement("span");
    tag.className = "inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full cursor-pointer transition-colors";

    if (ev.tipo === "gol" || ev.tipo === "autogol") {
      const side = getEventoEquipoBeneficiado(ev) === Number(resultModalState.localEquipoId) ? "L" : "V";
      const label = ev.tipo === "autogol" ? "EC" : "⚽";
      const extra = ev.tipo === "autogol" ? "en contra" : "";
      tag.className += ev.tipo === "autogol"
        ? " bg-orange-500/20 text-orange-200 hover:bg-red-500/30"
        : " bg-primary/20 text-primary hover:bg-red-500/30";
      tag.innerHTML = `${label} ${ev.jugador_nombre} ${extra ? `<span class="text-[10px] uppercase text-orange-100">${extra}</span>` : ""} <span class="text-[10px] text-slate-500">(${side})</span> <span class='text-[10px] text-red-400'>✕</span>`;
      tag.addEventListener("click", () => { eventosTemp.splice(idx, 1); renderEventosTags(); autoUpdateScore(); });
      golesContainer.appendChild(tag);
    } else {
      const icon = ev.tipo === "amarilla" ? "🟨" : "🟥";
      tag.className += ev.tipo === "amarilla" ? " bg-yellow-500/20 text-yellow-300 hover:bg-red-500/30" : " bg-red-500/20 text-red-300 hover:bg-red-500/40";
      tag.innerHTML = `${icon} ${ev.jugador_nombre} <span class='text-[10px] text-red-400'>✕</span>`;
      tag.addEventListener("click", () => { eventosTemp.splice(idx, 1); renderEventosTags(); });
      tarjetasContainer.appendChild(tag);
    }
  });

  if (!eventosTemp.filter((e) => e.tipo === "gol" || e.tipo === "autogol").length) {
    golesContainer.innerHTML = "<span class='text-xs text-slate-500'>Ningún gol registrado</span>";
  }
  if (!eventosTemp.filter((e) => e.tipo !== "gol" && e.tipo !== "autogol").length) {
    tarjetasContainer.innerHTML = "<span class='text-xs text-slate-500'>Ninguna tarjeta registrada</span>";
  }
}

function switchTab(tab) {
  const isGoles = tab === "goles";
  $("panelGoles").classList.toggle("hidden", !isGoles);
  $("panelTarjetas").classList.toggle("hidden", isGoles);
  $("tabGoles").classList.toggle("border-primary", isGoles);
  $("tabGoles").classList.toggle("text-primary", isGoles);
  $("tabGoles").classList.toggle("border-transparent", !isGoles);
  $("tabGoles").classList.toggle("text-slate-400", !isGoles);
  $("tabTarjetas").classList.toggle("border-primary", !isGoles);
  $("tabTarjetas").classList.toggle("text-primary", !isGoles);
  $("tabTarjetas").classList.toggle("border-transparent", isGoles);
  $("tabTarjetas").classList.toggle("text-slate-400", isGoles);
}

async function saveResult() {
  const partidoId = $("resultPartidoId").value;
  const golesLocal = Number($("resultGolesLocal").value) || 0;
  const golesVisitante = Number($("resultGolesVisitante").value) || 0;
  const estadoSeleccionado = document.querySelector('input[name="resultEstado"]:checked')?.value || "jugado";
  const finalizar = estadoSeleccionado === "jugado";

  const mensaje = finalizar 
    ? `¿Guardar resultado ${golesLocal} - ${golesVisitante} y marcar como finalizado?`
    : `¿Guardar progreso ${golesLocal} - ${golesVisitante}? (No se finalizará el partido)`;

  if (!confirm(mensaje)) return;

  try {
    await api.put(`/partidos/${partidoId}/resultado`, {
      golesLocal,
      golesVisitante,
      finalizar,
      eventos: eventosTemp.map((ev) => ({
        jugador_id: ev.jugador_id,
        equipo_id: ev.equipo_id,
        tipo: ev.tipo,
        minuto: ev.minuto ?? null,
      })),
    });

    $("resultModal").classList.add("hidden");
    showToast(finalizar ? "Partido finalizado correctamente" : "Progreso guardado");
    tablaDataCache = null;
    // Recargar solo los partidos de la jornada actual
    await loadPartidosJornada(currentJornada);
    // Recargar jornadas para actualizar contadores (sin auto-seleccionar)
    const cat = getCategoria();
    const tor = getTorneo();
    if (!cat || !tor) return;
    
    const jornadas = await api.get(`/partidos/jornadas?categoria=${encodeURIComponent(cat)}&torneo=${encodeURIComponent(tor)}`).catch(() => []);
    const container = $("jornadasContainer");
    container.innerHTML = "";
    
    if (!jornadas.length) {
      container.innerHTML = "<p class='text-sm text-slate-400'>No hay jornadas. Creá la primera con el botón de arriba.</p>";
      return;
    }
    
    jornadas.forEach((j) => {
      if (j.jornada == null) return;
      const total = Number(j.total);
      const jugados = Number(j.jugados);
      let statusClass = "jornada-vacia";
      if (jugados === total && total > 0) statusClass = "jornada-completa";
      else if (jugados > 0) statusClass = "jornada-parcial";
      
      const btn = document.createElement("button");
      btn.className = `jornada-btn px-4 py-2 rounded-lg text-sm font-bold ${statusClass} ${currentJornada === j.jornada ? "active" : ""}`;
      btn.textContent = `Fecha ${j.jornada}`;
      btn.addEventListener("click", () => selectJornada(j.jornada));
      container.appendChild(btn);
    });
  } catch (error) {
    showToast(error.message, "error");
  }
}

async function generarSemanarioHtml() {
  const torneo = getTorneo();
  if (!torneo) {
    showToast("Seleccioná un torneo antes de generar el semanario", "error");
    return;
  }

  const generarBtn = $("generarSemanarioBtn");
  const copiarBtn = $("copiarSemanarioBtn");
  const output = $("semanarioHtmlOutput");
  const meta = $("semanarioHtmlMeta");

  try {
    generarBtn.disabled = true;
    generarBtn.textContent = "Generando...";

    const query = new URLSearchParams({ torneo });
    const response = await api.get(`/setup/semanario-html?${query.toString()}`);

    output.value = response?.html || "";
    meta.textContent = `Actualizado para ${response?.torneo || torneo} el ${new Date(response.generatedAt).toLocaleString("es-AR")}`;
    meta.classList.remove("hidden");
    copiarBtn.disabled = !output.value;
    showToast("HTML generado correctamente");
  } catch (error) {
    showToast(error.message || "No se pudo generar el semanario", "error");
  } finally {
    generarBtn.disabled = false;
    generarBtn.textContent = "Generar HTML";
  }
}

async function copiarSemanarioHtml() {
  const output = $("semanarioHtmlOutput");
  if (!output.value) {
    showToast("Primero generá el HTML", "error");
    return;
  }

  try {
    await navigator.clipboard.writeText(output.value);
    showToast("HTML copiado");
  } catch {
    showToast("No se pudo copiar el HTML", "error");
  }
}

// === Filter logic ===
function onFilterChange() {
  const cat = getCategoria();
  const tor = getTorneo();
  const hasFilters = cat && tor;

  $("mainContent").classList.toggle("hidden", !hasFilters);
  $("noFilters").classList.toggle("hidden", hasFilters);

  if (hasFilters) {
    currentJornada = null;
    $("jornadaDetail").classList.add("hidden");
    hideFixtureBuilder();
    toggleFlyerButtons(false, false);
    tablaDataCache = null;
    loadJornadas();
  }
}

// === Flyer Export ===
function toggleFlyerButtons(showResults, showFixture) {
  const bar = $("flyerExportBar");
  const resultsSection = $("flyerResultsSection");
  const positionsSection = $("flyerPositionsSection");
  const fixtureSection = $("flyerFixtureSection");
  if (bar) bar.classList.toggle("hidden", !showResults && !showFixture);
  if (resultsSection) resultsSection.classList.toggle("hidden", !showResults);
  if (positionsSection) positionsSection.classList.toggle("hidden", !showResults);
  if (fixtureSection) fixtureSection.classList.toggle("hidden", !showFixture);
}

let tablaDataCache = null;

async function fetchTablaData() {
  const cat = getCategoria();
  const tor = getTorneo();
  if (!cat || !tor) return null;
  try {
    const data = await api.get(`/semanario-data?torneo=${encodeURIComponent(tor)}&categoria=${encodeURIComponent(cat)}`);
    tablaDataCache = data;
    return data;
  } catch (error) {
    showToast("No se pudo obtener la tabla de posiciones", "error");
    return null;
  }
}

function buildFlyerHtml(partidos, tabla, meta, format, content) {
  const isStory = format === "story";
  const W = 1080;
  const H = isStory ? 1920 : 1350;

  const origin = window.location.origin;
  const fondoUrl = isStory ? origin + "/assets/fondo-story.png" : origin + "/assets/fondo-feed.png";
  const resolveEscudo = (esc) => {
    if (!esc || esc.startsWith("data:")) return esc || "";
    return esc.startsWith("http") ? esc : origin + esc;
  };

  const nPartidos = partidos.length;

  // --- Sizing ---
  const matchPadX = isStory ? 24 : 18;
  const matchPadY = isStory ? 16 : 12;
  const matchFontSize = isStory ? 28 : 24;
  const scoreFontSize = isStory ? 44 : 36;
  const logoSize = isStory ? 100 : 80;
  const scoreBoxW = isStory ? 180 : 150;

  const tablaLogoSize = isStory ? 34 : 28;
  const maxTablaRows = isStory ? 20 : 14;
  const tablaRows = (tabla || []).slice(0, maxTablaRows);

  // Layout zones (reservando espacio para publicidades en el fondo)
  const headerH = isStory ? 250 : 210;
  const footerH = isStory ? 250 : 450; // Espacio de publicidades en el fondo (story: 250px, feed: 450px)
  const contentPadX = isStory ? 40 : 30;
  const contentPadY = isStory ? 20 : 16;
  const titleH = isStory ? 80 : 72;
  const headerTopOffset = content === "posiciones" ? 40 : 0;
  // containerPad*2 = espacio de padding arriba+abajo del contenedor principal (30px cada lado)
  // contentMarginTop = margin-top extra del wrapper de contenido (solo en feed)
  const containerPad = 30;
  const contentMarginTop = !isStory ? 30 : 0;
  const contentAreaH = H - headerH - footerH - 3 - (contentPadY * 2) - titleH - headerTopOffset - containerPad * 2 - contentMarginTop;
  const contentDivH = H - headerH - footerH - 3 - headerTopOffset - containerPad * 2 - contentMarginTop;

  // --- Match rows ---
  const matchCardH = matchPadY * 2 + Math.max(logoSize, scoreFontSize * 1.3) + 2;
  const totalMatchesH = matchCardH * nPartidos;
  const maxGap = isStory ? 28 : 20;
  const minGap = isStory ? 10 : 8;
  const dynamicMatchGap = nPartidos > 1
    ? Math.max(minGap, Math.min(Math.floor((contentAreaH - totalMatchesH) / (nPartidos - 1)), maxGap))
    : 0;

  const matchesHtml = partidos.map((p, idx) => {
    const localEsc = resolveEscudo(p.localEscudo);
    const visitanteEsc = resolveEscudo(p.visitanteEscudo);
    const scoreContent = content === "fixture" ? "VS" : `${p.golesLocal ?? 0} - ${p.golesVisitante ?? 0}`;
    const scoreFontActual = content === "fixture" ? Math.floor(scoreFontSize * 0.6) : scoreFontSize;
    const isLast = idx === partidos.length - 1;
    return `
    <div style="display:flex;align-items:center;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:16px;${isLast ? '' : `margin-bottom:${dynamicMatchGap}px;`}padding:${matchPadY}px ${matchPadX}px;">
      <div style="flex:1;display:flex;justify-content:center;align-items:center;"><img src="${localEsc}" style="width:${logoSize}px;height:${logoSize}px;object-fit:contain;" /></div>
      <div style="width:${scoreBoxW}px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:rgba(140,82,255,0.2);border:1px solid rgba(140,82,255,0.15);border-radius:14px;padding:${isStory ? 12 : 10}px 0;font-weight:800;font-size:${scoreFontActual}px;color:#c9a7ff;line-height:1;">${scoreContent}</div>
      <div style="flex:1;display:flex;justify-content:center;align-items:center;"><img src="${visitanteEsc}" style="width:${logoSize}px;height:${logoSize}px;object-fit:contain;" /></div>
    </div>`;
  }).join("");

  // --- Tabla ---
  const tablaRowH = tablaRows.length > 0 ? Math.min(Math.floor(contentAreaH / (tablaRows.length + 1.5)), isStory ? 68 : 56) : 40;
  const tableFontSize = Math.min(isStory ? 26 : 22, Math.floor(tablaRowH * 0.42));
  const tableHeaderFontSize = Math.floor(tableFontSize * 0.82);
  const ptsFontSize = Math.floor(tableFontSize * 1.1);

  const tablaHtml = tablaRows.length ? `
    <table style="width:100%;border-collapse:collapse;font-size:${tableFontSize}px;color:#e0e0e0;">
      <thead>
        <tr style="color:#9ca3af;font-size:${tableHeaderFontSize}px;text-transform:uppercase;letter-spacing:1.5px;font-weight:700;">
          <th style="text-align:center;padding:6px 8px ${isStory ? 10 : 8}px;border-bottom:3px solid #8c52ff;width:${isStory ? 64 : 52}px;">Pos</th>
          <th style="text-align:left;padding:6px 8px ${isStory ? 10 : 8}px;border-bottom:3px solid #8c52ff;">Equipo</th>
          <th style="text-align:center;padding:6px 8px ${isStory ? 10 : 8}px;border-bottom:3px solid #8c52ff;width:${isStory ? 64 : 52}px;">PJ</th>
          <th style="text-align:center;padding:6px 8px ${isStory ? 10 : 8}px;border-bottom:3px solid #8c52ff;width:${isStory ? 72 : 58}px;">DG</th>
          <th style="text-align:center;padding:6px 8px ${isStory ? 10 : 8}px;border-bottom:3px solid #8c52ff;width:${isStory ? 72 : 58}px;color:#c9a7ff;background:rgba(255,255,255,0.06);">PTS</th>
        </tr>
      </thead>
      <tbody>
        ${tablaRows.map((row, i) => {
          const isEven = i % 2 === 0;
          const posText = String(i + 1).padStart(2, "0");
          const dgVal = row.dg;
          const dgColor = dgVal > 0 ? "#6ee7b7" : dgVal < 0 ? "#fca5a5" : "#9ca3af";
          return `
        <tr style="background:${isEven ? "rgba(255,255,255,0.04)" : "transparent"};height:${tablaRowH}px;">
          <td style="text-align:center;padding:0 8px;vertical-align:middle;font-weight:700;font-style:italic;color:${i < 3 ? "#c9a7ff" : "#6b7280"}">${posText}</td>
          <td style="padding:0 8px;vertical-align:middle;font-weight:600;">
            <div style="display:flex;align-items:center;gap:${isStory ? 12 : 8}px;">
              <img src="${resolveEscudo(row.escudo)}" style="width:${tablaLogoSize}px;height:${tablaLogoSize}px;object-fit:contain;flex-shrink:0;" crossorigin="anonymous" />
              <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${row.equipo}</span>
            </div>
          </td>
          <td style="text-align:center;padding:0 8px;vertical-align:middle;font-family:monospace;">${row.pj}</td>
          <td style="text-align:center;padding:0 8px;vertical-align:middle;font-family:monospace;color:${dgColor};">${dgVal > 0 ? "+" : ""}${dgVal}</td>
          <td style="text-align:center;padding:0 8px;vertical-align:middle;font-weight:800;font-size:${ptsFontSize}px;color:#c9a7ff;background:rgba(255,255,255,0.05);">${row.puntos}</td>
        </tr>`;
        }).join("")}
      </tbody>
    </table>` : "";

  const bigTitle = content === "posiciones"
    ? "Tabla de<br>Posiciones"
    : content === "fixture"
    ? "FIXTURE"
    : "RESULTADOS";

  // --- Content section ---
  const contentHtml = content === "fixture"
    ? `
      <div style="height:${contentDivH}px;display:flex;flex-direction:column;justify-content:flex-start;padding:30px ${contentPadX}px ${contentPadY}px;box-sizing:border-box;">
        ${matchesHtml}
      </div>`
    : content === "resultados"
    ? `
      <div style="height:${contentDivH}px;display:flex;flex-direction:column;justify-content:flex-start;padding:${isStory ? 30 : contentPadY}px ${contentPadX}px ${contentPadY}px;box-sizing:border-box;">
        ${matchesHtml}
      </div>`
    : `
      <div style="height:${contentDivH}px;display:flex;flex-direction:column;justify-content:flex-start;padding:${contentPadY}px ${contentPadX}px;box-sizing:border-box;">
        ${tablaHtml}
      </div>`;

  // --- Full layout ---
  return `
  <div style="width:${W}px;height:${H}px;font-family:'Lexend',sans-serif;color:#fff;display:flex;flex-direction:column;overflow:hidden;position:relative;padding:30px;box-sizing:border-box;">
    <img src="${fondoUrl}" style="position:absolute;top:0;left:0;width:${W}px;height:${H}px;object-fit:cover;z-index:0;" crossorigin="anonymous" />

    <div style="height:${headerH}px;display:flex;align-items:flex-end;justify-content:space-between;padding:${isStory ? 62 : 54}px ${isStory ? 70 : 60}px ${isStory ? 50 : 46}px;flex-shrink:0;position:relative;z-index:1;border-bottom:4px solid #8c52ff;box-sizing:border-box;margin-top:${headerTopOffset}px;">
      <div>
        <div style="font-size:${isStory ? 22 : 18}px;font-weight:900;color:#8c52ff;text-transform:uppercase;letter-spacing:1px;margin-bottom:${isStory ? 6 : 4}px;white-space:nowrap;">${meta.categoria} · ${meta.torneo}</div>
        <div style="font-size:${isStory ? 52 : 40}px;font-weight:900;line-height:1;text-transform:uppercase;">${bigTitle}</div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:${isStory ? 12 : 10}px;flex-shrink:0;">
        <img src="${origin}/assets/logo.png" style="height:${isStory ? 54 : 44}px;object-fit:contain;" crossorigin="anonymous" />
        <div style="background:#8c52ff;color:#000;padding:${isStory ? '8px 20px' : '6px 16px'};font-weight:900;font-size:${isStory ? 22 : 18}px;text-transform:uppercase;white-space:nowrap;">
          Fecha ${meta.jornada}
        </div>
      </div>
    </div>

    <div style="height:${contentDivH}px;overflow:hidden;position:relative;z-index:1;margin-top:${isStory ? 0 : 30}px;">
      ${contentHtml}
    </div>
  </div>`;
}

function waitForImages(container) {
  const imgs = container.querySelectorAll("img");
  const promises = Array.from(imgs).map(
    (img) =>
      new Promise((resolve) => {
        if (img.complete && img.naturalWidth > 0) return resolve();
        img.onload = resolve;
        img.onerror = resolve;
      })
  );
  return Promise.all(promises);
}

let flyerCanvasResult = null;

async function exportFlyer(format, content) {
  const partidos = fixturePartidosCache;
  if (!partidos.length) {
    showToast("No hay partidos cargados", "error");
    return;
  }
  if (content !== "fixture" && !partidos.every((p) => p.estado === "jugado")) {
    showToast("Todos los partidos deben estar jugados para exportar", "error");
    return;
  }

  // Show loading state on the clicked button
  const btnId = content === "posiciones"
    ? (format === "story" ? "btnTablaStory" : "btnTablaFeed")
    : content === "fixture"
    ? (format === "story" ? "btnFixtureStory" : "btnFixtureFeed")
    : (format === "story" ? "btnResultStory" : "btnResultFeed");
  const btn = $(btnId);
  const originalText = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = `<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span> ...`;

  try {
    // Fetch tabla data
    const data = tablaDataCache || (await fetchTablaData());
    const cat = getCategoria();
    const tabla = data?.[cat]?.tabla || [];

    const meta = {
      jornada: partidos[0]?.jornada || currentJornada || 1,
      categoria: cat,
      torneo: getTorneo(),
    };

    // Build and render flyer HTML
    const html = buildFlyerHtml(partidos, tabla, meta, format, content);
    const wrap = $("flyerCanvasWrap");
    wrap.innerHTML = html;

    const flyerEl = wrap.firstElementChild;

    // Wait for all images and fonts to load
    await waitForImages(flyerEl);
    await document.fonts.ready;
    // Small extra delay for rendering
    await new Promise((r) => setTimeout(r, 200));

    // Capture with html-to-image
    const dataUrl = await htmlToImage.toPng(flyerEl, {
      pixelRatio: 1,
      width: parseInt(flyerEl.style.width),
      height: parseInt(flyerEl.style.height),
    });

    flyerCanvasResult = { dataUrl, format, meta, content };

    // Show preview modal
    const previewWrap = $("flyerPreviewWrap");
    const isStory = format === "story";

    // Scale preview to fit modal
    const previewMaxW = 440;
    const previewScale = previewMaxW / 1080;
    const previewH = (isStory ? 1920 : 1350) * previewScale;

    const previewImg = document.createElement("img");
    previewImg.src = dataUrl;
    previewImg.style.cssText = `width:${previewMaxW}px;height:${Math.round(previewH)}px;display:block;`;

    previewWrap.innerHTML = "";
    previewWrap.appendChild(previewImg);
    previewWrap.style.maxHeight = `${Math.min(Math.round(previewH), 500)}px`;
    previewWrap.style.overflow = "auto";

    const contentLabel = content === "posiciones" ? "Posiciones" : content === "fixture" ? "Fixture" : "Resultados";
    $("flyerModalTitle").textContent = `${contentLabel} — ${isStory ? "Story (1080×1920)" : "Feed (1080×1350)"}`;
    $("flyerModal").classList.remove("hidden");
  } catch (error) {
    console.error("Flyer export error:", error);
    showToast("Error al generar el flyer: " + error.message, "error");
  } finally {
    btn.disabled = false;
    btn.innerHTML = originalText;
  }
}

function downloadFlyer() {
  if (!flyerCanvasResult) return;
  const { dataUrl, format, meta, content } = flyerCanvasResult;
  const contentSlug = content === "posiciones" ? "posiciones" : content === "fixture" ? "fixture" : "resultados";
  const filename = `${contentSlug}-fecha-${meta.jornada}-${meta.categoria.toLowerCase()}-${format}.png`;

  const a = document.createElement("a");
  a.href = dataUrl;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  showToast(`Descargado: ${filename}`);
}

function closeFlyerModal() {
  $("flyerModal").classList.add("hidden");
  $("flyerPreviewWrap").innerHTML = "";
  $("flyerCanvasWrap").innerHTML = "";
}

// === Init ===
document.addEventListener("DOMContentLoaded", () => {
  if (!checkAuth()) return;

  // Filters
  $("categoriaFiltro").addEventListener("change", onFilterChange);
  $("torneoFiltro").addEventListener("change", onFilterChange);
  $("generarSemanarioBtn").addEventListener("click", generarSemanarioHtml);
  $("copiarSemanarioBtn").addEventListener("click", copiarSemanarioHtml);
  onFilterChange();

  // Jornada
  $("nuevaJornadaBtn").addEventListener("click", crearJornada);

  // Fixture builder
  $("agregarPartidoBtn").addEventListener("click", () => {
    if (!currentJornada) { showToast("Seleccioná una fecha primero", "error"); return; }
    showFixtureBuilder();
  });
  $("addFixtureRowBtn").addEventListener("click", addFixtureRow);
  $("cancelFixtureBtn").addEventListener("click", hideFixtureBuilder);
  $("saveFixtureBtn").addEventListener("click", saveFixture);

  // Result modal
  $("tabGoles").addEventListener("click", () => switchTab("goles"));
  $("tabTarjetas").addEventListener("click", () => switchTab("tarjetas"));
  $("closeResultModal").addEventListener("click", () => $("resultModal").classList.add("hidden"));
  $("cancelResult").addEventListener("click", () => $("resultModal").classList.add("hidden"));
  $("saveResult").addEventListener("click", saveResult);
  $("closeScheduleModal")?.addEventListener("click", closeScheduleModal);
  $("cancelSchedule")?.addEventListener("click", closeScheduleModal);
  $("saveSchedule")?.addEventListener("click", saveScheduleModal);

  // Flyer export
  $("btnResultStory").addEventListener("click", () => exportFlyer("story", "resultados"));
  $("btnResultFeed").addEventListener("click", () => exportFlyer("feed", "resultados"));
  $("btnTablaStory").addEventListener("click", () => exportFlyer("story", "posiciones"));
  $("btnTablaFeed").addEventListener("click", () => exportFlyer("feed", "posiciones"));
  $("btnFixtureStory").addEventListener("click", () => exportFlyer("story", "fixture"));
  $("btnFixtureFeed").addEventListener("click", () => exportFlyer("feed", "fixture"));
  $("downloadFlyerBtn").addEventListener("click", downloadFlyer);
  $("closeFlyerModal").addEventListener("click", closeFlyerModal);
  $("closeFlyerModalBtn").addEventListener("click", closeFlyerModal);
});

})();

