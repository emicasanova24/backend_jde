/**
 * Generador de Semanario HTML — Juego de Equipo
 *
 * Descarga posiciones, goleadores, tarjetas y resultados para cada
 * categoría y genera un HTML standalone listo para publicar.
 *
 * Uso:
 *   node src/scripts/generar-semanario.js
 *   node src/scripts/generar-semanario.js --torneo Apertura
 *   node src/scripts/generar-semanario.js --output mi-semanario.html
 */

const fs = require("fs");
const path = require("path");

const BASE_URL = process.env.API_URL || "http://localhost:3000";
const CATEGORIAS = ["Primera", "Tercera", "Senior"];

const args = process.argv.slice(2);
const getArg = (flag) => {
  const idx = args.indexOf(flag);
  return idx !== -1 && args[idx + 1] ? args[idx + 1] : null;
};

const TORNEO = getArg("--torneo") || "";
const OUTPUT =
  getArg("--output") ||
  `semanario-${new Date().toISOString().slice(0, 10)}.html`;

async function fetchJson(urlPath) {
  const res = await fetch(`${BASE_URL}${urlPath}`);
  if (!res.ok) throw new Error(`HTTP ${res.status} en ${urlPath}`);
  return res.json();
}

async function main() {
  console.log("⚽ Generando semanario…");
  console.log(`   API: ${BASE_URL}`);
  console.log(`   Torneo: ${TORNEO || "(todos)"}`);

  const data = {};

  for (const cat of CATEGORIAS) {
    const qs = `categoria=${encodeURIComponent(cat)}`;
    const tqs = TORNEO ? `&torneo=${encodeURIComponent(TORNEO)}` : "";

    const [tabla, goleadores, tarjetas, partidos, jornadas] =
      await Promise.all([
        fetchJson(`/tabla?${qs}`),
        fetchJson(`/tabla/goleadores?${qs}`),
        fetchJson(`/tabla/tarjetas?${qs}`),
        fetchJson(`/partidos?${qs}${tqs}`),
        fetchJson(`/partidos/jornadas?${qs}${tqs}`),
      ]);

    // Make escudo URLs absolute so standalone HTML can load them
    partidos.forEach((p) => {
      if (p.localEscudo && p.localEscudo.startsWith("/"))
        p.localEscudo = BASE_URL + p.localEscudo;
      if (p.visitanteEscudo && p.visitanteEscudo.startsWith("/"))
        p.visitanteEscudo = BASE_URL + p.visitanteEscudo;
    });

    data[cat] = { tabla, goleadores, tarjetas, partidos, jornadas };
    console.log(
      `   ✓ ${cat}: ${tabla.length} equipos, ${partidos.length} partidos, ${goleadores.length} goleadores`
    );
  }

  const dateStr = new Date().toLocaleDateString("es-AR", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  const html = buildHtml(data, dateStr);
  const outPath = path.resolve(process.cwd(), OUTPUT);
  fs.writeFileSync(outPath, html, "utf-8");
  console.log(`\n✅ ${outPath}`);
  console.log(`   ${(Buffer.byteLength(html) / 1024).toFixed(1)} KB`);
}

/* ------------------------------------------------------------------ */
/*  HTML builder                                                       */
/* ------------------------------------------------------------------ */

function buildHtml(data, dateStr) {
  const esc = (s) =>
    String(s ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");

  return `<!DOCTYPE html>
<html class="dark" lang="es">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Semanario Deportivo — Juego de Equipo</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"><\/script>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script>
tailwind.config={darkMode:"class",theme:{extend:{colors:{"surface-container":"#1a191b","surface":"#0e0e0f","on-surface":"#ffffff","on-surface-variant":"#adaaab","surface-container-high":"#201f21","surface-container-highest":"#262627","surface-container-low":"#131314","surface-variant":"#262627","primary":"#8c52ff","primary-dim":"#884efb","on-primary":"#3b008a","primary-container":"#b08cff","secondary":"#ca93fe","tertiary":"#ff93b1","error":"#ff6e84","outline":"#767576","outline-variant":"#484849","background":"#0e0e0f","on-background":"#ffffff"},fontFamily:{headline:["Lexend","sans-serif"],body:["Manrope","sans-serif"],label:["Manrope","sans-serif"]},borderRadius:{DEFAULT:"0.25rem",lg:"0.5rem",xl:"0.75rem","2xl":"1rem",full:"9999px"}}}}
<\/script>
<style>
.material-symbols-outlined{font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:#0e0e0f}::-webkit-scrollbar-thumb{background:#262627;border-radius:10px}::-webkit-scrollbar-thumb:hover{background:#8c52ff}
body{font-family:'Manrope',sans-serif;background:#0e0e0f;color:#fff}
.cat-btn{transition:all .2s}
.cat-btn.active{background:#8c52ff;color:#fff;box-shadow:0 4px 20px rgba(140,82,255,.3)}
.cat-btn:not(.active){background:rgba(140,82,255,.1);color:#adaaab}
.cat-btn:not(.active):hover{background:rgba(140,82,255,.2);color:#fff}
.sec-tab{transition:all .2s;border-bottom:3px solid transparent;color:#adaaab}
.sec-tab.active{border-color:#8c52ff;color:#8c52ff}
.sec-tab:hover:not(.active){color:#fff}
.fade-in{animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body class="min-h-screen selection:bg-primary/30">

<div class="max-w-5xl mx-auto px-3 sm:px-6 py-6 sm:py-10">

  <!-- header -->
  <header class="text-center mb-8 sm:mb-12">
    <div class="inline-flex items-center gap-3 mb-4">
      <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-primary/20 flex items-center justify-center">
        <span class="material-symbols-outlined text-primary text-3xl sm:text-4xl" style="font-variation-settings:'FILL' 1">sports_soccer</span>
      </div>
    </div>
    <h1 class="font-headline text-3xl sm:text-5xl md:text-6xl font-black uppercase tracking-tighter bg-gradient-to-r from-primary via-secondary to-tertiary bg-clip-text text-transparent">Juego de Equipo</h1>
    <p class="text-on-surface-variant mt-2 text-sm sm:text-base font-medium">Semanario Deportivo</p>
    <p class="text-on-surface-variant/60 text-xs sm:text-sm mt-1">${esc(dateStr)}${TORNEO ? ` &bull; Torneo ${esc(TORNEO)}` : ""}</p>
  </header>

  <!-- category selector -->
  <div class="flex justify-center gap-2 sm:gap-3 mb-8">
${CATEGORIAS.map(
    (c, i) =>
      `    <button onclick="switchCat('${c}')" id="btn-${c}" class="cat-btn px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl text-sm sm:text-base font-bold font-headline${i === 0 ? " active" : ""}">${c === "Tercera" ? "Reserva" : c}</button>`
  ).join("\n")}
  </div>

  <!-- section tabs -->
  <div class="flex gap-4 sm:gap-8 border-b border-outline-variant/20 mb-6 overflow-x-auto">
    <button onclick="switchSec('posiciones')" id="tab-posiciones" class="sec-tab active pb-3 text-sm sm:text-base font-bold font-headline whitespace-nowrap flex items-center gap-1.5">
      <span class="material-symbols-outlined text-lg">format_list_numbered</span>Posiciones</button>
    <button onclick="switchSec('resultados')" id="tab-resultados" class="sec-tab pb-3 text-sm sm:text-base font-bold font-headline whitespace-nowrap flex items-center gap-1.5">
      <span class="material-symbols-outlined text-lg">calendar_month</span>Resultados</button>
    <button onclick="switchSec('goleadores')" id="tab-goleadores" class="sec-tab pb-3 text-sm sm:text-base font-bold font-headline whitespace-nowrap flex items-center gap-1.5">
      <span class="material-symbols-outlined text-lg">sports_soccer</span>Goleadores</button>
    <button onclick="switchSec('tarjetas')" id="tab-tarjetas" class="sec-tab pb-3 text-sm sm:text-base font-bold font-headline whitespace-nowrap flex items-center gap-1.5">
      <span class="material-symbols-outlined text-lg">style</span>Tarjetas</button>
  </div>

  <div id="content"></div>

  <footer class="mt-12 pt-6 border-t border-outline-variant/10 text-center">
    <p class="text-on-surface-variant/40 text-xs">Generado por <span class="text-primary font-bold">Juego de Equipo</span> &bull; ${esc(dateStr)}</p>
  </footer>
</div>

<script>
const DATA=${JSON.stringify(data).replace(/<\//g, '<\\/')};
const CATS=${JSON.stringify(CATEGORIAS)};
let curCat=CATS[0],curSec='posiciones';

function switchCat(c){curCat=c;CATS.forEach(x=>document.getElementById('btn-'+x).classList.toggle('active',x===c));render()}
function switchSec(s){curSec=s;['posiciones','resultados','goleadores','tarjetas'].forEach(x=>document.getElementById('tab-'+x).classList.toggle('active',x===s));render()}
function esc(s){const d=document.createElement('div');d.textContent=s||'';return d.innerHTML}

function render(){
  const d=DATA[curCat];
  const el=document.getElementById('content');
  if(!d){el.innerHTML='<p class="text-center text-on-surface-variant py-12">Sin datos</p>';return}
  switch(curSec){
    case 'posiciones':el.innerHTML=renderPos(d.tabla);break;
    case 'resultados':el.innerHTML=renderRes(d.partidos);break;
    case 'goleadores':el.innerHTML=renderGol(d.goleadores);break;
    case 'tarjetas':el.innerHTML=renderTar(d.tarjetas);break;
  }
  el.classList.remove('fade-in');void el.offsetWidth;el.classList.add('fade-in');
}

/* ---------- posiciones ---------- */
function renderPos(t){
  if(!t||!t.length) return '<p class="text-center text-on-surface-variant py-12">No hay datos de posiciones</p>';
  const hdr='<tr class="bg-surface-container text-on-surface-variant text-xs uppercase tracking-wider">'
    +'<th class="px-4 sm:px-6 py-3 text-left w-12">#</th>'
    +'<th class="px-4 py-3 text-left">Equipo</th>'
    +'<th class="px-3 py-3 text-center">PJ</th>'
    +'<th class="px-3 py-3 text-center">PG</th>'
    +'<th class="px-3 py-3 text-center">PE</th>'
    +'<th class="px-3 py-3 text-center">PP</th>'
    +'<th class="px-3 py-3 text-center hidden sm:table-cell">GF</th>'
    +'<th class="px-3 py-3 text-center hidden sm:table-cell">GC</th>'
    +'<th class="px-3 py-3 text-center">DG</th>'
    +'<th class="px-4 sm:px-6 py-3 text-right">PTS</th></tr>';

  const rows=t.map((r,i)=>{
    const pc=i===0?'text-yellow-400':i===1?'text-slate-400':i===2?'text-amber-600':'text-on-surface-variant';
    const ic=i===0?'stars':i<3?'workspace_premium':'shield';
    const icc=i===0?'text-yellow-400':i===1?'text-slate-400':i===2?'text-amber-600':'text-secondary/50';
    const dg=r.dg>0?'+'+r.dg:String(r.dg);
    const dgc=r.dg>0?'text-emerald-400':r.dg<0?'text-error':'text-on-surface-variant';
    return '<tr class="group hover:bg-primary/5 transition-colors">'
      +'<td class="px-4 sm:px-6 py-4 font-headline font-black text-base '+pc+'">'+String(i+1).padStart(2,'0')+'</td>'
      +'<td class="px-4 py-4"><div class="flex items-center gap-3">'
      +'<div class="w-8 h-8 rounded-lg bg-surface-container-highest flex items-center justify-center shrink-0">'
      +'<span class="material-symbols-outlined '+icc+' text-lg" style="font-variation-settings:\\'FILL\\' 1">'+ic+'</span></div>'
      +'<span class="font-bold group-hover:text-primary transition-colors">'+esc(r.equipo)+'</span></div></td>'
      +'<td class="px-3 py-4 text-center text-on-surface-variant">'+r.pj+'</td>'
      +'<td class="px-3 py-4 text-center text-on-surface-variant">'+r.pg+'</td>'
      +'<td class="px-3 py-4 text-center text-on-surface-variant">'+r.pe+'</td>'
      +'<td class="px-3 py-4 text-center text-on-surface-variant">'+r.pp+'</td>'
      +'<td class="px-3 py-4 text-center text-on-surface-variant hidden sm:table-cell">'+r.gf+'</td>'
      +'<td class="px-3 py-4 text-center text-on-surface-variant hidden sm:table-cell">'+r.gc+'</td>'
      +'<td class="px-3 py-4 text-center font-mono font-semibold '+dgc+'">'+dg+'</td>'
      +'<td class="px-4 sm:px-6 py-4 text-right font-headline font-black text-lg">'+r.puntos+'</td></tr>';
  }).join('');

  return '<div class="bg-surface-container-high rounded-2xl border border-outline-variant/10 overflow-hidden"><div class="overflow-x-auto">'
    +'<table class="w-full text-sm min-w-[580px]"><thead>'+hdr+'</thead><tbody class="divide-y divide-outline-variant/5">'+rows+'</tbody></table></div></div>';
}

/* ---------- resultados ---------- */
function renderRes(partidos){
  if(!partidos||!partidos.length) return '<p class="text-center text-on-surface-variant py-12">No hay partidos registrados</p>';
  const byJ={};
  partidos.forEach(p=>{const j=p.jornada||0;if(!byJ[j])byJ[j]=[];byJ[j].push(p)});
  const nums=Object.keys(byJ).map(Number).sort((a,b)=>a-b);

  return '<div class="space-y-6">'+nums.map(j=>{
    const mm=byJ[j];
    const jugados=mm.filter(m=>m.estado==='jugado').length;
    return '<div class="bg-surface-container-high rounded-2xl border border-outline-variant/10 overflow-hidden">'
      +'<div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-outline-variant/10 flex items-center justify-between">'
      +'<h3 class="font-headline font-bold text-base sm:text-lg">Fecha '+(j||'?')+'</h3>'
      +'<span class="text-xs text-on-surface-variant bg-surface-container px-2 py-1 rounded-lg">'+jugados+'/'+mm.length+' jugados</span></div>'
      +'<div class="divide-y divide-outline-variant/5">'+mm.map(p=>{
        const played=p.estado==='jugado';
        const le=p.localEscudo?'<img src="'+esc(p.localEscudo)+'" alt="" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover shrink-0" onerror="this.style.display=\\'none\\'"/>':'';
        const ve=p.visitanteEscudo?'<img src="'+esc(p.visitanteEscudo)+'" alt="" class="w-6 h-6 sm:w-8 sm:h-8 rounded-full object-cover shrink-0" onerror="this.style.display=\\'none\\'"/>':'';
        const score=played
          ?'<span class="bg-primary/20 text-primary font-black px-3 sm:px-4 py-1.5 rounded-xl text-sm sm:text-lg min-w-[60px] sm:min-w-[80px] text-center inline-block font-headline">'+p.golesLocal+' - '+p.golesVisitante+'</span>'
          :'<span class="bg-surface-container text-on-surface-variant px-3 py-1.5 rounded-xl text-xs font-semibold">Pendiente</span>';
        return '<div class="px-3 sm:px-6 py-3 sm:py-4 flex items-center gap-2 sm:gap-4 hover:bg-primary/5 transition-colors">'
          +'<div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">'+le
          +'<span class="font-bold text-right flex-1 text-xs sm:text-sm truncate">'+esc(p.local)+'</span></div>'
          +'<div class="shrink-0">'+score+'</div>'
          +'<div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">'
          +'<span class="font-bold flex-1 text-xs sm:text-sm truncate">'+esc(p.visitante)+'</span>'+ve+'</div></div>';
      }).join('')+'</div></div>';
  }).join('')+'</div>';
}

/* ---------- goleadores ---------- */
function renderGol(g){
  if(!g||!g.length) return '<p class="text-center text-on-surface-variant py-12">No hay goleadores registrados</p>';
  const rows=g.slice(0,20).map((x,i)=>{
    const medal=i===0?'\\u{1F947}':i===1?'\\u{1F948}':i===2?'\\u{1F949}':'';
    return '<tr class="group hover:bg-primary/5 transition-colors">'
      +'<td class="px-4 sm:px-6 py-3.5 font-headline font-black '+(i<3?'text-primary':'text-on-surface-variant')+'">'+(medal||String(i+1).padStart(2,'0'))+'</td>'
      +'<td class="px-4 py-3.5 font-bold group-hover:text-primary transition-colors">'+esc(x.nombre)+'</td>'
      +'<td class="px-4 py-3.5 text-on-surface-variant">'+esc(x.equipo)+'</td>'
      +'<td class="px-4 sm:px-6 py-3.5 text-right"><span class="inline-flex items-center bg-primary/10 text-primary px-3 py-1 rounded-full font-headline font-black text-base">'+x.goles+'</span></td></tr>';
  }).join('');

  return '<div class="bg-surface-container-high rounded-2xl border border-outline-variant/10 overflow-hidden"><div class="overflow-x-auto">'
    +'<table class="w-full text-sm"><thead><tr class="bg-surface-container text-on-surface-variant text-xs uppercase tracking-wider">'
    +'<th class="px-4 sm:px-6 py-3 text-left w-12">#</th><th class="px-4 py-3 text-left">Jugador</th>'
    +'<th class="px-4 py-3 text-left">Equipo</th><th class="px-4 sm:px-6 py-3 text-right">Goles</th>'
    +'</tr></thead><tbody class="divide-y divide-outline-variant/5">'+rows+'</tbody></table></div></div>';
}

/* ---------- tarjetas ---------- */
function renderTar(t){
  const am=t&&t.amarillas||[];const ro=t&&t.rojas||[];
  if(!am.length&&!ro.length) return '<p class="text-center text-on-surface-variant py-12">No hay tarjetas registradas</p>';

  function tbl(items,emoji,field){
    if(!items.length) return '<p class="text-center text-on-surface-variant py-8 text-sm">Sin tarjetas</p>';
    return '<table class="w-full text-sm"><thead><tr class="bg-surface-container text-on-surface-variant text-xs uppercase tracking-wider">'
      +'<th class="px-4 py-2.5 text-left w-10">#</th><th class="px-3 py-2.5 text-left">Jugador</th>'
      +'<th class="px-3 py-2.5 text-left hidden sm:table-cell">Equipo</th>'
      +'<th class="px-4 py-2.5 text-right">'+emoji+'</th></tr></thead>'
      +'<tbody class="divide-y divide-outline-variant/5">'+items.slice(0,15).map((x,i)=>
        '<tr class="hover:bg-primary/5 transition-colors"><td class="px-4 py-3 font-bold text-on-surface-variant">'+(i+1)+'</td>'
        +'<td class="px-3 py-3 font-bold">'+esc(x.nombre)+'</td>'
        +'<td class="px-3 py-3 text-on-surface-variant hidden sm:table-cell">'+esc(x.equipo)+'</td>'
        +'<td class="px-4 py-3 text-right font-headline font-black">'+x[field]+'</td></tr>'
      ).join('')+'</tbody></table>';
  }

  return '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">'
    +'<div class="bg-surface-container-high rounded-2xl border border-outline-variant/10 overflow-hidden">'
    +'<div class="px-4 sm:px-6 py-3 border-b border-outline-variant/10"><h3 class="font-headline font-bold flex items-center gap-2">\\u{1F7E8} Tarjetas Amarillas</h3></div>'
    +tbl(am,'\\u{1F7E8}','amarillas')+'</div>'
    +'<div class="bg-surface-container-high rounded-2xl border border-outline-variant/10 overflow-hidden">'
    +'<div class="px-4 sm:px-6 py-3 border-b border-outline-variant/10"><h3 class="font-headline font-bold flex items-center gap-2">\\u{1F7E5} Tarjetas Rojas</h3></div>'
    +tbl(ro,'\\u{1F7E5}','rojas')+'</div></div>';
}

render();
<\/script>
</body>
</html>`;
}

main().catch((err) => {
  console.error("❌ Error:", err.message);
  process.exit(1);
});
