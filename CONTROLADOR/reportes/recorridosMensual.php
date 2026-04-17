<?php
// api/recorridos_mensual.php
require($_SERVER["DOCUMENT_ROOT"].'/configuraciones/inicializacion.php');

try {
  $pdo = new PDO("pgsql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME", $DB_USER, $DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
  ]);

  // Gasto mensual
  $sql1 = "SELECT DATE_TRUNC('month', fecha_planificada)::date AS mes,
                  SUM(km_recorridos) AS km_totales,
                  SUM(costo_combustible) AS gasto_total
           FROM v_recorridos_costo
           GROUP BY mes
           ORDER BY mes DESC";
  $stmt1 = $pdo->query($sql1);
  $gastoMensual = $stmt1->fetchAll();

  // Evolución precio/variación
  $sql2 = "SELECT DATE_TRUNC('month', fecha_planificada)::date AS mes,
                  ROUND(AVG(precio_litro)::numeric,2) AS precio_prom,
                  SUM(km_recorridos) AS km_totales,
                  SUM(costo_combustible) AS gasto_total,
                  ROUND(
                    SUM(costo_combustible)
                    / NULLIF(LAG(SUM(costo_combustible)) OVER (ORDER BY DATE_TRUNC('month', fecha_planificada)),0)
                    * 100 - 100, 1
                  ) AS variacion_pct
           FROM v_recorridos_costo
           GROUP BY mes
           ORDER BY mes";
  $stmt2 = $pdo->query($sql2);
  $evolucion = $stmt2->fetchAll();

  echo json_encode(['gastoMensual'=>$gastoMensual, 'evolucion'=>$evolucion]);
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['error'=>$e->getMessage()]);
}
?>