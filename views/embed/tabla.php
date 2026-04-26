<!DOCTYPE html>

<html class="dark" lang="es"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;700;800;900&amp;family=Manrope:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #0e0e0f;
        }
        ::-webkit-scrollbar-thumb {
            background: #262627;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #8c52ff;
        }
    </style>
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "tertiary-fixed": "#ff8eae",
                        "primary-fixed": "#b08cff",
                        "tertiary-container": "#fa7ea3",
                        "error-container": "#a70138",
                        "on-error-container": "#ffb2b9",
                        "secondary-fixed-dim": "#dcb4ff",
                        "surface-container": "#1a191b",
                        "on-secondary": "#430474",
                        "primary-fixed-dim": "#a47bff",
                        "secondary-dim": "#bc86f0",
                        "surface-variant": "#262627",
                        "error": "#ff6e84",
                        "on-primary": "#3b008a",
                        "on-tertiary-fixed-variant": "#701037",
                        "tertiary": "#ff93b1",
                        "tertiary-dim": "#f0779b",
                        "on-primary-fixed-variant": "#380084",
                        "surface-container-high": "#201f21",
                        "on-surface": "#ffffff",
                        "on-tertiary": "#670731",
                        "surface-dim": "#0e0e0f",
                        "on-background": "#ffffff",
                        "on-tertiary-container": "#550026",
                        "surface-container-highest": "#262627",
                        "on-surface-variant": "#adaaab",
                        "surface-tint": "#bc9eff",
                        "inverse-on-surface": "#565556",
                        "surface-container-lowest": "#000000",
                        "on-primary-container": "#2d006d",
                        "primary-container": "#b08cff",
                        "inverse-primary": "#7232e4",
                        "on-secondary-container": "#e4c4ff",
                        "inverse-surface": "#fcf8f9",
                        "tertiary-fixed-dim": "#f77ca0",
                        "outline": "#767576",
                        "on-secondary-fixed-variant": "#6a369b",
                        "surface": "#0e0e0f",
                        "surface-bright": "#2c2c2d",
                        "on-error": "#490013",
                        "secondary-container": "#602b90",
                        "background": "#0e0e0f",
                        "primary-dim": "#884efb",
                        "on-secondary-fixed": "#4c137c",
                        "outline-variant": "#484849",
                        "primary": "#bc9eff",
                        "on-primary-fixed": "#000000",
                        "secondary-fixed": "#e5c6ff",
                        "error-dim": "#d73357",
                        "on-tertiary-fixed": "#380017",
                        "secondary": "#ca93fe",
                        "surface-container-low": "#131314"
                    },
                    fontFamily: {
                        "headline": ["Lexend"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    },
                    borderRadius: {"DEFAULT": "0.125rem", "lg": "0.25rem", "xl": "0.5rem", "full": "0.75rem"},
                },
            },
        }
    </script>
</head>
<body class="bg-background text-on-surface font-body selection:bg-primary/30">
<!-- TopNavBar Shell -->
<header class="bg-[#0e0e0f]/80 backdrop-blur-xl border-b border-[#8c52ff]/15 shadow-[0_8px_32px_rgba(140,82,255,0.08)] docked full-width top-0 z-50 transition-all duration-300 ease-in-out">
<div class="flex justify-between items-center w-full px-3 sm:px-6 md:px-8 py-3 sm:py-4 max-w-none">
<div class="flex items-center gap-3 sm:gap-8">
<img src="/assets/logo.png" alt="Juego de Equipo" class="h-8 sm:h-10 w-auto object-contain" />
<span class="text-lg sm:text-xl md:text-2xl font-black text-[#8c52ff] italic font-headline uppercase tracking-wider">JUEGO DE EQUIPO</span>
<nav class="hidden md:flex gap-6 items-center">
<a class="font-['Lexend'] uppercase tracking-wider text-sm text-[#adaaab] hover:text-white transition-colors" href="#">Dashboard</a>
<a class="font-['Lexend'] uppercase tracking-wider text-sm text-[#adaaab] hover:text-white transition-colors" href="#">Teams</a>
<a class="font-['Lexend'] uppercase tracking-wider text-sm text-[#adaaab] hover:text-white transition-colors" href="#">Players</a>
<a class="font-['Lexend'] uppercase tracking-wider text-sm text-[#adaaab] hover:text-white transition-colors" href="#">Matches</a>
<a class="font-['Lexend'] uppercase tracking-wider text-sm text-[#8c52ff] border-b-2 border-[#8c52ff] pb-1" href="#">Stats</a>
</nav>
</div>
<div class="flex items-center gap-4">
<button class="p-2 rounded-lg hover:bg-[#8c52ff]/10 text-on-surface-variant transition-all">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="p-2 rounded-lg hover:bg-[#8c52ff]/10 text-on-surface-variant transition-all">
<span class="material-symbols-outlined">account_circle</span>
</button>
</div>
</div>
</header>

<main class="max-w-[1440px] mx-auto px-3 sm:px-6 md:px-8 pt-20 sm:pt-24 pb-8 sm:pb-12">
      <div class="mb-6 sm:mb-12">
        <h1 class="font-headline text-2xl sm:text-4xl md:text-5xl font-black uppercase tracking-tighter mb-2">
          ESTADÍSTICAS GENERALES
        </h1>
        <span>Temporada 2026</span>
      </div>

      <h2>Tabla de Posiciones</h2>
      <div class="overflow-x-auto -mx-3 sm:mx-0">
      <table class="min-w-[540px]">
        
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-primary text-lg">01</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-primary" style="font-variation-settings:'FILL' 1;">stars</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Deportivo 17</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">1</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">1</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">2</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">1</td>
  <td class="px-4 py-5 text-center font-mono">+1</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">3</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">02</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Atlético Argentino</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">03</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Atlético Quenumá</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">04</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Deportivo Argentino</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">05</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Deportivo Garré</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">06</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Deportivo Maza</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">07</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">El Ceibo</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">08</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Jorge Newbery</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">09</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Juventud Unida</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">10</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">La Gloria</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">11</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Salazar FC</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">12</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Unión de Bonifacio</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">13</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Unión Deportiva</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">14</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Villa del Parque</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">0</td>
  <td class="px-4 py-5 text-center font-mono">+0</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
<tr class="group hover:bg-primary/5 transition-colors cursor-pointer">
  <td class="px-6 py-5 font-headline font-black text-on-surface-variant text-lg">15</td>
  <td class="px-4 py-5">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded bg-surface-container-highest flex items-center justify-center">
        <span class="material-symbols-outlined text-secondary" style="font-variation-settings:'FILL' 1;">shield</span>
      </div>
      <span class="font-bold text-on-surface group-hover:text-primary transition-colors">Cecil A. Roberts</span>
    </div>
  </td>
  <td class="px-4 py-5 text-center font-medium">1</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">0</td>
  <td class="px-4 py-5 text-center text-on-surface-variant">1</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">1</td>
  <td class="px-4 py-5 text-center text-on-surface-variant hidden sm:table-cell">2</td>
  <td class="px-4 py-5 text-center font-mono">-1</td>
  <td class="px-6 py-5 text-right font-headline font-bold text-xl text-on-surface">0</td>
</tr>
      </table>
      </div>

      <h2>Resultados – Fecha 1</h2>
      
<div class="bg-surface-container-low p-5 rounded-xl border border-outline-variant/5 hover:bg-surface-container-high transition-all">
  <div class="flex justify-between items-center mb-4">
    <span class="text-[10px] font-black uppercase text-on-surface-variant bg-surface-container-highest px-2 py-0.5 rounded">15:30</span>
    <span class="text-[10px] font-bold text-on-surface-variant uppercase">Estadio de Cecil A. Roberts</span>
  </div>
  <div class="flex items-center justify-between opacity-80">
    <div class="flex flex-col items-center gap-2 w-1/3">
      <div class="w-12 h-12 rounded-full bg-surface-container-highest flex items-center justify-center border border-outline-variant/10">
        <span class="material-symbols-outlined text-primary text-3xl">sports_soccer</span>
      </div>
      <span class="text-xs font-bold text-center">Cecil A. Roberts</span>
    </div>
    <div class="flex flex-col items-center gap-1 w-1/3">
      <div class="font-headline text-3xl font-black tracking-tighter flex items-center gap-3">
        <span class="text-primary">1</span>
        <span class="text-on-surface-variant text-sm font-normal">:</span>
        <span>2</span>
      </div>
    </div>
    <div class="flex flex-col items-center gap-2 w-1/3">
      <div class="w-12 h-12 rounded-full bg-surface-container-highest flex items-center justify-center border border-outline-variant/10">
        <span class="material-symbols-outlined text-secondary text-3xl">groups</span>
      </div>
      <span class="text-xs font-bold text-center">Deportivo 17</span>
    </div>
  </div>
</div>
</main>

<footer class="bg-surface-container-lowest mt-8 sm:mt-12 py-8 sm:py-12 px-3 sm:px-6 md:px-8 border-t border-outline-variant/10">
<div class="max-w-[1440px] mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 md:gap-12">
<div class="col-span-1 sm:col-span-2">
<div class="flex items-center gap-2 sm:gap-3 mb-4">
<img src="/assets/logo.png" alt="Juego de Equipo" class="h-8 sm:h-10 w-auto object-contain" />
<span class="text-lg sm:text-2xl font-black text-[#8c52ff] italic font-headline uppercase tracking-wider">JUEGO DE EQUIPO</span>
</div>
<p class="text-on-surface-variant text-sm max-w-sm">
                    La plataforma definitiva para el seguimiento estadístico y gestión de ligas deportivas profesionales. Rendimiento, precisión y pasión en cada jugada.
                </p>
</div>
<div>
<h5 class="font-headline text-xs font-bold uppercase mb-4 text-on-surface">Enlaces Rápidos</h5>
<ul class="space-y-2 text-on-surface-variant text-sm font-medium">
<li><a class="hover:text-primary transition-colors" href="#">Reglamento</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Afiliaciones</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Prensa</a></li>
<li><a class="hover:text-primary transition-colors" href="#">Contacto</a></li>
</ul>
</div>
<div>
<h5 class="font-headline text-xs font-bold uppercase mb-4 text-on-surface">Redes Sociales</h5>
<div class="flex gap-4">
<a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary transition-all" href="#">
<span class="material-symbols-outlined text-lg">public</span>
</a>
<a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary transition-all" href="#">
<span class="material-symbols-outlined text-lg">share</span>
</a>
<a class="w-10 h-10 rounded-full bg-surface-container-high flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary transition-all" href="#">
<span class="material-symbols-outlined text-lg">videocam</span>
</a>
</div>
</div>
</div>
<div class="max-w-[1440px] mx-auto mt-8 sm:mt-12 pt-6 sm:pt-8 border-t border-outline-variant/5 text-center text-on-surface-variant text-[10px] uppercase tracking-widest font-bold">
            © 2024 JUEGO DE EQUIPO - TODOS LOS DERECHOS RESERVADOS
        </div>
</footer>
<!-- BottomNavBar (Mobile Only) -->
<nav class="md:hidden fixed bottom-0 left-0 w-full flex justify-around items-center px-4 py-3 pb-safe bg-[#0e0e0f]/90 backdrop-blur-lg border-t border-[#8c52ff]/10 shadow-[0_-4px_24px_rgba(0,0,0,0.5)] z-50 rounded-t-2xl">
<a class="flex flex-col items-center justify-center text-[#adaaab] p-2 active:scale-95 transition-transform hover:scale-110" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="font-['Manrope'] text-[10px] font-bold uppercase mt-1">Dashboard</span>
</a>
<a class="flex flex-col items-center justify-center text-[#adaaab] p-2 active:scale-95 transition-transform hover:scale-110" href="#">
<span class="material-symbols-outlined">groups</span>
<span class="font-['Manrope'] text-[10px] font-bold uppercase mt-1">Teams</span>
</a>
<a class="flex flex-col items-center justify-center text-[#adaaab] p-2 active:scale-95 transition-transform hover:scale-110" href="#">
<span class="material-symbols-outlined">person</span>
<span class="font-['Manrope'] text-[10px] font-bold uppercase mt-1">Players</span>
</a>
<a class="flex flex-col items-center justify-center text-[#adaaab] p-2 active:scale-95 transition-transform hover:scale-110" href="#">
<span class="material-symbols-outlined">sports_soccer</span>
<span class="font-['Manrope'] text-[10px] font-bold uppercase mt-1">Matches</span>
</a>
<a class="flex flex-col items-center justify-center bg-[#8c52ff]/20 text-[#8c52ff] rounded-xl p-2 active:scale-95 transition-transform hover:scale-110" href="#">
<span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">leaderboard</span>
<span class="font-['Manrope'] text-[10px] font-bold uppercase mt-1">Stats</span>
</a>
</nav>
</body></html>