<?php
// ============================================================
//  VISTA/clientes/portal/layout/head.php
//  Head HTML reutilizable para todo el portal de clientes
// ============================================================
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/dist/css/adminlte.min.css">
<link rel="stylesheet" href="/plugins/fontawesome-free/css/all.min.css">
<style>
  body {
    background-color: #f0f2f5;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
  }
  .portal-content {
    flex: 1;
    padding: 40px 0;
  }
  /* Tarjetas del home */
  .card-opcion {
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
    min-height: 140px;
    text-decoration: none;
  }
  .card-opcion:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.18) !important;
    text-decoration: none;
  }
  .card-opcion .card-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }
  .card-opcion .card-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: #fff;
  }
  .card-opcion .card-text {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.85);
  }
  .card-opcion .card-icon {
    font-size: 2.5rem;
    color: rgba(255,255,255,0.3);
    text-align: right;
  }
  .bg-verde    { background-color: #28a745; }
  .bg-celeste  { background-color: #17a2b8; }
  .bg-amarillo { background-color: #ffc107; }
  .bg-rojo     { background-color: #dc3545; }
  .navbar-brand img { border-radius: 4px; }
</style>
