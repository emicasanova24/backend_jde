<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Jugadores — Juego de Equipo</title>
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
      <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-4 sm:mb-5 font-headline">Gestión de Jugadores</h1>

      <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl mb-4">
        <form id="jugadorForm" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
          <input id="jugadorNombre" type="text" placeholder="Nombre" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary focus:border-transparent" required/>
          <select id="jugadorPosicion" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface focus:ring-2 focus:ring-primary focus:border-transparent" required>
            <option value="" selected disabled>Seleccionar posición</option>
            <option value="PEND">Pendiente de asignación (PEND)</option>
            <option value="PT">Arquero (PT)</option>
            <option value="LIB">Líbero (LIB)</option>
            <option value="CT">Defensa Central (CT)</option>
            <option value="LAT">Defensa Lateral (CA/LAT)</option>
            <option value="MCD">Mediocampista Defensivo (MCD)</option>
            <option value="MC">Mediocampista Centro (MC)</option>
            <option value="CRR">Carrilero (LA/CRR)</option>
            <option value="INT">Volante Exterior (VOL/INT)</option>
            <option value="MP">Enganche (MO/MP)</option>
            <option value="EXT">Extremo (EXT)</option>
            <option value="SD">Media Punta (SP/SD)</option>
            <option value="DC">Delantero Centro (CD/DC)</option>
          </select>
          <select id="jugadorEquipo" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface focus:ring-2 focus:ring-primary"></select>
          <button class="bg-primary text-on-primary rounded-lg px-4 py-2 text-sm font-semibold hover:bg-primary-dim transition-colors">Agregar jugador</button>
        </form>
      </div>

      <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm min-w-[400px]">
          <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
            <tr>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Nombre</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Posición</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Equipo</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Acciones</th>
            </tr>
          </thead>
          <tbody id="jugadoresTableBody"></tbody>
        </table>
      </div>

      <!-- Modal editar jugador -->
      <div id="jugadorEditModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-3 sm:p-4 z-50">
        <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-4 sm:p-6 max-w-md w-full">
          <h2 class="text-xl font-bold mb-4 font-headline">Editar Jugador</h2>
          <form id="editJugadorForm" class="space-y-3">
            <input type="hidden" id="editJugadorId"/>
            <input id="editJugadorNombre" type="text" placeholder="Nombre" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary" required/>
            <select id="editJugadorPosicion" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface focus:ring-2 focus:ring-primary" required>
              <option value="" selected disabled>Seleccionar posición</option>
              <option value="PEND">Pendiente de asignación (PEND)</option>
              <option value="PT">Arquero (PT)</option>
              <option value="LIB">Líbero (LIB)</option>
              <option value="CT">Defensa Central (CT)</option>
              <option value="LAT">Defensa Lateral (CA/LAT)</option>
              <option value="MCD">Mediocampista Defensivo (MCD)</option>
              <option value="MC">Mediocampista Centro (MC)</option>
              <option value="CRR">Carrilero (LA/CRR)</option>
              <option value="INT">Volante Exterior (VOL/INT)</option>
              <option value="MP">Enganche (MO/MP)</option>
              <option value="EXT">Extremo (EXT)</option>
              <option value="SD">Media Punta (SP/SD)</option>
              <option value="DC">Delantero Centro (CD/DC)</option>
            </select>
            <select id="editJugadorEquipo" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface focus:ring-2 focus:ring-primary"></select>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" id="closeJugadorModal" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors">Cancelar</button>
              <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold hover:bg-primary-dim transition-colors">Guardar</button>
            </div>
          </form>
        </div>
      </div>

      <div id="jugadorDisciplinaModal" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center p-3 sm:p-4 z-50">
        <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-4 sm:p-6 max-w-4xl w-full max-h-[85vh] overflow-hidden flex flex-col">
          <div class="flex items-start justify-between gap-4 mb-4">
            <div>
              <h2 class="text-xl font-bold font-headline">Historial disciplinario</h2>
              <p id="jugadorDisciplinaSubtitle" class="text-sm text-on-surface-variant mt-1"></p>
            </div>
            <button id="closeJugadorDisciplinaModal" class="text-on-surface-variant hover:text-on-surface transition-colors">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl bg-surface-container border border-outline-variant/10 p-4">
              <p class="text-xs uppercase tracking-widest text-on-surface-variant font-bold">Total</p>
              <p id="disciplinaTotal" class="text-2xl font-black font-headline mt-1">0</p>
            </div>
            <div class="rounded-xl bg-yellow-500/10 border border-yellow-500/20 p-4">
              <p class="text-xs uppercase tracking-widest text-yellow-200 font-bold">Amarillas</p>
              <p id="disciplinaAmarillas" class="text-2xl font-black font-headline mt-1 text-yellow-300">0</p>
            </div>
            <div class="rounded-xl bg-red-500/10 border border-red-500/20 p-4">
              <p class="text-xs uppercase tracking-widest text-red-200 font-bold">Rojas</p>
              <p id="disciplinaRojas" class="text-2xl font-black font-headline mt-1 text-red-300">0</p>
            </div>
          </div>

          <div id="jugadorDisciplinaList" class="overflow-y-auto flex-1 rounded-xl border border-outline-variant/10 bg-surface-container p-3 sm:p-4">
            <p class="text-sm text-on-surface-variant">Seleccioná un jugador para ver su historial.</p>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="/frontend/app.js"></script>
</body>
</html>
