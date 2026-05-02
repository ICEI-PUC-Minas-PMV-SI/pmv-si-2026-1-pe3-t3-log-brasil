<?php

namespace App\Services;

/**
 * Integração REST com api.openrouteservice.org (geocodificação e direções).
 * Documentação: https://openrouteservice.org/dev/#/api-docs/v2/directions
 */
final class OpenRouteService
{
    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (defined('CONF_ORS_API_KEY') ? CONF_ORS_API_KEY : '');
    }

    /** Brasil (ISO alpha-2) para filtro Pelias do ORS. */
    private const BOUNDARY_COUNTRY_BR = 'BR';

    /**
     * Dados mínimos para último degrau da cascata (cidade + UF).
     *
     * @param array<string, string> $d
     */
    public static function enderecoPossuiGeocodeCascadeMinimo(array $d): bool
    {
        $cidade = preg_replace('/\s+/', ' ', trim((string) ($d['cidade'] ?? '')));
        $ufRaw = preg_replace('/\s+/', ' ', trim((string) ($d['uf'] ?? '')));

        return $cidade !== '' && mb_strlen($ufRaw) >= 2;
    }

    /**
     * Cascata Brasil: tenta granularidade maior → menor até obter resultado.
     * Ordens pedidas pelo negócio: (1) nº + compl + rua + bairro + cidade + UF;
     * (2) rua + bairro + cidade + UF; (3) bairro + cidade + UF; (4) cidade + UF.
     * País sempre Brasil (+ boundary.country=BR na API).
     *
     * @param array<string, string> $d logradouro (rua), numero, complemento, bairro, cidade, uf
     *
     * @return array{0: float, 1: float}|null latitude, longitude
     */
    public function geocodeEnderecoBrasilCascade(array $d): ?array
    {
        if ($this->apiKey === '' || ! self::enderecoPossuiGeocodeCascadeMinimo($d)) {
            return null;
        }

        $norm = fn (string $s): string => preg_replace('/\s+/', ' ', trim($s));

        $rua = $norm((string) ($d['logradouro'] ?? ''));
        $num = $norm((string) ($d['numero'] ?? ''));
        $compl = $norm((string) ($d['complemento'] ?? ''));
        $bairro = $norm((string) ($d['bairro'] ?? ''));
        $cidade = $norm((string) ($d['cidade'] ?? ''));
        $uf = mb_strtoupper($norm((string) ($d['uf'] ?? '')), 'UTF-8');

        /** @var list<list<string>> */
        $niveis = [
            [$num, $compl, $rua, $bairro, $cidade, $uf],
            [$rua, $bairro, $cidade, $uf],
            [$bairro, $cidade, $uf],
            [$cidade, $uf],
        ];

        $visto = [];

        foreach ($niveis as $partes) {
            $texto = $this->concatenarEnderecoParaBrasil($partes);
            if ($texto === null || $texto === '') {
                continue;
            }
            if (isset($visto[$texto])) {
                continue;
            }
            $visto[$texto] = true;
            $g = $this->geocodePeliasForward($texto);
            if ($g !== null) {
                return $g;
            }
        }

        return null;
    }

    /**
     * Concatena apenas partes não vazias e acrescenta "Brasil" (sem duplicar).
     *
     * @param list<string> $partesOrdemUsuario
     */
    private function concatenarEnderecoParaBrasil(array $partesOrdemUsuario): ?string
    {
        $chunks = [];
        foreach ($partesOrdemUsuario as $p) {
            $t = preg_replace('/\s+/', ' ', trim($p));
            if ($t === '') {
                continue;
            }
            $chunks[] = $t;
        }
        if ($chunks === []) {
            return null;
        }

        return implode(', ', $chunks) . ', Brasil';
    }

    /** Texto livre (legado): acrescenta Brasil se faltar e restringe ao país BR. */
    public function geocodeStructuredText(string $text): ?array
    {
        $t = preg_replace('/\s+/', ' ', trim($text));
        if ($t === '') {
            return null;
        }
        if (! preg_match('/\b(brasil|brazil)\b/iu', $t)) {
            $t .= ', Brasil';
        }

        return $this->geocodePeliasForward($t);
    }

    /**
     * Geocódigo forward Pelias via ORS: primeiro hit [lat,lng] ou null.
     */
    private function geocodePeliasForward(string $text): ?array
    {
        if ($this->apiKey === '') {
            return null;
        }

        $query = http_build_query([
            'api_key' => $this->apiKey,
            'text' => $text,
            'boundary.country' => self::BOUNDARY_COUNTRY_BR,
            'size' => 1,
        ], '', '&', PHP_QUERY_RFC3986);

        $url = 'https://api.openrouteservice.org/geocode/search?' . $query;
        $res = $this->httpGetJson($url);
        if (($res['features'][0]['geometry']['coordinates'] ?? null) === null) {
            return null;
        }
        $c = $res['features'][0]['geometry']['coordinates'];

        return [(float) $c[1], (float) $c[0]];
    }

    /**
     * Distância cumulativa (metros) usando o mesmo perfil configurado para carro genérico.
     * Espera pontos já ordenados, inclui retorno ao ponto inicial se $closeLoop=true.
     *
     * @param list<array{0:float,1:float}> $coordinates lat,lng
     */
    public function directionsDistanceMeters(array $coordinates, bool $closeLoop): ?float
    {
        if ($this->apiKey === '' || count($coordinates) < 2) {
            return null;
        }
        $coords = [];
        foreach ($coordinates as [$lat, $lng]) {
            $coords[] = [$lng, $lat];
        }
        if ($closeLoop) {
            $coords[] = $coords[0];
        }

        $url = sprintf(
            'https://api.openrouteservice.org/v2/directions/%s',
            rawurlencode(defined('CONF_ORS_PROFILE') ? CONF_ORS_PROFILE : 'driving-car')
        );

        $payload = json_encode(['coordinates' => $coords], JSON_UNESCAPED_UNICODE);
        $res = $this->httpPostJson($url . '?api_key=' . rawurlencode($this->apiKey), $payload);
        return isset($res['routes'][0]['summary']['distance'])
            ? (float) $res['routes'][0]['summary']['distance']
            : null;
    }

    private function httpGetJson(string $url): array
    {
        $body = file_get_contents($url, false, stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 25,
                'header' => "Accept: application/json\r\nUser-Agent: LogBrasil/1.1\r\n",
            ],
        ]));
        if ($body === false) {
            return [];
        }
        $dec = json_decode($body, true);

        return is_array($dec) ? $dec : [];
    }

    private function httpPostJson(string $url, string $json): array
    {
        $opts = [
            'http' => [
                'method' => 'POST',
                'content' => $json,
                'timeout' => 45,
                'header' => "Content-Type: application/json\r\nAccept: application/json\r\nUser-Agent: LogBrasil/1.1\r\n",
            ],
        ];
        $body = file_get_contents($url, false, stream_context_create($opts));
        if ($body === false) {
            return [];
        }
        $dec = json_decode($body, true);

        return is_array($dec) ? $dec : [];
    }
}
