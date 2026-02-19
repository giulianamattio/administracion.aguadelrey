<?php
// ============================================================
//  configuraciones/session.php
//  Sesiones almacenadas en PostgreSQL — compartidas entre
//  todos los procesos del contenedor en Render.
// ============================================================

if (session_status() !== PHP_SESSION_NONE) {
    return;
}

function _session_pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "pgsql:host=" . getenv('DB_HOST') .
               ";port=" . (getenv('DB_PORT') ?: '5432') .
               ";dbname=" . getenv('DB_NAME') .
               ";sslmode=require";
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("CREATE TABLE IF NOT EXISTS php_sessions (
            id          VARCHAR(128) PRIMARY KEY,
            data        TEXT         NOT NULL DEFAULT '',
            last_access TIMESTAMPTZ  NOT NULL DEFAULT NOW()
        )");
    }
    return $pdo;
}

function _session_open($path, $name): bool { return true; }
function _session_close(): bool { return true; }

function _session_read(string $id): string {
    try {
        $stmt = _session_pdo()->prepare("SELECT data FROM php_sessions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['data'] : '';
    } catch (Exception $e) {
        error_log("SESSION READ ERROR: " . $e->getMessage());
        return '';
    }
}

function _session_write(string $id, string $data): bool {
    try {
        $stmt = _session_pdo()->prepare("
            INSERT INTO php_sessions (id, data, last_access)
            VALUES (:id, :data, NOW())
            ON CONFLICT (id) DO UPDATE
            SET data = :data, last_access = NOW()
        ");
        $stmt->execute([':id' => $id, ':data' => $data]);
        return true;
    } catch (Exception $e) {
        error_log("SESSION WRITE ERROR: " . $e->getMessage());
        return false;
    }
}

function _session_destroy(string $id): bool {
    try {
        $stmt = _session_pdo()->prepare("DELETE FROM php_sessions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    } catch (Exception $e) { return false; }
}

function _session_gc(int $maxlifetime): int|false {
    try {
        $stmt = _session_pdo()->prepare("
            DELETE FROM php_sessions
            WHERE last_access < NOW() - INTERVAL '1 second' * :max
        ");
        $stmt->execute([':max' => $maxlifetime]);
        return $stmt->rowCount();
    } catch (Exception $e) { return false; }
}

session_set_save_handler(
    '_session_open', '_session_close', '_session_read',
    '_session_write', '_session_destroy', '_session_gc'
);

session_start();
