<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Partidos — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    input, textarea { background-color: transparent !important; color: #f3f4f6; }
    select { background-color: #1a191b !important; color: #ffffff !important; }
    input::placeholder, textarea::placeholder { color: #adaaab; }
    .jornada-btn { transition: all 0.2s; }
    .jornada-btn.active { background-color: #8c52ff; color: white; box-shadow: 0 0 0 2px rgba(140,82,255,0.4); }
    .jornada-btn:not(.active) { background-color: rgba(140, 82, 255, 0.15); color: #adaaab; }
    .jornada-completa::after { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #22c55e; margin-left: 6px; }
    .jornada-parcial::after { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #eab308; margin-left: 6px; }
    .jornada-vacia::after { content: ''; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #767576; margin-left: 6px; }
    .toast { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 100; padding: 0.75rem 1.5rem; border-radius: 0.5rem; color: white; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s ease; pointer-events: none; }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast-success { background-color: #16a34a; }
    .toast-error { background-color: #dc2626; }
    .logo-team { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: rgba(255,255,255,0.1); flex-shrink: 0; }
    .logo-team-sm { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; background: rgba(255,255,255,0.1); flex-shrink: 0; }
    .logo-team-lg { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; background: rgba(255,255,255,0.1); flex-shrink: 0; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body min-h-screen">
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto">
      <div class="mb-4 sm:mb-6">
        <h1 class="text-xl sm:text-2xl md:text-4xl font-black leading-tight tracking-[-0.033em] font-headline">Gestión de Partidos</h1>
        <p class="text-on-surface-variant mt-1 text-sm sm:text-base">Visualización, creación y carga de resultados</p>
      </div>

      <!-- Filtros categoría / torneo / estado -->
      <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 grid grid-cols-1 sm:grid-cols-[auto_1fr_auto_1fr_auto_1fr] gap-2 sm:gap-3 items-center">
        <label class="text-sm font-semibold">Categoría:</label>
        <select id="categoriaFiltro" class="border border-outline-variant/20 rounded-lg p-2 w-full sm:w-auto">
          <option value="" selected disabled>Seleccionar categoría</option>
          <option value="Primera">Primera</option>
          <option value="Tercera">Tercera</option>
          <option value="Senior">Senior</option>
        </select>
        <label class="text-sm font-semibold">Torneo:</label>
        <select id="torneoFiltro" class="border border-outline-variant/20 rounded-lg p-2 w-full sm:w-auto">
          <option value="" selected disabled>Seleccionar torneo</option>
          <option value="Apertura">Apertura</option>
          <option value="Clausura">Clausura</option>
          <option value="Anual">Anual</option>
        </select>
        <label class="text-sm font-semibold">Estado:</label>
        <select id="estadoFiltro" class="border border-outline-variant/20 rounded-lg p-2 w-full sm:w-auto">
          <option value="">Todos</option>
          <option value="pendiente">Pendiente</option>
          <option value="en_curso">En curso</option>
          <option value="postergado">Postergado</option>
          <option value="jugado">Jugado</option>
        </select>
      </div>

      <!-- Contenido principal: oculto hasta seleccionar filtros -->
      <div id="mainContent" class="hidden">
        <!-- Panel de jornadas -->
        <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-4 mb-4 sm:mb-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold font-headline">Jornadas</h2>
            <button id="toggleNuevoPartido" class="flex items-center gap-1 bg-primary hover:bg-primary-dim text-on-primary px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
              <span class="material-symbols-outlined text-[18px]">add</span> Nuevo Partido
            </button>
          </div>
          <div id="jornadasContainer" class="flex flex-wrap gap-2"></div>
        </div>

        <!-- Panel crear partido (colapsable) -->
        <div id="nuevoPartidoPanel" class="hidden mb-4 sm:mb-6">
          <div class="bg-surface-container-high border border-primary/20 rounded-xl p-4">
            <h3 class="text-sm font-bold text-primary mb-3 flex items-center gap-1 font-headline">
              <span class="material-symbols-outlined text-[18px]">edit_calendar</span> Crear Partido
            </h3>
            <form id="partidoForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs text-on-surface-variant mb-1">Fecha *</label>
                <input id="partidoFecha" type="date" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary" required/>
              </div>
              <div>
                <label class="block text-xs text-on-surface-variant mb-1">Hora *</label>
                <input id="partidoHora" type="time" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary" required/>
              </div>
              <div>
                <label class="block text-xs text-on-surface-variant mb-1">Estadio</label>
                <input id="partidoEstadio" type="text" placeholder="Se completa con el estadio del local" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary"/>
                <p class="mt-1 text-[11px] text-on-surface-variant">Se completa automáticamente con el estadio del local, pero podés editarlo.</p>
              </div>
              <div>
                <label class="block text-xs text-on-surface-variant mb-1">Equipo Local *</label>
                <select id="partidoLocal" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary">
                  <option value="" disabled selected>Seleccionar</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-on-surface-variant mb-1">Equipo Visitante *</label>
                <select id="partidoVisitante" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary">
                  <option value="" disabled selected>Seleccionar</option>
                </select>
              </div>
              <div>
                <label class="block text-xs text-on-surface-variant mb-1">Jornada</label>
                <input id="partidoJornada" type="number" min="1" placeholder="Nº de fecha" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary"/>
              </div>
              <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-xs text-on-surface-variant mb-1">Link de transmisión</label>
                <input id="partidoBroadcast" type="url" placeholder="https://youtube.com/live/..." class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm focus:ring-2 focus:ring-primary"/>
              </div>
              <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-4 py-2 text-sm font-semibold transition-colors flex items-center justify-center gap-1">
                  <span class="material-symbols-outlined text-[16px]">save</span> Crear
                </button>
                <button type="button" id="cancelNuevoPartido" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm transition-colors">Cancelar</button>
              </div>
            </form>
          </div>
        </div>

        <!-- Cards de partidos -->
        <div id="partidosContainer" class="space-y-3"></div>
        <div id="noPartidos" class="hidden text-center py-8 text-on-surface-variant">
          <span class="material-symbols-outlined text-4xl mb-2 block">sports_soccer</span>
          No hay partidos con los filtros seleccionados.
        </div>
      </div>

      <!-- Placeholder sin filtros -->
      <div id="noFilters" class="text-center py-8 sm:py-12 md:py-16 text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl sm:text-5xl mb-2 sm:mb-3 block">filter_alt</span>
        <p class="text-base sm:text-lg font-semibold">Seleccioná categoría y torneo para comenzar</p>
      </div>
    </div>
  </main>
</div>

<!-- Modal: Cargar resultado -->
<div id="partidoResultModal" class="hidden fixed inset-0 bg-black/50 flex items-start justify-center p-2 sm:p-4 z-50 overflow-y-auto">
  <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl w-full max-w-full sm:max-w-lg p-4 sm:p-6 my-4 sm:my-8">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg sm:text-xl font-bold font-headline">Cargar Resultado</h2>
      <button id="closePartidoResultModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <input type="hidden" id="resultadoPartidoId"/>

    <!-- Score -->
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-6 items-center text-center">
      <div>
        <img id="resLocalEscudo" class="logo-team-lg mx-auto mb-2" src="" alt="" />
        <p class="font-bold text-sm sm:text-base truncate" id="resLocalEquipo">—</p>
        <input id="resGolesLocal" type="number" min="0" value="0" class="w-16 sm:w-20 mx-auto border border-outline-variant/20 rounded-lg p-2 text-center text-2xl sm:text-3xl font-bold mt-2"/>
      </div>
      <div class="text-2xl sm:text-3xl font-bold text-on-surface-variant">vs</div>
      <div>
        <img id="resVisitanteEscudo" class="logo-team-lg mx-auto mb-2" src="" alt="" />
        <p class="font-bold text-sm sm:text-base truncate" id="resVisitanteEquipo">—</p>
        <input id="resGolesVisitante" type="number" min="0" value="0" class="w-16 sm:w-20 mx-auto border border-outline-variant/20 rounded-lg p-2 text-center text-2xl sm:text-3xl font-bold mt-2"/>
      </div>
    </div>

    <!-- Tarjetas -->
    <div class="grid grid-cols-2 gap-3 mb-6">
      <div>
        <label class="block text-xs text-on-surface-variant mb-1 flex items-center gap-1">🟨 Amarillas</label>
        <input id="resAmarillas" type="number" min="0" value="0" class="w-full border border-outline-variant/20 rounded-lg p-2 text-center text-lg font-bold"/>
      </div>
      <div>
        <label class="block text-xs text-on-surface-variant mb-1 flex items-center gap-1">🟥 Rojas</label>
        <input id="resRojas" type="number" min="0" value="0" class="w-full border border-outline-variant/20 rounded-lg p-2 text-center text-lg font-bold"/>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t border-outline-variant/10">
      <button id="cancelResult" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm transition-colors">Cancelar</button>
      <button id="saveResult" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-1 text-sm">
        <span class="material-symbols-outlined text-[18px]">save</span> Guardar Resultado
      </button>
    </div>
  </div>
</div>

<!-- Modal: Editar programación -->
<div id="partidoEditModal" class="hidden fixed inset-0 bg-black/50 flex items-start justify-center p-2 sm:p-4 z-50 overflow-y-auto">
  <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl w-full max-w-full sm:max-w-lg p-4 sm:p-6 my-4 sm:my-8">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-lg sm:text-xl font-bold font-headline">Editar Programación</h2>
        <p id="editPartidoLabel" class="text-xs text-on-surface-variant mt-1">—</p>
      </div>
      <button id="closePartidoEditModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <input type="hidden" id="editPartidoId"/>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
      <div>
        <label class="block text-xs text-on-surface-variant mb-1">Fecha</label>
        <input id="editPartidoFecha" type="date" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm"/>
      </div>
      <div>
        <label class="block text-xs text-on-surface-variant mb-1">Hora</label>
        <input id="editPartidoHora" type="time" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm"/>
      </div>
      <div>
        <label class="block text-xs text-on-surface-variant mb-1">Estado</label>
        <select id="editPartidoEstado" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm">
          <option value="pendiente">Pendiente</option>
          <option value="en_curso">En curso</option>
          <option value="postergado">Postergado</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-on-surface-variant mb-1">Estadio</label>
        <input id="editPartidoEstadio" type="text" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm" placeholder="Estadio del partido"/>
      </div>
      <div class="sm:col-span-2">
        <label class="block text-xs text-on-surface-variant mb-1">Link de transmisión</label>
        <input id="editPartidoBroadcast" type="url" class="w-full border border-outline-variant/20 rounded-lg p-2 text-sm" placeholder="https://youtube.com/live/..."/>
      </div>
    </div>
    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-4 border-t border-outline-variant/10">
      <button id="cancelPartidoEdit" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm transition-colors">Cancelar</button>
      <button id="savePartidoEdit" class="px-6 py-2 bg-primary hover:bg-primary-dim text-on-primary rounded-lg font-semibold transition-colors text-sm">Guardar cambios</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<script src="/frontend/app.js"></script>
</body>
</html>
