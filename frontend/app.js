const ACCOUNT_STORAGE_KEY = "accountProfile";
const PROTECTED_PAGES = new Set(["dashboard.html", "equipos.html", "jugadores.html", "partidos.html", "carga.html", "account.html"]);

const getCurrentPage = () => {
  const path = window.location.pathname.split("/").pop();
  return path || "index.html";
};

const isProtectedPage = () => PROTECTED_PAGES.has(getCurrentPage());

const getAuthHeaders = () => {
  const headers = { "Content-Type": "application/json" };
  const token = localStorage.getItem("token");
  if (token) headers.Authorization = `Bearer ${token}`;
  return headers;
};

const clearSession = () => {
  localStorage.removeItem("token");
  localStorage.removeItem(ACCOUNT_STORAGE_KEY);
};

const redirectToLogin = () => {
  if (getCurrentPage() !== "login.html") {
    window.location.href = "login.html";
  }
};

const getCachedAccount = () => {
  try {
    const raw = localStorage.getItem(ACCOUNT_STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch (error) {
    console.error("No se pudo leer la cuenta cacheada", error);
    return null;
  }
};

const setCachedAccount = (account) => {
  if (!account) return;
  localStorage.setItem(ACCOUNT_STORAGE_KEY, JSON.stringify(account));
};

const requestJson = async (path, options = {}) => {
  const response = await fetch(path, {
    ...options,
    headers: {
      ...getAuthHeaders(),
      ...(options.headers || {}),
    },
  });

  let payload = null;
  try {
    payload = await response.json();
  } catch (error) {
    payload = null;
  }

  if (response.status === 401 && isProtectedPage()) {
    clearSession();
    redirectToLogin();
    throw new Error(payload?.message || payload?.error || "Sesión inválida");
  }

  if (!response.ok) {
    throw new Error(payload?.message || payload?.error || "Error en la operación");
  }

  return payload;
};

const api = {
  get: (path) => requestJson(path),
  post: (path, body) => requestJson(path, { method: "POST", body: JSON.stringify(body) }),
  put: (path, body) => requestJson(path, { method: "PUT", body: JSON.stringify(body) }),
  del: (path) => requestJson(path, { method: "DELETE" }),
};

const getSelectedCategoria = () => document.getElementById("categoriaFiltro")?.value || "";
const getSelectedTorneo = () => document.getElementById("torneoFiltro")?.value || "";

const handleError = (err, id) => {
  console.error(err);
  const el = document.getElementById(id);
  if (el) el.textContent = err.message || "Error en la operación";
};

const applyAccountToUi = (account) => {
  if (!account) return;

  document.querySelectorAll("[data-account-name]").forEach((el) => {
    el.textContent = account.fullName || "Administrador";
  });
  document.querySelectorAll("[data-account-email]").forEach((el) => {
    el.textContent = account.email || "admin@juegodeequipo.com";
  });
  document.querySelectorAll("[data-account-role]").forEach((el) => {
    el.textContent = account.roleLabel || "Administrador Principal";
  });
  document.querySelectorAll("[data-account-code]").forEach((el) => {
    el.textContent = account.adminCode || "ADMIN-001";
  });
  document.querySelectorAll("[data-account-member-since]").forEach((el) => {
    el.textContent = account.memberSince || "2023";
  });
  document.querySelectorAll("[data-account-avatar]").forEach((el) => {
    el.src = account.avatarUrl || "/assets/logo.png";
    el.alt = account.fullName || "Administrador";
  });
};

const wireLogoutActions = (root = document) => {
  root.querySelectorAll("[data-action='logout']").forEach((button) => {
    if (button.dataset.bound === "true") return;
    button.dataset.bound = "true";
    button.addEventListener("click", () => {
      clearSession();
      redirectToLogin();
    });
  });
};

const markActiveNav = (root = document) => {
  const currentPage = getCurrentPage();
  root.querySelectorAll("[data-nav-page]").forEach((link) => {
    const isActive = link.dataset.navPage === currentPage;
    if (link.classList.contains("sidebar-link")) {
      link.classList.toggle("bg-primary", isActive);
      link.classList.toggle("text-on-primary", isActive);
      link.classList.toggle("shadow-lg", isActive);
      link.classList.toggle("shadow-primary/20", isActive);
      link.classList.toggle("text-on-surface-variant", !isActive);
    } else {
      link.classList.toggle("text-primary", isActive);
      link.classList.toggle("font-semibold", isActive);
    }
  });
};

const syncSharedNavState = async (root = document) => {
  const token = localStorage.getItem("token");
  root.querySelectorAll("[data-auth-only]").forEach((el) => {
    el.classList.toggle("hidden", !token);
  });
  root.querySelectorAll("[data-guest-only]").forEach((el) => {
    el.classList.toggle("hidden", Boolean(token));
  });

  if (!token) return;

  const cachedAccount = getCachedAccount();
  if (cachedAccount) applyAccountToUi(cachedAccount);

  try {
    const response = await api.get("/auth/account");
    if (response?.account) {
      setCachedAccount(response.account);
      applyAccountToUi(response.account);
    }
  } catch (error) {
    console.error("No se pudo sincronizar la cuenta", error);
  }
};

const wireSidebar = () => {
  const sidebar = document.getElementById("sidebarNav");
  const overlay = document.getElementById("sidebarOverlay");
  const toggle = document.getElementById("sidebarToggle");
  const close = document.getElementById("sidebarClose");
  if (!sidebar) return;

  const open = () => {
    sidebar.classList.remove("-translate-x-full");
    sidebar.classList.add("translate-x-0");
    overlay?.classList.remove("hidden");
  };
  const shut = () => {
    sidebar.classList.add("-translate-x-full");
    sidebar.classList.remove("translate-x-0");
    overlay?.classList.add("hidden");
  };

  toggle?.addEventListener("click", open);
  close?.addEventListener("click", shut);
  overlay?.addEventListener("click", shut);
};

const withNav = () => {
  const layout = document.getElementById("sharedLayout");
  if (!layout) return;

  fetch("shared-sidebar.html")
    .then((r) => r.text())
    .then(async (html) => {
      const mainContent = layout.querySelector("main");
      layout.insertAdjacentHTML("afterbegin", html);

      if (mainContent) {
        const wrapper = document.createElement("div");
        wrapper.className = "flex flex-1 overflow-hidden";
        const aside = layout.querySelector("#sidebarNav");
        if (aside && mainContent) {
          aside.remove();
          mainContent.remove();
          wrapper.appendChild(aside);
          wrapper.appendChild(mainContent);
          const header = layout.querySelector("#sidebarHeader");
          const overlayEl = layout.querySelector("#sidebarOverlay");
          if (header) {
            if (overlayEl) header.after(overlayEl);
            const insertAfter = overlayEl || header;
            insertAfter.after(wrapper);
          } else {
            layout.appendChild(wrapper);
          }
        }
      }

      wireSidebar();
      markActiveNav(layout);
      wireLogoutActions(layout);
      await syncSharedNavState(layout);
    })
    .catch((err) => console.error("No se encontró shared-sidebar", err));
};

const loginScreen = () => {
  const form = document.getElementById("loginForm");
  if (!form) return;
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const emailInput = document.getElementById("loginEmail") || document.getElementById("email");
    const passwordInput = document.getElementById("loginPassword") || document.getElementById("password");
    const errorBox = document.getElementById("loginError") || document.getElementById("loginMessage");
    const email = emailInput?.value.trim() || "";
    const password = passwordInput?.value || "";
    if (errorBox) errorBox.textContent = "";

    if (!email || !password) {
      if (errorBox) errorBox.textContent = "Email y password son obligatorios";
      return;
    }

    try {
      const resp = await api.post("/auth/login", { email, password });
      if (resp && resp.token) {
        localStorage.setItem("token", resp.token);
        if (resp.account) setCachedAccount(resp.account);
        window.location.href = "dashboard.html";
      } else {
        if (errorBox) errorBox.textContent = resp.message || "Credenciales inválidas";
      }
    } catch (err) {
      handleError(err, "loginError");
    }
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
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `<td class="px-3 py-2"><img src="${team.escudo || 'https://via.placeholder.com/40'}" class="w-10 h-10 rounded-lg object-cover" alt='escudo'></td><td class="px-3 py-2 font-medium">${team.nombre}</td><td class="px-3 py-2 text-on-surface-variant">${team.estadio}</td><td class="px-3 py-2"><span class="px-2 py-0.5 text-xs rounded-full bg-primary/10 text-primary font-semibold">${team.categoria || '-'}</span></td><td class='px-3 py-2 space-x-2'><button data-id='${team.id}' data-nombre='${team.nombre}' data-estadio='${team.estadio}' data-escudo='${team.escudo}' data-categoria='${team.categoria || ""}' class='editEquipo px-2 py-1 bg-primary/20 text-primary rounded-lg text-xs font-semibold hover:bg-primary/30 transition-colors'>Editar</button><button data-id='${team.id}' class='deleteEquipo px-2 py-1 bg-error/20 text-error rounded-lg text-xs font-semibold hover:bg-error/30 transition-colors'>Eliminar</button></td>`;
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

  document.getElementById("closeEquipoModal")?.addEventListener("click", () => editModal.classList.add("hidden"));
  document.getElementById("editEquipoForm")?.addEventListener("submit", async (e) => {
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
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `<td class="px-3 py-2 font-medium">${jr.nombre}</td><td class="px-3 py-2 text-on-surface-variant">${jr.posicion}</td><td class="px-3 py-2">${jr.equipoNombre ? `<span class="px-2 py-0.5 text-xs rounded-full bg-primary/10 text-primary font-semibold">${jr.equipoNombre} (${jr.equipoCategoria || ''})</span>` : jr.equipo || "-"}</td><td class='px-3 py-2 space-x-2'><button data-id='${jr.id}' data-nombre='${jr.nombre}' data-posicion='${jr.posicion}' data-equipo='${jr.equipo}' class='editJugador px-2 py-1 bg-primary/20 text-primary rounded-lg text-xs font-semibold hover:bg-primary/30 transition-colors'>Editar</button><button data-id='${jr.id}' class='deleteJugador px-2 py-1 bg-error/20 text-error rounded-lg text-xs font-semibold hover:bg-error/30 transition-colors'>Eliminar</button></td>`;
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

  document.getElementById("closeJugadorModal")?.addEventListener("click", () => document.getElementById("jugadorEditModal").classList.add("hidden"));
  document.getElementById("editJugadorForm")?.addEventListener("submit", async (e) => {
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
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `<td class="px-3 py-2">${p.fecha}</td><td class="px-3 py-2">${p.hora}</td><td class="px-3 py-2 text-on-surface-variant">${p.estadio}</td><td class="px-3 py-2 font-medium">${p.local} vs ${p.visitante}</td><td class="px-3 py-2"><span class="px-2 py-0.5 text-xs rounded-full ${p.estado === 'jugado' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-primary/10 text-primary'} font-semibold">${p.estado}</span></td><td class="px-3 py-2"><button data-id='${p.id}' class='cargarResultado px-2 py-1 bg-tertiary/20 text-tertiary rounded-lg text-xs font-semibold hover:bg-tertiary/30 transition-colors'>Cargar</button></td>`;
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

  document.getElementById("closePartidoResultModal")?.addEventListener("click", () => document.getElementById("partidoResultModal").classList.add("hidden"));
  document.getElementById("partidoResultForm")?.addEventListener("submit", async (e) => {
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
      el.classList.toggle("text-on-surface-variant", key !== active);
    });
  };

  Object.entries(navTabs).forEach(([key, el]) => {
    if (!el) return;
    el.addEventListener("click", (e) => { e.preventDefault(); switchSection(key); });
  });

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
        tr.className = "border-b border-outline-variant/10";
        tr.innerHTML = `<td class="px-2 py-1">${i + 1}</td><td class="px-2 py-1 font-medium">${row.equipo}</td><td class="px-2 py-1 font-bold text-primary">${row.puntos}</td><td class="px-2 py-1">${row.pj}</td><td class="px-2 py-1">${row.pg}</td><td class="px-2 py-1">${row.pe}</td><td class="px-2 py-1">${row.pp}</td><td class="px-2 py-1">${row.gf}</td><td class="px-2 py-1">${row.gc}</td><td class="px-2 py-1">${row.dg}</td>`;
        el.appendChild(tr);
      });
      if (!data.length) {
        const tr = document.createElement("tr");
        tr.innerHTML = `<td colspan='10' class='text-center text-sm text-on-surface-variant py-4'>No hay datos para esta categoría</td>`;
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
        tr.className = "border-b border-outline-variant/10";
        tr.innerHTML = `<td class="px-2 py-1">${i + 1}</td><td class="px-2 py-1 font-medium">${g.nombre}</td><td class="px-2 py-1 text-on-surface-variant">${g.equipo}</td><td class="px-2 py-1 font-bold text-primary">${g.goles}</td>`;
        bodyG.appendChild(tr);
      });
      if (!goleadores.length) bodyG.innerHTML = "<tr><td colspan='4' class='text-center text-sm text-on-surface-variant py-4'>No hay goleadores registrados</td></tr>";
    }

    const bodyA = document.getElementById("tarjetasAmarillasBody");
    const bodyR = document.getElementById("tarjetasRojasBody");
    if (bodyA) bodyA.innerHTML = "";
    if (bodyR) bodyR.innerHTML = "";
    tarjetas.amarillas?.slice(0, 20).forEach((x, i) => {
      if (!bodyA) return;
      const tr = document.createElement("tr");
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `<td class="px-2 py-1">${i + 1}</td><td class="px-2 py-1 font-medium">${x.nombre}</td><td class="px-2 py-1 text-on-surface-variant">${x.equipo}</td><td class="px-2 py-1 font-bold text-yellow-400">${x.amarillas}</td>`;
      bodyA.appendChild(tr);
    });
    tarjetas.rojas?.slice(0, 20).forEach((x, i) => {
      if (!bodyR) return;
      const tr = document.createElement("tr");
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `<td class="px-2 py-1">${i + 1}</td><td class="px-2 py-1 font-medium">${x.nombre}</td><td class="px-2 py-1 text-on-surface-variant">${x.equipo}</td><td class="px-2 py-1 font-bold text-error">${x.rojas}</td>`;
      bodyR.appendChild(tr);
    });

    await loadJornadaResults(categoria, torneo);
  };

  const loadJornadaResults = async (categoria, torneo) => {
    const container = document.getElementById("jornadaResultados");
    const tabsContainer = document.getElementById("jornadaTabs");
    const section = document.getElementById("resultadosJornada");
    if (!container || !tabsContainer || !section) return;

    const jornadas = await api.get(`/partidos/jornadas?categoria=${encodeURIComponent(categoria)}&torneo=${encodeURIComponent(torneo)}`).catch(() => []);
    if (!jornadas.length) {
      container.innerHTML = "<p class='text-sm text-on-surface-variant'>No hay resultados disponibles</p>";
      tabsContainer.innerHTML = "";
      return;
    }

    tabsContainer.innerHTML = "";
    jornadas.forEach((j, idx) => {
      const btn = document.createElement("button");
      btn.className = `px-3 py-1 rounded-lg text-sm font-bold transition-colors ${idx === 0 ? "bg-primary text-white" : "bg-primary/20 text-on-surface-variant"}`;
      btn.textContent = `Fecha ${j.jornada}`;
      btn.addEventListener("click", async () => {
        tabsContainer.querySelectorAll("button").forEach((b) => { b.className = "px-3 py-1 rounded-lg text-sm font-bold bg-primary/20 text-on-surface-variant transition-colors"; });
        btn.className = "px-3 py-1 rounded-lg text-sm font-bold bg-primary text-white transition-colors";
        await renderJornadaPartidos(j.jornada, categoria, torneo);
      });
      tabsContainer.appendChild(btn);
    });

    await renderJornadaPartidos(jornadas[0].jornada, categoria, torneo);
  };

  const renderJornadaPartidos = async (jornada, categoria, torneo) => {
    const container = document.getElementById("jornadaResultados");
    const partidos = await api.get(`/partidos?categoria=${encodeURIComponent(categoria)}&torneo=${encodeURIComponent(torneo)}&jornada=${jornada}`).catch(() => []);
    container.innerHTML = "";

    if (!partidos.length) {
      container.innerHTML = "<p class='text-sm text-on-surface-variant'>No hay partidos en esta jornada</p>";
      return;
    }

    partidos.forEach((p) => {
      const isJugado = p.estado === "jugado";
      const localEscudo = p.localEscudo || "";
      const visitanteEscudo = p.visitanteEscudo || "";
      const card = document.createElement("div");
      card.className = "bg-surface-container-high border border-outline-variant/10 rounded-xl p-2 sm:p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-1 sm:gap-2";
      card.innerHTML = `
        <div class="flex items-center gap-2 sm:gap-3 flex-1">
          ${localEscudo ? `<img src="${localEscudo}" alt="" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover" onerror="this.style.display='none'" />` : ""}
          <span class="font-semibold text-right flex-1 text-xs sm:text-sm truncate">${p.local}</span>
          ${isJugado
            ? `<span class="bg-primary/20 text-primary font-bold px-2 sm:px-3 py-1 rounded-lg text-sm sm:text-base min-w-[50px] sm:min-w-[70px] text-center">${p.golesLocal} - ${p.golesVisitante}</span>`
            : `<span class="bg-surface-container text-on-surface-variant px-2 sm:px-3 py-1 rounded-lg text-xs">Pendiente</span>`
          }
          <span class="font-semibold flex-1 text-xs sm:text-sm truncate">${p.visitante}</span>
          ${visitanteEscudo ? `<img src="${visitanteEscudo}" alt="" class="w-5 h-5 sm:w-6 sm:h-6 rounded-full object-cover" onerror="this.style.display='none'" />` : ""}
        </div>
        <span class="text-[10px] sm:text-xs text-on-surface-variant sm:ml-3 mt-0.5 sm:mt-0">${p.estadio || ""}</span>
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
    if (!data) { if (h2hPartidos) h2hPartidos.innerHTML = "<p class='text-sm text-on-surface-variant'>Error al cargar historial</p>"; return; }

    const team1Name = h2hTeam1.options[h2hTeam1.selectedIndex]?.text || "Equipo 1";
    const team2Name = h2hTeam2.options[h2hTeam2.selectedIndex]?.text || "Equipo 2";
    const r = data.resumen;

    if (h2hResumen) {
      h2hResumen.innerHTML = r.totalMatches === 0
        ? `<div class="bg-surface-container-high p-4 rounded-xl text-center"><p class="text-sm text-on-surface-variant">No hay partidos registrados entre estos equipos en esta categoría</p></div>`
        : `<div class="grid grid-cols-3 sm:grid-cols-5 gap-2 sm:gap-3">
            <div class="bg-surface-container-high p-3 sm:p-4 rounded-xl text-center">
              <div class="text-2xl sm:text-3xl font-black text-primary">${r.team1Wins}</div>
              <div class="text-[10px] sm:text-xs text-on-surface-variant mt-1 truncate">${team1Name}</div>
            </div>
            <div class="bg-surface-container-high p-3 sm:p-4 rounded-xl text-center">
              <div class="text-2xl sm:text-3xl font-black text-on-surface-variant">${r.draws}</div>
              <div class="text-[10px] sm:text-xs text-on-surface-variant mt-1">Empates</div>
            </div>
            <div class="bg-surface-container-high p-3 sm:p-4 rounded-xl text-center">
              <div class="text-2xl sm:text-3xl font-black text-primary">${r.team2Wins}</div>
              <div class="text-[10px] sm:text-xs text-on-surface-variant mt-1 truncate">${team2Name}</div>
            </div>
            <div class="bg-surface-container-high p-3 sm:p-4 rounded-xl text-center hidden sm:block">
              <div class="text-lg sm:text-2xl font-bold">${r.team1Goals} - ${r.team2Goals}</div>
              <div class="text-[10px] sm:text-xs text-on-surface-variant mt-1">Goles</div>
            </div>
            <div class="bg-surface-container-high p-3 sm:p-4 rounded-xl text-center hidden sm:block">
              <div class="text-lg sm:text-2xl font-bold">${r.totalMatches}</div>
              <div class="text-[10px] sm:text-xs text-on-surface-variant mt-1">Partidos</div>
            </div>
          </div>
          <div class="flex sm:hidden gap-2 mt-2">
            <div class="bg-surface-container-high p-2 rounded-xl text-center flex-1">
              <span class="text-sm font-bold">${r.team1Goals}-${r.team2Goals}</span>
              <span class="text-[10px] text-on-surface-variant ml-1">goles</span>
            </div>
            <div class="bg-surface-container-high p-2 rounded-xl text-center flex-1">
              <span class="text-sm font-bold">${r.totalMatches}</span>
              <span class="text-[10px] text-on-surface-variant ml-1">partidos</span>
            </div>
          </div>`;
    }

    if (h2hPartidos) {
      if (!data.partidos.length) { h2hPartidos.innerHTML = ""; return; }
      h2hPartidos.innerHTML = "";
      data.partidos.forEach((p) => {
        const localEscudo = p.localEscudo || "";
        const visitanteEscudo = p.visitanteEscudo || "";
        const goleadoresHTML = p.goleadores.length
          ? `<div class="flex flex-wrap gap-1 mt-2 pt-2 border-t border-outline-variant/10">
              ${p.goleadores.map((g) => `<span class="text-[10px] sm:text-xs bg-primary/10 text-primary px-1.5 py-0.5 rounded">⚽ ${g.jugador || "Gol"}${g.minuto ? " " + g.minuto + "'" : ""}</span>`).join("")}
            </div>`
          : "";
        const fechaStr = p.fecha ? new Date(p.fecha).toLocaleDateString("es-AR", { day: "2-digit", month: "short", year: "numeric" }) : "";

        const card = document.createElement("div");
        card.className = "bg-surface-container-high p-3 sm:p-4 rounded-xl";
        card.innerHTML = `
          <div class="flex items-center justify-between mb-2">
            <span class="text-[10px] sm:text-xs text-on-surface-variant">${fechaStr}${p.jornada ? " • Fecha " + p.jornada : ""}${p.torneo ? " • " + p.torneo : ""}</span>
            <span class="text-[10px] sm:text-xs text-on-surface-variant">${p.estadio || ""}</span>
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

const accountScreen = async () => {
  const page = document.getElementById("accountPage");
  if (!page) return;

  const profileForm = document.getElementById("accountProfileForm");
  const passwordForm = document.getElementById("accountPasswordForm");
  const messageBox = document.getElementById("accountMessage");
  const passwordMessageBox = document.getElementById("passwordMessage");

  const showMessage = (el, message, type = "success") => {
    if (!el) return;
    el.textContent = message;
    el.className = "rounded-xl border px-4 py-3 text-sm font-body";
    el.classList.add(type === "success" ? "border-emerald-500/30" : "border-error/30");
    el.classList.add(type === "success" ? "text-emerald-300" : "text-error");
    el.classList.add(type === "success" ? "bg-emerald-500/10" : "bg-error/10");
  };

  const populateForm = (account) => {
    const el = (id) => document.getElementById(id);
    el("accountFullName").value = account.fullName || "";
    el("accountEmail").value = account.email || "";
    el("accountPhone").value = account.phone || "";
    el("accountBio").value = account.bio || "";
    const avatarInput = el("accountAvatarUrl");
    if (avatarInput) avatarInput.value = account.avatarUrl || "";
    el("accountEmailNotifications").checked = Boolean(account.preferences?.emailNotifications);
    el("accountLiveNotifications").checked = Boolean(account.preferences?.liveNotifications);
    el("accountDeveloperMode").checked = Boolean(account.preferences?.developerMode);
    applyAccountToUi(account);
  };

  try {
    const response = await api.get("/auth/account");
    if (response?.account) {
      setCachedAccount(response.account);
      populateForm(response.account);
    }
  } catch (error) {
    showMessage(messageBox, error.message || "No se pudo cargar la cuenta", "error");
  }

  page.querySelector("[data-avatar-trigger]")?.addEventListener("click", () => {
    const input = document.getElementById("accountAvatarUrl");
    if (input) { input.scrollIntoView({ behavior: "smooth", block: "center" }); input.focus(); }
  });

  profileForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const response = await api.put("/auth/account", {
        fullName: document.getElementById("accountFullName").value.trim(),
        email: document.getElementById("accountEmail").value.trim(),
        phone: document.getElementById("accountPhone").value.trim(),
        bio: document.getElementById("accountBio").value.trim(),
        avatarUrl: document.getElementById("accountAvatarUrl")?.value.trim() || "",
        preferences: {
          emailNotifications: document.getElementById("accountEmailNotifications").checked,
          liveNotifications: document.getElementById("accountLiveNotifications").checked,
          developerMode: document.getElementById("accountDeveloperMode").checked,
        },
      });

      if (response?.token) localStorage.setItem("token", response.token);
      if (response?.account) {
        setCachedAccount(response.account);
        populateForm(response.account);
      }
      showMessage(messageBox, response?.message || "Cuenta actualizada");
      await syncSharedNavState(document);
    } catch (error) {
      showMessage(messageBox, error.message || "No se pudo guardar la cuenta", "error");
    }
  });

  passwordForm?.addEventListener("submit", async (event) => {
    event.preventDefault();

    try {
      const response = await api.post("/auth/account/password", {
        currentPassword: document.getElementById("accountCurrentPassword").value,
        newPassword: document.getElementById("accountNewPassword").value,
        confirmPassword: document.getElementById("accountConfirmPassword").value,
      });

      passwordForm.reset();
      showMessage(passwordMessageBox, response?.message || "Contraseña actualizada");
    } catch (error) {
      showMessage(passwordMessageBox, error.message || "No se pudo actualizar la contraseña", "error");
    }
  });

  wireLogoutActions(page);
};

window.addEventListener("DOMContentLoaded", () => {
  if (isProtectedPage() && !localStorage.getItem("token")) {
    redirectToLogin();
    return;
  }

  withNav();
  markActiveNav(document);
  wireLogoutActions(document);
  syncSharedNavState(document);
  loginScreen();
  equiposScreen();
  jugadoresScreen();
  partidosScreen();
  estadisticasScreen();
  accountScreen();
});
