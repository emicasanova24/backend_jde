<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Carga de Datos — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>
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
  </style>
</head>
<body class="bg-surface text-on-surface font-body min-h-screen">
<?php require $root . '/views/partials/gtm-body.php'; ?>
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto">
      <div class="mb-4 sm:mb-6">
        <h1 class="text-xl sm:text-2xl md:text-4xl font-black leading-tight tracking-[-0.033em] font-headline">Carga de Datos</h1>
        <p class="text-on-surface-variant mt-1 text-sm sm:text-base">Gestión de jornadas, partidos y resultados</p>
      </div>
      <div class="ml-auto">
        <button onclick="abrirProdeModal()"
          class="mb-4 flex items-center gap-2 px-3 py-2 rounded-xl bg-primary/15 border border-primary/30 text-primary hover:bg-primary/25 transition-colors text-sm font-semibold">
          <span class="material-symbols-outlined text-[18px]">emoji_events</span>
          Prode
        </button>
      </div>

      <!-- Filtros categoría / torneo -->
      <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-3 sm:p-4 mb-4 sm:mb-6 grid grid-cols-1 sm:grid-cols-[auto_1fr_auto_1fr] gap-2 sm:gap-3 items-center">
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
      </div>

      <!-- Contenido principal: oculto hasta seleccionar filtros -->
      <div id="mainContent" class="hidden">
          <div class="bg-white rounded-2xl shadow-md p-5 space-y-4 border border-slate-200 mb-6">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h2 class="text-lg font-semibold text-slate-900">Exportar semanario HTML</h2>
                <p class="text-sm text-slate-600">Genera un HTML standalone con los datos cargados hasta este momento para el torneo seleccionado.</p>
              </div>
              <div class="flex flex-col gap-2 sm:flex-row">
                <button id="generarSemanarioBtn" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-medium hover:bg-slate-800 transition-colors">Generar HTML</button>
                <button id="copiarSemanarioBtn" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>Copiar HTML</button>
              </div>
            </div>
            <p id="semanarioHtmlMeta" class="hidden text-sm text-slate-500"></p>
            <textarea id="semanarioHtmlOutput" class="w-full min-h-[320px] rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-xs text-slate-800 font-mono" spellcheck="false" placeholder="Acá se va a mostrar el HTML final listo para enviar."></textarea>
          </div>

        <!-- Panel de jornadas -->
        <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-4 mb-6">
          <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold font-headline">Jornadas</h2>
            <button id="nuevaJornadaBtn" class="flex items-center gap-1 bg-primary hover:bg-primary-dim text-on-primary px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
              <span class="material-symbols-outlined text-[18px]">add</span> Nueva Fecha
            </button>
          </div>
          <div id="jornadasContainer" class="flex flex-wrap gap-2"></div>
        </div>

        <!-- Panel de partidos de la jornada -->
        <div id="jornadaDetail" class="hidden">
          <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-bold font-headline" id="jornadaTitle">Fecha 1</h2>
              <button id="agregarPartidoBtn" class="flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors">
                <span class="material-symbols-outlined text-[18px]">add</span> Agregar Partido
              </button>
            </div>

            <!-- Fixture Builder -->
            <div id="fixtureBuilder" class="hidden mb-4">
              <div class="border border-primary/20 rounded-xl p-4 bg-primary/5">
                <h3 class="text-sm font-bold text-primary mb-3 flex items-center gap-1 font-headline">
                  <span class="material-symbols-outlined text-[18px]">edit_calendar</span> Armar Fixture
                </h3>
                <div id="fixtureRows" class="space-y-3"></div>
                <div class="flex flex-wrap gap-2 mt-4">
                  <button id="addFixtureRowBtn" class="flex items-center gap-1 text-sm text-primary hover:text-primary-dim font-semibold">
                    <span class="material-symbols-outlined text-[16px]">add_circle</span> Agregar otro partido
                  </button>
                  <div class="flex-1"></div>
                  <button id="cancelFixtureBtn" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm transition-colors">Cancelar</button>
                  <button id="saveFixtureBtn" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-sm flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">save</span> Guardar Fixture
                  </button>
                </div>
              </div>
            </div>

            <!-- Existing matches list -->
            <div id="partidosContainer" class="space-y-3"></div>
            <div id="noPartidos" class="hidden text-center py-8 text-on-surface-variant">
              <span class="material-symbols-outlined text-4xl mb-2 block">sports_soccer</span>
              No hay partidos en esta fecha. Usá "Agregar Partido" para armar el fixture.
            </div>

            <!-- Export flyer buttons (visible when jornada completa) -->
            <div id="flyerExportBar" class="hidden mt-4 pt-4 border-t border-outline-variant/10">
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Fixture -->
                <div id="flyerFixtureSection" class="hidden bg-white/5 border border-white/10 rounded-lg p-3">
                  <p class="text-xs text-on-surface-variant mb-2 font-semibold uppercase tracking-wide">Fixture</p>
                  <div class="flex gap-2">
                    <button id="btnFixtureStory" class="flex-1 flex items-center justify-center gap-1.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                      <span class="material-symbols-outlined text-[16px]">phone_iphone</span> Story
                    </button>
                    <button id="btnFixtureFeed" class="flex-1 flex items-center justify-center gap-1.5 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                      <span class="material-symbols-outlined text-[16px]">crop_square</span> Feed
                    </button>
                  </div>
                </div>
                <!-- Resultados -->
                <div id="flyerResultsSection" class="hidden bg-white/5 border border-white/10 rounded-lg p-3">
                  <p class="text-xs text-on-surface-variant mb-2 font-semibold uppercase tracking-wide">Resultados</p>
                  <div class="flex gap-2">
                    <button id="btnResultStory" class="flex-1 flex items-center justify-center gap-1.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                      <span class="material-symbols-outlined text-[16px]">phone_iphone</span> Story
                    </button>
                    <button id="btnResultFeed" class="flex-1 flex items-center justify-center gap-1.5 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                      <span class="material-symbols-outlined text-[16px]">crop_square</span> Feed
                    </button>
                  </div>
                </div>
                <!-- Posiciones -->
                <div id="flyerPositionsSection" class="hidden bg-white/5 border border-white/10 rounded-lg p-3">
                  <p class="text-xs text-on-surface-variant mb-2 font-semibold uppercase tracking-wide">Posiciones</p>
                  <div class="flex gap-2">
                    <button id="btnTablaStory" class="flex-1 flex items-center justify-center gap-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                      <span class="material-symbols-outlined text-[16px]">phone_iphone</span> Story
                    </button>
                    <button id="btnTablaFeed" class="flex-1 flex items-center justify-center gap-1.5 bg-gradient-to-r from-teal-600 to-cyan-600 hover:from-teal-500 hover:to-cyan-500 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                      <span class="material-symbols-outlined text-[16px]">crop_square</span> Feed
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div id="noFilters" class="text-center py-8 sm:py-12 md:py-16 text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl sm:text-5xl mb-2 sm:mb-3 block">filter_alt</span>
        <p class="text-base sm:text-lg font-semibold">Seleccioná categoría y torneo para comenzar</p>
      </div>
    </div>
  </main>
</div>

<!-- Modal: Cargar resultado -->
<div id="resultModal" class="hidden fixed inset-0 bg-black/50 flex items-start justify-center p-2 sm:p-4 z-50 overflow-y-auto">
  <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl w-full max-w-full sm:max-w-lg md:max-w-2xl p-3 sm:p-4 md:p-6 my-4 sm:my-8">
    <div class="flex items-center justify-between mb-3 sm:mb-4">
      <h2 class="text-lg sm:text-xl font-bold font-headline">Cargar Resultado</h2>
      <button id="closeResultModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <input type="hidden" id="resultPartidoId"/>

    <!-- Score -->
    <div class="grid grid-cols-3 gap-2 sm:gap-4 mb-4 sm:mb-6 items-center text-center">
      <div>
        <img id="resultLocalEscudo" class="logo-team mx-auto mb-1" src="" alt=""/>
        <p class="font-bold text-sm sm:text-lg truncate" id="resultLocalName">—</p>
        <input id="resultGolesLocal" type="number" min="0" value="0" class="w-14 sm:w-20 mx-auto border border-outline-variant/20 rounded-lg p-1.5 sm:p-2 text-center text-xl sm:text-2xl font-bold mt-1 sm:mt-2"/>
      </div>
      <div class="text-2xl sm:text-3xl font-bold text-on-surface-variant">vs</div>
      <div>
        <img id="resultVisitanteEscudo" class="logo-team mx-auto mb-1" src="" alt=""/>
        <p class="font-bold text-sm sm:text-lg truncate" id="resultVisitanteName">—</p>
        <input id="resultGolesVisitante" type="number" min="0" value="0" class="w-14 sm:w-20 mx-auto border border-outline-variant/20 rounded-lg p-1.5 sm:p-2 text-center text-xl sm:text-2xl font-bold mt-1 sm:mt-2"/>
      </div>
    </div>

    <!-- Tabs: Goleadores / Tarjetas -->
    <div class="flex border-b border-outline-variant/10 mb-4">
      <button id="tabGoles" class="px-4 py-2 text-sm font-bold border-b-2 border-primary text-primary">Goleadores</button>
      <button id="tabTarjetas" class="px-4 py-2 text-sm font-bold border-b-2 border-transparent text-on-surface-variant hover:text-on-surface transition-colors">Tarjetas</button>
    </div>

    <!-- Panel Goleadores -->
    <div id="panelGoles" class="mb-4">
      <p class="text-xs text-on-surface-variant mb-2">Hacé clic en un jugador para registrar un gol.</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div>
          <h4 class="text-sm font-semibold mb-2 text-primary font-headline" id="golesLocalTitle">Local</h4>
          <div id="golesLocalList" class="space-y-1 max-h-40 sm:max-h-48 overflow-y-auto"></div>
        </div>
        <div>
          <h4 class="text-sm font-semibold mb-2 text-primary font-headline" id="golesVisitanteTitle">Visitante</h4>
          <div id="golesVisitanteList" class="space-y-1 max-h-40 sm:max-h-48 overflow-y-auto"></div>
        </div>
      </div>
      <div class="mt-3">
        <h4 class="text-sm font-semibold mb-1 font-headline">Goles registrados:</h4>
        <div id="golesRegistrados" class="flex flex-wrap gap-1 min-h-[2rem]"></div>
      </div>
    </div>

    <!-- Panel Tarjetas -->
    <div id="panelTarjetas" class="hidden mb-4">
      <p class="text-xs text-on-surface-variant mb-2">Hacé clic en 🟨 para amarilla o 🟥 para roja.</p>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div>
          <h4 class="text-sm font-semibold mb-2 text-primary font-headline" id="tarjetasLocalTitle">Local</h4>
          <div id="tarjetasLocalList" class="space-y-1 max-h-40 sm:max-h-48 overflow-y-auto"></div>
        </div>
        <div>
          <h4 class="text-sm font-semibold mb-2 text-primary font-headline" id="tarjetasVisitanteTitle">Visitante</h4>
          <div id="tarjetasVisitanteList" class="space-y-1 max-h-40 sm:max-h-48 overflow-y-auto"></div>
        </div>
      </div>
      <div class="mt-3">
        <h4 class="text-sm font-semibold mb-1 font-headline">Tarjetas registradas:</h4>
        <div id="tarjetasRegistradas" class="flex flex-wrap gap-1 min-h-[2rem]"></div>
      </div>
    </div>

    <!-- Estado del partido -->
    <div class="px-3 py-3 rounded-lg bg-slate-700/30 border border-slate-600/30 mb-3">
      <p class="text-sm font-semibold text-slate-200 mb-2">Estado del partido</p>
      <div class="space-y-2">
        <label class="flex items-center gap-2 cursor-pointer hover:bg-white/5 rounded p-2 transition-colors">
          <input type="radio" name="resultEstado" value="en_curso" class="text-amber-500 focus:ring-amber-500"/>
          <div class="flex-1">
            <span class="text-sm font-medium text-amber-300">🟡 En curso</span>
            <span class="block text-xs text-amber-500/70 mt-0.5">Guardar progreso sin actualizar tablas ni goleadores</span>
          </div>
        </label>
        <label class="flex items-center gap-2 cursor-pointer hover:bg-white/5 rounded p-2 transition-colors">
          <input type="radio" name="resultEstado" value="jugado" class="text-emerald-500 focus:ring-emerald-500"/>
          <div class="flex-1">
            <span class="text-sm font-medium text-emerald-300">🟢 Finalizado</span>
            <span class="block text-xs text-emerald-500/70 mt-0.5">Marcar como finalizado y actualizar tablas/goleadores</span>
          </div>
        </label>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-end gap-2 pt-3 sm:pt-4 border-t border-outline-variant/10">
      <button id="cancelResult" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm sm:text-base transition-colors">Cancelar</button>
      <button id="saveResult" class="px-4 sm:px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold transition-colors flex items-center justify-center gap-1 text-sm sm:text-base">
        <span class="material-symbols-outlined text-[18px]">save</span> Guardar Resultado
      </button>
    </div>
  </div>
</div>

<!-- Modal: Programar partido -->
<div id="scheduleModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-2 sm:p-4 z-50">
  <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl w-full max-w-md p-4 sm:p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg sm:text-xl font-bold font-headline">Programar Partido</h2>
      <button id="closeScheduleModal" class="text-on-surface-variant hover:text-on-surface transition-colors" type="button">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <input type="hidden" id="schedulePartidoId"/>

    <div class="space-y-3">
      <div>
        <p class="text-xs text-on-surface-variant mb-1">Partido</p>
        <p id="schedulePartidoLabel" class="text-sm font-semibold">—</p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label for="scheduleFecha" class="block text-xs font-semibold text-on-surface-variant mb-1">Fecha</label>
          <input id="scheduleFecha" type="date" class="w-full border border-outline-variant/20 rounded-lg p-2"/>
        </div>
        <div>
          <label for="scheduleHora" class="block text-xs font-semibold text-on-surface-variant mb-1">Hora</label>
          <input id="scheduleHora" type="time" class="w-full border border-outline-variant/20 rounded-lg p-2"/>
        </div>
      </div>

      <div>
        <label for="scheduleEstadio" class="block text-xs font-semibold text-on-surface-variant mb-1">Estadio</label>
        <input id="scheduleEstadio" type="text" class="w-full border border-outline-variant/20 rounded-lg p-2" placeholder="Nombre del estadio"/>
      </div>

      <div>
        <label for="scheduleEstado" class="block text-xs font-semibold text-on-surface-variant mb-1">Estado</label>
        <select id="scheduleEstado" class="w-full border border-outline-variant/20 rounded-lg p-2">
          <option value="pendiente">Pendiente</option>
          <option value="en_curso">En curso</option>
          <option value="postergado">Postergado</option>
          <option value="suspendido">Suspendido</option>
          <option value="cancelado">Cancelado</option>
          <option value="jugado">Jugado</option>
        </select>
      </div>

      <div>
        <label for="scheduleBroadcast" class="block text-xs font-semibold text-on-surface-variant mb-1">Link transmisión (opcional)</label>
        <input id="scheduleBroadcast" type="url" class="w-full border border-outline-variant/20 rounded-lg p-2" placeholder="https://..."/>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-end gap-2 mt-5 pt-4 border-t border-outline-variant/10">
      <button id="cancelSchedule" type="button" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm transition-colors">Cancelar</button>
      <button id="saveSchedule" type="button" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-sm transition-colors">Guardar programación</button>
    </div>
  </div>
</div>

<!-- Modal: Flyer Preview -->
<div id="flyerModal" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50 overflow-y-auto">
  <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl w-full max-w-lg p-4 my-4">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-bold font-headline" id="flyerModalTitle">Preview Flyer</h2>
      <button id="closeFlyerModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div id="flyerPreviewWrap" class="flex justify-center mb-4 overflow-hidden rounded-lg bg-black/40"></div>
    <div class="flex gap-2 justify-end">
      <button id="closeFlyerModalBtn" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container text-sm transition-colors">Cerrar</button>
      <button id="downloadFlyerBtn" class="flex items-center gap-1 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-semibold text-sm transition-colors">
        <span class="material-symbols-outlined text-[18px]">download</span> Descargar PNG
      </button>
    </div>
  </div>
</div>

<!-- Hidden offscreen container for flyer rendering -->
<div id="flyerCanvasWrap" style="position:fixed;left:-9999px;top:0;z-index:-1;"></div>

<!-- Toast -->
<div id="toast" class="toast"></div>

<!-- ===================== MODAL PRODE ADMIN ===================== -->
<div id="prodeAdminModal" class="hidden fixed inset-0 bg-black/60 flex items-start justify-center p-3 z-50 overflow-y-auto">
  <div class="bg-surface-container-high border border-outline-variant/10 rounded-2xl w-full max-w-2xl p-5 my-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-xl font-bold font-headline flex items-center gap-2">
          <span class="material-symbols-outlined text-primary">emoji_events</span>
          Prode — Admin
        </h2>
        <p class="text-sm text-on-surface-variant mt-0.5">Gestión de fechas y puntos</p>
      </div>
      <button onclick="cerrarProdeModal()" class="text-on-surface-variant hover:text-on-surface transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>

    <!-- Tabs Liga / Mundial -->
    <div class="flex border-b border-outline-variant/20 mb-5 gap-0">
      <button id="prodeTabLiga" onclick="switchProdeTab('liga')"
        class="prode-admin-tab border-b-2 border-primary text-primary px-4 py-2 text-sm font-semibold transition-all">
        <span class="material-symbols-outlined text-[14px] align-middle mr-1">sports_soccer</span>Liga
      </button>
      <button id="prodeTabMundial" onclick="switchProdeTab('mundial')"
        class="prode-admin-tab border-b-2 border-transparent text-on-surface-variant px-4 py-2 text-sm font-semibold transition-all">
        <span class="material-symbols-outlined text-[14px] align-middle mr-1">public</span>Mundial 2026
      </button>
    </div>

    <!-- ======= PANEL LIGA ======= -->
    <div id="prodePanelLiga">
      <div id="prodeAdminLoading" class="text-center py-8">
        <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-on-surface-variant mt-2">Cargando fechas del prode...</p>
      </div>

      <div id="prodeAdminContent" class="hidden">
        <div id="prodeFechasList" class="space-y-3"></div>
        <div id="prodeNoFechas" class="hidden text-center py-8 text-on-surface-variant text-sm">
          No hay fechas con pronósticos registrados aún.
          <br><a href="/prode" target="_blank" class="text-primary underline mt-2 inline-block">Ver página del Prode →</a>
        </div>
      </div>

      <div class="mt-5 pt-4 border-t border-outline-variant/20 flex items-center justify-between gap-3">
        <a href="/prode" target="_blank" class="text-xs text-primary flex items-center gap-1 hover:underline">
          <span class="material-symbols-outlined text-[14px]">open_in_new</span>
          Ver página pública del Prode
        </a>
        <button onclick="cargarProdeAdmin()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant/20 text-sm text-on-surface-variant hover:bg-surface-container transition-colors">
          <span class="material-symbols-outlined text-[16px]">refresh</span> Actualizar
        </button>
      </div>
    </div>

    <!-- ======= PANEL MUNDIAL ======= -->
    <div id="prodePanelMundial" class="hidden space-y-5">

      <!-- Toggle visibilidad en el header -->
      <div class="bg-surface-container border border-outline-variant/20 rounded-xl p-4">
        <div class="flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[#f5d57a] text-2xl">emoji_events</span>
            <div>
              <p class="font-semibold text-sm">Mostrar en el sitio</p>
              <p class="text-xs text-on-surface-variant mt-0.5">Activa el botón &ldquo;Prode Mundial&rdquo; en el header del inicio</p>
            </div>
          </div>
          <button id="btnMundialEnabled" onclick="toggleMundialEnabled()"
            role="switch" aria-checked="false"
            class="relative inline-flex items-center h-7 w-12 flex-shrink-0 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary/30 bg-outline-variant/40">
            <span id="mundialEnabledThumb"
              class="inline-block w-5 h-5 rounded-full bg-white shadow-md transform transition-transform duration-200 translate-x-1"></span>
          </button>
        </div>
      </div>

      <!-- Importar selecciones -->
      <div class="bg-surface-container border border-outline-variant/20 rounded-xl p-4">
        <div class="flex items-center justify-between gap-3">
          <div>
            <p class="font-semibold text-sm">Selecciones del Mundial</p>
            <p class="text-xs text-on-surface-variant mt-0.5" id="mundialEquiposCount">—</p>
          </div>
          <button onclick="importarSelecciones()"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-[14px]">download</span>
            Importar 48 selecciones
          </button>
        </div>
      </div>

      <!-- Rondas -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-sm">Rondas</h3>
          <button onclick="abrirCrearRonda()"
            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/20 text-primary text-xs font-bold hover:bg-primary/30 transition-colors border border-primary/30">
            <span class="material-symbols-outlined text-[14px]">add</span>Nueva ronda
          </button>
        </div>

        <!-- Form crear ronda (oculto por default) -->
        <div id="formCrearRonda" class="hidden bg-surface-container border border-outline-variant/20 rounded-xl p-4 mb-3 space-y-3">
          <p class="font-semibold text-xs text-on-surface-variant uppercase tracking-wide">Nueva ronda</p>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1">Nombre</label>
              <input id="inputRondaNombre" type="text" placeholder="ej: Fase de Grupos — Jornada 1"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Fase</label>
              <select id="inputRondaFase"
                class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60">
                <option value="grupos">Grupos</option>
                <option value="octavos">Octavos de final</option>
                <option value="cuartos">Cuartos de final</option>
                <option value="semifinal">Semifinal</option>
                <option value="tercer_puesto">Tercer puesto</option>
                <option value="final">Final</option>
              </select>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button onclick="document.getElementById('formCrearRonda').classList.add('hidden')"
              class="px-3 py-1.5 text-xs text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">Cancelar</button>
            <button onclick="crearRondaMundial()"
              class="px-4 py-1.5 text-xs font-bold bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
              Crear ronda
            </button>
          </div>
        </div>

        <div id="mundialRondasLoading" class="text-center py-4">
          <div class="inline-block w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>
        <div id="mundialRondasList" class="hidden space-y-3"></div>
        <div id="mundialNoRondas" class="hidden text-center py-6 text-on-surface-variant text-sm">
          No hay rondas creadas. Creá la primera ronda arriba.
        </div>
      </div>

      <!-- Partidos de la ronda seleccionada -->
      <div id="mundialPartidosSection" class="hidden">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-sm">Partidos — <span id="mundialRondaSelNombre" class="text-primary"></span></h3>
          <button onclick="abrirFormPartido()"
            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary/20 text-primary text-xs font-bold hover:bg-primary/30 transition-colors border border-primary/30">
            <span class="material-symbols-outlined text-[14px]">add</span>Partido
          </button>
        </div>

        <!-- Form nuevo partido (oculto) -->
        <div id="formCrearPartido" class="hidden bg-surface-container border border-outline-variant/20 rounded-xl p-4 mb-3 space-y-3">
          <p class="font-semibold text-xs text-on-surface-variant uppercase tracking-wide">Nuevo partido</p>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold mb-1">Local</label>
              <select id="inputPartidoLocal" class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"></select>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Visitante</label>
              <select id="inputPartidoVisitante" class="w-full bg-surface border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"></select>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Fecha</label>
              <input id="inputPartidoFecha" type="date"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Hora</label>
              <input id="inputPartidoHora" type="time" value="00:00"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Grupo (opcional)</label>
              <input id="inputPartidoGrupo" type="text" maxlength="1" placeholder="A"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Estadio (opcional)</label>
              <input id="inputPartidoEstadio" type="text" placeholder="Estadio"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-primary/60"/>
            </div>
          </div>
          <div class="flex gap-2 justify-end">
            <button onclick="document.getElementById('formCrearPartido').classList.add('hidden')"
              class="px-3 py-1.5 text-xs text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">Cancelar</button>
            <button onclick="crearPartidoMundial()"
              class="px-4 py-1.5 text-xs font-bold bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
              Crear partido
            </button>
          </div>
        </div>

        <div id="mundialPartidosList" class="space-y-2"></div>
      </div>

      <!-- Footer panel mundial -->
      <div class="pt-4 border-t border-outline-variant/20 flex items-center justify-between gap-3">
        <a href="/prode-mundial" target="_blank" class="text-xs text-primary flex items-center gap-1 hover:underline">
          <span class="material-symbols-outlined text-[14px]">open_in_new</span>
          Ver página del Prode Mundial
        </a>
        <button onclick="cargarMundialAdmin()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant/20 text-sm text-on-surface-variant hover:bg-surface-container transition-colors">
          <span class="material-symbols-outlined text-[16px]">refresh</span> Actualizar
        </button>
      </div>
    </div>

  </div>
</div>

<script src="/frontend/app.js"></script>
<script src="/frontend/carga.js"></script>
<script>
// ===================== PRODE ADMIN — TABS =====================
let mundialRondaSelId = null;

function switchProdeTab(tab) {
  document.getElementById('prodePanelLiga').classList.toggle('hidden', tab !== 'liga');
  document.getElementById('prodePanelMundial').classList.toggle('hidden', tab !== 'mundial');
  document.getElementById('prodeTabLiga').className =
    'prode-admin-tab border-b-2 px-4 py-2 text-sm font-semibold transition-all ' +
    (tab === 'liga' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant');
  document.getElementById('prodeTabMundial').className =
    'prode-admin-tab border-b-2 px-4 py-2 text-sm font-semibold transition-all ' +
    (tab === 'mundial' ? 'border-primary text-primary' : 'border-transparent text-on-surface-variant');
  if (tab === 'mundial') cargarMundialAdmin();
}

// ===================== PRODE ADMIN — LIGA =====================
function abrirProdeModal() {
  document.getElementById('prodeAdminModal').classList.remove('hidden');
  cargarProdeAdmin();
}
function cerrarProdeModal() {
  document.getElementById('prodeAdminModal').classList.add('hidden');
}

async function cargarProdeAdmin() {
  const loading = document.getElementById('prodeAdminLoading');
  const content = document.getElementById('prodeAdminContent');
  const lista   = document.getElementById('prodeFechasList');
  const noData  = document.getElementById('prodeNoFechas');

  loading.classList.remove('hidden');
  content.classList.add('hidden');

  try {
    const token = localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
    const res = await fetch('/api/prode/admin/fechas', {
      headers: { 'Authorization': 'Bearer ' + token, 'X-Auth-Token': token }
    });
    if (!res.ok) throw new Error('No autorizado');
    const data = await res.json();

    loading.classList.add('hidden');
    content.classList.remove('hidden');
    lista.innerHTML = '';

    if (!data.fechas || data.fechas.length === 0) {
      noData.classList.remove('hidden');
      return;
    }
    noData.classList.add('hidden');

    data.fechas.forEach(f => {
      const cerrada = f.estado === 'cerrada';
      const deadline = f.deadline ? new Date(f.deadline.replace(' ','T')).toLocaleString('es-AR', {day:'numeric',month:'short',hour:'2-digit',minute:'2-digit'}) : '—';

      const row = document.createElement('div');
      row.className = 'flex items-center gap-3 bg-surface-container border border-outline-variant/20 rounded-xl px-4 py-3';
      row.innerHTML = `
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm">Fecha ${f.jornada} — ${f.torneo}</div>
          <div class="text-xs text-on-surface-variant mt-0.5">
            Cierre: ${deadline} &bull; ${f.participantes} participantes &bull; ${f.pronosticos_total} pronósticos
          </div>
        </div>
        <div class="flex-shrink-0 flex items-center gap-2">
          <span class="text-xs px-2 py-0.5 rounded-full font-semibold
            ${cerrada ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'}">
            ${cerrada ? '✓ Cerrada' : 'Abierta'}
          </span>
          ${!cerrada ? `<button onclick="cerrarFechaProde(${f.jornada}, '${f.torneo}', ${f.temporada_id}, this)"
            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-[14px]">calculate</span>
            Calcular puntos
          </button>` : `<span class="text-xs text-on-surface-variant">${f.puntos_calculados ? '✓ Puntos calculados' : '—'}</span>`}
        </div>
      `;
      lista.appendChild(row);
    });
  } catch (err) {
    loading.classList.add('hidden');
    content.classList.remove('hidden');
    lista.innerHTML = `<p class="text-error text-sm text-center py-4">${err.message}</p>`;
  }
}

async function cerrarFechaProde(jornada, torneo, temporadaId, btn) {
  if (!confirm(`¿Cerrar Fecha ${jornada} y calcular puntos del Prode?\nEsta acción no se puede deshacer.`)) return;

  btn.disabled = true;
  btn.textContent = 'Calculando...';

  try {
    const token = localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
    const res = await fetch(`/api/prode/admin/fecha/${jornada}/cerrar`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token,
        'X-Auth-Token': token
      },
      body: JSON.stringify({ torneo, temporada_id: temporadaId })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error al cerrar');

    const toast = document.getElementById('toast');
    toast.textContent = `✓ Fecha ${jornada}: ${data.calculados} pronósticos calculados`;
    toast.className = 'toast toast-success show';
    setTimeout(() => { toast.className = 'toast'; }, 3500);

    cargarProdeAdmin();
  } catch (err) {
    alert('Error: ' + err.message);
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined text-[14px]">calculate</span> Calcular puntos';
  }
}

// ===================== PRODE MUNDIAL — ADMIN =====================
function mundialAuthHeaders() {
  const token = localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
  return { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token, 'X-Auth-Token': token };
}

async function cargarMundialAdmin() {
  cargarEstadoMundialEnabled();
  await Promise.all([cargarEquiposMundial(), cargarRondasMundial()]);
}

async function cargarEstadoMundialEnabled() {
  try {
    const res = await fetch('/api/prode-mundial/config');
    const data = await res.json();
    const enabled = data.enabled === true;
    const btn   = document.getElementById('btnMundialEnabled');
    const thumb = document.getElementById('mundialEnabledThumb');
    if (btn) {
      btn.setAttribute('aria-checked', enabled ? 'true' : 'false');
      btn.classList.toggle('bg-[#c6992a]', enabled);
      btn.classList.toggle('bg-outline-variant/40', !enabled);
    }
    if (thumb) {
      thumb.classList.toggle('translate-x-6', enabled);
      thumb.classList.toggle('translate-x-1', !enabled);
    }
  } catch (_) {}
}

async function toggleMundialEnabled() {
  const btn   = document.getElementById('btnMundialEnabled');
  const thumb = document.getElementById('mundialEnabledThumb');
  const currentEnabled = btn?.getAttribute('aria-checked') === 'true';
  const newEnabled = !currentEnabled;
  // Actualizar UI de inmediato (optimistic)
  if (btn) {
    btn.setAttribute('aria-checked', newEnabled ? 'true' : 'false');
    btn.classList.toggle('bg-[#c6992a]', newEnabled);
    btn.classList.toggle('bg-outline-variant/40', !newEnabled);
  }
  if (thumb) {
    thumb.classList.toggle('translate-x-6', newEnabled);
    thumb.classList.toggle('translate-x-1', !newEnabled);
  }
  try {
    const res = await fetch('/api/prode-mundial/admin/config', {
      method: 'PUT',
      headers: mundialAuthHeaders(),
      body: JSON.stringify({ enabled: newEnabled })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast(newEnabled ? '\u2713 Botón del Mundial activado en el header' : '\u2713 Botón del Mundial ocultado del header');
  } catch (err) {
    // Revertir UI
    if (btn) {
      btn.setAttribute('aria-checked', currentEnabled ? 'true' : 'false');
      btn.classList.toggle('bg-[#c6992a]', currentEnabled);
      btn.classList.toggle('bg-outline-variant/40', !currentEnabled);
    }
    if (thumb) {
      thumb.classList.toggle('translate-x-6', currentEnabled);
      thumb.classList.toggle('translate-x-1', !currentEnabled);
    }
    showMundialToast('Error: ' + err.message, true);
  }
}

async function cargarEquiposMundial() {
  try {
    const res = await fetch('/api/prode-mundial/admin/equipos', { headers: mundialAuthHeaders() });
    const data = await res.json();
    const count = data.total || 0;
    document.getElementById('mundialEquiposCount').textContent =
      count > 0 ? `${count} selecciones cargadas` : 'Sin selecciones importadas aún';
  } catch (_) {
    document.getElementById('mundialEquiposCount').textContent = 'Error al cargar';
  }
}

async function importarSelecciones() {
  if (!confirm('¿Importar las 48 selecciones del Mundial 2026?\nSe actualizarán las existentes.')) return;
  try {
    const res = await fetch('/api/prode-mundial/admin/equipos/seed', {
      method: 'POST', headers: mundialAuthHeaders(), body: JSON.stringify({})
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast(`✓ ${data.insertados} insertadas, ${data.actualizadas || data.actualizados} actualizadas`);
    await cargarEquiposMundial();
    await cargarPartidosFormSelects();
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
  }
}

async function cargarRondasMundial() {
  const loading = document.getElementById('mundialRondasLoading');
  const lista   = document.getElementById('mundialRondasList');
  const noData  = document.getElementById('mundialNoRondas');

  loading.classList.remove('hidden');
  lista.classList.add('hidden');
  noData.classList.add('hidden');

  try {
    const res = await fetch('/api/prode-mundial/admin/rondas', { headers: mundialAuthHeaders() });
    const data = await res.json();

    loading.classList.add('hidden');
    const rondas = data.rondas || [];

    if (rondas.length === 0) {
      noData.classList.remove('hidden');
      return;
    }

    lista.classList.remove('hidden');
    lista.innerHTML = '';

    rondas.forEach(r => {
      const cerrada = r.estado === 'cerrada';
      const row = document.createElement('div');
      row.className = 'flex items-center gap-3 bg-surface-container border border-outline-variant/20 rounded-xl px-4 py-3 cursor-pointer hover:border-primary/30 transition-all';
      row.onclick = () => seleccionarRondaMundial(r.id, r.nombre);
      row.innerHTML = `
        <div class="flex-1 min-w-0">
          <div class="font-bold text-sm">${r.nombre}</div>
          <div class="text-xs text-on-surface-variant mt-0.5">
            ${r.partidos_total || 0} partidos &bull; ${r.partidos_jugados || 0} jugados &bull;
            <span class="capitalize">${(r.fase || '').replace(/_/g,' ')}</span>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <span class="text-xs px-2 py-0.5 rounded-full font-semibold
            ${cerrada ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30'}">
            ${cerrada ? '✓ Cerrada' : 'Abierta'}
          </span>
          ${!cerrada ? `<button onclick="event.stopPropagation(); cerrarRondaMundial(${r.id}, '${r.nombre.replace(/'/g,"\\'")}', this)"
            class="flex items-center gap-1 px-3 py-1.5 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors">
            <span class="material-symbols-outlined text-[14px]">calculate</span>
            Calcular puntos
          </button>` : `<span class="text-xs text-on-surface-variant">${r.puntos_calculados ? '✓ Calculados' : '—'}</span>`}
        </div>
      `;
      lista.appendChild(row);
    });
  } catch (err) {
    loading.classList.add('hidden');
    lista.classList.remove('hidden');
    lista.innerHTML = `<p class="text-error text-sm text-center py-4">${err.message}</p>`;
  }
}

function seleccionarRondaMundial(id, nombre) {
  mundialRondaSelId = id;
  document.getElementById('mundialRondaSelNombre').textContent = nombre;
  document.getElementById('mundialPartidosSection').classList.remove('hidden');
  document.getElementById('formCrearPartido').classList.add('hidden');
  cargarPartidosMundial(id);
  cargarPartidosFormSelects();
}

async function cargarPartidosMundial(rondaId) {
  const lista = document.getElementById('mundialPartidosList');
  lista.innerHTML = '<div class="text-center py-3"><div class="inline-block w-5 h-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div></div>';

  try {
    const res = await fetch(`/api/prode-mundial/admin/partidos?ronda_id=${rondaId}`, { headers: mundialAuthHeaders() });
    const data = await res.json();
    const partidos = data.partidos || [];

    if (partidos.length === 0) {
      lista.innerHTML = '<p class="text-xs text-on-surface-variant text-center py-3">No hay partidos en esta ronda aún.</p>';
      return;
    }

    lista.innerHTML = '';
    partidos.forEach(p => {
      const jugado = p.estado === 'jugado';
      const grupoStr = p.grupo ? `Grupo ${p.grupo} · ` : '';
      const fechaStr = p.fecha || '';
      const horaStr = (p.hora || '').slice(0, 5);

      const row = document.createElement('div');
      row.id = `partido_row_${p.id}`;
      row.className = 'bg-surface-container border border-outline-variant/20 rounded-xl overflow-hidden';
      row.innerHTML = `
        <!-- fila principal -->
        <div class="flex items-center gap-2 px-3 py-2.5">
          <div class="flex-1 min-w-0">
            <div class="text-sm font-semibold truncate">${p.local} vs ${p.visitante}</div>
            <div class="text-xs text-on-surface-variant">${grupoStr}${fechaStr} ${horaStr}</div>
            ${jugado ? `<div class="text-xs text-green-400 font-bold">Resultado: ${p.golesLocal}\u2013${p.golesVisitante}</div>` : ''}
          </div>
          <div class="flex items-center gap-1.5 flex-shrink-0">
            ${!jugado ? `
              <input type="number" min="0" max="20" placeholder="L" id="gl_${p.id}"
                class="w-10 text-center bg-transparent border border-outline-variant/30 rounded-lg px-1 py-1 text-sm focus:outline-none focus:border-primary/60"/>
              <span class="text-on-surface-variant text-xs">&ndash;</span>
              <input type="number" min="0" max="20" placeholder="V" id="gv_${p.id}"
                class="w-10 text-center bg-transparent border border-outline-variant/30 rounded-lg px-1 py-1 text-sm focus:outline-none focus:border-primary/60"/>
              <button onclick="guardarResultadoMundial(${p.id}, this)"
                class="px-2 py-1 rounded-lg bg-primary/20 text-primary text-xs font-bold hover:bg-primary/30 transition-colors border border-primary/30">OK</button>
            ` : `
              <button onclick="toggleEditPartido(${p.id})"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors border border-outline-variant/20" title="Editar resultado">
                <span class="material-symbols-outlined text-[16px]">edit</span>
              </button>
            `}
            <button onclick="toggleEditPartidoDatos(${p.id})"
              class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-colors border border-outline-variant/20" title="Editar datos">
              <span class="material-symbols-outlined text-[16px]">tune</span>
            </button>
            <button onclick="eliminarPartidoMundial(${p.id}, this)"
              class="w-7 h-7 flex items-center justify-center rounded-lg text-on-surface-variant hover:text-error hover:bg-error/10 transition-colors border border-outline-variant/20" title="Eliminar">
              <span class="material-symbols-outlined text-[16px]">delete</span>
            </button>
          </div>
        </div>
        <!-- editar resultado -->
        <div id="editResultado_${p.id}" class="hidden border-t border-outline-variant/20 px-3 py-2.5 bg-surface-container-high">
          <div class="flex items-center gap-3">
            <span class="text-xs text-on-surface-variant flex-1">Corregir resultado:</span>
            <input type="number" min="0" max="20" id="glEdit_${p.id}" value="${p.golesLocal ?? ''}" placeholder="L"
              class="w-10 text-center bg-transparent border border-outline-variant/30 rounded-lg px-1 py-1 text-sm focus:outline-none focus:border-primary/60"/>
            <span class="text-on-surface-variant text-xs">&ndash;</span>
            <input type="number" min="0" max="20" id="gvEdit_${p.id}" value="${p.golesVisitante ?? ''}" placeholder="V"
              class="w-10 text-center bg-transparent border border-outline-variant/30 rounded-lg px-1 py-1 text-sm focus:outline-none focus:border-primary/60"/>
            <button onclick="guardarResultadoEdit(${p.id}, this)"
              class="px-3 py-1 rounded-lg bg-primary text-white text-xs font-bold hover:bg-primary/90 transition-colors">Guardar</button>
            <button onclick="document.getElementById('editResultado_${p.id}').classList.add('hidden')"
              class="px-2 py-1 rounded-lg text-on-surface-variant text-xs hover:bg-surface-container transition-colors">✕</button>
          </div>
        </div>
        <!-- editar datos del partido -->
        <div id="editDatos_${p.id}" class="hidden border-t border-outline-variant/20 px-3 py-3 bg-surface-container-high space-y-2">
          <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wide mb-1">Editar partido</p>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-xs font-semibold mb-1">Local</label>
              <select id="editLocal_${p.id}" class="w-full bg-surface border border-outline-variant/30 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-primary/60"></select>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Visitante</label>
              <select id="editVisitante_${p.id}" class="w-full bg-surface border border-outline-variant/30 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-primary/60"></select>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Fecha</label>
              <input id="editFecha_${p.id}" type="date" value="${p.fecha || ''}"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Hora</label>
              <input id="editHora_${p.id}" type="time" value="${horaStr}"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Grupo</label>
              <input id="editGrupo_${p.id}" type="text" maxlength="1" value="${p.grupo || ''}"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-primary/60"/>
            </div>
            <div>
              <label class="block text-xs font-semibold mb-1">Estadio</label>
              <input id="editEstadio_${p.id}" type="text" value="${(p.estadio || '').replace(/"/g,'&quot;')}"
                class="w-full bg-transparent border border-outline-variant/30 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:border-primary/60"/>
            </div>
          </div>
          <div class="flex gap-2 justify-end pt-1">
            <button onclick="document.getElementById('editDatos_${p.id}').classList.add('hidden')"
              class="px-3 py-1.5 text-xs text-on-surface-variant rounded-lg hover:bg-surface-container transition-colors">Cancelar</button>
            <button onclick="guardarDatosPartido(${p.id}, ${p.local_id}, ${p.visitante_id})"
              class="px-4 py-1.5 text-xs font-bold bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">Guardar cambios</button>
          </div>
        </div>
      `;
      // Pre-cargar selects con equipos guardados
      poblarSelectsEditPartido(p.id, p.local_id, p.visitante_id);
      lista.appendChild(row);
    });
  } catch (err) {
    lista.innerHTML = `<p class="text-error text-xs text-center py-2">${err.message}</p>`;
  }
}

async function guardarResultadoMundial(partidoId, btn) {
  const gl = parseInt(document.getElementById(`gl_${partidoId}`)?.value);
  const gv = parseInt(document.getElementById(`gv_${partidoId}`)?.value);

  if (isNaN(gl) || isNaN(gv) || gl < 0 || gv < 0) {
    showMundialToast('Ingresá los goles correctamente', true);
    return;
  }

  btn.disabled = true;
  btn.textContent = '...';

  try {
    const res = await fetch(`/api/prode-mundial/admin/partidos/${partidoId}/resultado`, {
      method: 'PUT',
      headers: mundialAuthHeaders(),
      body: JSON.stringify({ goles_local: gl, goles_visitante: gv })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast(`✓ Resultado guardado: ${gl}–${gv}`);
    if (mundialRondaSelId) cargarPartidosMundial(mundialRondaSelId);
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
    btn.disabled = false;
    btn.textContent = 'Guardar';
  }
}

function editarResultadoMundial(partidoId) {
  document.getElementById(`editResultado_${partidoId}`)?.classList.toggle('hidden');
}

function toggleEditPartido(partidoId) {
  document.getElementById(`editResultado_${partidoId}`)?.classList.toggle('hidden');
}

function toggleEditPartidoDatos(partidoId) {
  document.getElementById(`editDatos_${partidoId}`)?.classList.toggle('hidden');
}

async function guardarResultadoEdit(partidoId, btn) {
  const gl = parseInt(document.getElementById(`glEdit_${partidoId}`)?.value);
  const gv = parseInt(document.getElementById(`gvEdit_${partidoId}`)?.value);
  if (isNaN(gl) || isNaN(gv) || gl < 0 || gv < 0) {
    showMundialToast('Ingresá los goles correctamente', true); return;
  }
  btn.disabled = true;
  try {
    const res = await fetch(`/api/prode-mundial/admin/partidos/${partidoId}/resultado`, {
      method: 'PUT', headers: mundialAuthHeaders(),
      body: JSON.stringify({ goles_local: gl, goles_visitante: gv })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast(`✓ Resultado actualizado: ${gl}\u2013${gv}`);
    if (mundialRondaSelId) cargarPartidosMundial(mundialRondaSelId);
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
    btn.disabled = false;
  }
}

async function eliminarPartidoMundial(partidoId, btn) {
  if (!confirm('\u00bfEliminar este partido y todos sus pron\u00f3sticos? Esta acci\u00f3n no se puede deshacer.')) return;
  btn.disabled = true;
  try {
    const res = await fetch(`/api/prode-mundial/admin/partidos/${partidoId}`, {
      method: 'DELETE', headers: mundialAuthHeaders()
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast('\u2713 Partido eliminado');
    document.getElementById(`partido_row_${partidoId}`)?.remove();
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
    btn.disabled = false;
  }
}

let _equiposCache = null;
async function poblarSelectsEditPartido(partidoId, localId, visitanteId) {
  if (!_equiposCache) {
    try {
      const res = await fetch('/api/prode-mundial/admin/equipos', { headers: mundialAuthHeaders() });
      const data = await res.json();
      _equiposCache = data.equipos || [];
    } catch (_) { return; }
  }
  const opts = _equiposCache.map(e => `<option value="${e.id}">${e.nombre}${e.grupo ? ` (G${e.grupo})` : ''}</option>`).join('');
  ['editLocal', 'editVisitante'].forEach((prefix, i) => {
    const sel = document.getElementById(`${prefix}_${partidoId}`);
    if (!sel) return;
    sel.innerHTML = opts;
    sel.value = i === 0 ? localId : visitanteId;
  });
}

async function guardarDatosPartido(partidoId, origLocalId, origVisitanteId) {
  const localId     = parseInt(document.getElementById(`editLocal_${partidoId}`)?.value);
  const visitanteId = parseInt(document.getElementById(`editVisitante_${partidoId}`)?.value);
  const fecha       = document.getElementById(`editFecha_${partidoId}`)?.value;
  const hora        = document.getElementById(`editHora_${partidoId}`)?.value;
  const grupo       = document.getElementById(`editGrupo_${partidoId}`)?.value.toUpperCase().trim();
  const estadio     = document.getElementById(`editEstadio_${partidoId}`)?.value.trim();

  if (localId === visitanteId) {
    showMundialToast('Local y visitante no pueden ser el mismo equipo', true); return;
  }

  try {
    const res = await fetch(`/api/prode-mundial/admin/partidos/${partidoId}`, {
      method: 'PUT', headers: mundialAuthHeaders(),
      body: JSON.stringify({ local_id: localId, visitante_id: visitanteId, fecha, hora, grupo, estadio })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast('\u2713 Partido actualizado');
    if (mundialRondaSelId) cargarPartidosMundial(mundialRondaSelId);
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
  }
}

function toggleEditRonda(rondaId, nombre, fase) {
  document.getElementById(`editRonda_${rondaId}`)?.classList.toggle('hidden');
}

async function guardarEditRonda(rondaId) {
  const nombre = document.getElementById(`editRondaNombre_${rondaId}`)?.value.trim();
  const fase   = document.getElementById(`editRondaFase_${rondaId}`)?.value;
  if (!nombre) { showMundialToast('El nombre no puede estar vac\u00edo', true); return; }
  try {
    const res = await fetch(`/api/prode-mundial/admin/rondas/${rondaId}`, {
      method: 'PUT', headers: mundialAuthHeaders(),
      body: JSON.stringify({ nombre, fase })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast('\u2713 Ronda actualizada');
    cargarRondasMundial();
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
  }
}

async function eliminarRondaMundial(rondaId, nombre, btn) {
  if (!confirm(`\u00bfEliminar la ronda "${nombre}" y todos sus partidos y pron\u00f3sticos?\nEsta acci\u00f3n no se puede deshacer.`)) return;
  btn.disabled = true;
  try {
    const res = await fetch(`/api/prode-mundial/admin/rondas/${rondaId}`, {
      method: 'DELETE', headers: mundialAuthHeaders()
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast('\u2713 Ronda eliminada');
    // Si era la ronda seleccionada, ocultar panel de partidos
    if (mundialRondaSelId === rondaId) {
      mundialRondaSelId = null;
      document.getElementById('mundialPartidosSection')?.classList.add('hidden');
    }
    cargarRondasMundial();
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
    btn.disabled = false;
  }
}

async function cerrarRondaMundial(rondaId, nombre, btn) {
  if (!confirm(`¿Cerrar la ronda "${nombre}" y calcular puntos?\nEsta acción cierra el pronóstico para todos.`)) return;

  btn.disabled = true;
  btn.textContent = 'Calculando...';

  try {
    const res = await fetch(`/api/prode-mundial/admin/rondas/${rondaId}/cerrar`, {
      method: 'POST',
      headers: mundialAuthHeaders(),
      body: JSON.stringify({})
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast(`✓ Ronda cerrada. ${data.pronosticos_actualizados} pronósticos calculados.`);
    cargarRondasMundial();
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
    btn.disabled = false;
    btn.innerHTML = '<span class="material-symbols-outlined text-[14px]">calculate</span> Calcular puntos';
  }
}

function abrirCrearRonda() {
  document.getElementById('formCrearRonda').classList.remove('hidden');
  document.getElementById('inputRondaNombre').focus();
}

async function crearRondaMundial() {
  const nombre = document.getElementById('inputRondaNombre').value.trim();
  const fase   = document.getElementById('inputRondaFase').value;

  if (!nombre) { showMundialToast('El nombre es requerido', true); return; }

  try {
    const res = await fetch('/api/prode-mundial/admin/rondas', {
      method: 'POST',
      headers: mundialAuthHeaders(),
      body: JSON.stringify({ nombre, fase })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast(`✓ Ronda "${nombre}" creada`);
    document.getElementById('inputRondaNombre').value = '';
    document.getElementById('formCrearRonda').classList.add('hidden');
    cargarRondasMundial();
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
  }
}

function abrirFormPartido() {
  document.getElementById('formCrearPartido').classList.remove('hidden');
}

async function cargarPartidosFormSelects() {
  try {
    const res = await fetch('/api/prode-mundial/admin/equipos', { headers: mundialAuthHeaders() });
    const data = await res.json();
    const equipos = data.equipos || [];
    if (!equipos.length) return;

    const opts = equipos.map(e => `<option value="${e.id}">${e.nombre}${e.grupo ? ` (G${e.grupo})` : ''}</option>`).join('');
    ['inputPartidoLocal', 'inputPartidoVisitante'].forEach(id => {
      const sel = document.getElementById(id);
      if (sel) sel.innerHTML = opts;
    });
  } catch (_) {}
}

async function crearPartidoMundial() {
  const localId     = parseInt(document.getElementById('inputPartidoLocal').value);
  const visitanteId = parseInt(document.getElementById('inputPartidoVisitante').value);
  const fecha       = document.getElementById('inputPartidoFecha').value;
  const hora        = document.getElementById('inputPartidoHora').value || '00:00';
  const grupo       = document.getElementById('inputPartidoGrupo').value.toUpperCase().trim();
  const estadio     = document.getElementById('inputPartidoEstadio').value.trim();

  if (!localId || !visitanteId || !fecha) {
    showMundialToast('Local, visitante y fecha son requeridos', true); return;
  }
  if (localId === visitanteId) {
    showMundialToast('Local y visitante no pueden ser el mismo equipo', true); return;
  }

  try {
    const res = await fetch('/api/prode-mundial/admin/partidos', {
      method: 'POST',
      headers: mundialAuthHeaders(),
      body: JSON.stringify({
        ronda_id: mundialRondaSelId,
        local_id: localId,
        visitante_id: visitanteId,
        fecha, hora, grupo, estadio,
        fase: document.getElementById('inputRondaFase')?.value || 'grupos',
      })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Error');
    showMundialToast('✓ Partido creado');
    document.getElementById('formCrearPartido').classList.add('hidden');
    if (mundialRondaSelId) cargarPartidosMundial(mundialRondaSelId);
  } catch (err) {
    showMundialToast('Error: ' + err.message, true);
  }
}

function showMundialToast(msg, isError = false) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = msg;
  toast.className = `toast ${isError ? 'toast-error' : 'toast-success'} show`;
  setTimeout(() => { toast.className = 'toast'; }, 3500);
}
</script>
</body>
</html>

