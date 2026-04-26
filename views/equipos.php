<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Equipos — Juego de Equipo</title>
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
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto">
      <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-4 sm:mb-5 font-headline">Gestión de Equipos</h1>

      <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl mb-4">
        <form id="equipoForm" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
          <input id="equipoNombre" type="text" placeholder="Nombre" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary focus:border-transparent" required/>
          <input id="equipoEstadio" type="text" placeholder="Estadio" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary focus:border-transparent" required/>
          <input id="equipoEscudo" type="url" placeholder="URL del escudo" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary focus:border-transparent"/>
          <select id="equipoCategoria" class="bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-sm text-on-surface focus:ring-2 focus:ring-primary" required>
            <option value="">Categoría</option>
            <option value="Primera">Primera</option>
            <option value="Tercera">Tercera</option>
            <option value="Senior">Senior</option>
          </select>
          <button class="bg-primary text-on-primary rounded-lg px-4 py-2 text-sm font-semibold hover:bg-primary-dim transition-colors sm:col-span-2 md:col-span-1">Agregar equipo</button>
        </form>
      </div>

      <div class="bg-surface-container-high border border-outline-variant/10 p-3 sm:p-4 rounded-xl overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs sm:text-sm min-w-[480px]">
          <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
            <tr>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Escudo</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Nombre</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Estadio</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Categoría</th>
              <th class="px-2 py-1 sm:px-3 sm:py-2">Acciones</th>
            </tr>
          </thead>
          <tbody id="equiposTableBody"></tbody>
        </table>
      </div>

      <!-- Modal editar equipo -->
      <div id="equipoEditModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center p-3 sm:p-4 z-50">
        <div class="bg-surface-container-high border border-outline-variant/10 rounded-xl p-4 sm:p-6 max-w-md w-full">
          <h2 class="text-xl font-bold mb-4 font-headline">Editar Equipo</h2>
          <form id="editEquipoForm" class="space-y-3">
            <input type="hidden" id="equipoEditId"/>
            <input id="editEquipoNombre" type="text" placeholder="Nombre" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary" required/>
            <input id="editEquipoEstadio" type="text" placeholder="Estadio" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary" required/>
            <input id="editEquipoEscudo" type="url" placeholder="URL del escudo" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface placeholder:text-on-surface-variant focus:ring-2 focus:ring-primary"/>
            <select id="editEquipoCategoria" class="w-full bg-surface-container border border-outline-variant/20 rounded-lg p-2 text-on-surface focus:ring-2 focus:ring-primary" required>
              <option value="">Categoría</option>
              <option value="Primera">Primera</option>
              <option value="Tercera">Tercera</option>
              <option value="Senior">Senior</option>
            </select>
            <div class="flex justify-end gap-2 pt-2">
              <button type="button" id="closeEquipoModal" class="px-4 py-2 border border-outline-variant/20 rounded-lg text-on-surface-variant hover:bg-surface-container transition-colors">Cancelar</button>
              <button type="submit" class="px-4 py-2 bg-primary text-on-primary rounded-lg font-semibold hover:bg-primary-dim transition-colors">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="/frontend/app.js"></script>
</body>
</html>
