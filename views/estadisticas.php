<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Estadísticas — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <meta name="description" content="Estadísticas completas de la liga: posiciones, goleadores, tarjetas, resultados e históricos.">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    select { background-color: #1a191b !important; color: #ffffff !important; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body min-h-screen">
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto">

      <div class="flex flex-col md:flex-row md:items-end justify-between gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-4xl font-black leading-tight tracking-[-0.033em] font-headline">Estadísticas de la Liga</h1>
          <p class="text-on-surface-variant mt-1 sm:mt-2 text-sm sm:text-base">Temporada 2025/26 • Clasificación en tiempo real</p>
        </div>
        <div class="flex gap-2">
          <div class="flex rounded-xl bg-primary/10 p-1">
            <button id="tabDataGeneral" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-primary text-on-primary text-xs sm:text-sm font-bold">General</button>
            <button id="tabDataLocal" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-primary text-xs sm:text-sm font-bold">Local</button>
            <button id="tabDataVisitante" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-primary text-xs sm:text-sm font-bold">Visitante</button>
          </div>
        </div>
      </div>

      <div class="mb-4 grid grid-cols-1 sm:grid-cols-[auto_1fr_auto_1fr_auto] gap-2 sm:gap-3 items-center">
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
        <button id="aplicarFiltro" class="bg-primary px-3 py-2 rounded-lg text-on-primary text-sm font-semibold w-full sm:w-auto hover:bg-primary-dim transition-colors">Aplicar</button>
      </div>

      <!-- Resultados por jornada -->
      <section id="resultadosJornada" class="bg-surface-container-high border border-outline-variant/10 p-4 rounded-xl mb-6 hidden">
        <h2 class="text-xl font-semibold mb-3 font-headline">Resultados por Jornada</h2>
        <div id="jornadaTabs" class="flex flex-wrap gap-2 mb-4"></div>
        <div id="jornadaResultados" class="space-y-2"></div>
      </section>

      <!-- Section tabs -->
      <section class="mb-4 sm:mb-6">
        <div class="flex border-b border-outline-variant/10 gap-2 sm:gap-5 md:gap-8 overflow-x-auto">
          <a class="flex items-center gap-1 sm:gap-2 border-b-[3px] border-primary pb-2 sm:pb-3 pt-2 text-primary shrink-0" href="#" id="navTabTabla">
            <span class="material-symbols-outlined text-[20px]">format_list_numbered</span>
            <span class="text-xs sm:text-sm font-bold leading-normal tracking-[0.015em] whitespace-nowrap hidden sm:inline">Tabla de Posiciones</span>
            <span class="text-xs font-bold sm:hidden">Tabla</span>
          </a>
          <a class="flex items-center gap-1 sm:gap-2 border-b-[3px] border-transparent pb-2 sm:pb-3 pt-2 text-on-surface-variant hover:text-primary transition-colors shrink-0" href="#" id="navTabGoleadores">
            <span class="material-symbols-outlined text-[20px]">sports_soccer</span>
            <span class="text-xs sm:text-sm font-bold leading-normal tracking-[0.015em] whitespace-nowrap hidden sm:inline">Goleadores</span>
            <span class="text-xs font-bold sm:hidden">Goles</span>
          </a>
          <a class="flex items-center gap-1 sm:gap-2 border-b-[3px] border-transparent pb-2 sm:pb-3 pt-2 text-on-surface-variant hover:text-primary transition-colors shrink-0" href="#" id="navTabTarjetas">
            <span class="material-symbols-outlined text-[20px]">style</span>
            <span class="text-xs sm:text-sm font-bold leading-normal tracking-[0.015em] whitespace-nowrap hidden sm:inline">Tarjetas</span>
            <span class="text-xs font-bold sm:hidden">Tarjetas</span>
          </a>
          <a class="flex items-center gap-1 sm:gap-2 border-b-[3px] border-transparent pb-2 sm:pb-3 pt-2 text-on-surface-variant hover:text-primary transition-colors shrink-0" href="#" id="navTabResultados">
            <span class="material-symbols-outlined text-[20px]">calendar_month</span>
            <span class="text-xs sm:text-sm font-bold leading-normal tracking-[0.015em] whitespace-nowrap hidden sm:inline">Resultados</span>
            <span class="text-xs font-bold sm:hidden">Fechas</span>
          </a>
          <a class="flex items-center gap-1 sm:gap-2 border-b-[3px] border-transparent pb-2 sm:pb-3 pt-2 text-on-surface-variant hover:text-primary transition-colors shrink-0" href="#" id="navTabHistoricos">
            <span class="material-symbols-outlined text-[20px]">history</span>
            <span class="text-xs sm:text-sm font-bold leading-normal tracking-[0.015em] whitespace-nowrap hidden sm:inline">Históricos</span>
            <span class="text-xs font-bold sm:hidden">H2H</span>
          </a>
        </div>
      </section>

      <!-- Tabla de posiciones -->
      <section id="seccionTabla" class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl mb-4 sm:mb-6">
        <h2 class="text-base sm:text-xl font-semibold mb-2 sm:mb-3 font-headline">Tabla de posiciones <span id="tablaCategoriaLabel" class="text-primary"></span></h2>
        <div class="overflow-x-auto -mx-3 sm:mx-0">
          <table class="w-full text-left border-collapse text-xs sm:text-sm min-w-[540px]">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase"><tr><th class="px-1 sm:px-2 py-1">#</th><th class="px-1 sm:px-2 py-1">Equipo</th><th class="px-1 sm:px-2 py-1">P</th><th class="px-1 sm:px-2 py-1">PJ</th><th class="px-1 sm:px-2 py-1">PG</th><th class="px-1 sm:px-2 py-1">PE</th><th class="px-1 sm:px-2 py-1">PP</th><th class="px-1 sm:px-2 py-1">GF</th><th class="px-1 sm:px-2 py-1">GC</th><th class="px-1 sm:px-2 py-1">DG</th></tr></thead>
            <tbody id="tablaPosicionesBody"></tbody>
          </table>
        </div>
      </section>

      <!-- Goleadores -->
      <section id="seccionGoleadores" class="hidden">
        <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl">
          <h2 class="font-semibold mb-2 text-sm sm:text-base font-headline">Ranking de goleadores</h2>
          <div class="overflow-x-auto -mx-3 sm:mx-0">
            <table class="w-full text-xs sm:text-sm min-w-[400px]">
              <thead class="bg-surface-container text-on-surface-variant text-xs uppercase"><tr><th class="px-1 sm:px-2 py-1">#</th><th class="px-1 sm:px-2 py-1">Jugador</th><th class="px-1 sm:px-2 py-1">Equipo</th><th class="px-1 sm:px-2 py-1">Goles</th></tr></thead>
              <tbody id="goleadoresBody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Tarjetas -->
      <section id="seccionTarjetas" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
          <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl">
            <h2 class="font-semibold mb-2 text-sm sm:text-base font-headline">Tarjetas amarillas</h2>
            <div class="overflow-x-auto -mx-3 sm:mx-0">
              <table class="w-full text-xs sm:text-sm min-w-[360px]">
                <thead class="bg-surface-container text-on-surface-variant text-xs uppercase"><tr><th class="px-1 sm:px-2 py-1">#</th><th class="px-1 sm:px-2 py-1">Jugador</th><th class="px-1 sm:px-2 py-1">Equipo</th><th class="px-1 sm:px-2 py-1">Amarillas</th></tr></thead>
                <tbody id="tarjetasAmarillasBody"></tbody>
              </table>
            </div>
          </div>
          <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl">
            <h2 class="font-semibold mb-2 text-sm sm:text-base font-headline">Tarjetas rojas</h2>
            <div class="overflow-x-auto -mx-3 sm:mx-0">
              <table class="w-full text-xs sm:text-sm min-w-[360px]">
                <thead class="bg-surface-container text-on-surface-variant text-xs uppercase"><tr><th class="px-1 sm:px-2 py-1">#</th><th class="px-1 sm:px-2 py-1">Jugador</th><th class="px-1 sm:px-2 py-1">Equipo</th><th class="px-1 sm:px-2 py-1">Rojas</th></tr></thead>
                <tbody id="tarjetasRojasBody"></tbody>
              </table>
            </div>
          </div>
        </div>
      </section>

      <!-- Históricos H2H -->
      <section id="seccionHistoricos" class="hidden">
        <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl mb-4 sm:mb-6">
          <h2 class="text-base sm:text-xl font-semibold mb-3 font-headline">Historial entre equipos</h2>
          <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr_auto] gap-2 sm:gap-3 items-end mb-4">
            <div>
              <label class="text-xs sm:text-sm font-semibold block mb-1">Categoría</label>
              <select id="h2hCategoria" class="border border-outline-variant/20 rounded-lg p-2 text-sm w-full">
                <option value="Primera">Primera</option>
                <option value="Tercera">Tercera</option>
                <option value="Senior">Senior</option>
              </select>
            </div>
            <div class="hidden sm:block"></div>
            <div class="hidden sm:block"></div>
            <div class="hidden sm:block"></div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr_auto] gap-2 sm:gap-3 items-end">
            <div>
              <label class="text-xs sm:text-sm font-semibold block mb-1">Equipo 1</label>
              <select id="h2hTeam1" class="border border-outline-variant/20 rounded-lg p-2 text-sm w-full">
                <option value="" disabled selected>Seleccionar equipo</option>
              </select>
            </div>
            <span class="text-on-surface-variant font-bold text-sm text-center py-2">vs</span>
            <div>
              <label class="text-xs sm:text-sm font-semibold block mb-1">Equipo 2</label>
              <select id="h2hTeam2" class="border border-outline-variant/20 rounded-lg p-2 text-sm w-full">
                <option value="" disabled selected>Seleccionar equipo</option>
              </select>
            </div>
            <button id="h2hComparar" class="bg-primary px-4 py-2 rounded-lg text-on-primary text-sm font-semibold w-full sm:w-auto hover:bg-primary-dim transition-colors">Comparar</button>
          </div>
        </div>
        <div id="h2hResumen" class="mb-4 sm:mb-6"></div>
        <div id="h2hPartidos" class="space-y-3"></div>
      </section>

    </div>
  </main>
</div>
<script src="/frontend/app.js"></script>
</body>
</html>
