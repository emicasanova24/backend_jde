const API_BASE = "/api";
const ACCOUNT_STORAGE_KEY = "accountProfile";
const PROTECTED_PAGES = new Set(["dashboard", "equipos", "jugadores", "partidos", "carga", "account", "estadisticas", "noticias"]);

const getCurrentPage = () => {
  const routeMap = {
    '/': 'home', '/login': 'login', '/dashboard': 'dashboard',
    '/equipos': 'equipos', '/jugadores': 'jugadores',
    '/partidos': 'partidos', '/carga': 'carga',
    '/account': 'account', '/estadisticas': 'estadisticas',
    '/fixture': 'fixture',
    '/noticias': 'noticias',
  };
  return routeMap[window.location.pathname] || 'home';
};

const isProtectedPage = () => PROTECTED_PAGES.has(getCurrentPage());

const getAuthHeaders = (includeJsonContentType = true) => {
  const headers = includeJsonContentType ? { "Content-Type": "application/json" } : {};
  const token = localStorage.getItem("token");
  if (token) {
    headers.Authorization = `Bearer ${token}`;
    headers["X-Auth-Token"] = token;
  }
  return headers;
};

const clearSession = () => {
  localStorage.removeItem("token");
  localStorage.removeItem(ACCOUNT_STORAGE_KEY);
};

const redirectToLogin = () => {
  if (getCurrentPage() !== "login") {
    window.location.href = "/login";
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
  const url = path.startsWith("http") ? path : API_BASE + path;
  const response = await fetch(url, {
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

  fetch("/sidebar")
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
        window.location.href = "/dashboard";
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
      tr.innerHTML = `<td class="px-3 py-2"><img src="${team.escudo || 'https://via.placeholder.com/40'}" class="w-10 h-auto rounded-lg object-cover" alt='escudo'></td><td class="px-3 py-2 font-medium">${team.nombre}</td><td class="px-3 py-2 text-on-surface-variant">${team.estadio}</td><td class="px-3 py-2"><span class="px-2 py-0.5 text-xs rounded-full bg-primary/10 text-primary font-semibold">${team.categoria || '-'}</span></td><td class='px-3 py-2 space-x-2'><button data-id='${team.id}' data-nombre='${team.nombre}' data-estadio='${team.estadio}' data-escudo='${team.escudo}' data-categoria='${team.categoria || ""}' class='editEquipo px-2 py-1 bg-primary/20 text-primary rounded-lg text-xs font-semibold hover:bg-primary/30 transition-colors'>Editar</button><button data-id='${team.id}' class='deleteEquipo px-2 py-1 bg-error/20 text-error rounded-lg text-xs font-semibold hover:bg-error/30 transition-colors'>Eliminar</button></td>`;
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
  const POSITION_OPTIONS = [
    { value: "PEND", label: "Pendiente de asignación (PEND)" },
    { value: "PT", label: "Arquero (PT)" },
    { value: "LIB", label: "Libero (LIB)" },
    { value: "CT", label: "Defensa Central (CT)" },
    { value: "LAT", label: "Defensa Lateral (CA/LAT)" },
    { value: "MCD", label: "Mediocampista Defensivo (MCD)" },
    { value: "MC", label: "Mediocampista Centro (MC)" },
    { value: "CRR", label: "Carrilero (LA/CRR)" },
    { value: "INT", label: "Volante Exterior (VOL/INT)" },
    { value: "MP", label: "Enganche (MO/MP)" },
    { value: "EXT", label: "Extremo (EXT)" },
    { value: "SD", label: "Media Punta (SP/SD)" },
    { value: "DC", label: "Delantero Centro (CD/DC)" },
  ];
  const POSITION_LABELS = Object.fromEntries(POSITION_OPTIONS.map((option) => [option.value, option.label]));
  const esc = (value) => String(value ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
  const disciplinaModal = document.getElementById("jugadorDisciplinaModal");

  const getPositionLabel = (value) => POSITION_LABELS[value] || value || "-";
  const syncPositionSelect = (selectId, currentValue) => {
    const select = document.getElementById(selectId);
    if (!select) return;

    Array.from(select.options)
      .filter((option) => option.dataset.legacy === "true")
      .forEach((option) => option.remove());

    if (currentValue && !POSITION_LABELS[currentValue]) {
      const legacyOption = document.createElement("option");
      legacyOption.value = currentValue;
      legacyOption.textContent = `Actual (legacy): ${currentValue}`;
      legacyOption.dataset.legacy = "true";
      select.appendChild(legacyOption);
    }

    select.value = currentValue || "";
  };

  const closeDisciplinaModal = () => disciplinaModal?.classList.add("hidden");

  const renderDisciplinaHistorial = (payload) => {
    const subtitle = document.getElementById("jugadorDisciplinaSubtitle");
    const totalEl = document.getElementById("disciplinaTotal");
    const amarillasEl = document.getElementById("disciplinaAmarillas");
    const rojasEl = document.getElementById("disciplinaRojas");
    const list = document.getElementById("jugadorDisciplinaList");
    const jugador = payload?.jugador || {};
    const summary = payload?.summary || {};
    const historial = payload?.historial || [];

    if (subtitle) {
      subtitle.textContent = `${jugador.nombre || "Jugador"} · ${jugador.equipoNombre || "Sin equipo"}${jugador.equipoCategoria ? ` (${jugador.equipoCategoria})` : ""}`;
    }
    if (totalEl) totalEl.textContent = String(summary.total || 0);
    if (amarillasEl) amarillasEl.textContent = String(summary.amarillas || 0);
    if (rojasEl) rojasEl.textContent = String(summary.rojas || 0);
    if (!list) return;

    if (!historial.length) {
      list.innerHTML = "<p class='text-sm text-on-surface-variant'>Este jugador no registra tarjetas cargadas.</p>";
      return;
    }

    list.innerHTML = historial.map((evento) => {
      const tipo = evento.tipo === "amarilla" ? "Amarilla" : "Roja";
      const tipoClass = evento.tipo === "amarilla"
        ? "bg-yellow-500/10 border-yellow-500/20 text-yellow-300"
        : "bg-red-500/10 border-red-500/20 text-red-300";
      const rival = Number(evento.localId) === Number(jugador.equipo)
        ? evento.visitanteNombre
        : evento.localNombre;
      const fecha = evento.fecha || "Sin fecha";
      const torneo = evento.torneo || "Sin torneo";
      const categoria = evento.categoria || "Sin categoría";
      const jornada = evento.jornada ? `Fecha ${evento.jornada}` : "Fecha s/d";
      const minuto = Number.isInteger(Number(evento.minuto)) ? `${Number(evento.minuto)}'` : "Minuto s/d";

      return `
        <article class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4 mb-3 last:mb-0">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-bold ${tipoClass}">${tipo}</span>
                <span class="text-xs text-on-surface-variant">${esc(minuto)}</span>
              </div>
              <p class="font-semibold text-on-surface">vs ${esc(rival || "Rival desconocido")}</p>
              <p class="text-sm text-on-surface-variant mt-1">${esc(fecha)} · ${esc(jornada)} · ${esc(torneo)} · ${esc(categoria)}</p>
            </div>
            <div class="text-xs text-on-surface-variant sm:text-right">
              <p>Partido #${esc(evento.partidoId)}</p>
              <p>Equipo: ${esc(evento.equipoEventoNombre || jugador.equipoNombre || "-")}</p>
            </div>
          </div>
        </article>`;
    }).join("");
  };

  const openDisciplinaModal = async (jugadorId) => {
    const list = document.getElementById("jugadorDisciplinaList");
    if (list) {
      list.innerHTML = "<p class='text-sm text-on-surface-variant'>Cargando historial disciplinario…</p>";
    }
    disciplinaModal?.classList.remove("hidden");
    try {
      const payload = await api.get(`/jugadores/${jugadorId}/disciplina`);
      renderDisciplinaHistorial(payload);
    } catch (error) {
      if (list) {
        list.innerHTML = `<p class='text-sm text-error'>${esc(error.message || "No se pudo cargar el historial disciplinario")}</p>`;
      }
    }
  };

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
      tr.innerHTML = `<td class="px-3 py-2 font-medium">${esc(jr.nombre)}</td><td class="px-3 py-2 text-on-surface-variant"><span title="${esc(getPositionLabel(jr.posicion))}">${esc(jr.posicion)}</span></td><td class="px-3 py-2">${jr.equipoNombre ? `<span class="px-2 py-0.5 text-xs rounded-full bg-primary/10 text-primary font-semibold">${esc(jr.equipoNombre)} (${esc(jr.equipoCategoria || '')})</span>` : esc(jr.equipo || "-")}</td><td class='px-3 py-2 space-x-2'><button data-id='${jr.id}' data-nombre='${esc(jr.nombre)}' data-posicion='${esc(jr.posicion)}' data-equipo='${jr.equipo}' class='editJugador px-2 py-1 bg-primary/20 text-primary rounded-lg text-xs font-semibold hover:bg-primary/30 transition-colors'>Editar</button><button data-id='${jr.id}' class='disciplinaJugador px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs font-semibold hover:bg-yellow-500/30 transition-colors'>Disciplina</button><button data-id='${jr.id}' class='deleteJugador px-2 py-1 bg-error/20 text-error rounded-lg text-xs font-semibold hover:bg-error/30 transition-colors'>Eliminar</button></td>`;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll(".deleteJugador").forEach((btn) => {
      btn.addEventListener("click", async () => { await api.del(`/jugadores/${btn.dataset.id}`); load(); });
    });

    tbody.querySelectorAll(".editJugador").forEach((btn) => {
      btn.addEventListener("click", () => {
        document.getElementById("editJugadorId").value = btn.dataset.id;
        document.getElementById("editJugadorNombre").value = btn.dataset.nombre;
        syncPositionSelect("editJugadorPosicion", btn.dataset.posicion);
        document.getElementById("editJugadorEquipo").value = btn.dataset.equipo;
        document.getElementById("jugadorEditModal").classList.remove("hidden");
      });
    });

    tbody.querySelectorAll(".disciplinaJugador").forEach((btn) => {
      btn.addEventListener("click", () => openDisciplinaModal(btn.dataset.id));
    });
  };

  await loadEquipos();

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    await api.post("/jugadores", {
      nombre: document.getElementById("jugadorNombre").value.trim(),
      posicion: document.getElementById("jugadorPosicion").value,
      equipo: document.getElementById("jugadorEquipo").value,
    });
    form.reset();
    await load();
  });

  document.getElementById("closeJugadorModal")?.addEventListener("click", () => document.getElementById("jugadorEditModal").classList.add("hidden"));
  document.getElementById("closeJugadorDisciplinaModal")?.addEventListener("click", closeDisciplinaModal);
  disciplinaModal?.addEventListener("click", (event) => {
    if (event.target === disciplinaModal) closeDisciplinaModal();
  });
  document.getElementById("editJugadorForm")?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = document.getElementById("editJugadorId").value;
    await api.put(`/jugadores/${id}`, {
      nombre: document.getElementById("editJugadorNombre").value.trim(),
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

  const DEFAULT_ESCUDO = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32'%3E%3Crect width='32' height='32' rx='16' fill='%23334155'/%3E%3Ctext x='16' y='20' text-anchor='middle' font-size='14' fill='%23cbd5e1'%3E?%3C/text%3E%3C/svg%3E";
  let equiposCache = [];
  let currentJornada = null; // null = "Todas"
  let partidosCache = [];

  const $ = (id) => document.getElementById(id);
  const getCat = () => $("categoriaFiltro")?.value || "";
  const getTor = () => $("torneoFiltro")?.value || "";
  const getEst = () => $("estadoFiltro")?.value || "";

  const getEquipoById = (equipoId) => equiposCache.find((equipo) => String(equipo.id) === String(equipoId)) || null;

  const syncPartidoEstadioFromLocal = () => {
    const input = $("partidoEstadio");
    const localId = $("partidoLocal")?.value;
    const equipo = getEquipoById(localId);
    if (!input || !equipo?.estadio) return;
    if (input.value.trim() !== "") return;
    input.value = equipo.estadio;
  };

  function showToast(message, type = "success") {
    const t = $("toast");
    if (!t) return;
    t.textContent = message;
    t.className = `toast ${type === "success" ? "toast-success" : "toast-error"} show`;
    setTimeout(() => t.classList.remove("show"), 3000);
  }

  // --- Load equipos for selects ---
  const loadEquipos = async () => {
    const cat = getCat();
    if (!cat) return;
    equiposCache = await api.get(`/equipos?categoria=${encodeURIComponent(cat)}`).catch(() => []);
    ["partidoLocal", "partidoVisitante"].forEach((id) => {
      const sel = $(id);
      if (!sel) return;
      sel.innerHTML = '<option value="" disabled selected>Seleccionar</option>';
      equiposCache.forEach((eq) => {
        const opt = document.createElement("option");
        opt.value = eq.id;
        opt.textContent = eq.nombre;
        sel.appendChild(opt);
      });
    });

    syncPartidoEstadioFromLocal();
  };

  // --- Load jornadas pills ---
  const loadJornadas = async () => {
    const cat = getCat(), tor = getTor();
    if (!cat || !tor) return;

    const jornadas = await api.get(`/partidos/jornadas?categoria=${encodeURIComponent(cat)}&torneo=${encodeURIComponent(tor)}`).catch(() => []);
    const container = $("jornadasContainer");
    container.innerHTML = "";

    // "Todas" button
    const allBtn = document.createElement("button");
    allBtn.className = `jornada-btn px-4 py-2 rounded-lg text-sm font-bold ${currentJornada === null ? "active" : ""}`;
    allBtn.textContent = "Todas";
    allBtn.addEventListener("click", () => { currentJornada = null; loadJornadas(); loadPartidos(); });
    container.appendChild(allBtn);

    jornadas.forEach((j) => {
      if (j.jornada == null) return;
      const total = Number(j.total), jugados = Number(j.jugados);
      let cls = "jornada-vacia";
      if (jugados === total && total > 0) cls = "jornada-completa";
      else if (jugados > 0) cls = "jornada-parcial";

      const btn = document.createElement("button");
      btn.className = `jornada-btn px-4 py-2 rounded-lg text-sm font-bold ${cls} ${currentJornada === j.jornada ? "active" : ""}`;
      btn.textContent = `Fecha ${j.jornada}`;
      btn.addEventListener("click", () => { currentJornada = j.jornada; loadJornadas(); loadPartidos(); });
      container.appendChild(btn);
    });
  };

  // --- Render match cards ---
  const renderCard = (p) => {
    const isJugado = p.estado === "jugado";
    const isEnCurso = p.estado === "en_curso";
    const localEsc = p.localEscudo || DEFAULT_ESCUDO;
    const visitanteEsc = p.visitanteEscudo || DEFAULT_ESCUDO;
    const broadcastHtml = p.broadcast
      ? `<a href="${p.broadcast}" target="_blank" rel="noopener" class="text-primary hover:text-primary-dim transition-colors" title="Ver transmisión"><span class="material-symbols-outlined text-[16px]">live_tv</span></a>`
      : "";

    // Badges de estado
    let estadoBadge = "";
    let estadoClass = "";
    switch (p.estado) {
      case "jugado":
        estadoBadge = "🟢 Finalizado";
        estadoClass = "bg-emerald-500/20 text-emerald-300";
        break;
      case "en_curso":
        estadoBadge = "🟡 En vivo";
        estadoClass = "bg-amber-500/20 text-amber-300 animate-pulse";
        break;
      case "postergado":
        estadoBadge = "🔴 Postergado";
        estadoClass = "bg-red-500/20 text-red-300";
        break;
      default:
        estadoBadge = "🔵 Pendiente";
        estadoClass = "bg-slate-500/20 text-slate-300";
    }

    const card = document.createElement("div");
    card.className = "bg-white/5 border border-white/10 rounded-lg p-3 sm:p-4 flex flex-col gap-2 sm:gap-3";
    card.innerHTML = `
      <div class="flex items-center gap-2 sm:gap-3 flex-1 justify-center">
        <img src="${localEsc}" alt="" class="logo-team-sm" onerror="this.src='${DEFAULT_ESCUDO}'" />
        <span class="font-bold text-right flex-1 text-xs sm:text-sm truncate">${p.local}</span>
        ${isJugado || isEnCurso
          ? `<span class="bg-primary/20 text-primary font-bold px-2 sm:px-3 py-1 rounded-lg text-sm sm:text-lg min-w-[60px] sm:min-w-[80px] text-center">${p.golesLocal || 0} - ${p.golesVisitante || 0}</span>`
          : `<span class="bg-slate-700 text-slate-400 font-bold px-2 sm:px-3 py-1 rounded-lg text-xs sm:text-sm">vs</span>`
        }
        <span class="font-bold flex-1 text-xs sm:text-sm truncate">${p.visitante}</span>
        <img src="${visitanteEsc}" alt="" class="logo-team-sm" onerror="this.src='${DEFAULT_ESCUDO}'" />
      </div>
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div class="flex items-center gap-2 text-xs text-slate-400 flex-wrap">
          <span>${p.fecha || ""}</span>
          <span>${p.hora || ""}</span>
          <span class="hidden sm:inline">·</span>
          <span class="hidden sm:inline">${p.estadio || ""}</span>
          <span class="hidden sm:inline">·</span>
          <span class="px-2 py-0.5 rounded-full text-xs font-bold ${estadoClass}">${estadoBadge}</span>
          ${broadcastHtml}
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
          <button data-id="${p.id}" class="btnProgramar px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold bg-slate-700 hover:bg-slate-600 text-white transition-colors">
            <span class="material-symbols-outlined text-[16px] align-middle mr-0.5">schedule</span>
            Programar
          </button>
          <button data-id="${p.id}" class="btnCargar flex-1 sm:flex-none px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-colors ${isJugado ? "bg-blue-600 hover:bg-blue-700 text-white" : "bg-primary hover:bg-primary/80 text-white"}">
            <span class="material-symbols-outlined text-[16px] align-middle mr-0.5">${isJugado ? "edit" : "scoreboard"}</span>
            ${isJugado ? "Editar" : "Cargar"}
          </button>
          <button data-id="${p.id}" class="btnEliminar px-2 py-1.5 rounded-lg text-xs sm:text-sm bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-colors">
            <span class="material-symbols-outlined text-[16px]">delete</span>
          </button>
        </div>
      </div>
    `;
    return card;
  };

  // --- Load & display partidos ---
  const loadPartidos = async () => {
    const cat = getCat(), tor = getTor(), est = getEst();
    if (!cat || !tor) return;

    let url = `/partidos?categoria=${encodeURIComponent(cat)}&torneo=${encodeURIComponent(tor)}`;
    if (currentJornada !== null) url += `&jornada=${currentJornada}`;
    if (est) url += `&estado=${encodeURIComponent(est)}`;

    const partidos = await api.get(url).catch(() => []);
    partidosCache = partidos;
    const container = $("partidosContainer");
    const noPartidos = $("noPartidos");
    container.innerHTML = "";

    if (!partidos.length) {
      noPartidos.classList.remove("hidden");
      return;
    }
    noPartidos.classList.add("hidden");

    // Group by jornada when showing "Todas"
    if (currentJornada === null) {
      const groups = {};
      partidos.forEach((p) => {
        const key = p.jornada != null ? p.jornada : 0;
        if (!groups[key]) groups[key] = [];
        groups[key].push(p);
      });

      const sortedKeys = Object.keys(groups).map(Number).sort((a, b) => a - b);
      sortedKeys.forEach((key) => {
        const header = document.createElement("div");
        header.className = "flex items-center gap-2 mb-2 mt-4 first:mt-0";
        const jugados = groups[key].filter((p) => p.estado === "jugado").length;
        const enCurso = groups[key].filter((p) => p.estado === "en_curso").length;
        const total = groups[key].length;
        const statusBadge = enCurso > 0 
          ? `<span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400 font-semibold animate-pulse">🟡 ${enCurso} en vivo</span>`
          : `<span class="text-xs px-2 py-0.5 rounded-full ${jugados === total ? "bg-emerald-500/20 text-emerald-400" : jugados > 0 ? "bg-yellow-500/20 text-yellow-400" : "bg-slate-500/20 text-slate-400"} font-semibold">${jugados}/${total}</span>`;
        header.innerHTML = `
          <h3 class="text-sm font-bold font-headline text-on-surface-variant">${key > 0 ? `Fecha ${key}` : "Sin jornada"}</h3>
          ${statusBadge}
        `;
        container.appendChild(header);

        groups[key].forEach((p) => container.appendChild(renderCard(p)));
      });
    } else {
      partidos.forEach((p) => container.appendChild(renderCard(p)));
    }

    // Wire buttons
    container.querySelectorAll(".btnCargar").forEach((btn) => {
      btn.addEventListener("click", () => {
        const p = partidos.find((x) => x.id == btn.dataset.id);
        if (p) openResultModal(p);
      });
    });
    container.querySelectorAll(".btnProgramar").forEach((btn) => {
      btn.addEventListener("click", () => {
        const p = partidosCache.find((x) => x.id == btn.dataset.id);
        if (p) openPartidoEditModal(p);
      });
    });
    container.querySelectorAll(".btnEliminar").forEach((btn) => {
      btn.addEventListener("click", async () => {
        if (!confirm("¿Eliminar este partido?")) return;
        try {
          await api.del(`/partidos/${btn.dataset.id}`);
          showToast("Partido eliminado");
          await loadJornadas();
          await loadPartidos();
        } catch (err) {
          showToast(err.message || "Error al eliminar", "error");
        }
      });
    });
  };

  // --- Result modal ---
  const openResultModal = (p) => {
    $("resultadoPartidoId").value = p.id;
    $("resLocalEquipo").textContent = p.local;
    $("resVisitanteEquipo").textContent = p.visitante;
    $("resLocalEscudo").src = p.localEscudo || DEFAULT_ESCUDO;
    $("resVisitanteEscudo").src = p.visitanteEscudo || DEFAULT_ESCUDO;
    $("resGolesLocal").value = p.golesLocal || 0;
    $("resGolesVisitante").value = p.golesVisitante || 0;
    $("resAmarillas").value = p.amarillas || 0;
    $("resRojas").value = p.rojas || 0;
    $("partidoResultModal").classList.remove("hidden");
  };

  const closeResultModal = () => $("partidoResultModal").classList.add("hidden");

  const openPartidoEditModal = (p) => {
    $("editPartidoId").value = p.id;
    $("editPartidoLabel").textContent = `${p.local} vs ${p.visitante}`;
    $("editPartidoFecha").value = p.fecha || "";
    $("editPartidoHora").value = p.hora || "";
    $("editPartidoEstadio").value = p.estadio || "";
    $("editPartidoEstado").value = p.estado === "jugado" ? "pendiente" : (p.estado || "pendiente");
    $("editPartidoBroadcast").value = p.broadcast || "";
    $("partidoEditModal").classList.remove("hidden");
  };

  const closePartidoEditModal = () => $("partidoEditModal").classList.add("hidden");

  $("closePartidoResultModal")?.addEventListener("click", closeResultModal);
  $("cancelResult")?.addEventListener("click", closeResultModal);
  $("closePartidoEditModal")?.addEventListener("click", closePartidoEditModal);
  $("cancelPartidoEdit")?.addEventListener("click", closePartidoEditModal);

  $("saveResult")?.addEventListener("click", async () => {
    const id = $("resultadoPartidoId").value;
    try {
      await api.put(`/partidos/${id}`, {
        golesLocal: Number($("resGolesLocal").value),
        golesVisitante: Number($("resGolesVisitante").value),
        amarillas: Number($("resAmarillas").value),
        rojas: Number($("resRojas").value),
        estado: "jugado",
      });
      closeResultModal();
      showToast("Resultado guardado");
      await loadJornadas();
      await loadPartidos();
    } catch (err) {
      showToast(err.message || "Error al guardar resultado", "error");
    }
  });

  $("savePartidoEdit")?.addEventListener("click", async () => {
    const id = $("editPartidoId").value;
    const payload = {
      fecha: $("editPartidoFecha").value,
      hora: $("editPartidoHora").value,
      estadio: $("editPartidoEstadio").value.trim(),
      estado: $("editPartidoEstado").value,
      broadcast: $("editPartidoBroadcast").value.trim(),
    };

    if (!payload.fecha || !payload.hora) {
      showToast("Completá fecha y hora del partido", "error");
      return;
    }

    try {
      await api.put(`/partidos/${id}`, payload);
      closePartidoEditModal();
      showToast("Partido actualizado");
      await loadJornadas();
      await loadPartidos();
    } catch (err) {
      showToast(err.message || "Error al actualizar partido", "error");
    }
  });

  // --- Create form ---
  const togglePanel = () => {
    const panel = $("nuevoPartidoPanel");
    panel.classList.toggle("hidden");
    if (!panel.classList.contains("hidden")) panel.scrollIntoView({ behavior: "smooth", block: "nearest" });
  };

  $("toggleNuevoPartido")?.addEventListener("click", togglePanel);
  $("cancelNuevoPartido")?.addEventListener("click", () => $("nuevoPartidoPanel").classList.add("hidden"));
  $("partidoLocal")?.addEventListener("change", syncPartidoEstadioFromLocal);

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const cat = getCat(), tor = getTor();
    if (!cat || !tor) { showToast("Seleccioná categoría y torneo primero", "error"); return; }

    try {
      await api.post("/partidos", {
        fecha: $("partidoFecha").value,
        hora: $("partidoHora").value,
        estadio: $("partidoEstadio").value.trim(),
        local: $("partidoLocal").value,
        visitante: $("partidoVisitante").value,
        torneo: tor,
        categoria: cat,
        jornada: $("partidoJornada").value ? Number($("partidoJornada").value) : null,
        broadcast: $("partidoBroadcast").value || null,
        estado: "pendiente",
      });
      form.reset();
      $("nuevoPartidoPanel").classList.add("hidden");
      showToast("Partido creado");
      await loadJornadas();
      await loadPartidos();
    } catch (err) {
      showToast(err.message || "Error al crear partido", "error");
    }
  });

  // --- Filter change handler ---
  const onFilterChange = async () => {
    const cat = getCat(), tor = getTor();
    if (cat && tor) {
      $("mainContent").classList.remove("hidden");
      $("noFilters").classList.add("hidden");
      currentJornada = null;
      await loadEquipos();
      await loadJornadas();
      await loadPartidos();
    } else {
      $("mainContent").classList.add("hidden");
      $("noFilters").classList.remove("hidden");
    }
  };

  $("categoriaFiltro")?.addEventListener("change", onFilterChange);
  $("torneoFiltro")?.addEventListener("change", onFilterChange);
  $("estadoFiltro")?.addEventListener("change", async () => {
    if (getCat() && getTor()) await loadPartidos();
  });
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

    // If no category selected, clear everything and bail out
    const categoriaLabel = document.getElementById("tablaCategoriaLabel");
    if (categoriaLabel) categoriaLabel.textContent = categoria ? `— ${categoria}` : "";

    if (!categoria) {
      const el = document.getElementById("tablaPosicionesBody");
      if (el) el.innerHTML = "";
      const bodyG = document.getElementById("goleadoresBody");
      if (bodyG) bodyG.innerHTML = "";
      const bodyA = document.getElementById("tarjetasAmarillasBody");
      const bodyR = document.getElementById("tarjetasRojasBody");
      if (bodyA) bodyA.innerHTML = "";
      if (bodyR) bodyR.innerHTML = "";
      const jornadaC = document.getElementById("jornadaResultados");
      const jornadaT = document.getElementById("jornadaTabs");
      if (jornadaC) jornadaC.innerHTML = "";
      if (jornadaT) jornadaT.innerHTML = "";
      return;
    }

    const torneoQuery = torneo ? `&torneo=${encodeURIComponent(torneo)}` : "";
    const tabla = await api.get(`/tabla?categoria=${encodeURIComponent(categoria)}${torneoQuery}`).catch(() => []);
    const goleadores = await api.get(`/tabla/goleadores?categoria=${encodeURIComponent(categoria)}${torneoQuery}`).catch(() => []);
    const tarjetas = await api.get(`/tabla/tarjetas?categoria=${encodeURIComponent(categoria)}${torneoQuery}`).catch(() => ({ amarillas: [], rojas: [] }));

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

    fillBody("tablaPosicionesBody", tabla);

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

  const avatarFileInput = document.getElementById("avatarFileInput");
  page.querySelectorAll("[data-avatar-trigger]").forEach((btn) =>
    btn.addEventListener("click", () => avatarFileInput?.click())
  );
  avatarFileInput?.addEventListener("change", () => {
    if (avatarFileInput.files[0]) openCropWith(avatarFileInput.files[0]);
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

  // =================== CROP DE AVATAR ===================
  const cropModal      = document.getElementById("avatarCropModal");
  const cropCanvas     = document.getElementById("cropCanvas");
  const cropContainer  = document.getElementById("cropContainer");
  const cropZoomSlider = document.getElementById("cropZoom");
  const CIRCLE_D = 220;
  let cropImg = null, cropBaseScale = 1, cropZoomVal = 1, cropOffX = 0, cropOffY = 0;

  const drawCropFrame = () => {
    if (!cropCanvas || !cropImg) return;
    const ctx = cropCanvas.getContext("2d");
    const cw = cropCanvas.width, ch = cropCanvas.height;
    ctx.clearRect(0, 0, cw, ch);
    const sc = cropBaseScale * cropZoomVal;
    const iw = cropImg.width * sc, ih = cropImg.height * sc;
    ctx.drawImage(cropImg, (cw - iw) / 2 + cropOffX, (ch - ih) / 2 + cropOffY, iw, ih);
  };

  const openCropWith = (file) => {
    const allowed = ["image/png", "image/jpeg", "image/jpg", "image/webp"];
    if (!allowed.includes(file.type)) { alert("Solo PNG, JPG o WEBP"); return; }
    if (file.size > 10 * 1024 * 1024) { alert("Imagen muy grande (máx 10 MB)"); return; }
    const reader = new FileReader();
    reader.onload = (ev) => {
      const img = new Image();
      img.onload = () => {
        cropImg = img;
        cropZoomVal = 1;
        cropOffX = 0; cropOffY = 0;
        if (cropZoomSlider) cropZoomSlider.value = 1;
        cropCanvas.width  = cropContainer.clientWidth;
        cropCanvas.height = cropContainer.clientHeight;
        cropBaseScale = Math.max(CIRCLE_D / img.width, CIRCLE_D / img.height);
        drawCropFrame();
        cropModal?.classList.remove("hidden");
      };
      img.src = ev.target.result;
    };
    reader.readAsDataURL(file);
  };

  // Pan con pointer events
  let panActive = false, panSX = 0, panSY = 0, panOX = 0, panOY = 0;
  cropContainer?.addEventListener("pointerdown", (e) => {
    panActive = true; panSX = e.clientX; panSY = e.clientY;
    panOX = cropOffX; panOY = cropOffY;
    cropContainer.setPointerCapture(e.pointerId);
  });
  cropContainer?.addEventListener("pointermove", (e) => {
    if (!panActive) return;
    cropOffX = panOX + (e.clientX - panSX);
    cropOffY = panOY + (e.clientY - panSY);
    drawCropFrame();
  });
  cropContainer?.addEventListener("pointerup", () => { panActive = false; });

  cropZoomSlider?.addEventListener("input", () => {
    cropZoomVal = Number(cropZoomSlider.value);
    drawCropFrame();
  });

  document.getElementById("cropCancel")?.addEventListener("click", () => {
    cropModal?.classList.add("hidden");
    if (avatarFileInput) avatarFileInput.value = "";
  });

  document.getElementById("cropConfirm")?.addEventListener("click", async () => {
    const confirmBtn  = document.getElementById("cropConfirm");
    const confirmText = document.getElementById("cropConfirmText");
    if (confirmBtn)  confirmBtn.disabled = true;
    if (confirmText) confirmText.textContent = "Subiendo…";
    try {
      const OUT = 300;
      const off = document.createElement("canvas");
      off.width = OUT; off.height = OUT;
      const ctx = off.getContext("2d");
      ctx.beginPath();
      ctx.arc(OUT / 2, OUT / 2, OUT / 2, 0, Math.PI * 2);
      ctx.clip();
      const sc  = cropBaseScale * cropZoomVal;
      const iw  = cropImg.width * sc, ih = cropImg.height * sc;
      const cw  = cropCanvas.width,   ch = cropCanvas.height;
      const ratio     = OUT / CIRCLE_D;
      const imgX      = (cw - iw) / 2 + cropOffX;
      const imgY      = (ch - ih) / 2 + cropOffY;
      const circleTLX = cw / 2 - CIRCLE_D / 2;
      const circleTLY = ch / 2 - CIRCLE_D / 2;
      ctx.drawImage(cropImg, (imgX - circleTLX) * ratio, (imgY - circleTLY) * ratio, iw * ratio, ih * ratio);

      off.toBlob(async (blob) => {
        try {
          const fd = new FormData();
          fd.append("image", blob, "avatar.jpg");
          const token = localStorage.getItem("token") || "";
          const r = await fetch("/api/uploads/image", {
            method: "POST",
            headers: getAuthHeaders(false),
            body: fd,
          });
          const data = await r.json();
          if (!r.ok) throw new Error(data.error || "Error al subir");

          document.getElementById("accountAvatarUrl").value = data.url;
          document.querySelectorAll("[data-account-avatar]").forEach((img) => { img.src = data.url; });

          // Auto-guardar perfil con el nuevo avatar
          try {
            const accResp = await api.put("/auth/account", {
              fullName: document.getElementById("accountFullName").value.trim(),
              email:    document.getElementById("accountEmail").value.trim(),
              phone:    document.getElementById("accountPhone").value.trim(),
              bio:      document.getElementById("accountBio").value.trim(),
              avatarUrl: data.url,
              preferences: {
                emailNotifications: document.getElementById("accountEmailNotifications").checked,
                liveNotifications:  document.getElementById("accountLiveNotifications").checked,
                developerMode:      document.getElementById("accountDeveloperMode").checked,
              },
            });
            if (accResp?.token)   localStorage.setItem("token", accResp.token);
            if (accResp?.account) { setCachedAccount(accResp.account); await syncSharedNavState(document); }
          } catch (_) { /* avatar ya guardado, ignorar error de perfil */ }

          cropModal?.classList.add("hidden");
          if (avatarFileInput) avatarFileInput.value = "";
        } catch (err) {
          alert(err.message || "Error al subir la imagen");
        } finally {
          if (confirmBtn)  confirmBtn.disabled = false;
          if (confirmText) confirmText.textContent = "Usar foto";
        }
      }, "image/jpeg", 0.92);
    } catch (err) {
      alert(err.message || "Error al procesar la imagen");
      if (confirmBtn)  confirmBtn.disabled = false;
      if (confirmText) confirmText.textContent = "Usar foto";
    }
  });

  // =================== GESTIÓN DE USUARIOS ===================
  const usersWrap       = document.getElementById("usersTableWrap");
  const createUserModal = document.getElementById("createUserModal");
  const createUserForm  = document.getElementById("createUserForm");
  const createUserMsg   = document.getElementById("createUserMsg");
  const esc = (s) => String(s ?? "").replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");

  const loadUsers = async () => {
    if (!usersWrap) return;
    try {
      const resp  = await api.get("/auth/users");
      const users = resp.users || [];
      if (!users.length) {
        usersWrap.innerHTML = `<p class="text-on-surface-variant text-sm">No hay usuarios secundarios. Creá el primero.</p>`;
        return;
      }
      usersWrap.innerHTML = `<div class="overflow-x-auto"><table class="w-full text-sm">
        <thead><tr class="text-left text-xs text-on-surface-variant uppercase border-b border-outline-variant/10">
          <th class="pb-3 pr-4">Nombre</th><th class="pb-3 pr-4">Email</th><th class="pb-3 pr-4">Rol</th><th class="pb-3 pr-4">Creado</th><th class="pb-3"></th>
        </tr></thead>
        <tbody id="usersTableBody" class="divide-y divide-outline-variant/10"></tbody>
      </table></div>`;
      const tbody = document.getElementById("usersTableBody");
      users.forEach((u) => {
        const tr = document.createElement("tr");
        const d  = u.createdAt
          ? new Date(u.createdAt).toLocaleDateString("es-AR", { day: "numeric", month: "short", year: "numeric" })
          : "-";
        tr.innerHTML = `
          <td class="py-3 pr-4 font-semibold">${esc(u.fullName)}</td>
          <td class="py-3 pr-4 text-on-surface-variant">${esc(u.email)}</td>
          <td class="py-3 pr-4"><span class="px-2 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary">${esc(u.role)}</span></td>
          <td class="py-3 pr-4 text-on-surface-variant text-xs">${d}</td>
          <td class="py-3"><button data-uid="${esc(String(u.id))}" class="del-user text-xs text-error hover:underline">Eliminar</button></td>`;
        tbody.appendChild(tr);
      });
      tbody.querySelectorAll(".del-user").forEach((btn) => {
        btn.addEventListener("click", async () => {
          const name = btn.closest("tr")?.querySelector("td")?.textContent?.trim() || "este usuario";
          if (!confirm(`¿Eliminar a ${name}?`)) return;
          await api.del(`/auth/users/${btn.dataset.uid}`);
          await loadUsers();
        });
      });
    } catch (err) {
      if (usersWrap) usersWrap.innerHTML = `<p class="text-sm text-error">${esc(err?.message || "Error al cargar usuarios")}</p>`;
    }
  };

  document.getElementById("btnNuevoUsuario")?.addEventListener("click", () => {
    createUserForm?.reset();
    if (createUserMsg) { createUserMsg.className = "text-sm hidden"; createUserMsg.textContent = ""; }
    createUserModal?.classList.remove("hidden");
  });

  [document.getElementById("closeCreateUserModal"), document.getElementById("closeCreateUserModal2")].forEach((btn) => {
    btn?.addEventListener("click", () => createUserModal?.classList.add("hidden"));
  });

  createUserForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (createUserMsg) { createUserMsg.className = "text-sm hidden"; createUserMsg.textContent = ""; }
    try {
      await api.post("/auth/users", {
        fullName: document.getElementById("newUserName").value.trim(),
        email:    document.getElementById("newUserEmail").value.trim(),
        password: document.getElementById("newUserPassword").value,
        role:     document.getElementById("newUserRole").value,
      });
      createUserModal?.classList.add("hidden");
      await loadUsers();
    } catch (err) {
      if (createUserMsg) {
        createUserMsg.textContent = err.message || "Error al crear el usuario";
        createUserMsg.className   = "text-sm text-error bg-error/10 border border-error/30 rounded-lg px-4 py-3";
      }
    }
  });

  await loadUsers();

  wireLogoutActions(page);
};

const noticiasScreen = () => {
  const form            = document.getElementById("noticiaForm");
  const tbody           = document.getElementById("noticiasTableBody");
  const temporadaSelect = document.getElementById("noticiaTemporada");
  const tipoSelect      = document.getElementById("noticiaTipoContenido");
  let editingId              = null;
  let relacionadasTodas      = [];
  let relacionadasSel        = []; // [{ id, titulo }]
  let galeriaItems           = []; // [{ imagenUrl, epigrafe }]
  let storedPublishedAt      = null;
  let republishFlag          = false;
  let cropper                = null;

  const escapeHtml = (value) => {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  };

  const buildGaleriaHtml = (items) => {
    return items.map((item) => {
      const caption = item.epigrafe
        ? `<figcaption>${escapeHtml(item.epigrafe)}</figcaption>`
        : "";
      return `<figure class=\"gallery-item\"><img src=\"${escapeHtml(item.imagenUrl)}\" alt=\"\" loading=\"lazy\"/>${caption}</figure>`;
    }).join("\n");
  };

  const updateTipoUi = () => {
    const tipo = tipoSelect?.value || "comun";
    document.getElementById("bloqueContenidoComun")?.classList.toggle("hidden", tipo === "galeria");
    document.getElementById("bloqueGaleria")?.classList.toggle("hidden", tipo !== "galeria");
    document.getElementById("bloqueBoletin")?.classList.toggle("hidden", tipo !== "boletin");
  };

  const renderGaleria = () => {
    const container = document.getElementById("noticiaGaleriaItems");
    if (!container) return;

    if (!galeriaItems.length) {
      container.innerHTML = `<p class=\"text-xs text-on-surface-variant/60 italic\">No hay fotos cargadas.</p>`;
      return;
    }

    container.innerHTML = galeriaItems.map((item, index) => `
      <div class=\"rounded-lg border border-outline-variant/15 bg-surface-container-high p-3\">
        <div class=\"grid grid-cols-1 md:grid-cols-[150px_1fr] gap-3\">
          <img src=\"${escapeHtml(item.imagenUrl)}\" alt=\"Imagen ${index + 1}\" class=\"w-full h-28 md:h-24 rounded object-cover\"/>
          <div class=\"space-y-2\">
            <textarea data-galeria-epigrafe=\"${index}\" class=\"w-full min-h-[72px] rounded border border-outline-variant/20 bg-surface-container p-2 text-sm\" placeholder=\"Epígrafe opcional\">${escapeHtml(item.epigrafe || "")}</textarea>
            <div class=\"flex justify-end\">
              <button type=\"button\" data-galeria-remove=\"${index}\" class=\"rounded border border-error/30 bg-error/10 px-3 py-1.5 text-xs font-semibold text-error\">Quitar</button>
            </div>
          </div>
        </div>
      </div>
    `).join("");

    container.querySelectorAll("[data-galeria-remove]").forEach((button) => {
      button.addEventListener("click", () => {
        const idx = Number(button.getAttribute("data-galeria-remove"));
        galeriaItems = galeriaItems.filter((_, i) => i !== idx);
        renderGaleria();
      });
    });

    container.querySelectorAll("[data-galeria-epigrafe]").forEach((textarea) => {
      textarea.addEventListener("input", () => {
        const idx = Number(textarea.getAttribute("data-galeria-epigrafe"));
        if (galeriaItems[idx]) {
          galeriaItems[idx].epigrafe = textarea.value;
        }
      });
    });
  };

  const setBoletinPdfUrl = (url) => {
    const hidden = document.getElementById("noticiaBoletinPdfUrl");
    const link = document.getElementById("noticiaBoletinPdfLink");
    if (hidden) hidden.value = url || "";
    if (!link) return;
    if (url) {
      link.href = url;
      link.textContent = url;
      link.classList.remove("hidden");
    } else {
      link.href = "#";
      link.textContent = "";
      link.classList.add("hidden");
    }
  };

  const mapForm = () => {
    const estado = document.getElementById("noticiaEstado").value;
    let publishedAt;
    if (republishFlag) {
      publishedAt = new Date().toISOString();
    } else if (editingId) {
      publishedAt = storedPublishedAt;
    } else {
      publishedAt = estado === "publicada" ? new Date().toISOString() : null;
    }
    return {
      titulo:      document.getElementById("noticiaTitulo").value.trim(),
      volanta:     document.getElementById("noticiaVolanta").value.trim() || null,
      slug:        document.getElementById("noticiaSlug").value.trim() || null,
      copete:      document.getElementById("noticiaCopete").value.trim(),
      contenido:   (tipoSelect?.value || "comun") === "galeria"
        ? buildGaleriaHtml(galeriaItems)
        : document.getElementById("noticiaContenido").innerHTML.trim(),
      imagenUrl:   document.getElementById("noticiaImagen").value.trim() || null,
      imagenAlt:   document.getElementById("noticiaImagenAlt").value.trim() || null,
      tag:         document.getElementById("noticiaTag").value.trim() || null,
      categoria:   document.getElementById("noticiaCategoria").value.trim() || "General",
      autorNombre: document.getElementById("noticiaAutor").value.trim() || "Redacción Juego de Equipo",
      estado,
      destacada:   document.getElementById("noticiaDestacada").checked,
      tipoContenido: tipoSelect?.value || "comun",
      boletinPdfUrl: document.getElementById("noticiaBoletinPdfUrl")?.value?.trim() || null,
      galeria: galeriaItems.map((item) => ({ imagenUrl: item.imagenUrl, epigrafe: (item.epigrafe || "").trim() })),
      ordenHome:   Number(document.getElementById("noticiaOrden").value || 999),
      temporadaId: Number(temporadaSelect.value),
      publishedAt,
      relacionadas: relacionadasSel.map((r) => r.id),
    };
  };

  const resetImgDrop = () => {
    document.getElementById("noticiaImagen").value = "";
    document.getElementById("dropImgPreview").src = "";
    document.getElementById("dropImgPreviewWrap").classList.add("hidden");
    document.getElementById("dropImgPlaceholder").classList.remove("hidden");
    document.getElementById("dropImgUploading").classList.add("hidden");
  };

  const renderRelacionadas = () => {
    const container = document.getElementById("relacionadasSelected");
    if (!container) return;
    if (!relacionadasSel.length) {
      container.innerHTML = `<span class="text-xs text-on-surface-variant/50 italic">Sin notas seleccionadas</span>`;
      return;
    }
    container.innerHTML = relacionadasSel.map((r) =>
      `<span class="inline-flex items-center gap-1 rounded-full bg-primary/10 border border-primary/20 px-2.5 py-1 text-xs font-semibold text-primary">
        ${r.titulo}
        <button type="button" data-id="${r.id}" class="rel-rm ml-1 text-primary/60 hover:text-primary leading-none">&times;</button>
      </span>`
    ).join("");
    container.querySelectorAll(".rel-rm").forEach((btn) => {
      btn.addEventListener("click", () => {
        relacionadasSel = relacionadasSel.filter((r) => r.id !== Number(btn.dataset.id));
        renderRelacionadas();
      });
    });
  };

  const resetForm = () => {
    editingId = null;
    storedPublishedAt = null;
    republishFlag = false;
    form.reset();
    document.getElementById("noticiaOrden").value = 999;
    document.getElementById("noticiaContenido").innerHTML = "";
    document.getElementById("noticiaSlug").value = "";
    document.getElementById("noticiaVolanta").value = "";
    if (tipoSelect) tipoSelect.value = "comun";
    galeriaItems = [];
    renderGaleria();
    setBoletinPdfUrl("");
    updateTipoUi();
    relacionadasSel = [];
    renderRelacionadas();
    const btnRep = document.getElementById("btnRepublicar");
    if (btnRep) { btnRep.classList.add("hidden"); btnRep.textContent = "Republicar"; btnRep.classList.remove("bg-primary/20", "border-primary/50"); }
    resetImgDrop();
  };

  const loadTemporadas = async () => {
    const temporadas = await api.get("/temporadas").catch(() => []);
    temporadaSelect.innerHTML = "";
    temporadas.forEach((item) => {
      const opt = document.createElement("option");
      opt.value = item.id;
      opt.textContent = `${item.nombre}${item.isActive ? " • activa" : ""}`;
      if (item.isActive) opt.selected = true;
      temporadaSelect.appendChild(opt);
    });
  };

  const loadNoticias = async () => {
    const estadoFiltro = document.getElementById("noticiasEstadoFiltro")?.value || "";
    const url = "/noticias?limit=50" + (estadoFiltro ? `&estado=${encodeURIComponent(estadoFiltro)}` : "");
    const adminRows = await api.get(url).catch(() => []);
    relacionadasTodas = Array.isArray(adminRows) ? adminRows : [];

    tbody.innerHTML = "";
    adminRows.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `
        <td class="px-4 py-3">
          <div class="font-semibold">${item.titulo}</div>
          <div class="text-xs text-on-surface-variant">/${item.slug}</div>
        </td>
        <td class="px-4 py-3 text-on-surface-variant">${item.temporadaNombre || "-"}</td>
        <td class="px-4 py-3">
          <div class="flex flex-wrap gap-1">
            <span class="rounded-full bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">${item.estado}</span>
            <span class="rounded-full bg-surface-container px-2 py-1 text-[11px] font-semibold text-on-surface-variant border border-outline-variant/20">${item.tipoContenido || "comun"}</span>
          </div>
        </td>
        <td class="px-4 py-3">${item.destacada ? `Sí · ${item.ordenHome}` : "No"}</td>
        <td class="px-4 py-3 text-on-surface-variant">${item.publishedAt || item.createdAt || "-"}</td>
        <td class="px-4 py-3 space-x-2">
          <button data-id="${item.id}" class="noticiaEdit rounded-lg bg-primary/15 px-2.5 py-1 text-xs font-semibold text-primary">Editar</button>
          <button data-id="${item.id}" class="noticiaDelete rounded-lg bg-error/15 px-2.5 py-1 text-xs font-semibold text-error">Eliminar</button>
        </td>`;
      tbody.appendChild(tr);
    });

    tbody.querySelectorAll(".noticiaEdit").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const item = await api.get(`/noticias/${btn.dataset.id}`);
        editingId = item.id;
        storedPublishedAt = item.publishedAt || null;
        republishFlag = false;
        document.getElementById("noticiaTitulo").value    = item.titulo || "";
        document.getElementById("noticiaVolanta").value   = item.volanta || "";
        document.getElementById("noticiaSlug").value      = item.slug || "";
        document.getElementById("noticiaCopete").value    = item.copete || "";
        document.getElementById("noticiaContenido").innerHTML = item.contenido || "";
        document.getElementById("noticiaImagen").value    = item.imagenUrl || "";
        if (item.imagenUrl) {
          document.getElementById("dropImgPreview").src = item.imagenUrl;
          document.getElementById("dropImgPlaceholder").classList.add("hidden");
          document.getElementById("dropImgPreviewWrap").classList.remove("hidden");
        } else {
          resetImgDrop();
        }
        document.getElementById("noticiaImagenAlt").value = item.imagenAlt || "";
        document.getElementById("noticiaTag").value       = item.tag || "";
        document.getElementById("noticiaCategoria").value = item.categoria || "";
        document.getElementById("noticiaAutor").value     = item.autorNombre || "";
        document.getElementById("noticiaEstado").value    = item.estado || "borrador";
        if (tipoSelect) tipoSelect.value = item.tipoContenido || "comun";
        galeriaItems = Array.isArray(item.galeria) ? item.galeria.map((g) => ({ imagenUrl: g.imagenUrl, epigrafe: g.epigrafe || "" })) : [];
        renderGaleria();
        setBoletinPdfUrl(item.boletinPdfUrl || "");
        updateTipoUi();
        document.getElementById("noticiaDestacada").checked = Boolean(item.destacada);
        document.getElementById("noticiaOrden").value     = item.ordenHome ?? 999;
        temporadaSelect.value = item.temporadaId || temporadaSelect.value;
        const btnRep = document.getElementById("btnRepublicar");
        if (btnRep) { btnRep.classList.remove("hidden"); btnRep.textContent = "Republicar"; btnRep.classList.remove("bg-primary/20", "border-primary/50"); }
        relacionadasSel = (item.relacionadas || []).map((rel) => {
          const n = relacionadasTodas.find((x) => x.id === Number(rel.id));
          return { id: Number(rel.id), titulo: n ? n.titulo : `Nota #${rel.id}` };
        });
        renderRelacionadas();
      });
    });

    tbody.querySelectorAll(".noticiaDelete").forEach((btn) => {
      btn.addEventListener("click", async () => {
        if (!confirm("¿Eliminar esta noticia?")) return;
        await api.del(`/noticias/${btn.dataset.id}`);
        await loadNoticias();
      });
    });
  };

  // Editor enriquecido
  document.querySelectorAll(".ed-btn[data-cmd]").forEach((btn) => {
    btn.addEventListener("mousedown", (e) => {
      e.preventDefault();
      document.execCommand(btn.dataset.cmd, false, null);
      document.getElementById("noticiaContenido").focus();
    });
  });
  document.getElementById("edBtnLink")?.addEventListener("mousedown", (e) => {
    e.preventDefault();
    const url = prompt("URL del link:");
    if (url) document.execCommand("createLink", false, url);
    document.getElementById("noticiaContenido").focus();
  });

  // Upload imagen drag-and-drop
  const dropZoneImg  = document.getElementById("dropZoneImg");
  const imgFileInput = document.getElementById("noticiaImgFile");

  // ─── Crop modal ──────────────────────────────────────────────────────────────
  const openCropModal = (file) => {
    const reader = new FileReader();
    reader.onload = (evt) => {
      const img = document.getElementById("cropPreview");
      img.src = evt.target.result;
      const modal = document.getElementById("cropModal");
      modal.classList.remove("hidden");
      modal.classList.add("flex");
      if (cropper) { cropper.destroy(); cropper = null; }
      setTimeout(() => {
        cropper = new Cropper(img, {
          viewMode: 1,
          dragMode: "move",
          autoCropArea: 0.9,
          restore: false,
          guides: true,
          center: true,
          highlight: false,
          cropBoxResizable: true,
          toggleDragModeOnDblclick: false,
        });
      }, 100);
    };
    reader.readAsDataURL(file);
  };

  const closeCropModal = () => {
    if (cropper) { cropper.destroy(); cropper = null; }
    const modal = document.getElementById("cropModal");
    modal.classList.add("hidden");
    modal.classList.remove("flex");
    imgFileInput.value = "";
  };

  document.getElementById("btnCancelCropX")?.addEventListener("click", closeCropModal);
  document.getElementById("btnCancelCrop")?.addEventListener("click", closeCropModal);

  document.getElementById("btnConfirmCrop")?.addEventListener("click", () => {
    if (!cropper) return;
    const btn = document.getElementById("btnConfirmCrop");
    btn.disabled = true;
    btn.textContent = "Subiendo…";
    cropper.getCroppedCanvas({ maxWidth: 2000, maxHeight: 2000 }).toBlob(async (blob) => {
      closeCropModal();
      await doUploadBlob(blob);
      btn.disabled = false;
      btn.textContent = "Recortar y subir";
    }, "image/jpeg", 0.92);
  });

  // ─── Upload de imagen ─────────────────────────────────────────────────────────
  const prepareImage = (file) => {
    const allowed = ["image/png", "image/jpeg", "image/jpg"];
    if (!allowed.includes(file.type)) { alert("Solo se permiten PNG, JPG y JPEG"); return; }
    if (file.size > 5 * 1024 * 1024) { alert("La imagen supera 5 MB"); return; }
    openCropModal(file);
  };

  const doUploadBlob = async (blob) => {
    document.getElementById("dropImgPlaceholder").classList.add("hidden");
    document.getElementById("dropImgPreviewWrap").classList.add("hidden");
    const uploading = document.getElementById("dropImgUploading");
    uploading.classList.remove("hidden");
    try {
      const fd = new FormData();
      fd.append("image", blob, "imagen.jpg");
      const r = await fetch("/api/uploads/image", {
        method: "POST",
        headers: getAuthHeaders(false),
        body: fd,
      });
      const data = await r.json();
      if (!r.ok) throw new Error(data.error || "Error al subir");
      document.getElementById("noticiaImagen").value = data.url;
      document.getElementById("dropImgPreview").src  = data.url;
      document.getElementById("dropImgPreviewWrap").classList.remove("hidden");
    } catch (err) {
      alert(err.message);
      resetImgDrop();
    } finally {
      uploading.classList.add("hidden");
    }
  };

  const uploadImageFile = async (file) => {
    const fd = new FormData();
    fd.append("image", file, file.name || "imagen.jpg");
    const r = await fetch("/api/uploads/image", {
      method: "POST",
      headers: getAuthHeaders(false),
      body: fd,
    });
    const data = await r.json();
    if (!r.ok) throw new Error(data.error || "Error al subir imagen");
    return data.url;
  };

  const uploadBoletinPdf = async (file) => {
    const fd = new FormData();
    fd.append("pdf", file, file.name || "boletin.pdf");
    const r = await fetch("/api/uploads/pdf", {
      method: "POST",
      headers: getAuthHeaders(false),
      body: fd,
    });
    const data = await r.json();
    if (!r.ok) throw new Error(data.error || "Error al subir PDF");
    return data.url;
  };

  dropZoneImg.addEventListener("click", (e) => { if (!e.target.closest("#btnQuitarImg")) imgFileInput.click(); });
  document.getElementById("dropImgLabel")?.addEventListener("click", () => imgFileInput.click());
  imgFileInput.addEventListener("change", () => { if (imgFileInput.files[0]) prepareImage(imgFileInput.files[0]); });
  dropZoneImg.addEventListener("dragover", (e) => { e.preventDefault(); dropZoneImg.classList.add("drag-over"); });
  dropZoneImg.addEventListener("dragleave", () => dropZoneImg.classList.remove("drag-over"));
  dropZoneImg.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZoneImg.classList.remove("drag-over");
    if (e.dataTransfer.files[0]) prepareImage(e.dataTransfer.files[0]);
  });
  document.getElementById("btnQuitarImg")?.addEventListener("click", (e) => { e.stopPropagation(); resetImgDrop(); });

  document.getElementById("btnAgregarFotosGaleria")?.addEventListener("click", () => {
    document.getElementById("noticiaGaleriaFiles")?.click();
  });

  document.getElementById("noticiaGaleriaFiles")?.addEventListener("change", async (event) => {
    const files = Array.from(event.target.files || []);
    if (!files.length) return;
    const uploading = document.getElementById("galeriaUploading");
    uploading?.classList.remove("hidden");
    try {
      for (const file of files) {
        const allowed = ["image/png", "image/jpeg", "image/jpg"];
        if (!allowed.includes(file.type)) {
          throw new Error("Solo se permiten PNG, JPG y JPEG en galería");
        }
        if (file.size > 5 * 1024 * 1024) {
          throw new Error("Cada imagen de galería debe pesar hasta 5 MB");
        }
        const url = await uploadImageFile(file);
        galeriaItems.push({ imagenUrl: url, epigrafe: "" });
      }
      renderGaleria();
    } catch (err) {
      alert(err.message || "Error al subir imágenes de galería");
    } finally {
      uploading?.classList.add("hidden");
      event.target.value = "";
    }
  });

  document.getElementById("btnSubirBoletinPdf")?.addEventListener("click", () => {
    document.getElementById("noticiaBoletinPdfFile")?.click();
  });

  document.getElementById("noticiaBoletinPdfFile")?.addEventListener("change", async (event) => {
    const file = event.target.files?.[0];
    if (!file) return;
    const uploading = document.getElementById("boletinPdfUploading");
    uploading?.classList.remove("hidden");
    try {
      const url = await uploadBoletinPdf(file);
      setBoletinPdfUrl(url);
    } catch (err) {
      alert(err.message || "Error al subir PDF");
    } finally {
      uploading?.classList.add("hidden");
      event.target.value = "";
    }
  });

  document.getElementById("noticiaReset").addEventListener("click", resetForm);

  // Slug auto-generado desde título (solo al crear)
  document.getElementById("noticiaTitulo").addEventListener("input", function () {
    if (!editingId) {
      document.getElementById("noticiaSlug").value = this.value.toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9\s-]/g, "").trim()
        .replace(/\s+/g, "-").replace(/-+/g, "-");
    }
  });

  // Republicar
  document.getElementById("btnRepublicar")?.addEventListener("click", () => {
    republishFlag = true;
    const btn = document.getElementById("btnRepublicar");
    btn.textContent = "\u2713 Se republicar\u00e1 al guardar";
    btn.classList.add("bg-primary/20", "border-primary/50");
  });

  tipoSelect?.addEventListener("change", updateTipoUi);

  // Buscador de relacionadas
  const searchInput   = document.getElementById("relacionadasSearch");
  const searchResults = document.getElementById("relacionadasResults");
  if (searchInput && searchResults) {
    searchInput.addEventListener("input", () => {
      const q = searchInput.value.trim().toLowerCase();
      if (!q) { searchResults.classList.add("hidden"); searchResults.innerHTML = ""; return; }
      const matches = relacionadasTodas.filter((n) =>
        n.titulo.toLowerCase().includes(q) && !relacionadasSel.some((r) => r.id === n.id)
      ).slice(0, 8);
      if (!matches.length) {
        searchResults.innerHTML = `<p class="px-3 py-2 text-xs text-on-surface-variant">Sin resultados</p>`;
        searchResults.classList.remove("hidden");
        return;
      }
      searchResults.innerHTML = matches.map((n) =>
        `<button type="button" data-id="${n.id}" class="rel-res w-full text-left px-3 py-2 text-sm hover:bg-primary/10 transition-colors">${n.titulo}</button>`
      ).join("");
      searchResults.classList.remove("hidden");
      searchResults.querySelectorAll(".rel-res").forEach((btn) => {
        btn.addEventListener("click", () => {
          const id = Number(btn.dataset.id);
          const noticia = relacionadasTodas.find((n) => n.id === id);
          if (noticia && !relacionadasSel.some((r) => r.id === id)) {
            relacionadasSel.push({ id, titulo: noticia.titulo });
            renderRelacionadas();
          }
          searchInput.value = "";
          searchResults.classList.add("hidden");
          searchResults.innerHTML = "";
        });
      });
    });
    document.addEventListener("click", (e) => {
      if (!e.target.closest("#relacionadasSearch") && !e.target.closest("#relacionadasResults")) {
        searchResults.classList.add("hidden");
      }
    });
  }

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const payload = mapForm();
    // Validar campos obligatorios solo cuando NO es borrador
    if (payload.estado !== "borrador") {
      if (!payload.titulo || !payload.copete) {
        alert("Título y copete son obligatorios para publicar");
        return;
      }
      if ((payload.tipoContenido === "comun" || payload.tipoContenido === "boletin") && !payload.contenido) {
        alert("El contenido es obligatorio para este tipo de nota");
        return;
      }
      if (payload.tipoContenido === "galeria" && !payload.galeria.length) {
        alert("La nota galería necesita al menos una foto");
        return;
      }
      if (payload.tipoContenido === "boletin" && !payload.boletinPdfUrl) {
        alert("La nota boletín necesita un PDF");
        return;
      }
    }
    try {
      if (editingId) {
        await api.put(`/noticias/${editingId}`, payload);
      } else {
        await api.post("/noticias", payload);
      }
      resetForm();
      await loadNoticias();
    } catch (err) {
      alert(err.message || "Error al guardar la noticia");
    }
  });

  // Filtro de estado
  const estadoFiltro = document.getElementById("noticiasEstadoFiltro");
  if (estadoFiltro) {
    estadoFiltro.addEventListener("change", loadNoticias);
  }

  updateTipoUi();
  renderGaleria();

  Promise.all([loadTemporadas(), loadNoticias()]);
};

const pageInitializers = {
  "login": loginScreen,
  "equipos": equiposScreen,
  "jugadores": jugadoresScreen,
  "partidos": partidosScreen,
  "estadisticas": estadisticasScreen,
  "account": accountScreen,
  "noticias": noticiasScreen,
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

  const initializer = pageInitializers[getCurrentPage()];
  if (initializer) initializer();
});
