<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Panel — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body">
<?php require $root . '/views/partials/gtm-body.php'; ?>
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto">
      <div class="mb-6 sm:mb-8">
        <h1 class="text-xl sm:text-2xl font-bold md:text-3xl font-headline">Vista General del Sistema</h1>
        <p class="text-sm sm:text-base text-on-surface-variant mt-1">Bienvenido de nuevo. Aquí está lo que está pasando hoy.</p>
      </div>

      <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
        <div>
          <p class="text-sm font-medium text-on-surface-variant">Filtrar categoría de equipos</p>
          <select id="filtroCategoria" class="mt-1 h-10 rounded-lg border border-outline-variant/20 bg-surface-container px-3 text-sm text-on-surface">
            <option value="">Todas</option>
            <option value="Primera">Primera</option>
            <option value="Tercera">Tercera</option>
            <option value="Senior">Senior</option>
          </select>
        </div>
        <p id="categoriaSeleccionada" class="text-sm text-on-surface-variant">Mostrando: Todas</p>
      </div>

      <div class="mb-6 sm:mb-8 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <div class="flex flex-col gap-1 sm:gap-2 rounded-xl border border-outline-variant/10 bg-surface-container-high p-3 sm:p-4 md:p-6">
          <span class="text-xs sm:text-sm font-medium text-on-surface-variant">Total de Equipos</span>
          <p id="totalEquipos" class="text-2xl sm:text-3xl font-bold font-headline">0</p>
        </div>
        <div class="flex flex-col gap-1 sm:gap-2 rounded-xl border border-outline-variant/10 bg-surface-container-high p-3 sm:p-4 md:p-6">
          <span class="text-xs sm:text-sm font-medium text-on-surface-variant">Jugadores Registrados</span>
          <p id="totalJugadores" class="text-2xl sm:text-3xl font-bold font-headline">0</p>
        </div>
        <div class="flex flex-col gap-1 sm:gap-2 rounded-xl border border-outline-variant/10 bg-surface-container-high p-3 sm:p-4 md:p-6">
          <span class="text-xs sm:text-sm font-medium text-on-surface-variant">Próximos Partidos</span>
          <p id="proximosPartidos" class="text-2xl sm:text-3xl font-bold font-headline">0</p>
        </div>
        <div class="flex flex-col gap-1 sm:gap-2 rounded-xl border border-outline-variant/10 bg-surface-container-high p-3 sm:p-4 md:p-6">
          <span class="text-xs sm:text-sm font-medium text-on-surface-variant">Últimos Resultados</span>
          <p id="ultimosResultados" class="text-2xl sm:text-3xl font-bold font-headline">0</p>
        </div>
      </div>

      <div class="mb-6 sm:mb-8 rounded-xl border border-outline-variant/10 bg-surface-container-high p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-primary">emoji_events</span>
              <h2 class="text-lg font-bold font-headline">Prode Deportivo</h2>
            </div>
            <p class="text-sm text-on-surface-variant mt-1">Activa o desactiva el acceso público al Prode. El botón en la home se muestra u oculta automáticamente.</p>
          </div>
          <div class="flex items-center gap-4">
            <span id="prodeStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold">
              <span id="prodeStatusDot" class="w-2 h-2 rounded-full inline-block"></span>
              <span id="prodeStatusText"></span>
            </span>
            <button id="toggleProde" class="h-10 rounded-lg px-5 text-sm font-semibold transition-colors"></button>
          </div>
        </div>
      </div>

      <div class="mb-6 sm:mb-8 rounded-xl border border-outline-variant/10 bg-surface-container-high p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h2 class="text-lg font-bold font-headline">Widget Marcadores en Vivo</h2>
            <p class="text-sm text-on-surface-variant mt-1">Controla la visibilidad de la sección de marcadores en la home.</p>
          </div>
          <div class="flex items-center gap-4">
            <span id="liveWidgetStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold">
              <span id="liveWidgetStatusDot" class="w-2 h-2 rounded-full inline-block"></span>
              <span id="liveWidgetStatusText"></span>
            </span>
            <button id="toggleLiveWidget" class="h-10 rounded-lg px-5 text-sm font-semibold transition-colors"></button>
          </div>
        </div>
        <div id="liveWidgetModeSection" class="hidden border-t border-outline-variant/10 mt-4 pt-4">
          <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-3">Modo de partidos</p>
          <div class="flex flex-wrap gap-5 mb-4">
            <label class="flex items-center gap-2 cursor-pointer text-sm">
              <input type="radio" name="liveWidgetMode" id="liveWidgetModeAuto" value="auto" class="accent-primary"/>
              <span>Automático <span class="text-on-surface-variant text-xs">(partidos del día en horario)</span></span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm">
              <input type="radio" name="liveWidgetMode" id="liveWidgetModeManual" value="manual" class="accent-primary"/>
              <span>Selección manual</span>
            </label>
          </div>
          <div id="liveWidgetMatchListSection" class="hidden">
            <p class="text-xs text-on-surface-variant mb-3">Marcá los partidos que querés mostrar en el widget:</p>
            <div id="liveWidgetMatchCheckboxes" class="space-y-1 max-h-52 overflow-y-auto pr-1 mb-3"></div>
            <div class="flex flex-wrap gap-2 mt-1">
              <button id="guardarLiveWidgetPartidos" class="h-9 rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:bg-primary/90 transition-colors">Guardar selección</button>
              <button id="limpiarLiveWidgetPartidos" class="h-9 rounded-lg border border-outline-variant/20 px-4 text-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-colors">Volver a automático</button>
            </div>
          </div>
        </div>
      </div>

      <div class="mb-6 sm:mb-8 rounded-xl border border-outline-variant/10 bg-surface-container-high p-4 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
          <div>
            <h2 class="text-lg font-bold font-headline">Partido Siguiente en Home</h2>
            <p id="dashboardProximoModo" class="text-sm text-on-surface-variant mt-1">Sin configuración cargada.</p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[180px_minmax(0,1fr)_minmax(0,320px)_auto_auto] gap-3 w-full lg:w-auto lg:min-w-[860px]">
            <div>
              <label class="block text-xs text-on-surface-variant mb-1">Torneo</label>
              <select id="dashboardProximoTorneo" class="w-full h-10 rounded-lg border border-outline-variant/20 bg-surface-container px-3 text-sm text-on-surface">
                <option value="Apertura">Apertura</option>
                <option value="Clausura">Clausura</option>
                <option value="Anual">Anual</option>
              </select>
            </div>
            <div>
              <label class="block text-xs text-on-surface-variant mb-1">Partido destacado</label>
              <select id="dashboardProximoSelect" class="w-full h-10 rounded-lg border border-outline-variant/20 bg-surface-container px-3 text-sm text-on-surface"></select>
            </div>
            <div>
              <label class="block text-xs text-on-surface-variant mb-1">Link de YouTube</label>
              <input id="dashboardProximoBroadcast" type="url" placeholder="https://youtube.com/live/..." class="w-full h-10 rounded-lg border border-outline-variant/20 bg-surface-container px-3 text-sm text-on-surface"/>
            </div>
            <button id="guardarProximoHome" class="h-10 rounded-lg bg-primary px-4 text-sm font-semibold text-white hover:bg-primary/90 transition-colors">Guardar</button>
            <button id="limpiarProximoHome" class="h-10 rounded-lg border border-outline-variant/20 px-4 text-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-colors">Volver a automático</button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:gap-8 lg:grid-cols-2">
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
          <div class="flex items-center justify-between border-b border-outline-variant/10 px-3 sm:px-6 py-3 sm:py-4">
            <h3 class="text-base sm:text-lg font-bold font-headline">Próximos Partidos</h3>
            <a href="/partidos" class="text-xs sm:text-sm font-semibold text-primary hover:underline">Ver Calendario</a>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm min-w-[480px]">
              <thead class="bg-surface-container text-xs uppercase text-on-surface-variant">
                <tr>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 font-semibold">Fecha/Hora</th>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 font-semibold">Encuentro</th>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 font-semibold">Estadio</th>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 font-semibold">Acción</th>
                </tr>
              </thead>
              <tbody id="tablaProximos" class="divide-y divide-outline-variant/10"></tbody>
            </table>
          </div>
        </div>

        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
          <div class="flex items-center justify-between border-b border-outline-variant/10 px-3 sm:px-6 py-3 sm:py-4">
            <h3 class="text-base sm:text-lg font-bold font-headline">Últimos Resultados</h3>
            <a href="/estadisticas" class="text-xs sm:text-sm font-semibold text-primary hover:underline">Ver Todos</a>
          </div>
          <div id="ultimosResultadosLista" class="p-3 sm:p-6 space-y-3 sm:space-y-4"></div>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="/frontend/app.js"></script>
<script>
const dashboardProximoTorneo = document.getElementById("dashboardProximoTorneo");
const dashboardProximoSelect = document.getElementById("dashboardProximoSelect");
const dashboardProximoBroadcast = document.getElementById("dashboardProximoBroadcast");
const dashboardProximoModo = document.getElementById("dashboardProximoModo");
const guardarProximoHome = document.getElementById("guardarProximoHome");
const limpiarProximoHome = document.getElementById("limpiarProximoHome");

let dashboardPartidosCache = [];

const getDashboardManagedCategoria = (categoria = "", partidoId = "") => {
  if (categoria) return categoria;
  const selected = dashboardPartidosCache.find((p) => String(p.id) === String(partidoId));
  return selected?.categoria || "Primera";
};

const formatDashboardPartidoOption = (partido) => {
  const fecha = [partido.fecha, partido.hora].filter(Boolean).join(" ");
  return `${fecha} · ${partido.local} vs ${partido.visitante}`;
};

const getDashboardFilteredMatches = (categoria = "") => {
  const torneo = dashboardProximoTorneo.value;
  const managedCategoria = getDashboardManagedCategoria(categoria);
  return dashboardPartidosCache
    .filter((p) => p.categoria === managedCategoria && p.torneo === torneo && p.estado !== "jugado")
    .sort((a, b) => new Date(`${a.fecha}T${a.hora || "00:00"}`) - new Date(`${b.fecha}T${b.hora || "00:00"}`));
};

const syncDashboardSelectedMatch = (matches, partidoId) => {
  const selected = matches.find((p) => String(p.id) === String(partidoId)) || null;
  dashboardProximoBroadcast.value = selected?.broadcast || "";
};

const loadDashboardNextHome = async (categoria = "") => {
  const managedCategoria = getDashboardManagedCategoria(categoria);
  const matches = getDashboardFilteredMatches(managedCategoria);
  const torneo = dashboardProximoTorneo.value;
  dashboardProximoSelect.innerHTML = "";

  if (!matches.length) {
    dashboardProximoSelect.innerHTML = '<option value="">No hay partidos disponibles</option>';
    dashboardProximoBroadcast.value = "";
    dashboardProximoModo.textContent = `No hay partidos pendientes o postergados para ${managedCategoria} - ${torneo}.`;
    return;
  }

  matches.forEach((partido) => {
    const option = document.createElement("option");
    option.value = partido.id;
    option.textContent = formatDashboardPartidoOption(partido);
    dashboardProximoSelect.appendChild(option);
  });

  try {
    const query = new URLSearchParams({ torneo, categoria: managedCategoria });
    const response = await api.get(`/partidos/proximo?${query.toString()}`);
    const partido = response?.partido || matches[0];
    dashboardProximoSelect.value = partido ? String(partido.id) : String(matches[0].id);
    syncDashboardSelectedMatch(matches, dashboardProximoSelect.value);
    dashboardProximoModo.textContent = response?.modo === "manual"
      ? `Modo actual para ${managedCategoria}: selección manual activa en el home.`
      : `Modo actual para ${managedCategoria}: selección automática por fecha y hora.`;
  } catch (error) {
    dashboardProximoSelect.value = String(matches[0].id);
    syncDashboardSelectedMatch(matches, dashboardProximoSelect.value);
    dashboardProximoModo.textContent = `No se pudo leer la configuración actual del home para ${managedCategoria}.`;
  }
};

const cargarDashboard = async (categoria = "") => {
  const rutaEquipos = categoria ? `/equipos?categoria=${encodeURIComponent(categoria)}` : "/equipos";
  try {
    const equipos = await api.get(rutaEquipos).catch(() => []);
    const jugadores = await api.get("/jugadores").catch(() => []);
    const partidos = await api.get("/partidos").catch(() => []);
    dashboardPartidosCache = partidos;

    document.getElementById("totalEquipos").textContent = equipos.length;
    document.getElementById("categoriaSeleccionada").textContent = `Mostrando: ${categoria || "Todas"}`;
    document.getElementById("totalJugadores").textContent = jugadores.length;
    document.getElementById("proximosPartidos").textContent = partidos.filter((p) => p.estado !== "jugado").length;
    document.getElementById("ultimosResultados").textContent = partidos.filter((p) => p.estado === "jugado").length;

    const tbody = document.getElementById("tablaProximos");
    tbody.innerHTML = partidos.filter((p) => p.estado !== "jugado").slice(0, 6).map((p) => `
      <tr class="border-b border-outline-variant/10">
        <td class="px-3 sm:px-6 py-3">${p.fecha} ${p.hora}</td>
        <td class="px-3 sm:px-6 py-3 font-medium">${p.local} vs ${p.visitante}</td>
        <td class="px-3 sm:px-6 py-3 text-on-surface-variant">${p.estadio}</td>
        <td class="px-3 sm:px-6 py-3"><span class="px-2 py-1 text-xs font-semibold text-primary bg-primary/10 rounded-lg">Ver</span></td>
      </tr>
    `).join("");

    const ultimos = document.getElementById("ultimosResultadosLista");
    ultimos.innerHTML = partidos.filter((p) => p.estado === "jugado").slice(-5).reverse().map((p) => `
      <div class="rounded-xl bg-surface-container p-3 border border-outline-variant/10">
        <strong class="font-headline">${p.local} ${p.golesLocal} - ${p.golesVisitante} ${p.visitante}</strong>
        <p class="text-xs text-on-surface-variant mt-1">${p.fecha} · ${p.estadio}</p>
      </div>
    `).join("");

    await loadDashboardNextHome(categoria);
    renderLiveWidgetCheckboxes();
  } catch (err) {
    console.error("Error cargando dashboard:", err);
  }
};

const selectCategoria = document.getElementById("filtroCategoria");
selectCategoria.addEventListener("change", () => cargarDashboard(selectCategoria.value));
dashboardProximoTorneo.addEventListener("change", () => cargarDashboard(selectCategoria.value));
dashboardProximoSelect.addEventListener("change", () => {
  syncDashboardSelectedMatch(getDashboardFilteredMatches(getDashboardManagedCategoria(selectCategoria.value, dashboardProximoSelect.value)), dashboardProximoSelect.value);
});
guardarProximoHome.addEventListener("click", async () => {
  const partidoId = Number(dashboardProximoSelect.value);
  const categoria = getDashboardManagedCategoria(selectCategoria.value, partidoId);
  const torneo = dashboardProximoTorneo.value;
  if (!partidoId) return;

  try {
    await api.put("/partidos/proximo", { partidoId, categoria, torneo });
    await api.put(`/partidos/${partidoId}`, { broadcast: dashboardProximoBroadcast.value.trim() });
    await cargarDashboard(categoria);
  } catch (error) {
    console.error("No se pudo guardar el próximo partido manual", error);
  }
});
limpiarProximoHome.addEventListener("click", async () => {
  const categoria = getDashboardManagedCategoria(selectCategoria.value, dashboardProximoSelect.value);
  const torneo = dashboardProximoTorneo.value;
  try {
    const query = new URLSearchParams({ torneo, categoria });
    await api.del(`/partidos/proximo?${query.toString()}`);
    await cargarDashboard(categoria);
  } catch (error) {
    console.error("No se pudo limpiar el próximo partido manual", error);
  }
});
cargarDashboard();

// === Widget Marcadores en Vivo ===
let liveWidgetEnabled = true;
let liveWidgetOverrideIds = [];

function renderLiveWidgetCheckboxes() {
  const container = document.getElementById("liveWidgetMatchCheckboxes");
  if (!container) return;
  const candidates = dashboardPartidosCache
    .filter((p) => p.estado !== "jugado")
    .sort((a, b) => new Date(`${a.fecha}T${a.hora || "00:00"}`) - new Date(`${b.fecha}T${b.hora || "00:00"}`))
    .slice(0, 40);
  if (!candidates.length) {
    container.innerHTML = '<p class="text-xs text-on-surface-variant py-2">No hay partidos disponibles.</p>';
    return;
  }
  container.innerHTML = candidates.map((p) => {
    const checked = liveWidgetOverrideIds.includes(p.id) ? "checked" : "";
    const hora = p.hora ? p.hora.slice(0, 5) : "";
    return `<label class="flex items-center gap-2.5 cursor-pointer text-sm rounded-lg px-2 py-1.5 hover:bg-surface-container transition-colors">
      <input type="checkbox" value="${p.id}" ${checked} class="w-4 h-4 shrink-0 accent-primary"/>
      <span>${p.fecha}${hora ? " " + hora : ""} · <strong>${p.local} vs ${p.visitante}</strong> <span class="text-on-surface-variant text-xs">${p.categoria} · ${p.torneo}</span></span>
    </label>`;
  }).join("");
}

function applyLiveWidgetUI(enabled, overrideIds) {
  liveWidgetEnabled = enabled;
  liveWidgetOverrideIds = Array.isArray(overrideIds) ? overrideIds : [];
  const badge = document.getElementById("liveWidgetStatusBadge");
  const dot = document.getElementById("liveWidgetStatusDot");
  const text = document.getElementById("liveWidgetStatusText");
  const btn = document.getElementById("toggleLiveWidget");
  const modeSection = document.getElementById("liveWidgetModeSection");
  const matchListSection = document.getElementById("liveWidgetMatchListSection");

  if (enabled) {
    badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-500/15 text-green-400 border border-green-500/30";
    dot.className = "w-2 h-2 rounded-full inline-block bg-green-400";
    text.textContent = "Activo";
    btn.className = "h-10 rounded-lg px-5 text-sm font-semibold transition-colors bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500/25";
    btn.textContent = "Apagar widget";
    modeSection.classList.remove("hidden");
  } else {
    badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/5 text-gray-400 border border-white/10";
    dot.className = "w-2 h-2 rounded-full inline-block bg-gray-500";
    text.textContent = "Apagado";
    btn.className = "h-10 rounded-lg px-5 text-sm font-semibold transition-colors bg-green-500/15 border border-green-500/30 text-green-400 hover:bg-green-500/25";
    btn.textContent = "Activar widget";
    modeSection.classList.add("hidden");
  }

  const isManual = liveWidgetOverrideIds.length > 0;
  document.getElementById("liveWidgetModeAuto").checked = !isManual;
  document.getElementById("liveWidgetModeManual").checked = isManual;
  matchListSection.classList.toggle("hidden", !isManual);
  if (isManual) renderLiveWidgetCheckboxes();
}

async function loadLiveWidgetConfig() {
  try {
    const data = await api.get("/partidos/live-scores-config");
    applyLiveWidgetUI(data.enabled !== false, data.overrideIds || []);
  } catch (e) {
    console.error("No se pudo leer el estado del widget", e);
  }
}

document.getElementById("toggleLiveWidget").addEventListener("click", async () => {
  try {
    const data = await api.put("/partidos/live-scores-config", { enabled: !liveWidgetEnabled });
    applyLiveWidgetUI(data.enabled !== false, data.overrideIds || []);
  } catch (e) {
    console.error("No se pudo cambiar el estado del widget", e);
  }
});

document.querySelectorAll('input[name="liveWidgetMode"]').forEach((radio) => {
  radio.addEventListener("change", () => {
    const isManual = document.getElementById("liveWidgetModeManual").checked;
    document.getElementById("liveWidgetMatchListSection").classList.toggle("hidden", !isManual);
    if (isManual) renderLiveWidgetCheckboxes();
  });
});

document.getElementById("guardarLiveWidgetPartidos").addEventListener("click", async () => {
  const checked = document.querySelectorAll('#liveWidgetMatchCheckboxes input[type="checkbox"]:checked');
  const ids = Array.from(checked).map((cb) => Number(cb.value));
  try {
    const data = await api.put("/partidos/live-scores-config", { overrideIds: ids });
    applyLiveWidgetUI(data.enabled !== false, data.overrideIds || []);
  } catch (e) {
    console.error("Error guardando selección de partidos", e);
  }
});

document.getElementById("limpiarLiveWidgetPartidos").addEventListener("click", async () => {
  try {
    const data = await api.put("/partidos/live-scores-config", { overrideIds: [] });
    applyLiveWidgetUI(data.enabled !== false, data.overrideIds || []);
  } catch (e) {
    console.error("Error limpiando selección de partidos", e);
  }
});

loadLiveWidgetConfig();

// === Prode toggle ===
let prodeEnabled = false;

function applyProdeUI(enabled) {
  prodeEnabled = enabled;
  const badge = document.getElementById("prodeStatusBadge");
  const dot   = document.getElementById("prodeStatusDot");
  const text  = document.getElementById("prodeStatusText");
  const btn   = document.getElementById("toggleProde");
  if (enabled) {
    badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-green-500/15 text-green-400 border border-green-500/30";
    dot.className   = "w-2 h-2 rounded-full inline-block bg-green-400";
    text.textContent = "Activo";
    btn.className   = "h-10 rounded-lg px-5 text-sm font-semibold transition-colors bg-red-500/15 border border-red-500/30 text-red-400 hover:bg-red-500/25";
    btn.textContent = "Desactivar";
  } else {
    badge.className = "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/5 text-gray-400 border border-white/10";
    dot.className   = "w-2 h-2 rounded-full inline-block bg-gray-500";
    text.textContent = "Inactivo";
    btn.className   = "h-10 rounded-lg px-5 text-sm font-semibold transition-colors bg-green-500/15 border border-green-500/30 text-green-400 hover:bg-green-500/25";
    btn.textContent = "Activar";
  }
}

async function loadProdeConfig() {
  try {
    const data = await api.get("/prode/config");
    applyProdeUI(data.enabled !== false);
  } catch (e) {
    console.error("No se pudo leer el estado del prode", e);
  }
}

document.getElementById("toggleProde").addEventListener("click", async () => {
  try {
    const data = await api.put("/prode/config", { enabled: !prodeEnabled });
    applyProdeUI(data.enabled !== false);
  } catch (e) {
    console.error("No se pudo cambiar el estado del prode", e);
  }
});

loadProdeConfig();
</script>
</body>
</html>
