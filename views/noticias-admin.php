<!DOCTYPE html>
<html class="dark" lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Noticias — Juego de Equipo</title>
  <?php require $root . '/views/partials/favicon-links.php'; ?>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&family=Manrope:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
  <script src="/frontend/tailwind-config.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css"/>
  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    #noticiaContenido { min-height: 180px; outline: none; line-height: 1.7; }
    #noticiaContenido p { margin-bottom: 0.4rem; }
    #noticiaContenido ul, #noticiaContenido ol { padding-left: 1.5rem; margin-bottom: 0.4rem; }
    #noticiaContenido a { color: #8c52ff; text-decoration: underline; }
    #dropZoneImg.drag-over { border-color: #8c52ff; background: rgba(140,82,255,0.08); }
    .cropper-view-box, .cropper-face { border-radius: 0; }
    #cropModal img { max-height: 60vh; display: block; }
  </style>
</head>
<body class="bg-surface text-on-surface font-body">
<?php require $root . '/views/partials/gtm-body.php'; ?>
<div id="sharedLayout" class="flex min-h-screen flex-col">
  <main class="flex-1 overflow-y-auto p-4 md:p-8">
    <div class="max-w-[1600px] mx-auto space-y-6">
      <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold font-headline">Módulo Editorial</h1>
        <p class="text-on-surface-variant mt-1 text-sm sm:text-base">Crear, publicar, destacar y ordenar noticias de portada.</p>
      </div>

      <section class="rounded-xl border border-outline-variant/10 bg-surface-container-high p-4 sm:p-5">
        <form id="noticiaForm" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

          <!-- Volanta -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaVolanta">Volanta</label>
            <input id="noticiaVolanta" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          </div>

          <!-- Título -->
          <div class="xl:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaTitulo">Título <span class="text-on-surface-variant/50 text-[10px] font-normal">(requerido para publicar)</span></label>
            <input id="noticiaTitulo" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          </div>

          <!-- Slug -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaSlug">
              Slug <span class="text-[10px] font-normal text-on-surface-variant/50 ml-1">auto del título</span>
            </label>
            <input id="noticiaSlug" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm font-mono text-xs"/>
          </div>

          <!-- Autor -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaAutor">Autor</label>
            <input id="noticiaAutor" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          </div>

          <!-- Categoría -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaCategoria">Categoría</label>
            <input id="noticiaCategoria" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          </div>

          <!-- Tag -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaTag">Tag</label>
            <input id="noticiaTag" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          </div>

          <!-- Drop zone imagen -->
          <div class="xl:col-span-2 md:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Imagen de portada</label>
            <div id="dropZoneImg" class="relative flex flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-outline-variant/40 bg-surface-container p-3 text-center cursor-pointer hover:border-primary/50 transition-colors">
              <input id="noticiaImgFile" type="file" accept="image/png,image/jpeg,image/jpg" class="hidden"/>
              <div id="dropImgPlaceholder">
                <span class="material-symbols-outlined text-3xl text-on-surface-variant">image</span>
                <p class="text-xs text-on-surface-variant mt-0.5">Arrastrá o <span class="text-primary font-semibold" id="dropImgLabel">elegí</span> una imagen (PNG/JPG &le; 5MB)</p>
              </div>
              <div id="dropImgPreviewWrap" class="hidden w-full">
                <img id="dropImgPreview" class="max-h-36 mx-auto rounded object-cover"/>
                <button type="button" id="btnQuitarImg" class="mt-1 text-xs text-error hover:underline">Quitar</button>
              </div>
              <div id="dropImgUploading" class="hidden text-xs text-on-surface-variant">Subiendo…</div>
            </div>
            <input id="noticiaImagen" type="hidden"/>
          </div>

          <!-- Crédito / texto imagen -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaImagenAlt">Crédito de foto</label>
            <input id="noticiaImagenAlt" type="text" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
            <p class="mt-1 text-[11px] text-on-surface-variant/50">Ejemplo: PH: YAMI ROSAS</p>
          </div>

          <!-- Temporada -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaTemporada">Temporada</label>
            <select id="noticiaTemporada" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"></select>
          </div>

          <!-- Estado + Republicar -->
          <div class="xl:col-span-2 md:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaEstado">Estado de publicación</label>
            <div class="flex gap-2 items-center">
              <select id="noticiaEstado" class="flex-1 rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm">
                <option value="borrador">Borrador</option>
                <option value="publicada">Publicada</option>
                <option value="archivada">Archivada</option>
              </select>
              <button type="button" id="btnRepublicar" class="hidden rounded-lg border border-primary/30 bg-primary/10 px-3 py-2.5 text-xs font-semibold text-primary hover:bg-primary/20 transition-colors whitespace-nowrap">
                Republicar
              </button>
            </div>
            <p class="text-[11px] text-on-surface-variant/50 mt-1">La fecha se asigna al publicar. "Republicar" la actualiza a ahora.</p>
          </div>

          <!-- Tipo de contenido -->
          <div class="xl:col-span-2 md:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaTipoContenido">Tipo de contenido</label>
            <select id="noticiaTipoContenido" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm">
              <option value="comun">Nota común</option>
              <option value="galeria">Nota galería</option>
              <option value="boletin">Nota boletín</option>
            </select>
            <p class="text-[11px] text-on-surface-variant/50 mt-1">Galería: solo fotos con epígrafes. Boletín: nota común + PDF embebido.</p>
          </div>

          <!-- Prioridad home -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaOrden">
              Prioridad en home <span class="text-[10px] font-normal text-on-surface-variant/50">(1 = primero)</span>
            </label>
            <input id="noticiaOrden" type="number" min="1" value="999" class="w-full rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"/>
          </div>

          <!-- Destacada -->
          <div>
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">¿Destacada?</label>
            <label class="inline-flex items-center gap-2 rounded-lg border border-outline-variant/20 bg-surface-container px-3 py-2.5 text-sm w-full cursor-pointer" style="height:42px">
              <input id="noticiaDestacada" type="checkbox" class="rounded border-outline-variant"/> Destacar en portada
            </label>
          </div>

          <!-- Copete -->
          <div class="xl:col-span-4 md:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1" for="noticiaCopete">Copete <span class="text-on-surface-variant/50 text-[10px] font-normal">(requerido para publicar)</span></label>
            <textarea id="noticiaCopete" class="w-full min-h-[90px] rounded-lg border border-outline-variant/20 bg-surface-container p-2.5 text-sm"></textarea>
          </div>

          <!-- Editor de contenido -->
          <div id="bloqueContenidoComun" class="xl:col-span-4 md:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1">Cuerpo de la nota</label>
            <div class="flex flex-wrap gap-1 p-1 bg-surface-container rounded-t-lg border border-b-0 border-outline-variant/20 text-sm">
              <button type="button" data-cmd="bold"                class="ed-btn h-7 w-7 flex items-center justify-center rounded hover:bg-surface-container-high font-bold">B</button>
              <button type="button" data-cmd="italic"              class="ed-btn h-7 w-7 flex items-center justify-center rounded hover:bg-surface-container-high italic">I</button>
              <button type="button" data-cmd="underline"           class="ed-btn h-7 w-7 flex items-center justify-center rounded hover:bg-surface-container-high underline">U</button>
              <div class="w-px bg-outline-variant/30 mx-1"></div>
              <button type="button" data-cmd="insertUnorderedList" class="ed-btn h-7 px-2 flex items-center justify-center rounded hover:bg-surface-container-high text-xs">• Lista</button>
              <button type="button" data-cmd="insertOrderedList"   class="ed-btn h-7 px-2 flex items-center justify-center rounded hover:bg-surface-container-high text-xs">1. Núm</button>
              <div class="w-px bg-outline-variant/30 mx-1"></div>
              <button type="button" id="edBtnLink"                  class="ed-btn h-7 px-2 flex items-center justify-center rounded hover:bg-surface-container-high text-xs">🔗 Link</button>
              <button type="button" data-cmd="removeFormat"         class="ed-btn h-7 px-2 flex items-center justify-center rounded hover:bg-surface-container-high text-xs text-on-surface-variant">✕ Quitar formato</button>
            </div>
            <div id="noticiaContenido" contenteditable="true"
              class="w-full rounded-b-lg border border-outline-variant/20 bg-white text-gray-900 px-3 py-2.5 text-sm min-h-[200px]"></div>
          </div>

          <!-- Galería -->
          <div id="bloqueGaleria" class="hidden xl:col-span-4 md:col-span-2">
            <div class="rounded-lg border border-outline-variant/20 bg-surface-container p-3 space-y-3">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <h3 class="text-sm font-semibold">Galería de fotos</h3>
                  <p class="text-xs text-on-surface-variant/60">Se muestran una debajo de la otra y cada foto puede tener epígrafe.</p>
                </div>
                <div>
                  <input id="noticiaGaleriaFiles" type="file" accept="image/png,image/jpeg,image/jpg" multiple class="hidden"/>
                  <button type="button" id="btnAgregarFotosGaleria" class="rounded-lg border border-outline-variant/20 px-3 py-2 text-xs font-semibold hover:bg-surface-container-high">Agregar fotos</button>
                </div>
              </div>
              <div id="galeriaUploading" class="hidden text-xs text-on-surface-variant">Subiendo imágenes…</div>
              <div id="noticiaGaleriaItems" class="space-y-3"></div>
            </div>
          </div>

          <!-- Boletín PDF -->
          <div id="bloqueBoletin" class="hidden xl:col-span-4 md:col-span-2">
            <div class="rounded-lg border border-outline-variant/20 bg-surface-container p-3 space-y-3">
              <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                  <h3 class="text-sm font-semibold">PDF del boletín</h3>
                  <p class="text-xs text-on-surface-variant/60">El PDF se embebe al final de la nota ocupando todo el ancho.</p>
                </div>
                <div>
                  <input id="noticiaBoletinPdfFile" type="file" accept="application/pdf" class="hidden"/>
                  <button type="button" id="btnSubirBoletinPdf" class="rounded-lg border border-outline-variant/20 px-3 py-2 text-xs font-semibold hover:bg-surface-container-high">Subir PDF</button>
                </div>
              </div>
              <div id="boletinPdfUploading" class="hidden text-xs text-on-surface-variant">Subiendo PDF…</div>
              <input id="noticiaBoletinPdfUrl" type="hidden"/>
              <a id="noticiaBoletinPdfLink" href="#" target="_blank" rel="noopener noreferrer" class="hidden text-xs text-primary underline break-all"></a>
            </div>
          </div>

          <!-- Relacionadas — buscador -->
          <div class="xl:col-span-4 md:col-span-2">
            <label class="block text-xs font-semibold text-on-surface-variant mb-1.5">Notas relacionadas</label>
            <div class="rounded-lg border border-outline-variant/20 bg-surface-container p-3 space-y-2">
              <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[18px] text-on-surface-variant pointer-events-none select-none">search</span>
                <input id="relacionadasSearch" type="text" placeholder="Buscá por título de nota…"
                  class="w-full rounded-lg border border-outline-variant/10 bg-surface-container-high pl-8 pr-3 py-2 text-sm"/>
              </div>
              <div id="relacionadasResults" class="hidden max-h-44 overflow-y-auto rounded-lg border border-outline-variant/10 bg-surface-container-high divide-y divide-outline-variant/10"></div>
              <div id="relacionadasSelected" class="flex flex-wrap gap-1.5 min-h-[28px]">
                <span class="text-xs text-on-surface-variant/50 italic">Sin notas seleccionadas</span>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="xl:col-span-4 md:col-span-2 flex flex-wrap items-center gap-3 pt-2">
            <button class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary hover:bg-primary-dim transition-colors">Guardar noticia</button>
            <button type="button" id="noticiaReset" class="rounded-lg border border-outline-variant/20 px-4 py-2.5 text-sm font-semibold text-on-surface-variant hover:bg-surface-container">Limpiar</button>
          </div>

        </form>
      </section>

      <section class="rounded-xl border border-outline-variant/10 bg-surface-container-high overflow-hidden">
        <div class="border-b border-outline-variant/10 px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <h2 class="font-bold font-headline">Noticias cargadas</h2>
          <div class="flex items-center gap-2">
            <label for="noticiasEstadoFiltro" class="text-xs text-on-surface-variant">Filtrar por estado:</label>
            <select id="noticiasEstadoFiltro" class="rounded border border-outline-variant/20 bg-surface-container-high px-2 py-1 text-xs">
              <option value="">Todas</option>
              <option value="publicada">Publicadas</option>
              <option value="borrador">Borradores</option>
              <option value="archivada">Archivadas</option>
            </select>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[960px] text-left text-sm">
            <thead class="bg-surface-container text-on-surface-variant text-xs uppercase">
              <tr>
                <th class="px-4 py-3">Título</th>
                <th class="px-4 py-3">Temporada</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3">Home</th>
                <th class="px-4 py-3">Publicación</th>
                <th class="px-4 py-3">Acciones</th>
              </tr>
            </thead>
            <tbody id="noticiasTableBody"></tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</div>

<!-- Modal de recorte de imagen -->
<div id="cropModal" class="fixed inset-0 z-[150] hidden items-center justify-center bg-black/85 p-4">
  <div class="bg-surface rounded-2xl p-5 max-w-2xl w-full flex flex-col gap-4 shadow-2xl">
    <div class="flex items-center justify-between">
      <h3 class="font-bold text-base font-headline">Recortar imagen</h3>
      <button type="button" id="btnCancelCropX" class="flex h-9 w-9 items-center justify-center rounded-xl hover:bg-surface-container transition-colors">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
    <div class="overflow-hidden rounded-xl bg-black flex items-center justify-center" style="max-height:60vh">
      <img id="cropPreview" src="" alt="Recortar"/>
    </div>
    <div class="flex items-center justify-between">
      <p class="text-xs text-on-surface-variant">Arrastrá para mover · Usá las esquinas para redimensionar</p>
      <div class="flex gap-3">
        <button type="button" id="btnCancelCrop" class="px-4 py-2.5 rounded-xl border border-outline-variant/30 text-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-colors">
          Cancelar
        </button>
        <button type="button" id="btnConfirmCrop" class="px-5 py-2.5 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary/90 transition-colors">
          Recortar y subir
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
<script src="/frontend/app.js"></script>
</body>
</html>