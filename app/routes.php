<?php

declare(strict_types=1);

use App\Http\Router;
use App\Bootstrap;
use App\Presentation\AdminController;
use App\Presentation\Api\AdminLinksController;
use App\Presentation\Api\CollectController;
use App\Presentation\JoinController;
use App\Presentation\ReportController;

$router = new Router();

$admin = new AdminController();
$linksApi = new AdminLinksController();
$collect = new CollectController();
$join = new JoinController();
$report = new ReportController();

$router->get('/admin/login', [$admin, 'showLogin']);
$router->post('/admin/login', [$admin, 'login']);
$router->post('/admin/logout', [$admin, 'logout'], true);
$router->get('/admin', [$admin, 'dashboard'], true);

$router->get('/api/admin/links', [$linksApi, 'index'], true);
$router->post('/api/admin/links', [$linksApi, 'store'], true);
$router->patch('/api/admin/links/{uid}', [$linksApi, 'update'], true);
$router->delete('/api/admin/links/{uid}', [$linksApi, 'revoke'], true);

$router->get('/relatorio', [$report, 'index'], true);
$router->post('/api/collect', [$collect, 'store']);

$router->get('/', static function (): void {
    http_response_code(404);
    Bootstrap::view('errors/not_found');
});

$router->get('/{uid}', [$join, 'show']);

return $router;
