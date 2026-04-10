<?php
// ============================================================
//  CONTROLADOR/clientes/solicitarResetPassword.php
// ============================================================
require_once($_SERVER['DOCUMENT_ROOT'] . '/configuraciones/conexionBD.php');

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /clientes/login?reset_ok=1');
    exit;
}

// Buscar cliente por email
$stmt = $conexionbd->prepare("
    SELECT id_cliente, nombre FROM cliente
    WHERE email = :email AND fecha_baja IS NULL
");
$stmt->execute([':email' => $email]);
$cliente = $stmt->fetch();

// Siempre redirigir igual para no revelar si el email existe
if (!$cliente) {
    header('Location: /clientes/login?reset_ok=1');
    exit;
}

// Generar token único
$token  = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Guardar token en la base
$stmtToken = $conexionbd->prepare("
    INSERT INTO password_reset_tokens (id_cliente, token, expira_en)
    VALUES (:id_cliente, :token, :expira_en)
");
$stmtToken->execute([
    ':id_cliente' => $cliente['id_cliente'],
    ':token'      => $token,
    ':expira_en'  => $expira
]);

// Armar el link
$link = 'https://' . $_SERVER['HTTP_HOST'] . '/clientes/resetPassword?token=' . $token;

// HTML del correo
$htmlBody = "
    <div style='font-family:Arial,sans-serif;max-width:480px;margin:0 auto;'>
      <h2 style='color:#007bff;'>Agua del Rey</h2>
      <p>Hola <strong>{$cliente['nombre']}</strong>,</p>
      <p>Recibimos una solicitud para restablecer tu contraseña.</p>
      <p>Hacé clic en el botón para continuar:</p>
      <p style='text-align:center;margin:30px 0;'>
        <a href='{$link}'
           style='background:#007bff;color:#fff;padding:12px 28px;
                  border-radius:6px;text-decoration:none;font-weight:bold;'>
          Restablecer contraseña
        </a>
      </p>
      <p style='color:#888;font-size:13px;'>
        Este link es válido por <strong>1 hora</strong>.<br>
        Si no solicitaste esto, podés ignorar este email.
      </p>
      <hr style='border:none;border-top:1px solid #eee;'>
      <p style='color:#aaa;font-size:12px;'>Agua del Rey — Portal de Clientes</p>
    </div>
";

// Enviar email con Resend API via cURL
$payload = json_encode([
    'from'    => 'Agua del Rey <admin@aguadelrey.com>',
    'to'      => [$email],
    'subject' => 'Restablecer contraseña - Agua del Rey',
    'html'    => $htmlBody,
]);

$ch = curl_init('https://api.resend.com/emails');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . getenv('RESEND_API_KEY'),
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response   = curl_exec($ch);
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);

if ($curlError || $httpStatus >= 400) {
    error_log("Error enviando email reset (HTTP $httpStatus): $curlError $response");
}

header('Location: /clientes/login?reset_ok=1');
exit;

?>