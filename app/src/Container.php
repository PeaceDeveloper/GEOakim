<?php

declare(strict_types=1);

namespace App;

use App\Application\AuthService;
use App\Application\CollectionService;
use App\Application\InviteLinkService;
use App\Domain\GeoData\GeoDataRepository;
use App\Domain\GeoData\LocationResolver;
use App\Domain\InviteLink\InviteLinkRepository;
use App\Infrastructure\Auth\LdapAuthService;
use App\Infrastructure\Mongo\MongoConnection;
use App\Infrastructure\Security\RateLimiter;

final class Container
{
    private static ?self $instance = null;
    private MongoConnection $mongo;

    private function __construct()
    {
        $this->mongo = MongoConnection::getInstance();
    }

    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function authService(): AuthService
    {
        return new AuthService(new LdapAuthService());
    }

    public function inviteLinkService(): InviteLinkService
    {
        return new InviteLinkService(new InviteLinkRepository($this->mongo));
    }

    public function collectionService(): CollectionService
    {
        return new CollectionService(
            new GeoDataRepository($this->mongo),
            $this->inviteLinkService(),
            new LocationResolver(),
            new RateLimiter($this->mongo),
        );
    }

    public function geoDataRepository(): GeoDataRepository
    {
        return new GeoDataRepository($this->mongo);
    }
}
