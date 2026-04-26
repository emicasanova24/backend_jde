<?php
/**
 * Banner placeholder — views/partials/banner.php
 *
 * Variables (set before include):
 *   string $bannerType    — billboard | leaderboard | rectangle | half-page
 *                           mobile-banner | mobile-large | mobile-sticky
 *   string $bannerId      — unique position ID  (e.g. "home-hero-d")
 *   string $bannerClasses — (optional) overrides the outer-div visibility class;
 *                           when provided, the outer div's default padding is also suppressed.
 */

// Inject CSS exactly once per page request
if (!defined('_BANNER_CSS')) {
    define('_BANNER_CSS', 1);
    echo '<style>
.banner-slot{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;border-radius:12px;border:2px dashed rgba(140,82,255,0.22);background:rgba(18,13,26,0.88);padding:12px;box-sizing:border-box;text-align:center;overflow:hidden;flex-shrink:0;transition:border-color .25s,background .25s;cursor:default}
.banner-slot:hover{border-color:rgba(140,82,255,0.5);background:rgba(140,82,255,0.06)}
.bnt-p{color:#f59e0b}.bnt-s{color:#8c52ff}.bnt-e{color:#6b7280}
</style>';
}

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
$_bw     = (int)$_bs['w'];
$_bh     = (int)$_bs['h'];

// Clean up optional var so it doesn't bleed into the next include
unset($bannerClasses);
?>
<?php if ($_sticky): ?>
<div id="ad-<?= $_bid ?>" class="fixed bottom-0 inset-x-0 z-40 flex md:hidden justify-center items-center py-1.5 bg-[#120d1a]/95 border-t border-white/10" aria-label="Espacio publicitario">
  <div class="banner-slot <?= $_bs['tc'] ?>" style="width:<?= $_bw ?>px;height:<?= $_bh ?>px;max-width:calc(100vw - 2rem)">
    <span style="font-size:8px;font-weight:700;letter-spacing:.15em;opacity:.6;text-transform:uppercase;white-space:nowrap">Publicidad · <?= htmlspecialchars($_bs['label'], ENT_QUOTES, 'UTF-8') ?></span>
    <span style="font-size:8px;font-weight:700;padding:1px 7px;border-radius:4px;border:1px solid currentColor;opacity:.8;white-space:nowrap"><?= htmlspecialchars($_bs['tier'], ENT_QUOTES, 'UTF-8') ?> · Tu anuncio aquí · Contactanos</span>
  </div>
</div>
<div style="height:<?= $_bh + 10 ?>px" class="flex md:hidden" aria-hidden="true"></div>
<?php else: ?>
<div id="ad-<?= $_bid ?>" class="<?= htmlspecialchars($_cls, ENT_QUOTES, 'UTF-8') ?> justify-center items-center<?= $_pad ?>" aria-label="Espacio publicitario">
  <?php if ($_bid === 'home-fxt-tbl-d2' || $_bid === 'home-fxt-tbl-m'): ?>
    <a href="https://api.whatsapp.com/send?phone=5492392517048&text=Hola%20Eduardo!%20Me%20gustar%C3%ADa%20consultarte%20por%20un%20presupuesto." target="_blank" rel="noopener noreferrer">
      <img src="/banners-sponsors/eduardo-banner.gif" alt="Eduardo Banner" width="300" height="250" class="rounded-xl shadow-lg border border-white/10" style="max-width:100%;height:auto;" />
    </a>
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
