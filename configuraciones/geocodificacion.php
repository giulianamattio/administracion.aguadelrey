<?php
// ============================================================
//  configuraciones/geocodificacion.php
//  Servicio de geocodificación usando Nominatim (OpenStreetMap)
//  Gratis, sin API key. Límite: 1 req/segundo.
// ============================================================

class Geocodificacion {

    /**
     * Obtiene lat/lng de una dirección en San Francisco, Córdoba
     * usando la API gratuita de Nominatim (OpenStreetMap).
     *
     * @param string $domicilio  Ej: "Bv. Buenos Aires 528"
     * @param string $ciudad     Por defecto "San Francisco"
     * @param string $provincia  Por defecto "Córdoba"
     * @return array|null  ['lat' => float, 'lng' => float] o null si no encontró
     */
    public static function geocodificar(
        string $domicilio,
        string $ciudad   = 'San Francisco',
        string $provincia= 'Córdoba'
    ): ?array {

        // Construir query para Nominatim
        $query = urlencode("$domicilio, $ciudad, $provincia, Argentina");
        $url   = "https://nominatim.openstreetmap.org/search"
               . "?q=$query"
               . "&format=json"
               . "&limit=1"
               . "&countrycodes=ar";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => 'AguaDelReyMVP/1.0 (proyecto-academico-utn@example.com)',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($error)) return null;

        $data = json_decode($response, true);
        if (empty($data)) return null;

        return [
            'lat' => (float) $data[0]['lat'],
            'lng' => (float) $data[0]['lon'],
        ];
    }

    /**
     * Calcula distancia en km entre dos puntos usando fórmula Haversine.
     * Más precisa que distancia euclidiana para coordenadas geográficas.
     */
    public static function distanciaKm(
        float $lat1, float $lng1,
        float $lat2, float $lng2
    ): float {
        $radioTierra = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2) * sin($dLat/2)
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLng/2) * sin($dLng/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $radioTierra * $c;
    }

    /**
     * Algoritmo Nearest Neighbor (vecino más cercano).
     * Dado un punto de partida y N destinos, devuelve el orden
     * que minimiza la distancia total recorrida.
     *
     * @param array $origen   ['lat' => float, 'lng' => float]
     * @param array $destinos Array de ['id' => mixed, 'lat' => float, 'lng' => float, ...]
     * @return array  Los mismos destinos ordenados óptimamente, con 'distancia_km' agregada
     */
    public static function rutaOptima(array $origen, array $destinos): array {
        $pendientes = $destinos;
        $ordenados  = [];
        $actual     = $origen;

        while (!empty($pendientes)) {
            $minDist  = PHP_FLOAT_MAX;
            $minIndex = 0;

            foreach ($pendientes as $i => $destino) {
                $dist = self::distanciaKm(
                    $actual['lat'], $actual['lng'],
                    $destino['lat'], $destino['lng']
                );
                if ($dist < $minDist) {
                    $minDist  = $dist;
                    $minIndex = $i;
                }
            }

            $siguiente = $pendientes[$minIndex];
            $siguiente['distancia_km'] = round($minDist, 2);
            $ordenados[]  = $siguiente;
            $actual       = $siguiente;
            unset($pendientes[$minIndex]);
            $pendientes   = array_values($pendientes); // reindexar
        }

        return $ordenados;
    }
}

