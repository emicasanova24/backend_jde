<?php
namespace App;

require_once __DIR__ . '/Database.php';

function isHttpsRequest(): bool {
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        return strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
    }

    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function getRequestBaseUrl(): string {
    $scheme = isHttpsRequest() ? 'https' : 'http';
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');

    return $scheme . '://' . $host;
}

function absoluteUrl(?string $path): ?string {
    if ($path === null) {
        return null;
    }

    $path = trim($path);
    if ($path === '') {
        return null;
    }

    $parts = parse_url($path);
    if ($parts !== false && isset($parts['scheme'])) {
        return $path;
    }

    if (str_starts_with($path, '//')) {
        return (isHttpsRequest() ? 'https:' : 'http:') . $path;
    }

    if (!str_starts_with($path, '/')) {
        $path = '/' . $path;
    }

    return getRequestBaseUrl() . $path;
}

function fetchPublicNoticiaBySlug(string $slug): array|false {
    $row = Database::fetchOne(
        "SELECT n.id, n.slug, n.titulo, n.volanta, n.copete, n.contenido, n.imagen_url AS imagenUrl, n.imagen_alt AS imagenAlt,
            n.tag, n.categoria, n.autor_nombre AS autorNombre, n.estado, n.destacada AS destacada,
            n.tipo_contenido AS tipoContenido, n.boletin_pdf_url AS boletinPdfUrl,
            n.orden_home AS ordenHome, n.published_at AS publishedAt, n.created_at AS createdAt,
            n.updated_at AS updatedAt, n.temporada_id AS temporadaId, t.nombre AS temporadaNombre
        FROM noticias n
        LEFT JOIN temporadas t ON n.temporada_id = t.id
        WHERE n.deleted_at IS NULL AND n.estado = 'publicada' AND n.slug = ?
        LIMIT 1",
        [$slug]
    );

    if (!$row) {
        return false;
    }

    $row['relacionadas'] = Database::fetchAll(
        "SELECT n.id, n.slug, n.titulo, n.copete, n.imagen_url AS imagenUrl, n.tag, n.categoria
        FROM noticias_relacionadas nr
        JOIN noticias n ON nr.noticia_relacionada_id = n.id
        WHERE nr.noticia_id = ? AND n.deleted_at IS NULL AND n.estado = 'publicada'
        ORDER BY n.destacada DESC, n.orden_home ASC, COALESCE(n.published_at, n.created_at) DESC
        LIMIT 3",
        [(int) $row['id']]
    );

    $row['metaDescription'] = noticiaMetaDescription($row);
    $row['pageUrl'] = absoluteUrl('/noticias/' . rawurlencode((string) $row['slug']));
    $row['shareImageUrl'] = resolveNoticiaShareImageUrl($row);
    $row['formattedDate'] = formatNoticiaDate($row['publishedAt'] ?? $row['createdAt'] ?? null);
    $row['readingTime'] = noticiaReadingTime((string) ($row['contenido'] ?? ''));

    return $row;
}

function resolveNoticiaShareImageUrl(array $noticia): string {
    $imageUrl = absoluteUrl($noticia['imagenUrl'] ?? null);
    if ($imageUrl !== null) {
        return $imageUrl;
    }

    return absoluteUrl('/og/noticias/' . rawurlencode((string) $noticia['slug']));
}

function noticiaMetaDescription(array $noticia): string {
    $copete = trim((string) ($noticia['copete'] ?? ''));
    if ($copete !== '') {
        return noticiaTextExcerpt($copete, 180);
    }

    return noticiaTextExcerpt((string) ($noticia['contenido'] ?? ''), 180);
}

function noticiaTextExcerpt(string $html, int $limit = 180): string {
    $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
    if ($text === '') {
        return 'Segu\u00ed las novedades de Juego de Equipo.';
    }

    if (mb_strlen($text, 'UTF-8') <= $limit) {
        return $text;
    }

    return rtrim(mb_substr($text, 0, $limit - 1, 'UTF-8')) . '…';
}

function noticiaReadingTime(string $html): string {
    $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($html)) ?? '');
    if ($plainText === '') {
        return '1 min de lectura';
    }

    $words = preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY);
    $minutes = max(1, (int) ceil(count($words) / 200));

    return $minutes . ' min de lectura';
}

function formatNoticiaDate(?string $raw): string {
    if (!$raw) {
        return '';
    }

    try {
        $date = new \DateTimeImmutable($raw);
    } catch (\Throwable) {
        return '';
    }

    $formatter = new \IntlDateFormatter(
        'es_AR',
        \IntlDateFormatter::LONG,
        \IntlDateFormatter::NONE,
        'America/Argentina/Buenos_Aires',
        \IntlDateFormatter::GREGORIAN,
        "d 'de' MMMM 'de' y"
    );

    return $formatter->format($date) ?: $date->format('d/m/Y');
}

function streamNoticiaOgImage(string $slug, string $root): void {
    $noticia = fetchPublicNoticiaBySlug($slug);
    if (!$noticia) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not Found';
        return;
    }

    if (!empty($noticia['imagenUrl'])) {
        header('Location: ' . absoluteUrl((string) $noticia['imagenUrl']), true, 302);
        return;
    }

    if (!function_exists('imagecreatetruecolor')) {
        header('Location: ' . absoluteUrl('/assets/share-default.png'), true, 302);
        return;
    }

    $cacheDir = $root . '/uploads/generated/og-noticias';
    if (!is_dir($cacheDir) && !mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'No se pudo crear el cache de imagenes';
        return;
    }

    $version = preg_replace('/[^0-9]/', '', (string) ($noticia['updatedAt'] ?? $noticia['createdAt'] ?? ''));
    if ($version === '') {
        $version = (string) time();
    }
    $cacheFile = $cacheDir . '/' . $noticia['slug'] . '-' . $version . '.png';

    if (!is_file($cacheFile)) {
        foreach (glob($cacheDir . '/' . $noticia['slug'] . '-*.png') ?: [] as $existingFile) {
            if ($existingFile !== $cacheFile) {
                @unlink($existingFile);
            }
        }

        generateNoticiaOgImage($noticia, $root, $cacheFile);
    }

    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=3600');
    readfile($cacheFile);
}

function generateNoticiaOgImage(array $noticia, string $root, string $targetFile): void {
    $targetDir = dirname($targetFile);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new \RuntimeException('No se pudo crear el directorio de salida para la imagen OG');
    }

    $width = 1200;
    $height = 630;
    $image = imagecreatetruecolor($width, $height);

    if (function_exists('imageantialias')) {
        imageantialias($image, true);
    }

    $bgTop = [18, 13, 26];
    $bgBottom = [73, 34, 113];
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / max(1, $height - 1);
        $color = imagecolorallocate(
            $image,
            (int) round($bgTop[0] + (($bgBottom[0] - $bgTop[0]) * $ratio)),
            (int) round($bgTop[1] + (($bgBottom[1] - $bgTop[1]) * $ratio)),
            (int) round($bgTop[2] + (($bgBottom[2] - $bgTop[2]) * $ratio))
        );
        imageline($image, 0, $y, $width, $y, $color);
    }

    $panelColor = imagecolorallocate($image, 255, 255, 255);
    imagefilledrectangle($image, 72, 72, 1128, 558, $panelColor);

    $shadowColor = imagecolorallocatealpha($image, 0, 0, 0, 110);
    imagefilledrectangle($image, 84, 84, 1140, 570, $shadowColor);
    imagefilledrectangle($image, 72, 72, 1128, 558, $panelColor);

    $accentStart = imagecolorallocate($image, 140, 82, 255);
    $accentEnd = imagecolorallocate($image, 222, 93, 185);
    for ($x = 0; $x < 18; $x++) {
        $ratio = $x / 17;
        $color = imagecolorallocate(
            $image,
            (int) round(140 + ((222 - 140) * $ratio)),
            (int) round(82 + ((93 - 82) * $ratio)),
            (int) round(255 + ((185 - 255) * $ratio))
        );
        imageline($image, 72 + $x, 72, 72 + $x, 558, $color);
    }

    drawOgBrandMark($image, $root, 118, 118, 128);

    $textColor = imagecolorallocate($image, 19, 16, 27);
    $mutedColor = imagecolorallocate($image, 85, 77, 103);
    $accentTextColor = imagecolorallocate($image, 140, 82, 255);
    $footerColor = imagecolorallocate($image, 112, 104, 131);

    $fontBold = pickSystemFont(true);
    $fontRegular = pickSystemFont(false);

    $currentY = 148;
    if (!empty($noticia['categoria'])) {
        $currentY = drawOgTextBlock($image, mb_strtoupper((string) $noticia['categoria'], 'UTF-8'), 286, $currentY, 30, $accentTextColor, $fontBold, 880, 42, 5);
        $currentY += 10;
    }

    $currentY = drawOgTextBlock($image, (string) ($noticia['titulo'] ?? ''), 286, $currentY, 62, $textColor, $fontBold, 790, 78, 3);
    $currentY += 16;
    $currentY = drawOgTextBlock($image, noticiaMetaDescription($noticia), 286, $currentY, 28, $mutedColor, $fontRegular, 760, 40, 3);

    $footerLine = trim(implode(' · ', array_filter([
        $noticia['autorNombre'] ?? 'Redacción Juego de Equipo',
        formatNoticiaDate($noticia['publishedAt'] ?? $noticia['createdAt'] ?? null),
    ])));

    drawOgTextBlock($image, 'JUEGO DE EQUIPO', 118, 504, 24, $accentTextColor, $fontBold, 280, 32, 1);
    if ($footerLine !== '') {
        drawOgTextBlock($image, $footerLine, 286, 504, 22, $footerColor, $fontRegular, 760, 30, 1);
    }

    imagepng($image, $targetFile, 6);
    imagedestroy($image);
}

function drawOgBrandMark($image, string $root, int $x, int $y, int $size): void {
    $sourcePath = $root . '/assets/logo.png';
    if (!is_file($sourcePath)) {
        return;
    }

    $source = @imagecreatefrompng($sourcePath);
    if (!$source) {
        return;
    }

    $srcWidth = imagesx($source);
    $srcHeight = imagesy($source);
    $cropSize = min($srcHeight, $srcWidth);
    $dest = imagecreatetruecolor($size, $size);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
    imagefill($dest, 0, 0, $transparent);
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $size, $size, $cropSize, $cropSize);

    imagealphablending($image, true);
    imagecopy($image, $dest, $x, $y, 0, 0, $size, $size);

    imagedestroy($dest);
    imagedestroy($source);
}

function pickSystemFont(bool $bold): ?string {
    $candidates = $bold
        ? [
            'C:/Windows/Fonts/arialbd.ttf',
            'C:/Windows/Fonts/segoeuib.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
        ]
        : [
            'C:/Windows/Fonts/arial.ttf',
            'C:/Windows/Fonts/segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function drawOgTextBlock($image, string $text, int $x, int $y, int $fontSize, int $color, ?string $fontPath, int $maxWidth, int $lineHeight, int $maxLines): int {
    $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    if ($text === '') {
        return $y;
    }

    if ($fontPath && function_exists('imagettfbbox') && function_exists('imagettftext')) {
        $lines = wrapOgText($text, $fontPath, $fontSize, $maxWidth, $maxLines);
        foreach ($lines as $line) {
            imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $line);
            $y += $lineHeight;
        }

        return $y;
    }

    $wrapped = wordwrap($text, max(18, (int) floor($maxWidth / 12)), "\n", true);
    $lines = array_slice(explode("\n", $wrapped), 0, $maxLines);
    foreach ($lines as $line) {
        imagestring($image, 5, $x, $y - 18, $line, $color);
        $y += $lineHeight;
    }

    return $y;
}

function wrapOgText(string $text, string $fontPath, int $fontSize, int $maxWidth, int $maxLines): array {
    $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    if (!$words) {
        return [];
    }

    $lines = [];
    $current = '';

    foreach ($words as $word) {
        $candidate = $current === '' ? $word : $current . ' ' . $word;
        $box = imagettfbbox($fontSize, 0, $fontPath, $candidate);
        $lineWidth = $box ? abs($box[2] - $box[0]) : 0;

        if ($current !== '' && $lineWidth > $maxWidth) {
            $lines[] = $current;
            $current = $word;
        } else {
            $current = $candidate;
        }

        if (count($lines) === $maxLines) {
            break;
        }
    }

    if ($current !== '' && count($lines) < $maxLines) {
        $lines[] = $current;
    }

    if (count($lines) === $maxLines && count($words) > 0) {
        $lastIndex = count($lines) - 1;
        $lines[$lastIndex] = rtrim($lines[$lastIndex], ' .') . '…';
    }

    return $lines;
}