<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Temporadas — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>.material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }</style>
</head>
<body class="bg-surface text-on-surface font-body">
<?php require $root . '/views/partials/gtm-body.php'; ?>
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto space-y-6">
      <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold font-headline">Gestión de Temporadas</h1>
        <p class="text-on-surface-variant mt-1 text-sm sm:text-base">Campañas activas, archivadas y contexto histórico del sitio.</p>
      </div>

      <section class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4 sm:p-5">
        <form id="temporadaForm" class="grid grid-cols-1 md:grid-cols-6 gap-3">
          <input id="temporadaNombre" type="text" placeholder="Nombre visible" class="rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          <input id="temporadaSlug" type="text" placeholder="Slug" class="rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          <input id="temporadaInicio" type="number" min="2000" placeholder="Año inicio" class="rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm" required/>
          <input id="temporadaFin" type="number" min="2000" placeholder="Año fin" class="rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm" required/>
          <select id="temporadaEstado" class="rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm">
            <option value="borrador">Borrador</option>
            <option value="activa">Activa</option>
            <option value="cerrada">Cerrada</option>
            <option value="archivada">Archivada</option>
          </select>
          <button class="rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-dim transition-colors">Guardar temporada</button>
          <label class="md:col-span-6 inline-flex items-center gap-2 text-sm text-on-surface-variant"><input id="temporadaActiva" type="checkbox" class="rounded border-outline-variant"/> Marcar como temporada activa</label>
        </form>
      </section>

      <section class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
        <div class="border-b border-outline-variant/10 px-4 py-3">
          <h2 class="font-bold font-headline">Temporadas cargadas</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
              <tr>
                <th class="px-4 py-3">Nombre</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Años</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Activa</th>
                <th class="px-4 py-3">Acciones</th>
              </tr>
            </thead>
            <tbody id="temporadasTableBody"></tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</div>
<script src="/frontend/app.js"></script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("temporadaForm");
  const body = document.getElementById("temporadasTableBody");
  let editingId = null;

  const resetForm = () => {
    editingId = null;
    form.reset();
  };

  const load = async () => {
    const temporadas = await api.get("/temporadas").catch(() => []);
    body.innerHTML = "";
    temporadas.forEach((item) => {
      const tr = document.createElement("tr");
      tr.className = "border-b border-outline-variant/10";
      tr.innerHTML = `
        <td class="px-4 py-3 font-semibold">${item.nombre}</td>
        <td class="px-4 py-3 text-on-surface-variant">${item.slug}</td>
        <td class="px-4 py-3 text-on-surface-variant">${item.anioInicio} - ${item.anioFin}</td>
        <td class="px-4 py-3"><span class="rounded-full bg-primary/10 px-2 py-1 text-xs font-semibold text-primary">${item.estado}</span></td>
        <td class="px-4 py-3">${item.isActive ? "Sí" : "No"}</td>
        <td class="px-4 py-3 space-x-2">
          <button data-edit='${JSON.stringify(item).replace(/'/g, "&#39;")}' class="temporadaEdit rounded-lg bg-primary/15 px-2.5 py-1 text-xs font-semibold text-primary">Editar</button>
          <button data-activate="${item.id}" class="temporadaActivate rounded-lg bg-emerald-500/15 px-2.5 py-1 text-xs font-semibold text-emerald-400">Activar</button>
        </td>`;
      body.appendChild(tr);
    });

    body.querySelectorAll(".temporadaEdit").forEach((btn) => {
      btn.addEventListener("click", () => {
        const data = JSON.parse(btn.dataset.edit);
        editingId = data.id;
        document.getElementById("temporadaNombre").value = data.nombre || "";
        document.getElementById("temporadaSlug").value = data.slug || "";
        document.getElementById("temporadaInicio").value = data.anioInicio || "";
        document.getElementById("temporadaFin").value = data.anioFin || "";
        document.getElementById("temporadaEstado").value = data.estado || "borrador";
        document.getElementById("temporadaActiva").checked = Boolean(data.isActive);
      });
    });

    body.querySelectorAll(".temporadaActivate").forEach((btn) => {
      btn.addEventListener("click", async () => {
        await api.post(`/temporadas/${btn.dataset.activate}/activar`, {});
        await load();
      });
    });
  };

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    const payload = {
      nombre: document.getElementById("temporadaNombre").value.trim() || null,
      slug: document.getElementById("temporadaSlug").value.trim() || null,
      anioInicio: Number(document.getElementById("temporadaInicio").value),
      anioFin: Number(document.getElementById("temporadaFin").value),
      estado: document.getElementById("temporadaEstado").value,
      isActive: document.getElementById("temporadaActiva").checked,
    };

    if (editingId) {
      await api.put(`/temporadas/${editingId}`, payload);
    } else {
      await api.post("/temporadas", payload);
    }

    resetForm();
    await load();
  });

  load();
});
</script>
</body>
</html>