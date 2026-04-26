<!-- Shared Sidebar + Header — inyectado por app.js en #sharedLayout -->

<!-- Header sticky -->
<header id="sidebarHeader" class="sticky top-0 z-50 flex h-16 w-full items-center justify-between border-b border-outline-variant/20 bg-surface/80 px-4 backdrop-blur-md lg:px-8">
  <div class="flex items-center gap-4 lg:gap-8">
    <button id="sidebarToggle" class="flex h-10 w-10 items-center justify-center rounded-xl hover:bg-surface-container-high lg:hidden">
      <span class="material-symbols-outlined">menu</span>
    </button>
    <div class="flex items-center text-primary">
      <img src="/assets/logo.png" alt="Juego de Equipo" class="h-10 w-auto object-contain" />
    </div>
  </div>
  <div class="flex items-center gap-2 lg:gap-4">
    <button class="flex h-10 w-10 items-center justify-center rounded-xl hover:bg-surface-container-high">
      <span class="material-symbols-outlined">notifications</span>
    </button>
    <div class="h-8 w-[1px] bg-outline-variant/30 hidden lg:block"></div>
    <a href="/account" class="flex items-center gap-3 pl-2 hover:opacity-80 transition-opacity">
      <div class="hidden text-right lg:block">
        <p class="text-sm font-semibold text-on-surface font-headline" data-account-name>Administrador</p>
        <p class="text-xs text-on-surface-variant font-body" data-account-role>Administrador Principal</p>
      </div>
      <div class="h-10 w-10 overflow-hidden rounded-full border-2 border-primary/20">
        <img data-account-avatar src="/assets/logo.png" alt="Avatar" class="h-full w-full object-cover" />
      </div>
    </a>
  </div>
</header>

<!-- Sidebar overlay (mobile) -->
<div id="sidebarOverlay" class="fixed inset-0 z-40 bg-black/50 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebarNav" class="fixed top-0 left-0 z-40 h-full w-64 -translate-x-full flex-col border-r border-outline-variant/20 bg-surface transition-transform duration-300 lg:static lg:translate-x-0 lg:flex">
  <!-- Mobile close -->
  <div class="flex h-16 items-center justify-between border-b border-outline-variant/20 px-4 lg:hidden">
    <span class="text-lg font-bold font-headline text-primary">Menú</span>
    <button id="sidebarClose" class="flex h-10 w-10 items-center justify-center rounded-xl hover:bg-surface-container-high">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>

  <nav class="flex-1 space-y-1 px-4 py-6 overflow-y-auto">
    <a data-nav-page="dashboard" href="/dashboard" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">dashboard</span>
      <span class="font-medium">Panel Principal</span>
    </a>
    <a data-nav-page="equipos" href="/equipos" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">groups</span>
      <span class="font-medium">Equipos</span>
    </a>
    <a data-nav-page="jugadores" href="/jugadores" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">person</span>
      <span class="font-medium">Jugadores</span>
    </a>
    <a data-nav-page="partidos" href="/partidos" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">calendar_today</span>
      <span class="font-medium">Partidos</span>
    </a>
    <a data-nav-page="carga" href="/carga" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">edit_note</span>
      <span class="font-medium">Carga de Datos</span>
    </a>
    <a data-nav-page="noticias" href="/noticias" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">article</span>
      <span class="font-medium">Noticias</span>
    </a>
    <a data-nav-page="estadisticas" href="/estadisticas" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">bar_chart</span>
      <span class="font-medium">Estadísticas</span>
    </a>
    <a data-nav-page="account" href="/account" class="sidebar-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-all font-body">
      <span class="material-symbols-outlined">account_circle</span>
      <span class="font-medium">Mi Cuenta</span>
    </a>
  </nav>

  <div class="px-4 pb-6">
    <button type="button" data-action="logout" class="flex w-full items-center justify-center gap-2 rounded-xl border border-error/30 px-3 py-2.5 text-sm font-semibold text-error hover:bg-error/10 transition-all font-body">
      <span class="material-symbols-outlined">logout</span>
      Cerrar sesión
    </button>
  </div>
</aside>
