<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Helpers;
use App\Core\Router;

$routePath = isset($_GET['route']) ? (string) $_GET['route'] : '/';

if ($routePath === '' || $routePath[0] !== '/') {
    $routePath = '/' . $routePath;
}

if (! empty($_SESSION['user']['id'] ?? null) && ($_SESSION['user']['papel'] ?? '') === 'cliente') {
    $allowCliente = '#^/(acompanhar|logout|api/cliente/rastrear)$#u';
    if (! preg_match($allowCliente, $routePath)) {
        Helpers::redirect('/acompanhar');
        exit;
    }
}

$router = new Router();

$routes = [];

$routes[] = [['GET'], '^/login$', fn () => (new App\Controllers\AuthController())->loginForm()];
$routes[] = [['POST'], '^/login$', fn () => (new App\Controllers\AuthController())->loginAttempt()];
$routes[] = [['POST'], '^/logout$', fn () => (new App\Controllers\AuthController())->logout()];
$routes[] = [['GET'], '^/$', fn () => (new App\Controllers\DashboardController())->index()];
$routes[] = [['GET'], '^/inicio$', fn () => (new App\Controllers\DashboardController())->index()];

$routes[] = [['GET'], '^/acompanhar$', fn () => (new App\Controllers\ClientePortalController())->acompanhar()];
$routes[] = [['POST'], '^/api/cliente/rastrear$', fn () => (new App\Controllers\ClientePortalController())->apiRastrear()];

$routes[] = [['GET'], '^/usuarios$', fn () => (new App\Controllers\UsuariosController())->index()];
$routes[] = [['POST'], '^/api/usuarios$', fn () => (new App\Controllers\UsuariosController())->apiCriar()];

$routes[] = [['GET'], '^/monitoramento/divergencias$', fn () => (new App\Controllers\MonitoramentoController())->divergencias()];
$routes[] = [['POST'], '^/api/monitoramento/divergencia-revisao$', fn () => (new App\Controllers\MonitoramentoController())->apiRevisar()];

$routes[] = [['GET'], '^/motorista/login$', fn () => (new App\Controllers\MotoristaPortalController())->loginForm()];
$routes[] = [['POST'], '^/motorista/login$', fn () => (new App\Controllers\MotoristaPortalController())->loginAttempt()];
$routes[] = [['POST'], '^/motorista/logout$', fn () => (new App\Controllers\MotoristaPortalController())->logout()];
$routes[] = [['POST'], '^/motorista/foto$', fn () => (new App\Controllers\MotoristaPortalController())->enviarFotoPerfil()];
$routes[] = [['GET'], '^/motorista/viagens$', fn () => (new App\Controllers\MotoristaPortalController())->viagens()];
$routes[] = [['GET'], '^/motorista/viagem/(\\d+)/pedido/(\\d+)/entrega$', fn (string $a, string $b) => (new App\Controllers\MotoristaPortalController())->formEntrega($a, $b)];
$routes[] = [['GET'], '^/motorista/viagem/(\\d+)/pedido/(\\d+)$', fn (string $a, string $b) => (new App\Controllers\MotoristaPortalController())->parada($a, $b)];
$routes[] = [['GET'], '^/motorista/viagem/(\\d+)$', fn (string $id) => (new App\Controllers\MotoristaPortalController())->viagemDetalhe($id)];
$routes[] = [['GET'], '^/motorista$', fn () => (new App\Controllers\MotoristaPortalController())->home()];

$routes[] = [['POST'], '^/api/motorista/indo$', fn () => (new App\Controllers\MotoristaPortalController())->apiIndo()];
$routes[] = [['POST'], '^/api/motorista/divergencia$', fn () => (new App\Controllers\MotoristaPortalController())->apiDivergencia()];
$routes[] = [['POST'], '^/api/motorista/viagem-finalizar$', fn () => (new App\Controllers\MotoristaPortalController())->apiFinalizar()];
$routes[] = [['POST'], '^/api/motorista/concluir$', fn () => (new App\Controllers\MotoristaPortalController())->apiConcluir()];

$routes[] = [['GET'], '^/pedidos$', fn () => (new App\Controllers\PedidoController())->index()];
$routes[] = [['POST'], '^/api/pedidos$', fn () => (new App\Controllers\PedidoController())->apiCriar()];
$routes[] = [['POST'], '^/api/cliente/por-cpf$', fn () => (new App\Controllers\PedidoController())->apiClientePorCpf()];
$routes[] = [['POST'], '^/api/cep$', fn () => (new App\Controllers\PedidoController())->apiConsultaCep()];
$routes[] = [['POST'], '^/api/endereco-geocode$', fn () => (new App\Controllers\PedidoController())->apiGeocode()];
$routes[] = [['POST'], '^/api/pedidos/sugerir-rota$', fn () => (new App\Controllers\PedidoController())->apiSugerirRota()];
$routes[] = [['GET'], '^/api/pedido/(\\d+)/itens$', fn (string $id) => (new App\Controllers\PedidoController())->apiItens((int) $id)];
$routes[] = [['PUT', 'PATCH'], '^/api/pedido/(\\d+)$', fn (string $id) => (new App\Controllers\PedidoController())->apiAtualizar((int) $id)];
$routes[] = [['DELETE'], '^/api/pedido/(\\d+)$', fn (string $id) => (new App\Controllers\PedidoController())->apiExcluir((int) $id)];

$routes[] = [['GET'], '^/rotas$', fn () => (new App\Controllers\RotasController())->index()];
$routes[] = [['POST'], '^/api/rota$', fn () => (new App\Controllers\RotasController())->apiSalvar()];
$routes[] = [['PUT'], '^/api/rota/(\\d+)$', fn (string $id) => (new App\Controllers\RotasController())->apiSalvar((int) $id)];
$routes[] = [['DELETE'], '^/api/rota/(\\d+)$', fn (string $id) => (new App\Controllers\RotasController())->apiRemover((int) $id)];
$routes[] = [['POST'], '^/api/rota/(\\d+)/cidade$', fn (string $id) => (new App\Controllers\RotasController())->apiAddCidade((int) $id)];
$routes[] = [['DELETE'], '^/api/rota/cidade/(\\d+)$', fn (string $id) => (new App\Controllers\RotasController())->apiDelCidade((int) $id)];
$routes[] = [['POST'], '^/api/rota/(\\d+)/bairro$', fn (string $id) => (new App\Controllers\RotasController())->apiAddBairro((int) $id)];
$routes[] = [['DELETE'], '^/api/rota/bairro/(\\d+)$', fn (string $id) => (new App\Controllers\RotasController())->apiDelBairro((int) $id)];

$routes[] = [['GET'], '^/veiculos$', fn () => (new App\Controllers\VeiculosController())->index()];
$routes[] = [['POST'], '^/api/veiculos$', fn () => (new App\Controllers\VeiculosController())->apiSalvar()];
$routes[] = [['PUT'], '^/api/veiculos/(\\d+)$', fn (string $id) => (new App\Controllers\VeiculosController())->apiSalvar((int) $id)];
$routes[] = [['DELETE'], '^/api/veiculos/(\\d+)$', fn (string $id) => (new App\Controllers\VeiculosController())->apiRemover((int) $id)];

$routes[] = [['GET'], '^/motoristas$', fn () => (new App\Controllers\MotoristasController())->index()];
$routes[] = [['POST'], '^/api/motoristas$', fn () => (new App\Controllers\MotoristasController())->apiSalvar()];
$routes[] = [['PUT'], '^/api/motoristas/(\\d+)$', fn (string $id) => (new App\Controllers\MotoristasController())->apiSalvar((int) $id)];
$routes[] = [['DELETE'], '^/api/motoristas/(\\d+)$', fn (string $id) => (new App\Controllers\MotoristasController())->apiRemover((int) $id)];

$routes[] = [['GET'], '^/roteirizador$', fn () => (new App\Controllers\RoteirizadorController())->index()];
$routes[] = [['GET'], '^/api/roteirizador$', fn () => (new App\Controllers\RoteirizadorController())->apiResumo()];
$routes[] = [['GET'], '^/api/roteirizador/rota/(\\d+)$', fn (string $id) => (new App\Controllers\RoteirizadorController())->apiDetalhe((int) $id)];
$routes[] = [['POST'], '^/api/pedidos/alterar-rota$', fn () => (new App\Controllers\RoteirizadorController())->apiAlterarRota()];
$routes[] = [['POST'], '^/api/viagem/gerar$', fn () => (new App\Controllers\RoteirizadorController())->apiGerarViagem()];

$routes[] = [['GET'], '^/viagens/abertas$', fn () => (new App\Controllers\ViagensController())->abertas()];
$routes[] = [['GET'], '^/viagens/finalizadas$', fn () => (new App\Controllers\ViagensController())->finalizadas()];
$routes[] = [['GET'], '^/api/viagem/(\\d+)/pedidos$', fn (string $id) => (new App\Controllers\ViagensController())->apiPedidos((int) $id)];
$routes[] = [['POST'], '^/api/viagem/(\\d+)/finalizar$', fn (string $id) => (new App\Controllers\ViagensController())->apiFinalizar((int) $id)];
$routes[] = [['POST'], '^/api/viagem/(\\d+)/divergencia$', fn (string $id) => (new App\Controllers\ViagensController())->apiDivergencia((int) $id)];
$routes[] = [['GET'], '^/api/viagem/(\\d+)/divergencias$', fn (string $id) => (new App\Controllers\ViagensController())->apiListarDivergencias((int) $id)];

$routes[] = [['GET', 'POST'], '^/unidade$', fn () => (new App\Controllers\UnidadeController())->form()];

foreach ($routes as $r) {
    $router->add($r[0], $r[1], $r[2]);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$router->dispatch($method, $routePath);
