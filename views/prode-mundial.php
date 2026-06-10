<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prode Mundial 2026 — Juego de Equipo</title>
  <link rel="icon" href="/assets/favicon.ico" sizes="any">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/assets/favicon-16x16.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/assets/apple-touch-icon.png">
  <link rel="manifest" href="/assets/site.webmanifest">
  <meta name="theme-color" content="#8c52ff">
  <meta property="og:title" content="Prode del Mundial 2026 — Juego de Equipo">
  <meta property="og:description" content="Pronosticá los partidos del Mundial FIFA 2026 y competí en el ranking de Juego de Equipo.">
  <meta property="og:image" content="/assets/share-default-prode.png">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="/assets/share-default-prode.png">
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    input { background-color: transparent !important; color: #f3f4f6; }
    input::placeholder { color: #6b7280; }
    .toast { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 100; padding: 0.75rem 1.5rem; border-radius: 0.5rem; color: white; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s ease; pointer-events: none; }
    .toast.show { transform: translateY(0); opacity: 1; }
    .toast-success { background-color: #16a34a; }
    .toast-error { background-color: #dc2626; }
    .score-stepper { display: flex; align-items: center; background: rgba(0,0,0,0.45); border-radius: 0.625rem; border: 1.5px solid rgba(255,255,255,0.1); overflow: hidden; }
    .stepper-btn { width: 2.25rem; height: 3rem; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 600; line-height: 1; color: rgba(255,255,255,0.3); cursor: pointer; transition: background 0.15s, color 0.15s; background: transparent; border: none; user-select: none; -webkit-tap-highlight-color: transparent; flex-shrink: 0; }
    .stepper-btn:hover:not(:disabled) { color: #8c52ff; background: rgba(140,82,255,0.15); }
    .stepper-btn:active:not(:disabled) { background: rgba(140,82,255,0.25); }
    .stepper-btn:disabled { opacity: 0.2; cursor: not-allowed; }
    .score-input { width: 2.5rem; height: 3rem; text-align: center; font-size: 1.75rem; font-weight: 900; background: transparent !important; border: none; color: #f9fafb; padding: 0; line-height: 1; -moz-appearance: textfield; }
    .score-input::-webkit-outer-spin-button, .score-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
    .score-input:focus { outline: none; color: #8c52ff; }
    .score-input:disabled { color: rgba(255,255,255,0.35); cursor: not-allowed; }
    .tab-btn { border-bottom: 2px solid transparent; padding: 0.5rem 1rem; font-weight: 600; transition: all 0.2s; }
    .tab-btn.active { border-bottom-color: #8c52ff; color: #8c52ff; }
    .team-flag { width: 40px; height: 28px; flex-shrink: 0; object-fit: cover; border-radius: 3px; }
    .partido-card { transition: all 0.2s; }
    .partido-card:hover { border-color: rgba(140,82,255,0.4); }
    .partido-card.bloqueado { opacity: 0.7; }
    .grupo-chip { display: inline-block; padding: 1px 8px; border-radius: 999px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em; background: rgba(140,82,255,0.15); color: #a78bfa; border: 1px solid rgba(140,82,255,0.3); }
    .puntos-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 1.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; padding: 0 0.5rem; }
    .puntos-4 { background: #22c55e22; color: #22c55e; border: 1px solid #22c55e55; }
    .puntos-1 { background: #f59e0b22; color: #f59e0b; border: 1px solid #f59e0b55; }
    .puntos-0 { background: #ef444422; color: #ef4444; border: 1px solid #ef444455; }
    .top-1 { color: #facc15; }
    .top-2 { color: #94a3b8; }
    .top-3 { color: #b87333; }
    @keyframes pulse-border { 0%,100%{ border-color: rgba(140,82,255,0.3); } 50%{ border-color: rgba(140,82,255,0.8); } }
    .pulsing { animation: pulse-border 2s infinite; }
    /* Copa del Mundo gradient header */
    .mundial-header-badge { background: linear-gradient(135deg, #c6992a, #f5d57a, #c6992a); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body min-h-screen">
<?php require $root . '/views/partials/gtm-body.php'; ?>

<div class="max-w-2xl mx-auto px-4 py-6">

  <!-- Header -->
  <header class="flex items-center gap-2 mb-6 pb-3 border-b border-outline-variant/15">
    <a href="/" class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full hover:bg-white/5 transition-colors text-on-surface-variant">
      <span class="material-symbols-outlined text-[20px]">arrow_back</span>
    </a>
    <img src="/assets/logo.png" alt="Juego de Equipo" class="h-6 sm:h-8 w-auto object-contain flex-shrink-0"/>
    <div class="flex-1 min-w-0">
      <h1 class="text-sm sm:text-2xl font-black font-headline tracking-tight leading-tight mundial-header-badge">
        Prode Mundial 2026
      </h1>
      <p class="text-on-surface-variant text-[11px] sm:text-sm leading-tight truncate">FIFA World Cup · <span id="headerRonda">—</span></p>
    </div>
    <div class="flex-shrink-0">
      <button id="btnLogout" class="hidden items-center gap-1 text-[11px] sm:text-xs font-semibold text-on-surface-variant hover:text-error transition-colors px-2 py-1 rounded-lg hover:bg-error/5">
        <span class="material-symbols-outlined text-[14px] sm:text-[16px]">logout</span>
        Salir
      </button>
    </div>
  </header>

  <!-- ===================== SECCIÓN VERIFICACIÓN ===================== -->
  <section id="seccionVerificacion" class="hidden">
    <div class="bg-surface-container-high border border-outline-variant/20 rounded-2xl p-6">
      <div class="flex items-center gap-3 mb-5">
        <span class="material-symbols-outlined text-[#f5d57a] text-3xl">emoji_events</span>
        <div>
          <h2 class="text-lg font-bold font-headline">Ingresá al Prode</h2>
          <p class="text-on-surface-variant text-sm">Pronosticá el Mundial 2026</p>
        </div>
      </div>

      <!-- Paso 0 -->
      <div id="paso0">
        <p class="text-sm text-on-surface-variant mb-4">¿Cómo querés ingresar?</p>
        <div class="grid grid-cols-2 gap-3">
          <button onclick="mostrarPaso1('nuevo')"
            class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-outline-variant/30 hover:border-primary/50 hover:bg-primary/5 transition-all">
            <span class="material-symbols-outlined text-primary text-3xl">person_add</span>
            <div class="text-center">
              <p class="font-bold text-sm">Primera vez</p>
              <p class="text-xs text-on-surface-variant mt-0.5">Quiero registrarme</p>
            </div>
          </button>
          <button onclick="mostrarPaso1('relogin')"
            class="flex flex-col items-center gap-3 p-5 rounded-2xl border border-outline-variant/30 hover:border-primary/50 hover:bg-primary/5 transition-all">
            <span class="material-symbols-outlined text-primary text-3xl">person</span>
            <div class="text-center">
              <p class="font-bold text-sm">Ya jugué</p>
              <p class="text-xs text-on-surface-variant mt-0.5">Continuar con mi cuenta</p>
            </div>
          </button>
        </div>
      </div>

      <!-- Paso 1 -->
      <div id="paso1" class="hidden">
        <button onclick="mostrarVerificacion()" class="flex items-center gap-1 text-xs text-on-surface-variant hover:text-on-surface mb-5 transition-colors">
          <span class="material-symbols-outlined text-[16px]">arrow_back</span>
          Volver
        </button>
        <div class="space-y-4">
          <div id="aliasGroup">
            <label class="block text-sm font-semibold mb-1">Alias público</label>
            <input id="inputAlias" type="text" placeholder="Tu nombre en el ranking (ej: Chapita10)" maxlength="30"
              class="w-full border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary/60 focus:ring-1 focus:ring-primary/30"/>
            <p class="text-xs text-on-surface-variant mt-1">Así vas a aparecer en el ranking.</p>
          </div>
          <div>
            <label class="block text-sm font-semibold mb-1">Correo electrónico</label>
            <input id="inputEmail" type="email" placeholder="tucorreo@email.com"
              class="w-full border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary/60 focus:ring-1 focus:ring-primary/30"/>
            <p class="text-xs text-on-surface-variant mt-1">Tu email es tu identidad. No se muestra en el ranking.</p>
          </div>
          <button id="btnRegistrar"
            class="w-full py-3 rounded-xl font-bold text-sm bg-primary text-white hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">login</span>
            <span id="btnRegistrarLabel">Entrar al Prode Mundial</span>
          </button>
        </div>
      </div>

      <div id="loadingVerificacion" class="hidden text-center py-4">
        <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-on-surface-variant mt-2">Ingresando...</p>
      </div>
    </div>
  </section>

  <!-- ===================== SECCIÓN PRODE (post-login) ===================== -->
  <section id="seccionProde" class="hidden">

    <!-- Bienvenida -->
    <div id="bienvenidaBanner" class="flex items-center gap-3 bg-primary/10 border border-primary/20 rounded-xl px-4 py-3 mb-5">
      <span class="material-symbols-outlined text-[#f5d57a]">emoji_events</span>
      <div class="flex-1 min-w-0">
        <p class="font-semibold text-sm">Hola, <span id="aliasDisplay" class="text-primary"></span></p>
        <p id="deadlineBanner" class="text-xs text-on-surface-variant truncate"></p>
      </div>
    </div>

    <!-- Tabs -->
    <div class="flex border-b border-outline-variant/20 mb-5 gap-0">
      <button class="tab-btn active text-sm" data-tab="pronosticar" onclick="switchTab('pronosticar')">
        <span class="material-symbols-outlined text-[16px] align-middle mr-1">sports_soccer</span>Pronosticar
      </button>
      <button class="tab-btn text-sm text-on-surface-variant" data-tab="misPronosticos" onclick="switchTab('misPronosticos')">
        <span class="material-symbols-outlined text-[16px] align-middle mr-1">fact_check</span>Mis resultados
      </button>
      <button class="tab-btn text-sm text-on-surface-variant" data-tab="ranking" onclick="switchTab('ranking')">
        <span class="material-symbols-outlined text-[16px] align-middle mr-1">leaderboard</span>Ranking
      </button>
    </div>

    <!-- TAB: Pronosticar -->
    <div id="tabPronosticar">
      <div id="rondaHeader" class="mb-4">
        <div class="flex items-center justify-between">
          <h2 class="font-bold font-headline text-lg" id="rondaTitulo">Cargando ronda...</h2>
          <div id="deadlineChip" class="text-xs px-3 py-1 rounded-full font-semibold"></div>
        </div>
      </div>

      <div id="loadingPartidos" class="text-center py-8">
        <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-on-surface-variant mt-2">Cargando partidos...</p>
      </div>

      <div id="listaPartidos" class="space-y-3 hidden"></div>

      <div id="noRonda" class="hidden text-center py-8 text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl mb-2 block">sports_soccer</span>
        <p class="text-sm">No hay rondas activas en este momento</p>
        <p class="text-xs mt-1 text-on-surface-variant/60">El administrador debe abrir una ronda para poder pronosticar</p>
      </div>

      <div id="btnGuardarContainer" class="mt-5 hidden">
        <button id="btnGuardarPronosticos"
          class="w-full py-3 rounded-xl font-bold text-sm bg-primary text-white hover:bg-primary/90 transition-colors flex items-center justify-center gap-2">
          <span class="material-symbols-outlined text-[18px]">save</span>
          Guardar pronósticos
        </button>
      </div>
    </div>

    <!-- TAB: Mis resultados -->
    <div id="tabMisPronosticos" class="hidden">
      <div id="loadingMisPronosticos" class="text-center py-8">
        <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-on-surface-variant mt-2">Cargando...</p>
      </div>
      <div id="listaMisPronosticos" class="space-y-3 hidden"></div>
      <div id="noMisPronosticos" class="hidden text-center py-8 text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl mb-2 block">assignment</span>
        <p class="text-sm">Todavía no cargaste pronósticos para esta ronda</p>
      </div>
      <div id="totalPuntosContainer" class="hidden mt-4 text-right">
        <p class="text-sm text-on-surface-variant">Total: <span id="totalPuntosText" class="text-xl font-black text-primary">0</span> pts</p>
      </div>
    </div>

    <!-- TAB: Ranking -->
    <div id="tabRanking" class="hidden">
      <div id="loadingRanking" class="text-center py-8">
        <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        <p class="text-sm text-on-surface-variant mt-2">Cargando ranking...</p>
      </div>
      <div id="tablaRanking" class="hidden"></div>
      <div id="noRanking" class="hidden text-center py-8 text-on-surface-variant">
        <span class="material-symbols-outlined text-4xl mb-2 block">leaderboard</span>
        <p class="text-sm">Todavía no hay puntos calculados</p>
      </div>
    </div>

  </section>

  <!-- Ranking público (sin login) -->
  <section id="seccionRankingPublico" class="hidden">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-bold font-headline text-lg">Ranking Mundial 2026</h2>
    </div>
    <div id="loadingPublicRanking" class="text-center py-8">
      <div class="inline-block w-6 h-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
    </div>
    <div id="tablaPublicRanking" class="hidden"></div>
    <div class="mt-6 text-center">
      <button onclick="mostrarVerificacion()" class="px-6 py-3 rounded-xl font-bold text-sm bg-primary text-white hover:bg-primary/90 transition-colors">
        Participar en el Prode Mundial
      </button>
    </div>
  </section>

</div><!-- end max-w-2xl -->

<!-- ===================== FOOTER ===================== -->
<footer class="border-t border-white/5 pt-8 pb-10 mt-4">
  <div class="max-w-2xl mx-auto px-4">
    <div class="flex items-center gap-2 mb-3">
      <img src="/assets/logo.png" alt="Juego de Equipo" class="h-8 w-auto object-contain"/>
    </div>
    <p class="text-sm text-on-surface-variant mb-5">La liga de fútbol. Seguí todos los resultados, posiciones y goleadores.</p>
    <div class="flex gap-3 mb-6">
      <a class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-all" href="https://www.instagram.com/juegodequipo/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
        <span class="material-symbols-outlined text-[18px]">photo_camera</span>
      </a>
      <a class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-all" href="https://www.facebook.com/juegodquipo" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
        <svg viewBox="0 0 24 24" class="h-[18px] w-[18px] fill-current" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3h-3.1V8.1c0-.9.3-1.6 1.6-1.6h1.7V3.8c-.3 0-1.3-.1-2.5-.1-2.4 0-4.1 1.4-4.1 4.2V10H7.9v3h2.9v8h2.7z"/></svg>
      </a>
      <a class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center hover:bg-primary transition-all" href="https://www.youtube.com/@JuegodeEquipo" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
        <span class="material-symbols-outlined text-[18px]">smart_display</span>
      </a>
    </div>
    <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm text-on-surface-variant mb-6">
      <a href="/" class="hover:text-on-surface transition-colors">Inicio</a>
      <a href="/prode" class="hover:text-on-surface transition-colors">Prode Liga</a>
      <a href="/#fixture" class="hover:text-on-surface transition-colors">Fixture</a>
      <a href="/#tabla" class="hover:text-on-surface transition-colors">Posiciones</a>
      <a href="/#goleadores" class="hover:text-on-surface transition-colors">Goleadores</a>
    </div>
    <p class="text-xs text-on-surface-variant/50">&copy; 2026 Juego de Equipo. Todos los derechos reservados.</p>
  </div>
</footer>

<!-- Toast -->
<div id="toast" class="toast"></div>

<script src="/frontend/prode-mundial.js"></script>
</body>
</html>
