<?php

class MongoConnection {
    private static $instance = null;
    private $client;
    private $database;
    
    private function __construct() {
        try {
            $mongoHost = $_ENV['MONGO_HOST'] ?? 'localhost';
            $mongoPort = $_ENV['MONGO_PORT'] ?? '27017';
            $mongoUsername = $_ENV['MONGO_USERNAME'] ?? 'admin';
            $mongoPassword = $_ENV['MONGO_PASSWORD'] ?? 'adminpassword';
            $mongoDatabase = $_ENV['MONGO_DATABASE'] ?? 'geoakim';
            
            $uri = "mongodb://{$mongoUsername}:{$mongoPassword}@{$mongoHost}:{$mongoPort}";
            
            $this->client = new MongoDB\Driver\Manager($uri);
            $this->database = $mongoDatabase;
        } catch (Exception $e) {
            error_log("MongoDB connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function insertEntry($data) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->insert($data);
            
            $result = $this->client->executeBulkWrite($this->database . '.entries', $bulk);
            return $result->getInsertedCount() > 0;
        } catch (Exception $e) {
            error_log("MongoDB insert error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAllEntries() {
        try {
            $query = new MongoDB\Driver\Query([], ['sort' => ['timestamp' => -1]]);
            $cursor = $this->client->executeQuery($this->database . '.entries', $query);
            
            $entries = [];
            foreach ($cursor as $document) {
                $entries[] = (array) $document;
            }
            
            return $entries;
        } catch (Exception $e) {
            error_log("MongoDB query error: " . $e->getMessage());
            return [];
        }
    }
    
    public function isConnected() {
        try {
            $query = new MongoDB\Driver\Query(['ping' => 1]);
            $this->client->executeQuery('admin.$cmd', $query);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}