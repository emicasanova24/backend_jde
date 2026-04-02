const getAuthHeaders = () => {
  const headers = { "Content-Type": "application/json" };
  const token = localStorage.getItem("token");
  if (token) headers.Authorization = `Bearer ${token}`;
  return headers;
};

const api = {
  get: (path) => fetch(path, { headers: getAuthHeaders() }).then((r) => r.json()),
  post: (path, body) => fetch(path, { method: "POST", headers: getAuthHeaders(), body: JSON.stringify(body) }).then((r) => r.json()),
  put: (path, body) => fetch(path, { method: "PUT", headers: getAuthHeaders(), body: JSON.stringify(body) }).then((r) => r.json()),
  del: (path) => fetch(path, { method: "DELETE", headers: getAuthHeaders() }).then((r) => r.json()),
};

const getSelectedCategoria = () => document.getElementById("categoriaFiltro")?.value || "";
const getSelectedTorneo = () => document.getElementById("torneoFiltro")?.value || "";

const handleError = (err, id) => {
  console.error(err);
  const el = document.getElementById(id);
  if (el) el.textContent = err.message || "Error en la operación";
};

const withNav = () => {
  const nav = document.getElementById("sharedNav");
  if (!nav) return;
  fetch("shared-nav.html")
    .then((r) => r.text())
    .then((html) => { nav.innerHTML = html; })
    .catch((err) => console.error("No se encontró shared-nav", err));
};

const loginScreen = () => {
  const form = document.getElementById("loginForm");
  if (!form) return;
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = document.getElementById("loginEmail").value.trim();
    const password = document.getElementById("loginPassword").value;
    const errorBox = document.getElementById("loginError");
    errorBox.textContent = "";

    if (!email || !password) {
      errorBox.textContent = "Email y password son obligatorios";
      return;
    }

    try {
      // Simulación: back real en /auth/login
      const resp = await api.post("/auth/login", { email, password });
      if (resp && resp.token) {
        localStorage.setItem("token", resp.token);
        window.location.href = "dashboard.html";
      } else {
        errorBox.textContent = resp.message || "Credenciales inválidas";
      }
    } catch (err) {
      handleError(err, "loginError");
    }
  });
};

const loadDashboard = async () => {
  const mirror = document.getElementById("dashboardContainer");
  if (!mirror) return;

  const [equipos, jugadores, partidos] = await Promise.all([
    api.get("/equipos").catch(() => []),
    api.get("/jugadores").catch(() => []),
    api.get("/partidos").catch(() => []),
  ]);

  document.getElementById("totalEquipos").textContent = equipos.length;
  document.getElementById("totalJugadores").textContent = jugadores.length;

  const proxima = partidos.filter((p) => p.estado !== "jugado");
  document.getElementById("proximosPartidos").textContent = proxima.length;

  const ultimos = partidos.filter((p) => p.estado === "jugado").slice(-5).reverse();
  document.getElementById("ultimosResultados").textContent = ultimos.length;

  const tablaProx = document.getElementById("tablaProximos");
  const tablaUlt = document.getElementById("tablaUltimos");

  const tbodyProx = tablaProx.querySelector("tbody");
  const tbodyUlt = tablaUlt.querySelector("tbody");
  tbodyProx.innerHTML = "";
  tbodyUlt.innerHTML = "";

  proxima.slice(0, 10).forEach((p) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td>${p.fecha || "-"}</td><td>${p.hora||"-"}</td><td>${p.local} vs ${p.visitante}</td><td>${p.estadio||"-"}</td>`;
    tbodyProx.appendChild(tr);
  });

  ultimos.forEach((p) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `<td>${p.fecha||"-"}</td><td>${p.local} ${p.golesLocal||0} - ${p.golesVisitante||0} ${p.visitante}</td><td>${p.estadio||"-"}</td>`;
    tbodyUlt.appendChild(tr);
  });
};

const equiposScreen = async () => {
  const form = document.getElementById("equipoForm");
  const inputNombre = document.getElementById("equipoNombre");
  const inputEstadio = document.getElementById("equipoEstadio");
  const inputEscudo = document.getElementById("equipoEscudo");
  const editModal = document.getElementById("equipoEditModal");
  const editId = document.getElementById("equipoEditId");

  const load = async () => {
    const equipos = await api.get("/equipos").catch(() => []);
    const tbody = document.getElementById("equiposTableBody");
    tbody.innerHTML = "";
    equipos.forEach((team) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `<td><img src="${team.escudo || 'https://via.placeholder.com/40'}" class="w-10 h-10 rounded" alt='escudo'></td><td>${team.nombre}</td><td>${team.estadio}</td><td>${team.categoria || '-'}</td><td class='space-x-2'><button data-id='${team.id}' data-nombre='${team.nombre}' data-estadio='${team.estadio}' data-escudo='${team.escudo}' data-categoria='${team.categoria || ""}' class='editEquipo px-2 py-1 bg-blue-500 text-white rounded'>Editar</button><button data-id='${team.id}' class='deleteEquipo px-2 py-1 bg-red-500 text-white rounded'>Eliminar</button></td>`;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll(".deleteEquipo").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await api.del(`/equipos/${btn.dataset.id}`);
        load();
      });
    });

    tbody.querySelectorAll(".editEquipo").forEach((btn) => {
      btn.addEventListener("click", () => {
        editId.value = btn.dataset.id;
        document.getElementById("editEquipoNombre").value = btn.dataset.nombre;
        document.getElementById("editEquipoEstadio").value = btn.dataset.estadio;
        document.getElementById("editEquipoEscudo").value = btn.dataset.escudo;
        document.getElementById("editEquipoCategoria").value = btn.dataset.categoria || "";
        editModal.classList.remove("hidden");
      });
    });
  };

  form?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const category = document.getElementById("equipoCategoria").value;
    const payload = {
      nombre: inputNombre.value.trim(),
      estadio: inputEstadio.value.trim(),
      escudo: inputEscudo.value.trim(),
      categoria: category,
    };
    if (!payload.nombre || !payload.estadio || !payload.categoria) return;
    await api.post("/equipos", payload);
    form.reset();
    load();
  });

  document.getElementById("closeEquipoModal").addEventListener("click", () => editModal.classList.add("hidden"));
  document.getElementById("editEquipoForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const payload = {
      nombre: document.getElementById("editEquipoNombre").value.trim(),
      estadio: document.getElementById("editEquipoEstadio").value.trim(),
      escudo: document.getElementById("editEquipoEscudo").value.trim(),
      categoria: document.getElementById("editEquipoCategoria").value,
    };
    await api.put(`/equipos/${editId.value}`, payload);
    editModal.classList.add("hidden");
    load();
  });

  await load();
};

const jugadoresScreen = async () => {
  const form = document.getElementById("jugadorForm");
  if (!form) return;
  const loadEquipos = async () => {
    const equipos = await api.get("/equipos").catch(() => []);
    const select = document.getElementById("jugadorEquipo");
    select.innerHTML = "";
    equipos.forEach((eq) => {
      const o = document.createElement("option");
      o.value = eq.id;
      o.textContent = `${eq.nombre} (${eq.categoria || "Sin cat."})`;
      select.appendChild(o);
    });
    // Also update edit modal select
    const editSelect = document.getElementById("editJugadorEquipo");
    if (editSelect) {
      editSelect.innerHTML = "";
      equipos.forEach((eq) => {
        const o = document.createElement("option");
        o.value = eq.id;
        o.textContent = `${eq.nombre} (${eq.categoria || "Sin cat."})`;
        editSelect.appendChild(o);
      });
    }
    return equipos;
  };

  const load = async () => {
    const jugadores = await api.get("/jugadores").catch(() => []);
    const tbody = document.getElementById("jugadoresTableBody");
    tbody.innerHTML = "";

    jugadores.forEach((jr) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `<td>${jr.nombre}</td><td>${jr.posicion}</td><td>${jr.equipoNombre ? `${jr.equipoNombre} (${jr.equipoCategoria || ''})` : jr.equipo || "-"}</td><td class='space-x-2'><button data-id='${jr.id}' data-nombre='${jr.nombre}' data-posicion='${jr.posicion}' data-equipo='${jr.equipo}' class='editJugador px-2 py-1 bg-blue-500 text-white rounded'>Editar</button><button data-id='${jr.id}' class='deleteJugador px-2 py-1 bg-red-500 text-white rounded'>Eliminar</button></td>`;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll(".deleteJugador").forEach((btn) => {
      btn.addEventListener("click", async () => { await api.del(`/jugadores/${btn.dataset.id}`); load(); });
    });

    tbody.querySelectorAll(".editJugador").forEach((btn) => {
      btn.addEventListener("click", () => {
        document.getElementById("editJugadorId").value = btn.dataset.id;
        document.getElementById("editJugadorNombre").value = btn.dataset.nombre;
        document.getElementById("editJugadorPosicion").value = btn.dataset.posicion;
        document.getElementById("editJugadorEquipo").value = btn.dataset.equipo;
        document.getElementById("jugadorEditModal").classList.remove("hidden");
      });
    });
  };

  await loadEquipos();

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    await api.post("/jugadores", {
      nombre: document.getElementById("jugadorNombre").value.trim(),
      posicion: document.getElementById("jugadorPosicion").value.trim(),
      equipo: document.getElementById("jugadorEquipo").value,
    });
    form.reset();
    await load();
  });

  document.getElementById("closeJugadorModal").addEventListener("click", () => document.getElementById("jugadorEditModal").classList.add("hidden"));
  document.getElementById("editJugadorForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("editJugadorId").value;
    await api.put(`/jugadores/${id}`, {
      nombre: document.getElementById("editJugadorNombre").value,
      posicion: document.getElementById("editJugadorPosicion").value,
      equipo: document.getElementById("editJugadorEquipo").value,
    });
    document.getElementById("jugadorEditModal").classList.add("hidden");
    await load();
  });

  await load();
};

const partidosScreen = async () => {
  const form = document.getElementById("partidoForm");
  if (!form) return;
  const loadEquipos = async () => {
    const categoria = getSelectedCategoria();
    const equipos = await api.get(`/equipos?categoria=${encodeURIComponent(categoria)}`).catch(() => []);
    ["partidoLocal", "partidoVisitante", "fixtureLocal", "fixtureVisitante", "editPartidoLocal", "editPartidoVisitante"].forEach((id) => {
      const select = document.getElementById(id);
      if (!select) return;
      select.innerHTML = "";
      equipos.forEach((eq) => {
        const opt = document.createElement("option");
        opt.value = eq.id;
        opt.textContent = eq.nombre;
        select.appendChild(opt);
      });
    });
    return equipos;
  };

  const load = async () => {
    const partidos = await api.get("/partidos").catch(() => []);
    const tbody = document.getElementById("partidosTableBody");
    tbody.innerHTML = "";
    partidos.forEach((p) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `<td>${p.fecha}</td><td>${p.hora}</td><td>${p.estadio}</td><td>${p.local} vs ${p.visitante}</td><td>${p.estado}</td><td><button data-id='${p.id}' class='cargarResultado px-2 py-1 bg-orange-500 text-white rounded'>Cargar</button></td>`;
      tbody.appendChild(tr);
    });
    tbody.querySelectorAll(".cargarResultado").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const p = partidos.find((x) => x.id == btn.dataset.id);
        if (!p) return;
        document.getElementById("resultadoPartidoId").value = p.id;
        document.getElementById("resLocalEquipo").textContent = p.local;
        document.getElementById("resVisitanteEquipo").textContent = p.visitante;
        document.getElementById("partidoResultModal").classList.remove("hidden");
      });
    });
  };

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    await api.post("/partidos", {
      fecha: document.getElementById("partidoFecha").value,
      hora: document.getElementById("partidoHora").value,
      estadio: document.getElementById("partidoEstadio").value,
      local: document.getElementById("partidoLocal").value,
      visitante: document.getElementById("partidoVisitante").value,
      estado: "pendiente",
    });
    form.reset();
    await load();
  });

  document.getElementById("closePartidoResultModal").addEventListener("click", () => document.getElementById("partidoResultModal").classList.add("hidden"));
  document.getElementById("partidoResultForm").addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("resultadoPartidoId").value;
    await api.put(`/partidos/${id}`, {
      golesLocal: Number(document.getElementById("resGolesLocal").value),
      golesVisitante: Number(document.getElementById("resGolesVisitante").value),
      amarillas: Number(document.getElementById("resAmarillas").value),
      rojas: Number(document.getElementById("resRojas").value),
      estado: "jugado",
    });
    document.getElementById("partidoResultModal").classList.add("hidden");
    await load();
  });

  await loadEquipos();
  await load();
};

const estadisticasScreen = async () => {
  // === Nav tab switching ===
  const sections = {
    tabla: document.getElementById("seccionTabla"),
    goleadores: document.getElementById("seccionGoleadores"),
    tarjetas: document.getElementById("seccionTarjetas"),
    resultados: document.getElementById("resultadosJornada"),
    historicos: document.getElementById("seccionHistoricos"),
  };
  const navTabs = {
    tabla: document.getElementById("navTabTabla"),
    goleadores: document.getElementById("navTabGoleadores"),
    tarjetas: document.getElementById("navTabTarjetas"),
    resultados: document.getElementById("navTabResultados"),
    historicos: document.getElementById("navTabHistoricos"),
  };

  const switchSection = (active) => {
    Object.entries(sections).forEach(([key, el]) => {
      if (!el) return;
      el.classList.toggle("hidden", key !== active);
    });
    Object.entries(navTabs).forEach(([key, el]) => {
      if (!el) return;
      el.classList.toggle("border-primary", key === active);
      el.classList.toggle("text-primary", key === active);
      el.classList.toggle("border-transparent", key !== active);
      el.classList.toggle("text-slate-500", key !== active);
    });
  };

  Object.entries(navTabs).forEach(([key, el]) => {
    if (!el) return;
    el.addEventListener("click", (e) => { e.preventDefault(); switchSection(key); });
  });

  // Show carga link if logged in
  if (localStorage.getItem("token")) {
    const cargaLink = document.getElementById("cargaLink");
    if (cargaLink) cargaLink.style.display = "";
  }

  const load = async () => {
    const categoria = getSelectedCategoria();
    const torneo = getSelectedTorneo();
    const tabla = await api.get(`/tabla?categoria=${encodeURIComponent(categoria)}`).catch(() => []);
    const equipos = await api.get(`/equipos?categoria=${encodeURIComponent(categoria)}`).catch(() => []);
    const goleadores = await api.get(`/tabla/goleadores?categoria=${encodeURIComponent(categoria)}`).catch(() => []);
    const tarjetas = await api.get(`/tabla/tarjetas?categoria=${encodeURIComponent(categoria)}`).catch(() => ({ amarillas: [], rojas: [] }));

    const categoryMap = Object.fromEntries(equipos.map((e) => [e.nombre, e.categoria || "Sin categoría"]));

    const splitByCategory = { Primera: [], Tercera: [], Senior: [] };
    tabla.forEach((row) => {
      const category = categoryMap[row.equipo] || "Sin categoría";
      if (splitByCategory[category]) {
        splitByCategory[category].push(row);
      }
    });

    const fillBody = (id, data) => {
      const el = document.getElementById(id);
      if (!el) return;
      el.innerHTML = "";
      data.forEach((row, i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td>${i + 1}</td><td>${row.equipo}</td><td>${row.puntos}</td><td>${row.pj}</td><td>${row.pg}</td><td>${row.pe}</td><td>${row.pp}</td><td>${row.gf}</td><td>${row.gc}</td><td>${row.dg}</td>`;
        el.appendChild(tr);
      });
      if (!data.length) {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td colspan='10' class='text-center text-sm text-slate-300'>No hay datos para esta categoría</td>`;
        el.appendChild(tr);
      }
    };

    fillBody("tablaPrimeraBody", splitByCategory.Primera);
    fillBody("tablaTerceraBody", splitByCategory.Tercera);
    fillBody("tablaSeniorBody", splitByCategory.Senior);

    const bodyG = document.getElementById("goleadoresBody");
    if (bodyG) {
      bodyG.innerHTML = "";
      goleadores.slice(0, 20).forEach((g, i) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td>${i + 1}</td><td>${g.nombre}</td><td>${g.equipo}</td><td>${g.goles}</td>`;
        bodyG.appendChild(tr);
      });
      if (!goleadores.length) bodyG.innerHTML = "<tr><td colspan='4' class='text-center text-sm text-slate-300'>No hay goleadores registrados</td></tr>";
    }

    const bodyA = document.getElementById("tarjetasAmarillasBody");
    const bodyR = document.getElementById("tarjetasRojasBody");
    if (bodyA) bodyA.innerHTML = "";
    if (bodyR) bodyR.innerHTML = "";
    tarjetas.amarillas?.slice(0, 20).forEach((x, i) => {
      if (!bodyA) return;
      const tr = document.createElement("tr");
      tr.innerHTML = `<td>${i + 1}</td><td>${x.nombre}</td><td>${x.equipo}</td><td>${x.amarillas}</td>`;
      bodyA.appendChild(tr);
    });
    tarjetas.rojas?.slice(0, 20).forEach((x, i) => {
      if (!bodyR) return;
      const tr = document.createElement("tr");
      tr.innerHTML = `<td>${i + 1}</td><td>${x.nombre}</td><td>${x.equipo}</td><td>${x.rojas}</td>`;
      bodyR.appendChild(tr);
    });

    // Load jornadas results
    await loadJornadaResults(categoria, torneo);
  };

  const loadJornadaResults = async (categoria, torneo) => {
    const container = document.getElementById("jornadaResultados");
    const tabsContainer = document.getElementById("jornadaTabs");
    const section = document.getElementById("resultadosJornada");
    if (!container || !tabsContainer || !section) return;

    const jornadas = await api.get(`/partidos/jornadas?categoria=${encodeURIComponent(categoria)}&torneo=${encodeURIComponent(torneo)}`).catch(() => []);
    if (!jornadas.length) {
      container.innerHTML = "<p class='text-sm text-slate-400'>No hay resultados disponibles</p>";
      tabsContainer.innerHTML = "";
      return;
    }

    tabsContainer.innerHTML = "";
    jornadas.forEach((j, idx) => {
      const btn = document.createElement("button");
      btn.className = `px-3 py-1 rounded text-sm font-bold ${idx === 0 ? "bg-primary text-white" : "bg-primary/20 text-slate-300"}`;
      btn.textContent = `Fecha ${j.jornada}`;
      btn.addEventListener("click", async () => {
        tabsContainer.querySelectorAll("button").forEach((b) => { b.className = "px-3 py-1 rounded text-sm font-bold bg-primary/20 text-slate-300"; });
        btn.className = "px-3 py-1 rounded text-sm font-bold bg-primary text-white";
        await renderJornadaPartidos(j.jornada, categoria, torneo);
      });
      tabsContainer.appendChild(btn);
    });

    // Load first jornada by default
    await renderJornadaPartidos(jornadas[0].jornada, categoria, torneo);
  };

  const renderJornadaPartidos = async (jornada, categoria, torneo) => {
    const container = document.getElementById("jornadaResultados");
    const partidos = await api.get(`/partidos?categoria=${encodeURIComponent(categoria)}&torneo=${encodeURIComponent(torneo)}&jornada=${jornada}`).catch(() => []);
    container.innerHTML = "";

    if (!partidos.length) {
      container.innerHTML = "<p class='text-sm text-slate-400'>No hay partidos en esta jornada</p>";
      return;
    }

    partidos.forEach((p) => {
      const isJugado = p.estado === "jugado";
      const localEscudo = p.localEscudo || "";
      const visitanteEscudo = p.visitanteEscudo || "";
      const card = document.createElement("div");
      card.className = "bg-white/5 border border-white/10 rounded-lg p-2 sm:p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2";
      card.innerHTML = `
        <div class="flex items-center gap-2 sm:gap-3 flex-1">
          ${localEscudo ? `<img src="${localEscudo}" alt="" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover" onerror="this.style.display='none'" />` : ""}
          <span class="font-semibold text-right flex-1 text-xs sm:text-sm truncate">${p.local}</span>
          ${isJugado
            ? `<span class="bg-primary/20 text-primary font-bold px-2 sm:px-3 py-1 rounded-lg text-sm sm:text-base min-w-[50px] sm:min-w-[70px] text-center">${p.golesLocal} - ${p.golesVisitante}</span>`
            : `<span class="bg-slate-700 text-slate-400 px-2 sm:px-3 py-1 rounded-lg text-xs">Pendiente</span>`
          }
          <span class="font-semibold flex-1 text-xs sm:text-sm truncate">${p.visitante}</span>
          ${visitanteEscudo ? `<img src="${visitanteEscudo}" alt="" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover" onerror="this.style.display='none'" />` : ""}
        </div>
        <span class="text-[10px] sm:text-xs text-slate-500 sm:ml-3 mt-0.5 sm:mt-0">${p.estadio || ""}</span>
      `;
      container.appendChild(card);
    });
  };

  const showCategorySection = (activeCategory) => {
    const catSections = {
      Primera: document.getElementById("primeraContainer"),
      Tercera: document.getElementById("terceraContainer"),
      Senior: document.getElementById("seniorContainer"),
    };
    Object.entries(catSections).forEach(([cat, el]) => {
      if (!el) return;
      el.classList.toggle("hidden", cat !== activeCategory);
    });

    ["tabPrimera", "tabTercera", "tabSenior"].forEach((id) => {
      const btn = document.getElementById(id);
      if (!btn) return;
      btn.classList.toggle("bg-primary", id === `tab${activeCategory}`);
      btn.classList.toggle("bg-primary/20", id !== `tab${activeCategory}`);
    });
  };

  document.getElementById("tabPrimera")?.addEventListener("click", () => showCategorySection("Primera"));
  document.getElementById("tabTercera")?.addEventListener("click", () => showCategorySection("Tercera"));
  document.getElementById("tabSenior")?.addEventListener("click", () => showCategorySection("Senior"));

  // === Históricos H2H ===
  const h2hCategoria = document.getElementById("h2hCategoria");
  const h2hTeam1 = document.getElementById("h2hTeam1");
  const h2hTeam2 = document.getElementById("h2hTeam2");
  const h2hComparar = document.getElementById("h2hComparar");
  const h2hResumen = document.getElementById("h2hResumen");
  const h2hPartidos = document.getElementById("h2hPartidos");

  const loadH2HEquipos = async () => {
    const cat = h2hCategoria?.value;
    if (!cat || !h2hTeam1 || !h2hTeam2) return;
    const equipos = await api.get(`/equipos?categoria=${encodeURIComponent(cat)}`).catch(() => []);
    const buildOptions = (sel) => {
      sel.innerHTML = '<option value="" disabled selected>Seleccionar equipo</option>';
      equipos.forEach((e) => {
        const opt = document.createElement("option");
        opt.value = e.id;
        opt.textContent = e.nombre;
        sel.appendChild(opt);
      });
    };
    buildOptions(h2hTeam1);
    buildOptions(h2hTeam2);
    if (h2hResumen) h2hResumen.innerHTML = "";
    if (h2hPartidos) h2hPartidos.innerHTML = "";
  };

  h2hCategoria?.addEventListener("change", loadH2HEquipos);

  h2hComparar?.addEventListener("click", async () => {
    const t1 = h2hTeam1?.value;
    const t2 = h2hTeam2?.value;
    const cat = h2hCategoria?.value;
    if (!t1 || !t2) return;
    if (t1 === t2) { alert("Seleccioná dos equipos diferentes"); return; }

    const data = await api.get(`/partidos/h2h?team1=${t1}&team2=${t2}&categoria=${encodeURIComponent(cat)}`).catch(() => null);
    if (!data) { if (h2hPartidos) h2hPartidos.innerHTML = "<p class='text-sm text-slate-400'>Error al cargar historial</p>"; return; }

    const team1Name = h2hTeam1.options[h2hTeam1.selectedIndex]?.text || "Equipo 1";
    const team2Name = h2hTeam2.options[h2hTeam2.selectedIndex]?.text || "Equipo 2";
    const r = data.resumen;

    // Render resumen
    if (h2hResumen) {
      h2hResumen.innerHTML = r.totalMatches === 0
        ? `<div class="card-bg p-4 rounded-lg text-center"><p class="text-sm text-slate-400">No hay partidos registrados entre estos equipos en esta categoría</p></div>`
        : `<div class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-3">
            <div class="card-bg p-3 sm:p-4 rounded-lg text-center">
              <div class="text-2xl sm:text-3xl font-black text-primary">${r.team1Wins}</div>
              <div class="text-[10px] sm:text-xs text-slate-400 mt-1 truncate">${team1Name}</div>
            </div>
            <div class="card-bg p-3 sm:p-4 rounded-lg text-center">
              <div class="text-2xl sm:text-3xl font-black text-slate-400">${r.draws}</div>
              <div class="text-[10px] sm:text-xs text-slate-400 mt-1">Empates</div>
            </div>
            <div class="card-bg p-3 sm:p-4 rounded-lg text-center">
              <div class="text-2xl sm:text-3xl font-black text-primary">${r.team2Wins}</div>
              <div class="text-[10px] sm:text-xs text-slate-400 mt-1 truncate">${team2Name}</div>
            </div>
            <div class="card-bg p-3 sm:p-4 rounded-lg text-center hidden sm:block">
              <div class="text-lg sm:text-2xl font-bold">${r.team1Goals} - ${r.team2Goals}</div>
              <div class="text-[10px] sm:text-xs text-slate-400 mt-1">Goles</div>
            </div>
            <div class="card-bg p-3 sm:p-4 rounded-lg text-center hidden sm:block">
              <div class="text-lg sm:text-2xl font-bold">${r.totalMatches}</div>
              <div class="text-[10px] sm:text-xs text-slate-400 mt-1">Partidos</div>
            </div>
          </div>
          <div class="flex sm:hidden gap-2 mt-2">
            <div class="card-bg p-2 rounded-lg text-center flex-1">
              <span class="text-sm font-bold">${r.team1Goals}-${r.team2Goals}</span>
              <span class="text-[10px] text-slate-400 ml-1">goles</span>
            </div>
            <div class="card-bg p-2 rounded-lg text-center flex-1">
              <span class="text-sm font-bold">${r.totalMatches}</span>
              <span class="text-[10px] text-slate-400 ml-1">partidos</span>
            </div>
          </div>`;
    }

    // Render partidos
    if (h2hPartidos) {
      if (!data.partidos.length) { h2hPartidos.innerHTML = ""; return; }
      h2hPartidos.innerHTML = "";
      data.partidos.forEach((p) => {
        const localEscudo = p.localEscudo || "";
        const visitanteEscudo = p.visitanteEscudo || "";
        const goleadoresHTML = p.goleadores.length
          ? `<div class="flex flex-wrap gap-1 mt-2 pt-2 border-t border-white/5">
              ${p.goleadores.map((g) => `<span class="text-[10px] sm:text-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded">⚽ ${g.jugador || "Gol"}${g.minuto ? " " + g.minuto + "'" : ""}</span>`).join("")}
            </div>`
          : "";
        const fechaStr = p.fecha ? new Date(p.fecha).toLocaleDateString("es-AR", { day: "2-digit", month: "short", year: "numeric" }) : "";

        const card = document.createElement("div");
        card.className = "card-bg p-3 sm:p-4 rounded-lg";
        card.innerHTML = `
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] sm:text-xs text-slate-500">${fechaStr}${p.jornada ? " • Fecha " + p.jornada : ""}${p.torneo ? " • " + p.torneo : ""}</span>
            <span class="text-[10px] sm:text-xs text-slate-500">${p.estadio || ""}</span>
          </div>
          <div class="flex items-center gap-2 sm:gap-3">
            ${localEscudo ? `<img src="${localEscudo}" alt="" class="w-5 h-5 sm:w-7 sm:h-7 rounded-full object-cover" onerror="this.style.display='none'" />` : ""}
            <span class="font-semibold text-right flex-1 text-xs sm:text-sm truncate">${p.local}</span>
            <span class="bg-primary/20 text-primary font-bold px-2 sm:px-3 py-1 rounded-lg text-sm sm:text-base min-w-[50px] sm:min-w-[70px] text-center">${p.golesLocal} - ${p.golesVisitante}</span>
            <span class="font-semibold flex-1 text-xs sm:text-sm truncate">${p.visitante}</span>
            ${visitanteEscudo ? `<img src="${visitanteEscudo}" alt="" class="w-5 h-5 sm:w-7 sm:h-7 rounded-full object-cover" onerror="this.style.display='none'" />` : ""}
          </div>
          ${goleadoresHTML}
        `;
        h2hPartidos.appendChild(card);
      });
    }
  });

  // Load H2H equipos on first visit
  loadH2HEquipos();

  const applyFilter = async () => {
    await load();
  };
  document.getElementById("categoriaFiltro")?.addEventListener("change", applyFilter);
  document.getElementById("torneoFiltro")?.addEventListener("change", applyFilter);
  document.getElementById("aplicarFiltro")?.addEventListener("click", applyFilter);

  await applyFilter();
  showCategorySection("Primera");
  switchSection("tabla");
};

window.addEventListener("DOMContentLoaded", () => {
  withNav();
  loginScreen();
  loadDashboard();
  equiposScreen();
  jugadoresScreen();
  partidosScreen();
  estadisticasScreen();
});
