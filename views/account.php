<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mi Cuenta — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body selection:bg-primary-container/30">
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div id="accountPage" class="max-w-7xl mx-auto">

      <!-- Profile header -->
      <header class="mb-12 relative overflow-hidden p-8 rounded-xl bg-surface-container-low border border-outline-variant/10 shadow-2xl">
        <div class="absolute top-0 right-0 w-64 h-64 bg-primary/10 blur-[100px] rounded-full -mr-20 -mt-20"></div>
        <div class="relative flex flex-col md:flex-row items-center gap-8">
          <div class="relative group">
            <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-primary/20 p-1 bg-surface-container">
              <img class="w-full h-full rounded-full object-cover" data-account-avatar src="/assets/logo.png"/>
            </div>
            <button type="button" data-avatar-trigger class="absolute bottom-1 right-1 bg-primary text-on-primary p-2 rounded-full shadow-lg hover:scale-110 transition-transform">
              <span class="material-symbols-outlined text-sm">photo_camera</span>
            </button>
          </div>
          <div class="text-center md:text-left">
            <div class="flex flex-col md:flex-row md:items-baseline gap-2 md:gap-4">
              <h1 class="text-4xl md:text-5xl font-black font-headline tracking-tighter text-on-surface" data-account-name>Carlos Admin</h1>
              <span class="px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold uppercase tracking-widest font-headline" data-account-role>Administrador Principal</span>
            </div>
            <p class="mt-2 text-on-surface-variant font-medium font-headline">ID: <span class="text-primary" data-account-code>ADMIN-001</span></p>
            <div class="mt-4 flex gap-3 justify-center md:justify-start">
              <div class="px-4 py-2 bg-surface-container-highest rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">verified_user</span>
                <span class="text-xs font-bold uppercase tracking-tight text-on-surface-variant">Verificado</span>
              </div>
              <div class="px-4 py-2 bg-surface-container-highest rounded-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">calendar_today</span>
                <span class="text-xs font-bold uppercase tracking-tight text-on-surface-variant">Desde <span data-account-member-since>2023</span></span>
              </div>
            </div>
          </div>
        </div>
      </header>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        <!-- Personal info -->
        <section class="bg-surface-container-low rounded-xl p-8 border border-outline-variant/5">
          <div class="flex items-center gap-3 mb-8">
            <span class="material-symbols-outlined text-primary">person</span>
            <h2 class="text-xl font-bold font-headline tracking-tight">Información Personal</h2>
          </div>
          <form id="accountProfileForm" class="space-y-6">
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Nombre Completo</label>
              <input id="accountFullName" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium" type="text"/>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Correo Electrónico</label>
              <input id="accountEmail" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium" type="email"/>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Teléfono</label>
              <input id="accountPhone" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium" type="tel"/>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Biografía corta</label>
              <textarea id="accountBio" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium resize-none" rows="3"></textarea>
            </div>
            <input id="accountAvatarUrl" type="hidden"/>
            <p id="accountMessage" class="text-sm"></p>
          </form>
        </section>

        <!-- Security -->
        <section class="bg-surface-container-low rounded-xl p-8 border border-outline-variant/5 flex flex-col">
          <div class="flex items-center gap-3 mb-8">
            <span class="material-symbols-outlined text-primary">security</span>
            <h2 class="text-xl font-bold font-headline tracking-tight">Seguridad</h2>
          </div>
          <form id="accountPasswordForm" class="space-y-6 flex-grow">
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Contraseña Actual</label>
              <input id="accountCurrentPassword" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium" placeholder="••••••••••••" type="password"/>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Nueva Contraseña</label>
              <input id="accountNewPassword" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium" placeholder="Nueva contraseña" type="password"/>
            </div>
            <div>
              <label class="block text-xs font-bold uppercase text-on-surface-variant mb-2 tracking-widest font-headline">Confirmar Contraseña</label>
              <input id="accountConfirmPassword" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-on-surface focus:ring-2 focus:ring-primary transition-all font-medium" placeholder="Repetir nueva contraseña" type="password"/>
            </div>
            <p id="passwordMessage" class="text-sm"></p>
            <div class="mt-10">
              <button type="submit" class="w-full py-3 bg-primary/10 text-primary border border-primary/30 rounded-lg font-bold font-headline uppercase tracking-wider hover:bg-primary hover:text-on-primary transition-all active:scale-95">
                Cambiar Contraseña
              </button>
            </div>
          </form>
        </section>
      </div>

      <!-- Preferences -->
      <section class="mb-12 bg-surface-container-low rounded-xl p-8 border border-outline-variant/5">
        <div class="flex items-center gap-3 mb-8">
          <span class="material-symbols-outlined text-primary">tune</span>
          <h2 class="text-xl font-bold font-headline tracking-tight">Preferencias</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="flex items-center justify-between p-4 bg-surface-container-high rounded-xl">
            <span class="text-sm font-semibold font-headline text-on-surface-variant">Recibir notificaciones por correo</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input id="accountEmailNotifications" checked class="sr-only peer" type="checkbox"/>
              <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
          </div>
          <div class="flex items-center justify-between p-4 bg-surface-container-high rounded-xl">
            <span class="text-sm font-semibold font-headline text-on-surface-variant">Notificaciones en vivo</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input id="accountLiveNotifications" checked class="sr-only peer" type="checkbox"/>
              <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
          </div>
          <div class="flex items-center justify-between p-4 bg-surface-container-high rounded-xl">
            <span class="text-sm font-semibold font-headline text-on-surface-variant">Modo desarrollador</span>
            <label class="relative inline-flex items-center cursor-pointer">
              <input id="accountDeveloperMode" class="sr-only peer" type="checkbox"/>
              <div class="w-11 h-6 bg-surface-variant peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
            </label>
          </div>
        </div>
      </section>

      <!-- Users section -->
      <section class="mb-8 bg-surface-container-low rounded-xl p-8 border border-outline-variant/5">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary">group</span>
            <h2 class="text-xl font-bold font-headline tracking-tight">Usuarios</h2>
          </div>
          <button id="btnNuevoUsuario" type="button" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-primary text-on-primary text-sm font-bold hover:opacity-90 transition-opacity">
            <span class="material-symbols-outlined text-[18px]">person_add</span> Nuevo usuario
          </button>
        </div>
        <div id="usersTableWrap"><p class="text-on-surface-variant text-sm">Cargando…</p></div>
      </section>

      <!-- Action buttons -->
      <div class="flex flex-col md:flex-row items-center justify-end gap-4 mt-8 mb-8">
        <button type="button" data-action="logout" class="w-full md:w-auto px-8 py-4 border border-error/30 text-error rounded-xl font-bold font-headline uppercase tracking-widest hover:bg-error/10 transition-all flex items-center justify-center gap-2 active:scale-95">
          <span class="material-symbols-outlined text-sm">logout</span>
          Cerrar Sesión
        </button>
        <button type="submit" form="accountProfileForm" class="w-full md:w-auto px-12 py-4 bg-primary text-on-primary rounded-xl font-bold font-headline uppercase tracking-widest hover:shadow-[0_0_20px_rgba(140,82,255,0.4)] transition-all flex items-center justify-center gap-2 active:scale-95">
          <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1;">save</span>
          Guardar Cambios
        </button>
      </div>

    </div>
  </main>
</div>

<!-- Hidden file input for avatar -->
<input id="avatarFileInput" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="hidden"/>

<!-- Modal recorte de avatar -->
<div id="avatarCropModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/75 backdrop-blur-sm hidden p-4">
  <div class="bg-[#1e1830] rounded-2xl p-6 w-full max-w-sm shadow-2xl space-y-4">
    <h3 class="font-bold text-lg font-headline">Recortá tu foto</h3>
    <p class="text-xs text-on-surface-variant">Arrastrá para mover · Slider para zoom</p>
    <div class="relative rounded-xl overflow-hidden cursor-move select-none" id="cropContainer" style="height:280px;background:#0d0a14">
      <canvas id="cropCanvas" class="absolute inset-0 w-full h-full"></canvas>
      <div class="absolute inset-0 pointer-events-none" style="box-shadow:0 0 0 9999px rgba(0,0,0,0.6);border-radius:50%;margin:auto;width:220px;height:220px;top:0;bottom:0;left:0;right:0;border:2px dashed rgba(255,255,255,0.55)"></div>
    </div>
    <input id="cropZoom" type="range" min="1" max="5" step="0.02" value="1" class="w-full accent-primary"/>
    <div class="flex gap-3 justify-end">
      <button id="cropCancel" type="button" class="px-4 py-2 rounded-lg border border-white/10 text-sm font-semibold hover:bg-white/5 transition-colors">Cancelar</button>
      <button id="cropConfirm" type="button" class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-semibold hover:opacity-90 transition-opacity">
        <span id="cropConfirmText">Usar foto</span>
      </button>
    </div>
  </div>
</div>

<!-- Modal crear usuario -->
<div id="createUserModal" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/70 backdrop-blur-sm hidden p-4">
  <div class="bg-surface-container rounded-2xl p-6 w-full max-w-md shadow-2xl space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="font-bold text-lg font-headline">Nuevo Usuario</h3>
      <button id="closeCreateUserModal" type="button" class="text-on-surface-variant hover:text-on-surface"><span class="material-symbols-outlined">close</span></button>
    </div>
    <form id="createUserForm" class="space-y-4">
      <div>
        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Nombre completo</label>
        <input id="newUserName" type="text" required class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary"/>
      </div>
      <div>
        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Email</label>
        <input id="newUserEmail" type="email" required class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary"/>
      </div>
      <div>
        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Contraseña <span class="text-[10px] font-normal opacity-50">mín. 8 caracteres</span></label>
        <input id="newUserPassword" type="password" required minlength="8" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary"/>
      </div>
      <div>
        <label class="block text-xs font-semibold text-on-surface-variant mb-1">Rol</label>
        <select id="newUserRole" class="w-full bg-surface-container-highest border-none rounded-lg px-4 py-3 text-sm text-on-surface focus:ring-2 focus:ring-primary">
          <option value="editor">Editor</option>
          <option value="admin">Administrador</option>
        </select>
      </div>
      <p id="createUserMsg" class="text-sm hidden"></p>
      <div class="flex gap-3 justify-end pt-1">
        <button type="button" id="closeCreateUserModal2" class="px-4 py-2 rounded-lg border border-outline-variant/20 text-sm font-semibold hover:bg-surface-container transition-colors">Cancelar</button>
        <button type="submit" class="px-5 py-2 rounded-lg bg-primary text-on-primary text-sm font-semibold hover:opacity-90 transition-opacity">Crear usuario</button>
      </div>
    </form>
  </div>
</div>

<script src="/frontend/app.js"></script>
</body>
</html>
