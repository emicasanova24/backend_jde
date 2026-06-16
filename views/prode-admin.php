<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prode — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .prode-tab { border-bottom-color: transparent; color: var(--tw-prose-body); }
    .prode-tab.active { border-bottom-color: #8c52ff; color: #8c52ff; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body">
<?php require $root . '/views/partials/gtm-body.php'; ?>
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1400px] mx-auto space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold font-headline flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">emoji_events</span>
            Prode Deportivo
          </h1>
          <p class="text-on-surface-variant mt-1 text-sm">Administración del sistema de pronósticos.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <span id="prodeGlobalStatusBadge" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border">
            <span id="prodeGlobalStatusDot" class="w-2 h-2 rounded-full inline-block bg-gray-500"></span>
            <span id="prodeGlobalStatusText">Cargando...</span>
          </span>
          <button id="btnToggleProde" class="h-9 rounded-lg px-4 text-sm font-semibold border transition-colors bg-surface-container border-outline-variant/30 text-on-surface-variant">—</button>
          <a href="/prode" target="_blank" class="inline-flex items-center gap-1.5 h-9 rounded-lg bg-primary/15 border border-primary/30 px-4 text-sm font-semibold text-primary hover:bg-primary/25 transition-colors">
            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
            Ver Prode
          </a>
        </div>
      </div>

      <!-- Stats cards -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <div class="flex items-center gap-2 mb-1">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">group</span>
            <p class="text-xs text-on-surface-variant font-medium">Participantes</p>
          </div>
          <p id="statParticipantes" class="text-2xl font-bold font-headline">—</p>
        </div>
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <div class="flex items-center gap-2 mb-1">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">quiz</span>
            <p class="text-xs text-on-surface-variant font-medium">Pronósticos totales</p>
          </div>
          <p id="statPronosticos" class="text-2xl font-bold font-headline">—</p>
        </div>
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <div class="flex items-center gap-2 mb-1">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">event</span>
            <p class="text-xs text-on-surface-variant font-medium">Jornada activa</p>
          </div>
          <p id="statJornada" class="text-2xl font-bold font-headline">—</p>
        </div>
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <div class="flex items-center gap-2 mb-1">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">schedule</span>
            <p class="text-xs text-on-surface-variant font-medium">Deadline</p>
          </div>
          <p id="statDeadline" class="text-sm font-semibold font-headline mt-1">—</p>
        </div>
      </div>

      <!-- Tabs -->
      <div class="border-b border-outline-variant/20">
        <nav class="-mb-px flex gap-1 overflow-x-auto" id="prodeTabs">
          <button data-tab="fechas" class="prode-tab shrink-0 px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors hover:text-on-surface">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">calendar_month</span>Fechas</span>
          </button>
          <button data-tab="ranking" class="prode-tab shrink-0 px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors hover:text-on-surface">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">leaderboard</span>Ranking</span>
          </button>
          <button data-tab="participantes" class="prode-tab shrink-0 px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors hover:text-on-surface">
            <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[16px]">group</span>Participantes</span>
          </button>
        </nav>
      </div>

      <!-- Tab: Fechas -->
      <div id="tabFechas" class="prode-tab-content">
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
          <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-outline-variant/10">
            <div>
              <h2 class="font-bold font-headline text-base">Gestión de Jornadas</h2>
              <p class="text-xs text-on-surface-variant mt-0.5">Cerrá una fecha para calcular puntos automáticamente según los resultados cargados.</p>
            </div>
            <button id="btnRefreshFechas" class="flex items-center gap-1.5 text-xs text-on-surface-variant hover:text-on-surface transition-colors rounded-lg px-2 py-1.5 hover:bg-surface-container">
              <span class="material-symbols-outlined text-[16px]">refresh</span>Actualizar
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[720px]">
              <thead class="bg-surface-container text-xs uppercase text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold">Jornada</th>
                  <th class="px-4 py-3 font-semibold">Torneo</th>
                  <th class="px-4 py-3 font-semibold">Estado</th>
                  <th class="px-4 py-3 font-semibold">Deadline</th>
                  <th class="px-4 py-3 font-semibold text-right">Jugadores</th>
                  <th class="px-4 py-3 font-semibold text-right">Pronósticos</th>
                  <th class="px-4 py-3 font-semibold" colspan="2">Acción</th>
                </tr>
              </thead>
              <tbody id="tablaFechas" class="divide-y divide-outline-variant/10">
                <tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant text-sm">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab: Ranking -->
      <div id="tabRanking" class="prode-tab-content hidden">
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 justify-between px-4 sm:px-6 py-4 border-b border-outline-variant/10">
            <h2 class="font-bold font-headline text-base">Tabla de Posiciones</h2>
            <div class="flex items-center gap-2">
              <label class="text-xs text-on-surface-variant shrink-0">Ver jornada:</label>
              <select id="rankingJornadaSelect" class="h-9 rounded-lg border border-outline-variant/20 bg-surface-container px-3 text-sm text-on-surface min-w-[160px]">
                <option value="">General (acumulado)</option>
              </select>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[580px]">
              <thead class="bg-surface-container text-xs uppercase text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold w-14">#</th>
                  <th class="px-4 py-3 font-semibold">Alias</th>
                  <th class="px-4 py-3 font-semibold text-right">Puntos</th>
                  <th class="px-4 py-3 font-semibold text-right">Exactos</th>
                  <th class="px-4 py-3 font-semibold text-right" id="rankingColResultados">Resultados</th>
                  <th class="px-4 py-3 font-semibold text-right">Jugados</th>
                </tr>
              </thead>
              <tbody id="tablaRanking" class="divide-y divide-outline-variant/10">
                <tr><td colspan="6" class="px-4 py-10 text-center text-on-surface-variant text-sm">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab: Participantes -->
      <div id="tabParticipantes" class="prode-tab-content hidden">
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
          <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-outline-variant/10">
            <div>
              <h2 class="font-bold font-headline text-base">Participantes registrados</h2>
              <p id="totalParticipantesLabel" class="text-xs text-on-surface-variant mt-0.5"></p>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-[720px]">
              <thead class="bg-surface-container text-xs uppercase text-on-surface-variant">
                <tr>
                  <th class="px-4 py-3 font-semibold w-10">#</th>
                  <th class="px-4 py-3 font-semibold">Alias</th>
                  <th class="px-4 py-3 font-semibold">Email</th>
                  <th class="px-4 py-3 font-semibold text-right">Puntos</th>
                  <th class="px-4 py-3 font-semibold text-right">Exactos</th>
                  <th class="px-4 py-3 font-semibold text-right">Jugados</th>
                  <th class="px-4 py-3 font-semibold">Registrado</th>
                  <th class="px-4 py-3 font-semibold">Acción</th>
                </tr>
              </thead>
              <tbody id="tablaParticipantes" class="divide-y divide-outline-variant/10">
                <tr><td colspan="8" class="px-4 py-10 text-center text-on-surface-variant text-sm">Cargando...</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>
<script src="/frontend/app.js"></script>
<script src="/frontend/prode-admin.js"></script>
</body>
</html>
