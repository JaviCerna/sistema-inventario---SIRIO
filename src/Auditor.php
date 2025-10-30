<?php
// src/Auditor.php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

class Auditor {
    private static ?PDO $db = null;

    private static function getDb(): PDO {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }

    /**
     * Registra una acción de usuario en la tabla de auditoría.
     * @param int|null $userId El ID del usuario que realizó la acción.
     * @param string $tabla El nombre de la tabla afectada (ej: 'categorias').
     * @param int $registroId El ID del registro afectado.
     * @param string $accion El tipo de acción (CREATE, UPDATE, DELETE, LOGIN, LOGOUT).
     * @param array $detalle_cambios Array asociativo de detalles (opcional).
     * @param string|null $ip_origen Dirección IP del cliente.
     * @return bool
     */
    public static function log(
        ?int $userId,
        string $tabla,
        int $registroId,
        string $accion,
        array $detalle_cambios = [],
        ?string $ip_origen = null
    ): bool {
        $db = self::getDb();
        $ip = $ip_origen ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $json_cambios = $detalle_cambios ? json_encode($detalle_cambios, JSON_UNESCAPED_UNICODE) : null;

        $st = $db->prepare(
            "INSERT INTO log_auditoria (id_usuario, tabla_afectada, registro_id, accion, detalle_cambios, ip_origen)
             VALUES (:user_id, :tabla, :registro_id, :accion, :detalle, :ip)"
        );
        
        return $st->execute([
            ':user_id' => $userId,
            ':tabla' => $tabla,
            ':registro_id' => $registroId,
            ':accion' => $accion,
            ':detalle' => $json_cambios,
            ':ip' => $ip,
        ]);
    }
}