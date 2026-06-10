-- =====================================================
-- Tabla: banners
-- Descripción: Gestión de slots publicitarios del sitio
-- Creado: 2026-05-28
-- =====================================================

CREATE TABLE IF NOT EXISTS `banners` (
  `id`          INT            NOT NULL AUTO_INCREMENT,
  `position_id` VARCHAR(64)    NOT NULL,
  `label`       VARCHAR(120)   NOT NULL DEFAULT '',
  `page`        VARCHAR(60)    NOT NULL DEFAULT '',
  `type`        VARCHAR(32)    NOT NULL DEFAULT 'rectangle',
  `width`       SMALLINT       NOT NULL DEFAULT 300,
  `height`      SMALLINT       NOT NULL DEFAULT 250,
  `image_url`   VARCHAR(512)   NULL,
  `link_url`    VARCHAR(512)   NULL,
  `alt_text`    VARCHAR(255)   NULL DEFAULT '',
  `active`      TINYINT(1)     NOT NULL DEFAULT 0,
  `updated_at`  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_position_id` (`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Seed: 14 posiciones publicitarias
-- =====================================================

INSERT INTO `banners`
  (`position_id`, `label`, `page`, `type`, `width`, `height`, `image_url`, `link_url`, `alt_text`, `active`)
VALUES
  -- Home — Desktop
  ('home-hero-d',      'Home Hero — Desktop',          'home',    'billboard',     970,  90,  NULL, NULL, '', 0),
  ('home-news-d',      'Home Noticias — Desktop',       'home',    'leaderboard',   728,  90,  NULL, NULL, '', 0),
  ('home-fxt-tbl-d1',  'Home Fixture/Tabla #1 — Desktop', 'home', 'rectangle',     300, 250,  NULL, NULL, '', 0),
  ('home-fxt-tbl-d2',  'Home Fixture/Tabla #2 — Desktop', 'home', 'rectangle',     300, 250,
    '/banners-sponsors/eduardo-banner.gif',
    'https://api.whatsapp.com/send?phone=5492392517048&text=Hola%20Eduardo!%20Me%20gustar%C3%ADa%20consultarte%20por%20un%20presupuesto.',
    'Eduardo Banner', 1),
  ('home-tbl-gol',     'Home Tabla/Goleadores',         'home',    'leaderboard',   728,  90,  NULL, NULL, '', 0),
  ('home-prefooter-d', 'Home Pre-footer — Desktop',     'home',    'leaderboard',   728,  90,
    '/banners-sponsors/banner-juanoliva.png',
    'https://api.whatsapp.com/send?phone=5492392559254&text=Hola%20Juan!%20Me%20gustar%C3%ADa%20consultarte.',
    'Juan Oliva', 1),

  -- Home — Mobile
  ('home-hero-m',      'Home Hero — Mobile',            'home',    'mobile-large',  320, 100,
    '/banners-sponsors/banner-el-punto.png', NULL, 'El Punto', 1),
  ('home-news-m',      'Home Noticias — Mobile',        'home',    'mobile-banner', 320,  50,
    '/banners-sponsors/pampa-banner.png', NULL, 'Pampa', 1),
  ('home-fxt-tbl-m',   'Home Fixture/Tabla — Mobile',   'home',    'rectangle',     300, 250,
    '/banners-sponsors/eduardo-banner.gif',
    'https://api.whatsapp.com/send?phone=5492392517048&text=Hola%20Eduardo!%20Me%20gustar%C3%ADa%20consultarte%20por%20un%20presupuesto.',
    'Eduardo Banner', 1),
  ('home-tbl-gol-m',   'Home Tabla/Goleadores — Mobile',  'home',  'mobile-banner', 320,  50,  NULL, NULL, '', 0),
  ('home-gol-tarj-m',  'Home Goleadores/Tarjetas — Mobile','home',  'mobile-banner', 320,  50,  NULL, NULL, '', 0),
  ('home-prefooter-m', 'Home Pre-footer — Mobile',      'home',    'mobile-banner', 320,  50,  NULL, NULL, '', 0),
  ('home-sticky-m',    'Home Sticky — Mobile',          'home',    'mobile-sticky', 320,  50,  NULL, NULL, '', 0),

  -- Nota / Artículo
  ('nota-article-d',   'Nota Artículo — Desktop',       'nota',    'leaderboard',   728,  90,  NULL, NULL, '', 0),
  ('nota-article-m',   'Nota Artículo — Mobile',        'nota',    'mobile-large',  320, 100,  NULL, NULL, '', 0),
  ('nota-sidebar',     'Nota Sidebar — Desktop',        'nota',    'half-page',     300, 600,  NULL, NULL, '', 0)

ON DUPLICATE KEY UPDATE
  `label`      = VALUES(`label`),
  `page`       = VALUES(`page`),
  `type`       = VALUES(`type`),
  `width`      = VALUES(`width`),
  `height`     = VALUES(`height`),
  `image_url`  = COALESCE(`image_url`, VALUES(`image_url`)),
  `link_url`   = COALESCE(`link_url`,  VALUES(`link_url`)),
  `alt_text`   = COALESCE(`alt_text`,  VALUES(`alt_text`)),
  `active`     = COALESCE(`active`,    VALUES(`active`));
