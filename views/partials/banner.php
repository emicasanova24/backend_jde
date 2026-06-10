<?php
/**
 * Banner — views/partials/banner.php
 *
 * Variables (set before include):
 *   string $bannerType    — billboard | leaderboard | rectangle | half-page
 *                           mobile-banner | mobile-large | mobile-sticky
 *   string $bannerId      — unique position ID  (e.g. "home-hero-d")
 *   string $bannerClasses — (optional) overrides the outer-div visibility class;
 *                           when provided, the outer div's default padding is also suppressed.
 *
 * La configuración de cada posición se lee desde uploads/banners-cache.json
 * (generado por la API cada vez que se guarda un cambio desde el admin).
 * Si el archivo no existe aún, se mantiene el comportamiento de placeholder.
 */

// ── CSS inyectado una sola vez por request ──────────────────────────────────
if (!defined('_BANNER_CSS')) {
    define('_BANNER_CSS', 1);
    echo '<style>
.banner-slot{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;border-radius:12px;border:2px dashed rgba(140,82,255,0.22);background:rgba(18,13,26,0.88);padding:12px;box-sizing:border-box;text-align:center;overflow:hidden;flex-shrink:0;transition:border-color .25s,background .25s;cursor:default}
.banner-slot:hover{border-color:rgba(140,82,255,0.5);background:rgba(140,82,255,0.06)}
.bnt-p{color:#f59e0b}.bnt-s{color:#8c52ff}.bnt-e{color:#6b7280}
</style>';
}

// ── Cargar cache JSON una sola vez por request ──────────────────────────────
if (!defined('_BANNERS_DB_LOADED')) {
    define('_BANNERS_DB_LOADED', 1);
    $GLOBALS['_BANNERS_DB'] = [];
    $_cacheFile = dirname(__DIR__, 2) . '/uploads/banners-cache.json';
    if (is_file($_cacheFile)) {
        $_raw = file_get_contents($_cacheFile);
        if ($_raw !== false) {
            $_parsed = json_decode($_raw, true);
            if (is_array($_parsed)) {
                $GLOBALS['_BANNERS_DB'] = $_parsed;
            }
        }
    }
    unset($_cacheFile, $_raw, $_parsed);
}

// ── Specs por defecto de cada tipo ─────────────────────────────────────────
$_bspecs = [
    'billboard'     => ['w'=>970, 'h'=>90,  'label'=>'Billboard 970×90',      'tier'=>'$$$$', 'tc'=>'bnt-p', 'disp'=>'hidden md:flex'],
    'leaderboard'   => ['w'=>728, 'h'=>90,  'label'=>'Leaderboard 728×90',    'tier'=>'$$$',  'tc'=>'bnt-s', 'disp'=>'hidden md:flex'],
    'rectangle'     => ['w'=>300, 'h'=>250, 'label'=>'Rectángulo 300×250',    'tier'=>'$$$',  'tc'=>'bnt-s', 'disp'=>'flex'],
    'half-page'     => ['w'=>300, 'h'=>600, 'label'=>'Medio Página 300×600',  'tier'=>'$$$',  'tc'=>'bnt-s', 'disp'=>'hidden lg:flex'],
    'mobile-banner' => ['w'=>320, 'h'=>50,  'label'=>'Banner Mobile 320×50',  'tier'=>'$$',   'tc'=>'bnt-e', 'disp'=>'flex md:hidden'],
    'mobile-large'  => ['w'=>320, 'h'=>100, 'label'=>'Banner Mob. Lg 320×100','tier'=>'$$$',  'tc'=>'bnt-s', 'disp'=>'flex md:hidden'],
    'mobile-sticky' => ['w'=>320, 'h'=>50,  'label'=>'Sticky Mobile 320×50',  'tier'=>'$$$$', 'tc'=>'bnt-p', 'disp'=>'flex md:hidden'],
];

$_bs     = $_bspecs[$bannerType] ?? $_bspecs['rectangle'];
$_bid    = htmlspecialchars($bannerId ?? 'banner', ENT_QUOTES, 'UTF-8');
$_sticky = ($bannerType === 'mobile-sticky');
$_cls    = isset($bannerClasses) ? $bannerClasses : $_bs['disp'];
$_pad    = isset($bannerClasses) ? '' : ' py-5 px-4';

// ── Leer config desde el cache ──────────────────────────────────────────────
$_dbCfg  = $GLOBALS['_BANNERS_DB'][$bannerId ?? ''] ?? null;
$_bw     = ($_dbCfg && isset($_dbCfg['width']))   ? (int)$_dbCfg['width']  : (int)$_bs['w'];
$_bh     = ($_dbCfg && isset($_dbCfg['height']))  ? (int)$_dbCfg['height'] : (int)$_bs['h'];
$_imgUrl = ($_dbCfg && !empty($_dbCfg['image_url']) && $_dbCfg['active']) ? $_dbCfg['image_url'] : null;
$_lnkUrl = ($_dbCfg && !empty($_dbCfg['link_url'])) ? $_dbCfg['link_url'] : null;
$_altTxt = ($_dbCfg && !empty($_dbCfg['alt_text'])) ? $_dbCfg['alt_text'] : '';

// ── Limpiar variable opcional ───────────────────────────────────────────────
unset($bannerClasses);
?>
<?php if ($_sticky): ?>
<div id="ad-<?= $_bid ?>" class="fixed bottom-0 inset-x-0 z-40 flex md:hidden justify-center items-center py-1.5 bg-[#120d1a]/95 border-t border-white/10" aria-label="Espacio publicitario">
  <?php if ($_imgUrl): ?>
    <?php if ($_lnkUrl): ?>
      <a href="<?= htmlspecialchars($_lnkUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer sponsored">
        <img src="<?= htmlspecialchars($_imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($_altTxt, ENT_QUOTES, 'UTF-8') ?>" width="<?= $_bw ?>" height="<?= $_bh ?>" style="max-width:calc(100vw - 2rem);height:auto;display:block;" loading="lazy"/>
      </a>
    <?php else: ?>
      <img src="<?= htmlspecialchars($_imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($_altTxt, ENT_QUOTES, 'UTF-8') ?>" width="<?= $_bw ?>" height="<?= $_bh ?>" style="max-width:calc(100vw - 2rem);height:auto;display:block;" loading="lazy"/>
    <?php endif; ?>
  <?php else: ?>
    <div class="banner-slot <?= $_bs['tc'] ?>" style="width:<?= $_bw ?>px;height:<?= $_bh ?>px;max-width:calc(100vw - 2rem)">
      <span style="font-size:8px;font-weight:700;letter-spacing:.15em;opacity:.6;text-transform:uppercase;white-space:nowrap">Publicidad · <?= htmlspecialchars($_bs['label'], ENT_QUOTES, 'UTF-8') ?></span>
      <span style="font-size:8px;font-weight:700;padding:1px 7px;border-radius:4px;border:1px solid currentColor;opacity:.8;white-space:nowrap"><?= htmlspecialchars($_bs['tier'], ENT_QUOTES, 'UTF-8') ?> · Tu anuncio aquí · Contactanos</span>
    </div>
  <?php endif; ?>
</div>
<div style="height:<?= $_bh + 10 ?>px" class="flex md:hidden" aria-hidden="true"></div>
<?php else: ?>
<div id="ad-<?= $_bid ?>" class="<?= htmlspecialchars($_cls, ENT_QUOTES, 'UTF-8') ?> justify-center items-center<?= $_pad ?>" aria-label="Espacio publicitario">
  <?php if ($_imgUrl): ?>
    <?php if ($_lnkUrl): ?>
      <a href="<?= htmlspecialchars($_lnkUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer sponsored">
        <img src="<?= htmlspecialchars($_imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($_altTxt, ENT_QUOTES, 'UTF-8') ?>" width="<?= $_bw ?>" height="<?= $_bh ?>" class="rounded-xl shadow-lg border border-white/10" style="max-width:100%;height:auto;" loading="lazy"/>
      </a>
    <?php else: ?>
      <img src="<?= htmlspecialchars($_imgUrl, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($_altTxt, ENT_QUOTES, 'UTF-8') ?>" width="<?= $_bw ?>" height="<?= $_bh ?>" class="rounded-xl shadow-lg border border-white/10" style="max-width:100%;height:auto;" loading="lazy"/>
    <?php endif; ?>
  <?php else: ?>
    <div class="banner-slot <?= $_bs['tc'] ?>" style="width:<?= $_bw ?>px;height:<?= $_bh ?>px;max-width:calc(100vw - 2rem)">
      <span class="material-symbols-outlined" style="font-size:22px;opacity:.22;color:inherit">image</span>
      <span style="font-size:9px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.55)">Publicidad</span>
      <span style="font-size:12px;font-weight:700"><?= htmlspecialchars($_bs['label'], ENT_QUOTES, 'UTF-8') ?></span>
      <span style="font-size:10px;opacity:.35;color:#fff"><?= $_bw ?>×<?= $_bh ?>px</span>
      <span style="font-size:9px;font-weight:700;padding:2px 8px;border-radius:4px;border:1px solid currentColor;opacity:.85;margin-top:3px"><?= htmlspecialchars($_bs['tier'], ENT_QUOTES, 'UTF-8') ?></span>
      <span style="font-size:9px;color:rgba(255,255,255,.2);margin-top:3px">Tu anuncio aquí · Contactanos</span>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
