<?php
// ============================================================
//  configuraciones/sessionCliente.php
//  Verifica que el cliente esté logueado.
//  Incluir al inicio de cada página del portal de clientes.
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['cliente_id'])) {
    header('Location: /clientes/login?error=Debés+iniciar+sesión+para+acceder.');
    exit;
}
