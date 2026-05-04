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
</script>
</body>
</html>
