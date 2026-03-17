<?php
// ============================================================
//  configuraciones/sessionCliente.php
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['cliente_id'])) {
    header('Location: /clientes/login?error=Debés+iniciar+sesión+para+acceder.');
    exit;
}