<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Banners — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    .banner-card { transition: box-shadow .2s, border-color .2s; }
    .banner-card:hover { box-shadow: 0 0 0 2px rgba(140,82,255,.25); }
    .banner-preview-img { object-fit: contain; max-width: 100%; max-height: 120px; }
    .drop-zone { transition: border-color .2s, background .2s; }
    .drop-zone.drag-over { border-color: #8c52ff !important; background: rgba(140,82,255,.08); }
    .toggle-track { transition: background .2s; }
    .tab-btn.active { background: rgba(140,82,255,.18); color: #c4b5fd; border-color: rgba(140,82,255,.4); }
  </style>
</head>
<body class="bg-surface text-on-surface font-body">
<?php require $root . '/views/partials/gtm-body.php'; ?>

<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h1 class="text-xl sm:text-2xl md:text-3xl font-bold font-headline">Banners</h1>
          <p class="text-on-surface-variant mt-1 text-sm sm:text-base">Gestión de todos los espacios publicitarios del sitio.</p>
        </div>
        <a href="/publicidad" target="_blank" rel="noopener noreferrer"
           class="inline-flex items-center gap-2 rounded-xl border border-outline-variant/30 px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-colors self-start sm:self-auto">
          <span class="material-symbols-outlined text-[18px]">open_in_new</span>
          Ver página de publicidad
        </a>
      </div>

      <!-- Tabs de filtro -->
      <div class="flex gap-2 flex-wrap" id="tabsContainer">
        <button data-tab="all"  class="tab-btn active rounded-xl border border-outline-variant/20 px-4 py-2 text-sm font-semibold transition-all">Todos</button>
        <button data-tab="home" class="tab-btn rounded-xl border border-outline-variant/20 px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-all">Home</button>
        <button data-tab="nota" class="tab-btn rounded-xl border border-outline-variant/20 px-4 py-2 text-sm font-semibold text-on-surface-variant hover:bg-surface-container-high transition-all">Nota / Artículo</button>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="statsRow">
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wide mb-1">Total</p>
          <p class="text-2xl font-bold font-headline" id="statTotal">—</p>
        </div>
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wide mb-1">Activos</p>
          <p class="text-2xl font-bold font-headline text-green-400" id="statActivos">—</p>
        </div>
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wide mb-1">Con imagen</p>
          <p class="text-2xl font-bold font-headline text-primary" id="statConImagen">—</p>
        </div>
        <div class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4">
          <p class="text-xs text-on-surface-variant font-semibold uppercase tracking-wide mb-1">Placeholders</p>
          <p class="text-2xl font-bold font-headline text-on-surface-variant" id="statPlaceholders">—</p>
        </div>
      </div>

      <!-- Grid de cards -->
      <div id="bannersGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <!-- Skeleton de carga -->
        <div class="skeleton-card rounded-2xl border border-outline-variant/10 bg-surface-container-high p-5 space-y-3 animate-pulse">
          <div class="h-4 bg-surface-container rounded w-2/3"></div>
          <div class="h-24 bg-surface-container rounded-xl"></div>
          <div class="h-3 bg-surface-container rounded w-1/2"></div>
          <div class="h-8 bg-surface-container rounded-lg"></div>
        </div>
        <div class="skeleton-card rounded-2xl border border-outline-variant/10 bg-surface-container-high p-5 space-y-3 animate-pulse">
          <div class="h-4 bg-surface-container rounded w-2/3"></div>
          <div class="h-24 bg-surface-container rounded-xl"></div>
          <div class="h-3 bg-surface-container rounded w-1/2"></div>
          <div class="h-8 bg-surface-container rounded-lg"></div>
        </div>
        <div class="skeleton-card rounded-2xl border border-outline-variant/10 bg-surface-container-high p-5 space-y-3 animate-pulse">
          <div class="h-4 bg-surface-container rounded w-2/3"></div>
          <div class="h-24 bg-surface-container rounded-xl"></div>
          <div class="h-3 bg-surface-container rounded w-1/2"></div>
          <div class="h-8 bg-surface-container rounded-lg"></div>
        </div>
      </div>

    </div><!-- /max-w -->
  </main>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-5 right-5 z-[200] hidden items-center gap-3 rounded-xl border border-outline-variant/20 bg-surface-container-high px-4 py-3 shadow-2xl text-sm font-semibold max-w-xs">
  <span id="toastIcon" class="material-symbols-outlined text-[20px]">check_circle</span>
  <span id="toastMsg">OK</span>
</div>

<script src="/frontend/app.js"></script>
<script>
(function () {
  // ─── Estado global ────────────────────────────────────────
  let allBanners   = [];
  let currentTab   = 'all';
  let uploadingId  = null; // bannerId bajo upload activo

  const API = '/api/banners';

  // ─── Auth ─────────────────────────────────────────────────
  const token = localStorage.getItem('adminToken') || localStorage.getItem('token') || '';
  if (!token) { window.location.href = '/login'; return; }

  const authHeaders = {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  };

  // ─── Init ─────────────────────────────────────────────────
  loadBanners();
  bindTabs();

  // ─── Cargar banners ───────────────────────────────────────
  async function loadBanners() {
    try {
      const resp = await fetch(API);
      if (resp.status === 401) { window.location.href = '/login'; return; }
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      allBanners = await resp.json();
      updateStats();
      renderCards();
    } catch (e) {
      console.error(e);
      showToast('Error al cargar banners', 'error');
      document.getElementById('bannersGrid').innerHTML =
        '<p class="text-on-surface-variant text-sm col-span-full text-center py-10">No se pudo cargar. Intentá recargar la página.</p>';
    }
  }

  // ─── Estadísticas ─────────────────────────────────────────
  function updateStats() {
    const active     = allBanners.filter(b => b.active).length;
    const withImg    = allBanners.filter(b => b.imageUrl).length;
    const placeholders = allBanners.filter(b => !b.imageUrl).length;
    document.getElementById('statTotal').textContent       = allBanners.length;
    document.getElementById('statActivos').textContent     = active;
    document.getElementById('statConImagen').textContent   = withImg;
    document.getElementById('statPlaceholders').textContent = placeholders;
  }

  // ─── Tabs ─────────────────────────────────────────────────
  function bindTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        currentTab = btn.dataset.tab;
        document.querySelectorAll('.tab-btn').forEach(b => {
          b.classList.toggle('active', b === btn);
          if (b !== btn) b.classList.add('text-on-surface-variant');
          else b.classList.remove('text-on-surface-variant');
        });
        renderCards();
      });
    });
  }

  // ─── Filtrar por tab ──────────────────────────────────────
  function filteredBanners() {
    if (currentTab === 'all') return allBanners;
    return allBanners.filter(b => b.page === currentTab);
  }

  // ─── Etiqueta de tipo ─────────────────────────────────────
  const TYPE_LABELS = {
    'billboard':     'Billboard 970×90',
    'leaderboard':   'Leaderboard 728×90',
    'rectangle':     'Rectángulo 300×250',
    'half-page':     'Medio Página 300×600',
    'mobile-banner': 'Banner Mobile 320×50',
    'mobile-large':  'Banner Mobile 320×100',
    'mobile-sticky': 'Sticky Mobile 320×50',
  };

  const PAGE_LABELS = { home: 'Home', nota: 'Nota/Artículo' };

  // ─── Renderizar grid ──────────────────────────────────────
  function renderCards() {
    const grid   = document.getElementById('bannersGrid');
    const list   = filteredBanners();

    if (!list.length) {
      grid.innerHTML = '<p class="text-on-surface-variant text-sm col-span-full text-center py-10">Sin banners para este filtro.</p>';
      return;
    }

    grid.innerHTML = list.map(b => buildCard(b)).join('');
    grid.querySelectorAll('.banner-card').forEach(card => bindCard(card));
  }

  // ─── Construir HTML de una card ───────────────────────────
  function buildCard(b) {
    const typeLabel = TYPE_LABELS[b.type] || b.type;
    const pageLabel = PAGE_LABELS[b.page] || b.page;
    const isActive  = b.active;
    const hasImg    = !!b.imageUrl;

    const previewHtml = hasImg
      ? `<img src="${escHtml(b.imageUrl)}" alt="${escHtml(b.altText || b.label)}"
              class="banner-preview-img rounded-lg mx-auto" loading="lazy"
              onerror="this.style.display='none';this.nextElementSibling.style.display='flex'" />
         <div class="hidden flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container text-on-surface-variant" style="min-height:80px">
           <span class="material-symbols-outlined text-3xl opacity-30">broken_image</span>
           <span class="text-xs opacity-50">Imagen no encontrada</span>
         </div>`
      : `<div class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container text-on-surface-variant" style="min-height:80px">
           <span class="material-symbols-outlined text-3xl opacity-30">image</span>
           <span class="text-xs opacity-50">Sin imagen</span>
         </div>`;

    return `
    <div class="banner-card rounded-2xl border border-outline-variant/10 bg-surface-container-high p-5 space-y-4"
         data-id="${b.id}" data-position="${escHtml(b.positionId)}">

      <!-- Cabecera -->
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <p class="font-semibold font-headline text-sm leading-tight truncate">${escHtml(b.label)}</p>
          <div class="flex items-center gap-1.5 mt-1 flex-wrap">
            <span class="rounded-md bg-primary/15 border border-primary/25 px-2 py-0.5 text-[10px] font-bold text-primary font-mono leading-none">${escHtml(b.positionId)}</span>
            <span data-type-badge class="rounded-md bg-surface-container border border-outline-variant/20 px-2 py-0.5 text-[10px] text-on-surface-variant leading-none">${escHtml(typeLabel)}</span>
            <span class="rounded-md bg-surface-container border border-outline-variant/20 px-2 py-0.5 text-[10px] text-on-surface-variant leading-none">${escHtml(pageLabel)}</span>
          </div>
        </div>
        <!-- Toggle activo -->
        <label class="flex items-center gap-1.5 cursor-pointer flex-shrink-0" title="Activar / desactivar">
          <span class="text-[10px] font-semibold ${isActive ? 'text-green-400' : 'text-on-surface-variant'}">${isActive ? 'Activo' : 'Inactivo'}</span>
          <div class="relative">
            <input type="checkbox" class="toggle-active sr-only" ${isActive ? 'checked' : ''}/>
            <div class="toggle-track w-9 h-5 rounded-full ${isActive ? 'bg-green-500' : 'bg-outline-variant/40'} relative">
              <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${isActive ? 'translate-x-4' : ''}"></div>
            </div>
          </div>
        </label>
      </div>

      <!-- Preview -->
      <div class="banner-preview-wrap rounded-xl overflow-hidden bg-surface-container flex items-center justify-center" style="min-height:80px;padding:8px">
        ${previewHtml}
      </div>

      <!-- Drop zone de imagen -->
      <div>
        <label class="block text-[11px] font-semibold text-on-surface-variant mb-1.5">Imagen del banner</label>
        <div class="drop-zone relative flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-outline-variant/30 bg-surface-container p-3 text-center cursor-pointer hover:border-primary/40 transition-colors">
          <input type="file" class="img-file-input absolute inset-0 opacity-0 cursor-pointer" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp"/>
          <div class="img-drop-label pointer-events-none">
            <span class="material-symbols-outlined text-2xl text-on-surface-variant opacity-50">upload</span>
            <p class="text-xs text-on-surface-variant mt-0.5">Arrastrá o <span class="text-primary font-semibold">elegí</span> una imagen</p>
            <p class="text-[10px] text-on-surface-variant/40 mt-0.5">PNG · JPG · GIF · WebP · máx. 5 MB</p>
          </div>
          <div class="img-uploading hidden text-xs text-on-surface-variant">Subiendo imagen…</div>
        </div>
        ${hasImg ? `
        <div class="mt-2 flex items-center gap-2 text-xs">
          <span class="font-mono text-on-surface-variant/60 truncate flex-1 text-[10px]">${escHtml(b.imageUrl)}</span>
          <button type="button" class="btn-remove-img flex-shrink-0 rounded-lg border border-error/30 bg-error/10 px-2 py-1 text-[11px] font-semibold text-error hover:bg-error/20 transition-colors">
            <span class="material-symbols-outlined text-[14px] align-middle">delete</span> Quitar
          </button>
        </div>` : ''}
      </div>

      <!-- Campos editables -->
      <div class="space-y-3">
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Tipo de banner</label>
          <select class="field-type w-full rounded-lg border border-outline-variant/20 bg-surface-container px-2.5 py-2 text-sm">
            <option value="billboard"     ${b.type === 'billboard'     ? 'selected' : ''}>Billboard 970×90</option>
            <option value="leaderboard"   ${b.type === 'leaderboard'   ? 'selected' : ''}>Leaderboard 728×90</option>
            <option value="rectangle"     ${b.type === 'rectangle'     ? 'selected' : ''}>Rectángulo 300×250</option>
            <option value="half-page"     ${b.type === 'half-page'     ? 'selected' : ''}>Medio Página 300×600</option>
            <option value="mobile-banner" ${b.type === 'mobile-banner' ? 'selected' : ''}>Banner Mobile 320×50</option>
            <option value="mobile-large"  ${b.type === 'mobile-large'  ? 'selected' : ''}>Banner Mobile 320×100</option>
            <option value="mobile-sticky" ${b.type === 'mobile-sticky' ? 'selected' : ''}>Sticky Mobile 320×50</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Ancho (px)</label>
            <input type="number" class="field-width w-full rounded-lg border border-outline-variant/20 bg-surface-container px-2.5 py-2 text-sm" value="${b.width}" min="1" max="2000"/>
          </div>
          <div>
            <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Alto (px)</label>
            <input type="number" class="field-height w-full rounded-lg border border-outline-variant/20 bg-surface-container px-2.5 py-2 text-sm" value="${b.height}" min="1" max="2000"/>
          </div>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">URL de destino (link al hacer clic)</label>
          <input type="url" class="field-link w-full rounded-lg border border-outline-variant/20 bg-surface-container px-2.5 py-2 text-sm" value="${escHtml(b.linkUrl || '')}" placeholder="https://..."/>
        </div>
        <div>
          <label class="block text-[11px] font-semibold text-on-surface-variant mb-1">Texto alternativo (alt)</label>
          <input type="text" class="field-alt w-full rounded-lg border border-outline-variant/20 bg-surface-container px-2.5 py-2 text-sm" value="${escHtml(b.altText || '')}" placeholder="Descripción de la imagen"/>
        </div>
      </div>

      <!-- Pie: guardar + última actualización -->
      <div class="flex items-center justify-between pt-1 border-t border-outline-variant/10">
        <span class="text-[10px] text-on-surface-variant/40">
          ${b.updatedAt ? 'Actualizado: ' + formatDate(b.updatedAt) : 'Sin cambios'}
        </span>
        <button type="button" class="btn-save rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-on-primary hover:bg-primary/80 transition-colors flex items-center gap-1.5">
          <span class="material-symbols-outlined text-[15px]">save</span>
          Guardar
        </button>
      </div>
    </div>`;
  }

  // ─── Bindear eventos de una card ──────────────────────────
  function bindCard(card) {
    const id        = parseInt(card.dataset.id, 10);
    const toggleCb  = card.querySelector('.toggle-active');
    const fileInput = card.querySelector('.img-file-input');
    const dropZone  = card.querySelector('.drop-zone');
    const btnRemove = card.querySelector('.btn-remove-img');
    const btnSave   = card.querySelector('.btn-save');

    // Toggle activo/inactivo
    toggleCb.addEventListener('change', async () => {
      const newActive = toggleCb.checked;
      await saveBanner(id, { active: newActive ? 1 : 0 });
      // Actualizar label visual
      const lbl = toggleCb.closest('label').querySelector('span');
      lbl.textContent = newActive ? 'Activo' : 'Inactivo';
      lbl.className   = 'text-[10px] font-semibold ' + (newActive ? 'text-green-400' : 'text-on-surface-variant');
      const track = toggleCb.nextElementSibling;
      track.className = `toggle-track w-9 h-5 rounded-full ${newActive ? 'bg-green-500' : 'bg-outline-variant/40'} relative`;
      track.querySelector('div').className = `absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${newActive ? 'translate-x-4' : ''}`;
      updateBannerLocal(id, { active: newActive });
      updateStats();
    });

    // Drag-and-drop
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
      e.preventDefault();
      dropZone.classList.remove('drag-over');
      const file = e.dataTransfer?.files?.[0];
      if (file) uploadImage(id, file, card);
    });

    // Click en file input
    fileInput.addEventListener('change', () => {
      const file = fileInput.files?.[0];
      if (file) uploadImage(id, file, card);
    });

    // Quitar imagen
    if (btnRemove) {
      btnRemove.addEventListener('click', async () => {
        if (!confirm('¿Quitar la imagen de este banner? Se desactivará también.')) return;
        try {
          const resp = await fetch(`/api/banners/${id}/image`, {
            method: 'DELETE',
            headers: { 'Authorization': 'Bearer ' + token }
          });
          if (!resp.ok) throw new Error('HTTP ' + resp.status);
          updateBannerLocal(id, { imageUrl: null, linkUrl: null, altText: '', active: false });
          updateStats();
          renderCards();
          showToast('Imagen eliminada');
        } catch (e) {
          showToast('Error al eliminar la imagen', 'error');
        }
      });
    }

    // Guardar campos
    btnSave.addEventListener('click', async () => {
      const type   = card.querySelector('.field-type').value;
      const width  = parseInt(card.querySelector('.field-width').value, 10);
      const height = parseInt(card.querySelector('.field-height').value, 10);
      const link   = card.querySelector('.field-link').value.trim();
      const alt    = card.querySelector('.field-alt').value.trim();

      if (!width || !height || width < 1 || height < 1) {
        showToast('Ancho y alto deben ser mayores a 0', 'error');
        return;
      }

      const saved = await saveBanner(id, { type, width, height, link_url: link || null, alt_text: alt });
      if (saved) {
        updateBannerLocal(id, { type, width, height, linkUrl: link || null, altText: alt });
        // Actualizar badge de tipo en la cabecera
        const typeBadge = card.querySelector('[data-type-badge]');
        if (typeBadge) typeBadge.textContent = TYPE_LABELS[type] || type;
        // Actualizar timestamp en la card sin re-renderizar
        const tsEl = card.querySelector('.banner-card > .flex + .space-y-3 ~ div span, .border-t span');
        const lastTs = card.querySelector('.border-t span');
        if (lastTs) lastTs.textContent = 'Actualizado: ' + formatDate(new Date().toISOString());
      }
    });
  }

  // ─── Upload imagen ────────────────────────────────────────
  async function uploadImage(id, file, card) {
    const MAX = 5 * 1024 * 1024;
    const ALLOWED = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

    if (!ALLOWED.includes(file.type)) {
      showToast('Solo se permiten imágenes PNG, JPG, GIF o WebP', 'error');
      return;
    }
    if (file.size > MAX) {
      showToast('La imagen no puede superar 5 MB', 'error');
      return;
    }

    const dropZone    = card.querySelector('.drop-zone');
    const labelEl     = card.querySelector('.img-drop-label');
    const uploadingEl = card.querySelector('.img-uploading');

    labelEl.classList.add('hidden');
    uploadingEl.classList.remove('hidden');
    dropZone.style.pointerEvents = 'none';

    try {
      const formData = new FormData();
      formData.append('image', file);

      const resp = await fetch('/api/uploads/image', {
        method: 'POST',
        headers: { 'Authorization': 'Bearer ' + token },
        body: formData
      });

      if (!resp.ok) {
        const err = await resp.json().catch(() => ({}));
        throw new Error(err.error || 'HTTP ' + resp.status);
      }

      const { url } = await resp.json();

      // Guardar la URL en el banner
      const saved = await saveBanner(id, { image_url: url, active: 1 });
      if (saved) {
        updateBannerLocal(id, { imageUrl: url, active: true });
        updateStats();
        renderCards();
        showToast('Imagen guardada correctamente');
      }
    } catch (e) {
      showToast('Error al subir imagen: ' + e.message, 'error');
      labelEl.classList.remove('hidden');
      uploadingEl.classList.add('hidden');
      dropZone.style.pointerEvents = '';
    }
  }

  // ─── Guardar campos vía API ───────────────────────────────
  async function saveBanner(id, fields) {
    try {
      const resp = await fetch(`/api/banners/${id}`, {
        method: 'PUT',
        headers: authHeaders,
        body: JSON.stringify(fields)
      });

      if (resp.status === 401) { window.location.href = '/login'; return false; }
      if (!resp.ok) {
        const err = await resp.json().catch(() => ({}));
        throw new Error(err.error || 'HTTP ' + resp.status);
      }

      const updated = await resp.json();
      updateBannerLocal(id, {
        type:     updated.type,
        imageUrl: updated.imageUrl,
        linkUrl:  updated.linkUrl,
        altText:  updated.altText,
        active:   updated.active,
        width:    updated.width,
        height:   updated.height,
        updatedAt: updated.updatedAt,
      });

      showToast('Banner guardado');
      return true;
    } catch (e) {
      showToast('Error al guardar: ' + e.message, 'error');
      return false;
    }
  }

  // ─── Actualizar estado local ──────────────────────────────
  function updateBannerLocal(id, fields) {
    const idx = allBanners.findIndex(b => b.id === id);
    if (idx !== -1) Object.assign(allBanners[idx], fields);
  }

  // ─── Utilidades ───────────────────────────────────────────
  function escHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function formatDate(iso) {
    if (!iso) return '';
    try {
      return new Date(iso).toLocaleDateString('es-AR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    } catch { return iso; }
  }

  let toastTimer;
  function showToast(msg, type = 'success') {
    const t   = document.getElementById('toast');
    const ico = document.getElementById('toastIcon');
    const txt = document.getElementById('toastMsg');
    txt.textContent = msg;
    if (type === 'error') {
      ico.textContent = 'error';
      ico.className   = 'material-symbols-outlined text-[20px] text-error';
    } else {
      ico.textContent = 'check_circle';
      ico.className   = 'material-symbols-outlined text-[20px] text-green-400';
    }
    t.classList.remove('hidden');
    t.classList.add('flex');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      t.classList.add('hidden');
      t.classList.remove('flex');
    }, 3000);
  }

})();
</script>
</body>
</html>
