<?php
// MongoDB connection helper
class MongoConnection {
    private static $instance = null;
    private $client;
    private $database;

    private function __construct() {
        $host = $_ENV['MONGO_HOST'] ?? 'mongodb';
        $port = $_ENV['MONGO_PORT'] ?? '27017';
        $database = $_ENV['MONGO_DATABASE'] ?? 'geoakim';
        $username = $_ENV['MONGO_USERNAME'] ?? 'geoakim_user';
        $password = $_ENV['MONGO_PASSWORD'] ?? 'secure_password_123';

        $uri = "mongodb://{$username}:{$password}@{$host}:{$port}/{$database}";
        
        try {
            $this->client = new MongoDB\Client($uri);
            $this->database = $this->client->selectDatabase($database);
        } catch (Exception $e) {
            error_log("MongoDB connection failed: " . $e->getMessage());
            throw $e;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getDatabase() {
        return $this->database;
    }

    public function getCollection($name) {
        return $this->database->selectCollection($name);
    }
}

// Helper function to log to MongoDB
function logToMongo($data, $collection_name = 'logs') {
    try {
        $mongo = MongoConnection::getInstance();
        $collection = $mongo->getCollection($collection_name);
        
        $logEntry = [
            'timestamp' => new MongoDB\BSON\UTCDateTime(),
            'data' => $data,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $collection->insertOne($logEntry);
    } catch (Exception $e) {
        error_log("Failed to log to MongoDB: " . $e->getMessage());
        // Fallback to file logging
        file_put_contents('/var/www/html/logs/fallback.log', 
            "[" . date('Y-m-d H:i:s') . "] " . json_encode($data) . "\n", 
            FILE_APPEND);
    }
}
?>