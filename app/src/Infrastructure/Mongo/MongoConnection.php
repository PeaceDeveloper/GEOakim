<?php

declare(strict_types=1);

namespace App\Infrastructure\Mongo;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database;
use RuntimeException;

final class MongoConnection
{
    private static ?self $instance = null;
    private Client $client;
    private Database $database;

    private function __construct()
    {
        if (!extension_loaded('mongodb')) {
            throw new RuntimeException('MongoDB extension is required');
        }

        $host = $_ENV['MONGO_HOST'] ?? 'mongodb';
        $port = $_ENV['MONGO_PORT'] ?? '27017';
        $database = $_ENV['MONGO_DATABASE'] ?? 'geoakim';
        $username = $_ENV['MONGO_USERNAME'] ?? 'geoakim_user';
        $password = $_ENV['MONGO_PASSWORD'] ?? 'secure_password_123';

        $uri = sprintf('mongodb://%s:%s@%s:%s/%s', $username, $password, $host, $port, $database);
        $this->client = new Client($uri, [], [
            'typeMap' => ['root' => 'array', 'document' => 'array', 'array' => 'array'],
        ]);
        $this->database = $this->client->selectDatabase($database);
        $this->database->command(['ping' => 1]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function collection(string $name): Collection
    {
        return $this->database->selectCollection($name);
    }
}
