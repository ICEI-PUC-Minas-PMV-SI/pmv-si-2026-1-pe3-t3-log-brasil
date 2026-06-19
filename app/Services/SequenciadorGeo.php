<?php

namespace App\Services;

/**
 * Ordenação heurística próxima cidade (nearest neighbor a partir da unidade) para uso no mapa/OSR.
 *
 * @param array<int,array<string,mixed>> $pedidos
 * @param array{lat:float,lng:float} $origem
 * @return list<int> sequência dos IDs conforme entrada
 */
final class SequenciadorGeo
{
    public static function sequenciaNearestNeighbor(float $lat0, float $lng0, array $pedidos): array
    {
        /** @var list<int|null> */
        $ids = [];
        foreach ($pedidos as $row) {
            $ids[] = isset($row['id']) ? (int) $row['id'] : null;
        }
        $remain = [];
        foreach ($pedidos as $row) {
            $remain[(int) $row['id']] = $row;
        }

        /** @var list<int> $order */
        $order = [];
        $curLat = $lat0;
        $curLng = $lng0;

        while (count($remain) > 0) {
            $bestId = null;
            $bestD = INF;
            foreach ($remain as $id => $p) {
                $la = (float) $p['latitude'];
                $lo = (float) $p['longitude'];
                if ($la == 0.0 && $lo == 0.0) {
                    continue;
                }
                $d = self::haversine($curLat, $curLng, $la, $lo);
                if ($d < $bestD) {
                    $bestD = $d;
                    $bestId = $id;
                }
            }
            if ($bestId === null) {
                // Sem coordenadas válidas — usa ordem de inserção
                foreach (array_keys($remain) as $id) {
                    $order[] = (int) $id;
                    unset($remain[$id]);
                }
                break;
            }
            $order[] = $bestId;
            $nex = $remain[$bestId];
            unset($remain[$bestId]);
            $curLat = (float) $nex['latitude'];
            $curLng = (float) $nex['longitude'];
        }

        return $order;
    }

    /** Distância em metros entre dois pontos WGS84. */
    private static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth = 6371000;
        $φ1 = deg2rad($lat1);
        $φ2 = deg2rad($lat2);
        $Δφ = deg2rad($lat2 - $lat1);
        $Δλ = deg2rad($lon2 - $lon1);

        $a = sin($Δφ / 2) * sin($Δφ / 2)
            + cos($φ1) * cos($φ2) * sin($Δλ / 2) * sin($Δλ / 2);

        return 2 * $earth * atan2(sqrt($a), sqrt(max(1e-12, 1 - $a)));
    }
}
