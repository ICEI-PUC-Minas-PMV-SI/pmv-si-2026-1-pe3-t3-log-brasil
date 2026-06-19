<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Consulta pública ao CEP (dados compatíveis com base dos Correios agregados em APIs públicas).
 * Ordem: [BrasilAPI](https://brasilapi.com.br/) e, em falha, [ViaCEP](https://viacep.com.br/).
 */
final class ConsultaCepService
{
    /**
     * @return array{logradouro:string,bairro:string,cidade:string,uf:string,cep:string}|null
     */
    public function buscar(string $cepDigits): ?array
    {
        $digits = preg_replace('/\D/', '', $cepDigits);
        if (strlen($digits) !== 8) {
            return null;
        }

        $viaBrasil = self::jsonGet('https://brasilapi.com.br/api/cep/v1/' . $digits);
        if (is_array($viaBrasil) && ! empty($viaBrasil['city']) && ! empty($viaBrasil['state'])) {
            return [
                'logradouro' => trim((string) ($viaBrasil['street'] ?? '')),
                'bairro' => trim((string) ($viaBrasil['neighborhood'] ?? '')),
                'cidade' => trim((string) ($viaBrasil['city'] ?? '')),
                'uf' => mb_strtoupper(trim((string) ($viaBrasil['state'] ?? '')), 'UTF-8'),
                'cep' => preg_replace('/\D/', '', (string) ($viaBrasil['cep'])),
            ];
        }

        $viaViaCep = self::jsonGet('https://viacep.com.br/ws/' . $digits . '/json/');
        if (! is_array($viaViaCep) || (! empty($viaViaCep['erro'])) || empty($viaViaCep['cep'])) {
            return null;
        }

        return [
            'logradouro' => trim((string) ($viaViaCep['logradouro'] ?? '')),
            'bairro' => trim((string) ($viaViaCep['bairro'] ?? '')),
            'cidade' => trim((string) ($viaViaCep['localidade'] ?? '')),
            'uf' => mb_strtoupper(trim((string) ($viaViaCep['uf'] ?? '')), 'UTF-8'),
            'cep' => preg_replace('/\D/', '', (string) ($viaViaCep['cep'])),
        ];
    }

    private static function jsonGet(string $url): ?array
    {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 12,
                'header' => "Accept: application/json\r\nUser-Agent: LogBrasil/2.0 (operacao)\r\n",
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false || $raw === '') {
            return null;
        }
        $d = json_decode($raw, true);

        return is_array($d) ? $d : null;
    }
}
