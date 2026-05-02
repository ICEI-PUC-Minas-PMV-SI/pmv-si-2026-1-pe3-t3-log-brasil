<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helpers;
use App\Core\View;
use App\Models\UnidadePadrao;
use App\Services\OpenRouteService;

/**
 * Cadastro da unidade padrão (origem das viagens).
 */
final class UnidadeController extends Controller
{
    public function form(): void
    {
        $this->requireLogin();
        UnidadePadrao::ensureBootstrap();
        $row = UnidadePadrao::get();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->denyUnlessCsrf($_POST['_csrf'] ?? null);
            $lat = (float) str_replace(',', '.', (string) ($_POST['latitude'] ?? '0'));
            $lng = (float) str_replace(',', '.', (string) ($_POST['longitude'] ?? '0'));

            if ($lat == 0.0 && $lng == 0.0) {
                $ors = new OpenRouteService();
                $partesGeo = [
                    'logradouro' => (string) ($_POST['logradouro'] ?? ''),
                    'numero' => (string) ($_POST['numero'] ?? ''),
                    'complemento' => (string) ($_POST['complemento'] ?? ''),
                    'bairro' => (string) ($_POST['bairro'] ?? ''),
                    'cidade' => (string) ($_POST['cidade'] ?? ''),
                    'uf' => (string) ($_POST['uf'] ?? ''),
                ];
                if (OpenRouteService::enderecoPossuiGeocodeCascadeMinimo($partesGeo)) {
                    $geo = $ors->geocodeEnderecoBrasilCascade($partesGeo);
                    if ($geo !== null) {
                        [$lat, $lng] = $geo;
                    }
                }
            }

            UnidadePadrao::updateCoord((int) $_SESSION['user']['id'], [
                'nome' => trim((string) ($_POST['nome'] ?? 'Matriz')),
                'logradouro' => trim((string) ($_POST['logradouro'] ?? '')),
                'numero' => trim((string) ($_POST['numero'] ?? 'S/N')),
                'complemento' => trim((string) ($_POST['complemento'] ?? '')),
                'bairro' => trim((string) ($_POST['bairro'] ?? '')),
                'cidade' => trim((string) ($_POST['cidade'] ?? '')),
                'uf' => mb_strtoupper((string) ($_POST['uf'] ?? ''), 'UTF-8'),
                'cep' => preg_replace('/\D/', '', (string) ($_POST['cep'] ?? '')),
                'latitude' => $lat,
                'longitude' => $lng,
                'observacao' => trim((string) ($_POST['observacao'] ?? '')),
            ]);
            $_SESSION['flash_ok'] = 'Unidade atualizada.';
            Helpers::redirect('/unidade');
            return;
        }

        View::render('config/unidade', [
            'nav' => 'unidade',
            'title' => 'Unidade padrão',
            'u' => $row,
        ]);
    }
}
