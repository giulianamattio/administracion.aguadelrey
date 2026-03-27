<?php
// ============================================================
//  configuraciones/jwt.php
//  Implementación manual de JWT (HS256) sin dependencias
// ============================================================

class JWT {

    // Clave secreta para firmar los tokens
    // *** CAMBIÁ ESTE VALOR EN PRODUCCIÓN ***
    private static string $secretKey = 'AguaDelRey_Secret_2026_UTN';

    // Duración del token en segundos (24 horas)
    private static int $expiracion = 86400;

    /**
     * Genera un token JWT firmado con HS256
     */
    public static function generar(array $payload): string {
        $header = self::base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]));

        $payload['iat'] = time();
        $payload['exp'] = time() + self::$expiracion;

        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $firma = hash_hmac(
            'sha256',
            "$header.$payloadEncoded",
            self::$secretKey,
            true
        );

        return "$header.$payloadEncoded." . self::base64UrlEncode($firma);
    }

    /**
     * Valida un token JWT y devuelve el payload si es válido
     * Lanza excepción si es inválido o expirado
     */
    public static function validar(string $token): array {
        $partes = explode('.', $token);
        if (count($partes) !== 3) {
            throw new Exception('Token malformado');
        }

        [$header, $payloadEncoded, $firmaRecibida] = $partes;

        // Verificar firma
        $firmaEsperada = self::base64UrlEncode(
            hash_hmac('sha256', "$header.$payloadEncoded", self::$secretKey, true)
        );

        if (!hash_equals($firmaEsperada, $firmaRecibida)) {
            throw new Exception('Firma inválida');
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // Verificar expiración
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            throw new Exception('Token expirado');
        }

        return $payload;
    }

    /**
     * Extrae el token del header Authorization: Bearer <token>
     */
    public static function extraerDeHeader(): ?string {
        $headers = getallheaders();
        $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
