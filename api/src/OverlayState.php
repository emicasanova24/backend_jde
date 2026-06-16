<?php
namespace App;

class OverlayState
{
    private static string $stateFile = '';

    private static function getStatePath(): string
    {
        if (self::$stateFile === '') {
            // Sube dos niveles desde api/src/ → raíz del proyecto
            self::$stateFile = dirname(__DIR__, 2) . '/overlay-state.json';
        }
        return self::$stateFile;
    }

    public static function defaultState(): array
    {
        return [
            'match' => [
                'status'     => 'NO INICIADO',
                'minute'     => '',
                'referee'    => '',
                'stadium'    => '',
                'tournament' => 'APERTURA 2026',
            ],
            'timer' => [
                'running'        => false,
                'startTimestamp' => null,
                'baseMinute'     => 0,
                'period'         => 'PT',
                'addedTime'      => 0,
            ],
            'incidents' => [],
            'widgets' => [
                'marcador'        => true,
                'formaciones'     => false,
                'otrosPartidos'   => false,
                'tablaPosiciones' => false,
                'ticker'          => false,
                'publicidad'      => false,
                'estadisticas'    => false,
                'alerta'          => false,
                'formacionPrevia' => false,
            ],
            'alerts' => [
                'active' => false,
                'type'   => null,
                'team'   => null,
                'player' => null,
                'minute' => null,
            ],
            'emergencyMode' => false,
            'ads' => [
                'active'          => false,
                'current'         => null,
                'sponsor'         => '',
                'logoSponsor'     => '',
                'autoEnabled'     => false,
                'autoIntervalMin' => 10,
                'nextAt'          => null,
            ],
            'stats' => [
                'local' => [
                    'posesion'    => 50,
                    'tiros'       => 0,
                    'tirosPuerta' => 0,
                    'corners'     => 0,
                    'faltas'      => 0,
                    'offsides'    => 0,
                ],
                'visitante' => [
                    'posesion'    => 50,
                    'tiros'       => 0,
                    'tirosPuerta' => 0,
                    'corners'     => 0,
                    'faltas'      => 0,
                    'offsides'    => 0,
                ],
                'jugadorDestacado' => [
                    'team'   => null,
                    'nombre' => '',
                    'numero' => null,
                    'stat'   => '',
                ],
            ],
            'ticker'    => ['items' => []],
            'dt'        => ['local' => '', 'visitante' => ''],
            'history'   => [],
            'local' => [
                'nombre'      => '',
                'logo'        => '',
                'color'       => '#019604',
                'colorNumero' => '#ffffff',
                'formacion'   => '4-3-3',
                'goles'       => 0,
                'jugadores'   => [],
                'suplentes'   => [],
            ],
            'visitante' => [
                'nombre'      => '',
                'logo'        => '',
                'color'       => '#FFFFFF',
                'colorNumero' => '#000000',
                'formacion'   => '4-4-2',
                'goles'       => 0,
                'jugadores'   => [],
                'suplentes'   => [],
            ],
            'resultados' => [],
            'libre'      => '',
        ];
    }

    /**
     * Carga el estado desde disco. Si el archivo no existe o hay error,
     * devuelve el estado por defecto. Aplica guards para campos faltantes.
     */
    public static function load(): array
    {
        $path = self::getStatePath();
        $state = null;

        if (file_exists($path)) {
            $raw = @file_get_contents($path);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $state = $decoded;
                }
            }
        }

        if ($state === null) {
            return self::defaultState();
        }

        // Guards para campos que pueden faltar en versiones anteriores
        $defaults = self::defaultState();
        foreach ($defaults as $key => $defaultValue) {
            if (!array_key_exists($key, $state)) {
                $state[$key] = $defaultValue;
            }
        }
        // Guards de sub-objetos
        foreach (['match', 'timer', 'widgets', 'alerts', 'ads', 'stats', 'ticker', 'dt', 'local', 'visitante'] as $sub) {
            if (!is_array($state[$sub])) {
                $state[$sub] = $defaults[$sub];
            } else {
                foreach ($defaults[$sub] as $k => $v) {
                    if (!array_key_exists($k, $state[$sub])) {
                        $state[$sub][$k] = $v;
                    }
                }
            }
        }

        return $state;
    }

    /**
     * Guarda el estado en disco de forma atómica (escribe en tmp, luego renombra).
     */
    public static function save(array $state): void
    {
        $path = self::getStatePath();
        $tmp  = $path . '.tmp.' . getmypid();
        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($tmp, $json, LOCK_EX);
        rename($tmp, $path);
    }

    /**
     * Merge superficial: aplica $updates sobre $target ignorando keys con valor null
     * cuando el campo ya existe en target.
     */
    public static function patch(array &$target, array $updates): array
    {
        foreach ($updates as $key => $value) {
            if ($value !== null || !array_key_exists($key, $target)) {
                $target[$key] = $value;
            }
        }
        return $target;
    }

    /**
     * Agrega una entrada al historial (máximo 30 entradas).
     */
    public static function addHistory(array &$state, string $action, array $data = []): void
    {
        array_unshift($state['history'], [
            'action'    => $action,
            'data'      => $data,
            'timestamp' => round(microtime(true) * 1000),
        ]);
        if (count($state['history']) > 30) {
            $state['history'] = array_slice($state['history'], 0, 30);
        }
    }

    /**
     * Calcula el minuto actual del timer (equivalente a getTimerMinute() en Node.js).
     */
    public static function getTimerMinute(array $timer): int
    {
        if (empty($timer['running']) || empty($timer['startTimestamp'])) {
            return (int)($timer['baseMinute'] ?? 0);
        }
        $elapsed = (int)(round(microtime(true) * 1000) - $timer['startTimestamp']);
        return (int)($timer['baseMinute'] ?? 0) + (int)floor($elapsed / 60000);
    }
}
