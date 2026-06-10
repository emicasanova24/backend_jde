<?php
namespace App;

/**
 * WhatsApp.php — Integración Meta Cloud API para envío de códigos OTP
 *
 * Config requerida (variables de entorno o config.php):
 *   WA_PHONE_NUMBER_ID  — ID del número de teléfono de WhatsApp Business
 *   WA_ACCESS_TOKEN     — Token de acceso permanente de Meta
 *   WA_TEMPLATE_NAME    — Nombre del template aprobado (default: 'prode_verificacion')
 *
 * Si WA_ACCESS_TOKEN no está configurado, el código se loguea en /wa-otp-dev.log
 * (modo desarrollo — nunca usar en producción).
 *
 * Template de autenticación recomendado (Meta Business Suite):
 *   Categoría: AUTHENTICATION
 *   Cuerpo: "Tu código de verificación para el Prode JDE es: {{1}}. Válido por 10 minutos."
 *   Idioma: es_AR
 */
class WhatsApp
{
    private const GRAPH_API_VERSION = 'v20.0';
    private const GRAPH_API_BASE    = 'https://graph.facebook.com';

    /**
     * Envía un código OTP por WhatsApp al número indicado.
     *
     * @param string $telefono  Número en formato E.164 (+54XXXXXXXXXX)
     * @param string $codigo    Código de 6 dígitos
     * @return bool             true si el envío fue exitoso (o simulado en dev)
     */
    public static function sendOtp(string $telefono, string $codigo): bool
    {
        $phoneNumberId = $_ENV['WA_PHONE_NUMBER_ID'] ?? getenv('WA_PHONE_NUMBER_ID') ?: '';
        $accessToken   = $_ENV['WA_ACCESS_TOKEN']   ?? getenv('WA_ACCESS_TOKEN')   ?: '';
        $templateName  = $_ENV['WA_TEMPLATE_NAME']  ?? getenv('WA_TEMPLATE_NAME')  ?: 'prode_verificacion';

        // Modo desarrollo: si no hay credenciales, loguear el código
        if ($accessToken === '' || $phoneNumberId === '') {
            self::devLog($telefono, $codigo);
            return true;
        }

        $url = sprintf(
            '%s/%s/%s/messages',
            self::GRAPH_API_BASE,
            self::GRAPH_API_VERSION,
            $phoneNumberId
        );

        $payload = [
            'messaging_product' => 'whatsapp',
            'to'                => $telefono,
            'type'              => 'template',
            'template'          => [
                'name'       => $templateName,
                'language'   => ['code' => 'es_AR'],
                'components' => [
                    [
                        'type'       => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $codigo],
                        ],
                    ],
                    // Botón de copiado de OTP (si el template lo incluye)
                    [
                        'type'     => 'button',
                        'sub_type' => 'url',
                        'index'    => '0',
                        'parameters' => [
                            ['type' => 'text', 'text' => $codigo],
                        ],
                    ],
                ],
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[WhatsApp] cURL error: ' . $curlError);
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log('[WhatsApp] HTTP ' . $httpCode . ' — ' . $response);
            return false;
        }

        return true;
    }

    /**
     * Loguea el código OTP en archivo de desarrollo (nunca en producción).
     */
    private static function devLog(string $telefono, string $codigo): void
    {
        $line = sprintf(
            "[%s] PRODE OTP DEV | Teléfono: %s | Código: %s\n",
            date('Y-m-d H:i:s'),
            $telefono,
            $codigo
        );
        $logPath = dirname(__DIR__, 2) . '/wa-otp-dev.log';
        file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }
}
