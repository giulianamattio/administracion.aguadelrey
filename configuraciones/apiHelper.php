<?php
// ============================================================
//  configuraciones/apiHelper.php
//  Funciones auxiliares para endpoints de la API REST
// ============================================================

/**
 * Devuelve una respuesta JSON exitosa y termina la ejecución
 */
function apiOk(array $data = [], int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode(['ok' => true, ...$data]);
    exit;
}

/**
 * Devuelve una respuesta JSON de error y termina la ejecución
 */
function apiError(string $mensaje, int $httpCode = 400): void {
    http_response_code($httpCode);
    echo json_encode(['ok' => false, 'error' => $mensaje]);
    exit;
}

/**
 * Valida el token JWT del header y devuelve el payload
 * Si no es válido, responde 401 y termina
 */
function apiAutenticar(): array {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/jwt.php');
    $token = JWT::extraerDeHeader();
    if (!$token) {
        apiError('Token requerido', 401);
    }
    try {
        return JWT::validar($token);
    } catch (Exception $e) {
        apiError($e->getMessage(), 401);
    }
}

/**
 * Configura los headers necesarios para una API REST
 * Permite CORS para que Android pueda hacer requests
 */
function apiHeaders(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Authorization, Content-Type');

    // Preflight OPTIONS — Android lo manda antes de cada request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}
