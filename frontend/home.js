// === Home Page JS — Juego de Equipo ===
const API_BASE = "/api";
const api = { get: (url) => fetch(API_BASE + url).then((r) => r.json()) };

let currentCategoria = "Primera";
let currentTorneo = "Apertura";
let currentJornada = null;
let equiposMap = {}; // id -> {nombre, escudo}

// === Init ===
document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const toggle = document.getElementById("menuToggle");
  const menu = document.getElementById("mobileMenu");
  if (toggle && menu) {
    toggle.addEventListener("click", () => menu.classList.toggle("open"));
    menu.querySelectorAll("a").forEach((a) => a.addEventListener("click", () => menu.classList.remove("open")));
  }

  // Category tabs
  document.querySelectorAll(".cat-tab").forEach((btn) => {
    btn.addEventListener("click", () => {
      currentCategoria = btn.dataset.cat;
      document.querySelectorAll(".cat-tab").forEach((b) => {
        b.classList.toggle("bg-primary", b.dataset.cat === currentCategoria);
        b.classList.toggle("text-white", b.dataset.cat === currentCategoria);
        b.classList.toggle("text-gray-400", b.dataset.cat !== currentCategoria);
      });
      loadAll();
    });
  });

  // Torneo selector
  const torneoSel = document.getElementById("homeTorneo");
  if (torneoSel) {
    torneoSel.addEventListener("change", () => {
      currentTorneo = torneoSel.value;
      loadNextMatch();
      loadFixture();
    });
  }

  // H2H
  document.getElementById("homeH2hBtn")?.addEventListener("click", compareH2H);

  // Load everything
  loadLive();
  loadAll();
  loadNoticias();
});

function loadAll() {
  currentJornada = null;
  loadNextMatch();
  loadFixture();
  loadTabla();
  loadGoleadores();
  loadTarjetas();
  loadH2HEquipos();
}

function clearHeroEmbed() {
  document.querySelectorAll("#youtubeContainer .hero-video-embed").forEach((node) => node.remove());
}

function setHeroEmbed(videoId, autoplay = false) {
  const container = document.getElementById("youtubeContainer");
  const placeholder = document.getElementById("videoPlaceholder");
  if (!container || !placeholder || !videoId) return;

  clearHeroEmbed();
  placeholder.classList.add("hidden");

  const iframe = document.createElement("iframe");
  iframe.src = `https://www.youtube.com/embed/${encodeURIComponent(videoId)}?autoplay=${autoplay ? 1 : 0}&mute=${autoplay ? 1 : 0}&rel=0`;
  iframe.className = "hero-video-embed absolute inset-0 w-full h-full";
  iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture";
  iframe.allowFullscreen = true;
  iframe.setAttribute("loading", "lazy");
  container.appendChild(iframe);
}

function showHeroPlaceholder() {
  const placeholder = document.getElementById("videoPlaceholder");
  clearHeroEmbed();
  placeholder?.classList.remove("hidden");
}

function setHeroLiveState(match = null) {
  const liveBadgeHero = document.getElementById("liveBadgeHero");
  const noLiveBadge = document.getElementById("noLiveBadge");
  const liveOverlay = document.getElementById("liveOverlayBadge");
  const badgeText = document.getElementById("liveBadgeText");

  if (!liveBadgeHero || !noLiveBadge || !liveOverlay || !badgeText) return;

  if (match) {
    liveBadgeHero.classList.remove("hidden");
    liveBadgeHero.classList.add("inline-flex");
    badgeText.textContent = `En Vivo: ${match.local} vs ${match.visitante}`;
    noLiveBadge.classList.add("hidden");
    liveOverlay.classList.remove("hidden");
    return;
  }

  liveBadgeHero.classList.add("hidden");
  liveBadgeHero.classList.remove("inline-flex");
  noLiveBadge.classList.remove("hidden");
  liveOverlay.classList.add("hidden");
}

// === YouTube Live ===
async function loadLive() {
  try {
    const live = await api.get("/partidos/live");

    if (live.length > 0) {
      const match = live[0];
      const videoId = extractYouTubeId(match.broadcast);
      if (videoId && match.estado !== "jugado") {
        setHeroEmbed(videoId, true);
        setHeroLiveState(match);
        return;
      }
    }

    setHeroLiveState(null);
    loadNextMatch();
  } catch (e) {
    console.error("Error loading live:", e);
    setHeroLiveState(null);
  }
}

async function loadNextMatch() {
  try {
    const query = new URLSearchParams({
      categoria: currentCategoria,
      torneo: currentTorneo,
    });
    const response = await api.get(`/partidos/proximo?${query.toString()}`);
    const next = response?.partido || null;

    const info = document.getElementById("nextMatchInfo");
    const detail = document.getElementById("nextMatchDetail");
    const broadcast = document.getElementById("nextMatchBroadcast");

    if (!info || !detail) return;

    if (!next) {
      showHeroPlaceholder();
      info.classList.add("hidden");
      if (broadcast) {
        broadcast.classList.add("hidden");
        broadcast.removeAttribute("href");
      }
      return;
    }

    if (info && detail && next) {
      info.classList.remove("hidden");
      const localEsc = next.localEscudo || "";
      const visitEsc = next.visitanteEscudo || "";
      const videoId = extractYouTubeId(next.broadcast || "");
      detail.innerHTML = `
        <div class="flex items-center gap-2 min-w-0 flex-1 justify-end">
          ${localEsc ? `<img src="${localEsc}" alt="" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full object-cover bg-white/10 shrink-0" onerror="this.style.display='none'"/>` : ""}
          <span class="text-white truncate block max-w-[110px] sm:max-w-[150px]" title="${escapeAttr(next.local || "?")}">${next.local || "?"}</span>
        </div>
        <span class="text-gray-600 font-black shrink-0 px-1">vs</span>
        <div class="flex items-center gap-2 min-w-0 flex-1 justify-start">
          <span class="text-white truncate block max-w-[110px] sm:max-w-[150px]" title="${escapeAttr(next.visitante || "?")}">${next.visitante || "?"}</span>
          ${visitEsc ? `<img src="${visitEsc}" alt="" class="h-7 w-7 sm:h-8 sm:w-8 rounded-full object-cover bg-white/10 shrink-0" onerror="this.style.display='none'"/>` : ""}
        </div>
      `;
      if (videoId) {
        setHeroEmbed(videoId, false);
      } else {
        showHeroPlaceholder();
      }
      if (broadcast) {
        if (next.broadcast) {
          broadcast.href = next.broadcast;
          broadcast.classList.remove("hidden");
        } else {
          broadcast.classList.add("hidden");
          broadcast.removeAttribute("href");
        }
      }
    }
  } catch (e) {
    console.error("Error loading next match:", e);
    showHeroPlaceholder();
  }
}

function extractYouTubeId(url) {
  if (!url) return null;
  // youtube.com/live/ID, youtu.be/ID, youtube.com/watch?v=ID, youtube.com/embed/ID
  const patterns = [
    /youtube\.com\/live\/([^?&#]+)/,
    /youtu\.be\/([^?&#]+)/,
    /youtube\.com\/watch\?.*v=([^&#]+)/,
    /youtube\.com\/embed\/([^?&#]+)/,
  ];
  for (const p of patterns) {
    const m = url.match(p);
    if (m) return m[1];
  }
  return null;
}

// === Fixture ===
async function loadFixture() {
  const cat = currentCategoria;
  const tor = currentTorneo;
  const tabsEl = document.getElementById("homeJornadaTabs");
  const gridEl = document.getElementById("homeFixtureGrid");

  try {
    const [jornadas, partidosTorneo] = await Promise.all([
      api.get(`/partidos/jornadas?categoria=${enc(cat)}&torneo=${enc(tor)}`),
      api.get(`/partidos?categoria=${enc(cat)}&torneo=${enc(tor)}`),
    ]);
    tabsEl.innerHTML = "";

    if (!jornadas.length) {
      gridEl.innerHTML = `<p class="text-gray-500 col-span-full text-center py-8">No hay jornadas cargadas para ${cat} - ${tor}</p>`;
      return;
    }

    const validJornadas = jornadas.filter((j) => j.jornada != null);

    // Jornada sugerida por calendario: próxima jornada según fecha/hora real de partidos no jugados.
    const now = new Date();
    const partidosNoJugados = (partidosTorneo || []).filter((p) => p.jornada != null && p.estado !== "jugado");
    const partidosConFecha = partidosNoJugados
      .map((p) => {
        const fecha = (p.fecha || "").trim();
        if (!fecha) return null;
        const hora = (p.hora || "00:00").toString().slice(0, 5);
        const dt = new Date(`${fecha}T${hora}:00`);
        if (Number.isNaN(dt.getTime())) return null;
        return { jornada: p.jornada, dt };
      })
      .filter(Boolean)
      .sort((a, b) => a.dt - b.dt);

    const proximoPartido = partidosConFecha.find((p) => p.dt >= now) || null;
    const jornadaPorCalendario = proximoPartido?.jornada ?? null;

    const jornadaInicial = currentJornada
      ?? (jornadaPorCalendario
      ?? validJornadas.find((j) => Number(j.jugados) < Number(j.total))?.jornada
      ?? validJornadas[validJornadas.length - 1]?.jornada
      ?? null);

    validJornadas.forEach((j, i) => {
      const total = Number(j.total);
      const jugados = Number(j.jugados);
      let dotColor = "bg-gray-600";
      if (jugados === total && total > 0) dotColor = "bg-green-500";
      else if (jugados > 0) dotColor = "bg-yellow-500";

      const btn = document.createElement("button");
      const isActive = j.jornada === jornadaInicial;
      btn.className = `px-3 sm:px-4 py-2 rounded-lg text-xs sm:text-sm font-bold transition-all flex items-center gap-1.5 ${isActive ? "bg-primary text-white" : "bg-white/5 text-gray-400 hover:text-white hover:bg-white/10"}`;
      btn.innerHTML = `Fecha ${j.jornada} <span class="w-2 h-2 rounded-full ${dotColor}"></span>`;
      btn.addEventListener("click", () => {
        currentJornada = j.jornada;
        tabsEl.querySelectorAll("button").forEach((b) => { b.className = b.className.replace("bg-primary text-white", "bg-white/5 text-gray-400 hover:text-white hover:bg-white/10"); });
        btn.className = btn.className.replace("bg-white/5 text-gray-400 hover:text-white hover:bg-white/10", "bg-primary text-white");
        renderFixtureJornada(j.jornada);
      });
      tabsEl.appendChild(btn);
      if (isActive) currentJornada = j.jornada;
    });

    if (currentJornada) renderFixtureJornada(currentJornada);
  } catch (e) {
    gridEl.innerHTML = `<p class="text-red-400 col-span-full text-center py-8">Error al cargar fixture</p>`;
  }
}

async function renderFixtureJornada(jornada) {
  const gridEl = document.getElementById("homeFixtureGrid");
  const cat = currentCategoria;
  const tor = currentTorneo;

  try {
    const partidos = await api.get(`/partidos?categoria=${enc(cat)}&torneo=${enc(tor)}&jornada=${jornada}`);
    if (!partidos.length) {
      gridEl.innerHTML = `<p class="text-gray-500 col-span-full text-center py-8">No hay partidos en esta fecha</p>`;
      return;
    }

    gridEl.innerHTML = partidos.map((p) => {
      const isJugado = p.estado === "jugado";
      const isEnCurso = p.estado === "en_curso";
      const showScore = isJugado || isEnCurso;
      const localEsc = p.localEscudo || "";
      const visitEsc = p.visitanteEscudo || "";
      const hasBroadcast = p.broadcast && p.broadcast.trim();

      // Badge de estado
      let estadoBadgeClass = "bg-white/10 text-gray-400";
      let estadoBadgeText = p.hora || "Pendiente";
      if (isJugado) {
        estadoBadgeClass = "bg-green-500/20 text-green-400";
        estadoBadgeText = "Finalizado";
      } else if (isEnCurso) {
        estadoBadgeClass = "bg-amber-500/20 text-amber-400 animate-pulse";
        estadoBadgeText = "🟡 En vivo";
      }

      return `
        <div class="glass-card rounded-2xl p-4 sm:p-5 hover:border-primary/40 transition-colors group">
          <div class="flex justify-between items-center mb-5 sm:mb-6">
            <span class="text-[10px] font-bold tracking-[0.15em] text-gray-500 uppercase">${p.torneo || ""} • Fecha ${jornada}</span>
            <span class="text-[10px] font-bold px-2 py-1 rounded ${estadoBadgeClass}">${estadoBadgeText}</span>
          </div>
          <div class="flex items-center justify-between gap-3">
            <div class="flex flex-col items-center gap-2 flex-1 min-w-0">
              ${localEsc ? `<img src="${localEsc}" alt="${p.local}" class="w-12 h-12 sm:w-14 sm:h-14 object-contain" onerror="this.style.display='none'"/>` : `<div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white/5 flex items-center justify-center text-gray-600 text-xl font-bold">${(p.local || "?")[0]}</div>`}
              <span class="font-bold text-xs sm:text-sm text-center truncate w-full">${p.local || "?"}</span>
            </div>
            <div class="text-center shrink-0">
              ${showScore
                ? `<div class="text-2xl sm:text-3xl font-black">${p.golesLocal || 0}<span class="text-gray-600 mx-1">-</span>${p.golesVisitante || 0}</div>`
                : `<div class="text-xl font-black text-gray-700">VS</div>`}
            </div>
            <div class="flex flex-col items-center gap-2 flex-1 min-w-0">
              ${visitEsc ? `<img src="${visitEsc}" alt="${p.visitante}" class="w-12 h-12 sm:w-14 sm:h-14 object-contain" onerror="this.style.display='none'"/>` : `<div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-white/5 flex items-center justify-center text-gray-600 text-xl font-bold">${(p.visitante || "?")[0]}</div>`}
              <span class="font-bold text-xs sm:text-sm text-center truncate w-full">${p.visitante || "?"}</span>
            </div>
          </div>
          <div class="mt-4 sm:mt-5 flex items-center justify-between text-[10px] sm:text-xs text-gray-500">
            <span>${p.estadio || ""}</span>
            <span>${formatDate(p.fecha)}</span>
            ${hasBroadcast ? `<a href="${escapeAttr(p.broadcast)}" target="_blank" rel="noopener noreferrer" class="text-primary hover:text-primary-light flex items-center gap-0.5"><span class="material-symbols-outlined text-[14px]">videocam</span>Ver</a>` : ""}
          </div>
        </div>`;
    }).join("");
  } catch (e) {
    gridEl.innerHTML = `<p class="text-red-400 col-span-full text-center py-8">Error al cargar partidos</p>`;
  }
}

// === Tabla de Posiciones ===
async function loadTabla() {
  const cat = currentCategoria;
  const body = document.getElementById("homeTablaBody");

  try {
    const [tabla, equipos] = await Promise.all([
      api.get(`/tabla?categoria=${enc(cat)}`),
      api.get(`/equipos?categoria=${enc(cat)}`),
    ]);

    const escudoMap = {};
    equipos.forEach((e) => { escudoMap[e.nombre] = e.escudo; equiposMap[e.id] = e; });

    if (!tabla.length) {
      body.innerHTML = `<tr><td colspan="10" class="text-center py-8 text-gray-500">No hay datos</td></tr>`;
      return;
    }

    body.innerHTML = tabla.map((t, i) => {
      const esc = escudoMap[t.equipo] || "";
      const isTop = i < 3;
      return `
        <tr class="${isTop ? "bg-primary/5" : ""} hover:bg-white/5 transition-colors">
          <td class="px-3 sm:px-4 py-3 font-bold text-sm ${isTop ? "text-primary" : "text-gray-400"}">${i + 1}</td>
          <td class="px-3 sm:px-4 py-3">
            <div class="flex items-center gap-2">
              ${esc ? `<img src="${esc}" alt="" class="w-6 h-6  object-cover" onerror="this.style.display='none'"/>` : ""}
              <span class="font-semibold text-sm">${t.equipo}</span>
            </div>
          </td>
          <td class="px-3 sm:px-4 py-3 text-center font-black text-primary">${t.puntos}</td>
          <td class="px-3 sm:px-4 py-3 text-center text-gray-400">${t.pj}</td>
          <td class="px-3 sm:px-4 py-3 text-center text-gray-400">${t.pg}</td>
          <td class="px-3 sm:px-4 py-3 text-center text-gray-400">${t.pe}</td>
          <td class="px-3 sm:px-4 py-3 text-center text-gray-400">${t.pp}</td>
          <td class="px-3 sm:px-4 py-3 text-center text-gray-400">${t.gf}</td>
          <td class="px-3 sm:px-4 py-3 text-center text-gray-400">${t.gc}</td>
          <td class="px-3 sm:px-4 py-3 text-center font-semibold ${t.dg > 0 ? "text-green-400" : t.dg < 0 ? "text-red-400" : "text-gray-400"}">${t.dg > 0 ? "+" : ""}${t.dg}</td>
        </tr>`;
    }).join("");
  } catch (e) {
    body.innerHTML = `<tr><td colspan="10" class="text-center py-8 text-red-400">Error al cargar tabla</td></tr>`;
  }
}

// === Goleadores ===
async function loadGoleadores() {
  const cat = currentCategoria;
  const tor = currentTorneo;
  const grid = document.getElementById("homeGoleadoresGrid");

  try {
    const [data, equipos] = await Promise.all([
      api.get(`/tabla/goleadores?categoria=${enc(cat)}&torneo=${enc(tor)}`),
      api.get(`/equipos?categoria=${enc(cat)}`),
    ]);

    const escudoByEquipo = {};
    (equipos || []).forEach((equipo) => {
      escudoByEquipo[equipo.nombre] = equipo.escudo || "";
    });

    const top = data.slice(0, 15);

    if (!top.length) {
      grid.innerHTML = `<p class="text-gray-500 col-span-full text-center py-8">No hay goleadores registrados</p>`;
      return;
    }

    grid.innerHTML = top.map((g, i) => {
      const rankClass = i === 0 ? "from-yellow-500/20 to-yellow-600/5 border-yellow-500/20" :
                         i === 1 ? "from-gray-300/10 to-gray-400/5 border-gray-400/20" :
                         i === 2 ? "from-amber-700/10 to-amber-800/5 border-amber-700/20" :
                         "from-white/5 to-transparent border-white/5";
      const rankBg = i === 0 ? "bg-yellow-500 text-black" : i === 1 ? "bg-gray-400 text-black" : i === 2 ? "bg-amber-700 text-white" : "bg-white/10 text-gray-400";
      const escudo = (g.escudo || escudoByEquipo[g.equipo] || "").trim();
      const bgStyle = escudo
        ? `style="background-image: linear-gradient(110deg, rgba(15, 23, 42, 0.92) 0%, rgb(15 23 42) 45%, rgba(15, 23, 42, 0.52) 100%), url('${escapeAttr(escudo)}'); background-repeat: no-repeat, no-repeat; background-position: center center, 142px center;"`
        : "";
      return `
        <div class="glass-card rounded-xl p-3 sm:p-4 flex items-center gap-3 bg-gradient-to-r ${rankClass} hover:border-primary/30 transition-colors" ${bgStyle}>
          <span class="w-8 h-8 rounded-full ${rankBg} flex items-center justify-center text-xs font-black shrink-0">${i + 1}</span>
          <div class="flex-1 min-w-0">
            <p class="font-bold text-sm truncate">${g.nombre}</p>
            <p class="text-xs text-gray-500 truncate">${g.equipo}</p>
          </div>
          <div class="bg-black text-white font-black text-lg px-3 py-1 rounded-lg shrink-0">${g.goles}</div>
        </div>`;
    }).join("");
  } catch (e) {
    grid.innerHTML = `<p class="text-red-400 col-span-full text-center py-8">Error al cargar goleadores</p>`;
  }
}

// === Tarjetas ===
async function loadTarjetas() {
  const cat = currentCategoria;
  const amarBody = document.getElementById("homeAmarillasBody");
  const rojBody = document.getElementById("homeRojasBody");

  try {
    const data = await api.get(`/tabla/tarjetas?categoria=${enc(cat)}`);
    const renderCards = (arr, body, field) => {
      const top = (arr || []).slice(0, 10);
      if (!top.length) {
        body.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-gray-500 text-sm">Sin datos</td></tr>`;
        return;
      }
      body.innerHTML = top.map((t, i) => `
        <tr class="hover:bg-white/5 transition-colors">
          <td class="px-4 py-2.5 text-gray-500 font-bold text-sm">${i + 1}</td>
          <td class="px-4 py-2.5 font-semibold text-sm">${t.nombre}</td>
          <td class="px-4 py-2.5 text-gray-400 text-sm">${t.equipo}</td>
          <td class="px-4 py-2.5 text-center font-black text-sm">${t[field]}</td>
        </tr>`).join("");
    };
    renderCards(data.amarillas, amarBody, "amarillas");
    renderCards(data.rojas, rojBody, "rojas");
  } catch (e) {
    amarBody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-red-400">Error</td></tr>`;
    rojBody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-red-400">Error</td></tr>`;
  }
}

// === H2H ===
async function loadH2HEquipos() {
  const cat = currentCategoria;
  try {
    const equipos = await api.get(`/equipos?categoria=${enc(cat)}`);
    const sel1 = document.getElementById("homeH2hTeam1");
    const sel2 = document.getElementById("homeH2hTeam2");
    const opts = `<option value="" disabled selected>Seleccionar equipo</option>` + equipos.map((e) => `<option value="${e.id}">${e.nombre}</option>`).join("");
    sel1.innerHTML = opts;
    sel2.innerHTML = opts;
    document.getElementById("homeH2hResumen").innerHTML = "";
    document.getElementById("homeH2hPartidos").innerHTML = "";
  } catch (e) {
    console.error("Error loading H2H equipos:", e);
  }
}

async function compareH2H() {
  const t1 = document.getElementById("homeH2hTeam1").value;
  const t2 = document.getElementById("homeH2hTeam2").value;
  if (!t1 || !t2 || t1 === t2) return;

  const cat = currentCategoria;
  try {
    const data = await api.get(`/partidos/h2h?team1=${t1}&team2=${t2}&categoria=${enc(cat)}`);
    renderH2HResumen(data.resumen, t1, t2);
    renderH2HPartidos(data.partidos);
  } catch (e) {
    console.error("Error H2H:", e);
  }
}

function renderH2HResumen(r, t1Id, t2Id) {
  const el = document.getElementById("homeH2hResumen");
  const t1Name = document.getElementById("homeH2hTeam1").selectedOptions[0]?.text || "Equipo 1";
  const t2Name = document.getElementById("homeH2hTeam2").selectedOptions[0]?.text || "Equipo 2";

  el.innerHTML = `
    <div class="grid grid-cols-3 sm:grid-cols-5 gap-3">
      <div class="glass-card rounded-xl p-3 sm:p-4 text-center">
        <p class="text-2xl sm:text-3xl font-black text-primary">${r.team1Wins}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 mt-1 truncate">${t1Name}</p>
      </div>
      <div class="glass-card rounded-xl p-3 sm:p-4 text-center">
        <p class="text-2xl sm:text-3xl font-black text-gray-400">${r.draws}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 mt-1">Empates</p>
      </div>
      <div class="glass-card rounded-xl p-3 sm:p-4 text-center">
        <p class="text-2xl sm:text-3xl font-black text-primary">${r.team2Wins}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 mt-1 truncate">${t2Name}</p>
      </div>
      <div class="glass-card rounded-xl p-3 sm:p-4 text-center hidden sm:block">
        <p class="text-2xl sm:text-3xl font-black">${r.team1Goals}-${r.team2Goals}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 mt-1">Goles</p>
      </div>
      <div class="glass-card rounded-xl p-3 sm:p-4 text-center hidden sm:block">
        <p class="text-2xl sm:text-3xl font-black">${r.totalMatches}</p>
        <p class="text-[10px] sm:text-xs text-gray-400 mt-1">Partidos</p>
      </div>
    </div>
    <div class="flex justify-center gap-6 mt-3 sm:hidden text-xs text-gray-400">
      <span>Goles: ${r.team1Goals}-${r.team2Goals}</span>
      <span>Total: ${r.totalMatches}</span>
    </div>`;
}

function renderH2HPartidos(partidos) {
  const el = document.getElementById("homeH2hPartidos");
  if (!partidos.length) {
    el.innerHTML = `<p class="text-gray-500 text-center py-6">No hay partidos entre estos equipos</p>`;
    return;
  }

  el.innerHTML = partidos.map((p) => `
    <div class="glass-card rounded-xl p-3 sm:p-4">
      <div class="flex items-center justify-between mb-2 text-[10px] sm:text-xs text-gray-500">
        <span>${formatDate(p.fecha)}${p.jornada ? ` • Fecha ${p.jornada}` : ""}${p.torneo ? ` • ${p.torneo}` : ""}</span>
        <span>${p.estadio || ""}</span>
      </div>
      <div class="flex items-center justify-center gap-3">
        ${p.localEscudo ? `<img src="${p.localEscudo}" alt="" class="w-6 h-6 rounded-full object-cover" onerror="this.style.display='none'"/>` : ""}
        <span class="font-bold text-xs sm:text-sm">${p.local}</span>
        <span class="bg-primary/20 text-primary font-bold px-2 py-0.5 rounded text-sm">${p.golesLocal} - ${p.golesVisitante}</span>
        <span class="font-bold text-xs sm:text-sm">${p.visitante}</span>
        ${p.visitanteEscudo ? `<img src="${p.visitanteEscudo}" alt="" class="w-6 h-6 rounded-full object-cover" onerror="this.style.display='none'"/>` : ""}
      </div>
      ${p.goleadores && p.goleadores.length ? `<div class="flex flex-wrap gap-1.5 mt-2 justify-center">${p.goleadores.map((g) => `<span class="text-[10px] bg-white/5 px-2 py-0.5 rounded-full text-gray-400">⚽ ${g.jugador}${g.minuto ? ` ${g.minuto}'` : ""}</span>`).join("")}</div>` : ""}
    </div>`).join("");
}

// === Utilities ===
function enc(str) { return encodeURIComponent(str); }

function escapeAttr(str) {
  return str.replace(/&/g, "&amp;").replace(/"/g, "&quot;").replace(/'/g, "&#39;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  try {
    const d = new Date(dateStr + "T12:00:00");
    return d.toLocaleDateString("es-AR", { day: "numeric", month: "short" });
  } catch {
    return dateStr;
  }
}

// === Noticias Home ===
async function loadNoticias() {
  const section = document.getElementById("homeNoticiasSection");
  const grid = document.getElementById("homeNoticiasGrid");
  if (!section || !grid) return;
  try {
    const noticias = await api.get("/noticias?limit=6");
    if (!Array.isArray(noticias) || !noticias.length) return;
    section.classList.remove("hidden");
    grid.innerHTML = noticias.map((n) => {
      const fecha = n.publishedAt || n.createdAt;
      const fechaStr = fecha
        ? new Date(fecha).toLocaleDateString("es-AR", { day: "numeric", month: "long" })
        : "";
      const img = n.imagenUrl
        ? `<img src="${n.imagenUrl}" alt="${escapeAttr(n.titulo || "")}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy"/>`
        : `<div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-card-dark flex items-center justify-center"><span class="material-symbols-outlined" style="font-size:48px;color:rgba(140,82,255,0.3)">article</span></div>`;
      return `<a href="/noticias/${enc(n.slug)}" class="group glass-card rounded-2xl overflow-hidden hover:border-primary/30 transition-all duration-300 flex flex-col">
        <div class="relative aspect-video overflow-hidden bg-card-dark">
          ${img}
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
          ${n.categoria ? `<span class="absolute bottom-3 left-3 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/90 text-white">${n.categoria}</span>` : ""}
        </div>
        <div class="p-4 sm:p-5 flex flex-col flex-1">
          <h3 class="font-black text-base sm:text-lg leading-snug mb-2 line-clamp-2 group-hover:text-primary-light transition-colors">${n.titulo || ""}</h3>
          ${n.copete ? `<p class="text-gray-400 text-sm leading-relaxed line-clamp-2 mb-3">${n.copete}</p>` : ""}
          <div class="mt-auto flex items-center justify-between text-xs text-gray-500">
            <span>${escapeAttr(n.autorNombre || "Redacci\u00f3n")}</span>
            <span>${fechaStr}</span>
          </div>
        </div>
      </a>`;
    }).join("");
  } catch (e) {
    console.error("Error cargando noticias home:", e);
  }
}
