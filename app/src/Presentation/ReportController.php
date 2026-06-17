<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Bootstrap;
use App\Container;

final class ReportController
{
    public function index(): void
    {
        $user = Container::get()->authService()->currentUser();
        $linkUid = $_GET['link_uid'] ?? '';
        $apiKey = Bootstrap::env('GOOGLE_MAPS_API_KEY', '');

        $data = Container::get()->geoDataRepository()->findAll(1000, $linkUid !== '' ? $linkUid : null);
        $links = Container::get()->inviteLinkService()->listForAdmin();

        Bootstrap::view('report/index', [
            'user' => $user,
            'entries' => $data,
            'links' => $links,
            'selected_link_uid' => $linkUid,
            'api_key' => $apiKey,
        ]);
    }
}
