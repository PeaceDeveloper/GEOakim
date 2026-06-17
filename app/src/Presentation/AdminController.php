<?php

declare(strict_types=1);

namespace App\Presentation;

use App\Bootstrap;
use App\Container;
use App\Support\Csrf;
use RuntimeException;

final class AdminController
{
    public function showLogin(): void
    {
        if (!empty($_SESSION['user_id'])) {
            Bootstrap::redirect('/admin');
        }

        Bootstrap::view('admin/login', [
            'error' => $_GET['error'] ?? null,
            'redirect' => $_GET['redirect'] ?? '/admin',
        ]);
    }

    public function login(): void
    {
        if (!Csrf::validateRequest()) {
            Bootstrap::redirect('/admin/login?error=csrf');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? '/admin';

        try {
            Container::get()->authService()->login($username, $password);
            Bootstrap::redirect($redirect ?: '/admin');
        } catch (RuntimeException) {
            Bootstrap::redirect('/admin/login?error=1&redirect=' . urlencode($redirect));
        }
    }

    public function logout(): void
    {
        if (!Csrf::validateRequest()) {
            Bootstrap::redirect('/admin');
        }
        Container::get()->authService()->logout();
        Bootstrap::redirect('/admin/login');
    }

    public function dashboard(): void
    {
        $user = Container::get()->authService()->currentUser();
        $links = Container::get()->inviteLinkService()->listForAdmin();

        Bootstrap::view('admin/dashboard', [
            'user' => $user,
            'links' => $links,
            'csrf' => Csrf::token(),
        ]);
    }
}
