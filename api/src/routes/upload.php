<?php
namespace App\Routes;

use App\Router;
use App\Request;
use App\Response;
use function App\Middleware\authMiddleware;

function registerUploadRoutes(Router $router): void {
    // POST /uploads/image — sube una imagen y devuelve su URL pública
    $router->post('/uploads/image', function (Request $req, Response $res) {
        return authMiddleware($req, $res);
    }, function (Request $req, Response $res) {
        try {
            if (empty($_FILES['image'])) {
                $res->status(400)->json(['error' => 'No se recibió ningún archivo (campo: image)']);
                return;
            }

            $file = $_FILES['image'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors = [
                    UPLOAD_ERR_INI_SIZE   => 'El archivo supera el límite del servidor',
                    UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el límite del formulario',
                    UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
                    UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida',
                ];
                $msg = $errors[$file['error']] ?? 'Error desconocido al subir archivo';
                $res->status(400)->json(['error' => $msg]);
                return;
            }

            // Validar tamaño: máx 5 MB
            $maxBytes = 5 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $res->status(400)->json(['error' => 'El archivo supera el máximo permitido (5 MB)']);
                return;
            }

            // Validar tipo MIME real (no confíes en el nombre del archivo)
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!in_array($mime, $allowedMimes, true)) {
                $res->status(400)->json(['error' => 'Tipo de archivo no permitido. Solo PNG, JPG y JPEG']);
                return;
            }

            $ext = match ($mime) {
                'image/png'  => 'png',
                default      => 'jpg',
            };

            // Directorio de destino: /uploads/YYYY-MM/
            $uploadsRoot = dirname(__DIR__, 3) . '/uploads';
            $subDir      = date('Y-m');
            $targetDir   = $uploadsRoot . '/' . $subDir;

            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true)) {
                    $res->status(500)->json(['error' => 'No se pudo crear el directorio de uploads']);
                    return;
                }
            }

            // Nombre único con UUID v4 simple
            $uuid     = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            $filename = $uuid . '.' . $ext;
            $destPath = $targetDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $res->status(500)->json(['error' => 'No se pudo guardar el archivo']);
                return;
            }

            $url = '/uploads/' . $subDir . '/' . $filename;
            $res->status(201)->json(['url' => $url, 'mime' => $mime, 'size' => $file['size']]);
        } catch (\Exception $e) {
            error_log('Error POST /uploads/image: ' . $e->getMessage());
            $res->status(500)->json(['error' => 'Error interno al procesar la imagen']);
        }
    });

    // POST /uploads/pdf — sube un PDF y devuelve su URL pública
    $router->post('/uploads/pdf', function (Request $req, Response $res) {
        return authMiddleware($req, $res);
    }, function (Request $req, Response $res) {
        try {
            if (empty($_FILES['pdf'])) {
                $res->status(400)->json(['error' => 'No se recibió ningún archivo (campo: pdf)']);
                return;
            }

            $file = $_FILES['pdf'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors = [
                    UPLOAD_ERR_INI_SIZE   => 'El archivo supera el límite del servidor',
                    UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el límite del formulario',
                    UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente',
                    UPLOAD_ERR_NO_FILE    => 'No se subió ningún archivo',
                    UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal',
                    UPLOAD_ERR_CANT_WRITE => 'Error al escribir en disco',
                    UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida',
                ];
                $msg = $errors[$file['error']] ?? 'Error desconocido al subir archivo';
                $res->status(400)->json(['error' => $msg]);
                return;
            }

            // Validar tamaño: máx 20 MB
            $maxBytes = 20 * 1024 * 1024;
            if ($file['size'] > $maxBytes) {
                $res->status(400)->json(['error' => 'El archivo supera el máximo permitido (20 MB)']);
                return;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime  = $finfo->file($file['tmp_name']);
            if ($mime !== 'application/pdf') {
                $res->status(400)->json(['error' => 'Tipo de archivo no permitido. Solo PDF']);
                return;
            }

            $uploadsRoot = dirname(__DIR__, 3) . '/uploads';
            $subDir      = date('Y-m');
            $targetDir   = $uploadsRoot . '/' . $subDir;

            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true)) {
                    $res->status(500)->json(['error' => 'No se pudo crear el directorio de uploads']);
                    return;
                }
            }

            $uuid     = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
            $filename = $uuid . '.pdf';
            $destPath = $targetDir . '/' . $filename;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                $res->status(500)->json(['error' => 'No se pudo guardar el archivo']);
                return;
            }

            $url = '/uploads/' . $subDir . '/' . $filename;
            $res->status(201)->json(['url' => $url, 'mime' => $mime, 'size' => $file['size']]);
        } catch (\Exception $e) {
            error_log('Error POST /uploads/pdf: ' . $e->getMessage());
            $res->status(500)->json(['error' => 'Error interno al procesar el PDF']);
        }
    });
}
