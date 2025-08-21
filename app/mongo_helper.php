<?php
// MongoDB connection helper with fallback to file storage
class MongoConnection {
    private static $instance = null;
    private $client = null;
    private $database = null;
    private $isAvailable = false;

    private function __construct() {
        // Check if MongoDB extension is available
        if (!extension_loaded('mongodb')) {
            error_log("MongoDB extension not loaded, using file storage fallback");
            return;
        }

        $host = $_ENV['MONGO_HOST'] ?? 'mongodb';
        $port = $_ENV['MONGO_PORT'] ?? '27017';
        $database = $_ENV['MONGO_DATABASE'] ?? 'geoakim';
        $username = $_ENV['MONGO_USERNAME'] ?? 'geoakim_user';
        $password = $_ENV['MONGO_PASSWORD'] ?? 'secure_password_123';

        $uri = "mongodb://{$username}:{$password}@{$host}:{$port}/{$database}";
        
        try {
            $this->client = new MongoDB\Client($uri);
            $this->database = $this->client->selectDatabase($database);
            // Test connection
            $this->database->command(['ping' => 1]);
            $this->isAvailable = true;
        } catch (Exception $e) {
            error_log("MongoDB connection failed: " . $e->getMessage());
            error_log("Falling back to file storage");
            $this->isAvailable = false;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function isAvailable() {
        return $this->isAvailable;
    }

    public function getDatabase() {
        return $this->database;
    }

    public function getCollection($name) {
        if (!$this->isAvailable) {
            return null;
        }
        return $this->database->selectCollection($name);
    }
}

// Helper function to save data with automatic fallback
function saveGeoData($data) {
    try {
        $mongo = MongoConnection::getInstance();
        
        if ($mongo->isAvailable()) {
            $collection = $mongo->getCollection('geo_data');
            if (extension_loaded('mongodb')) {
                $data['created_at'] = new MongoDB\BSON\UTCDateTime();
            }
            $result = $collection->insertOne($data);
            return ['status' => 'success', 'id' => (string)$result->getInsertedId(), 'storage' => 'mongodb'];
        }
    } catch (Exception $e) {
        error_log("MongoDB save failed: " . $e->getMessage());
    }

    // Fallback to file storage
    return saveToFile($data);
}

function saveToFile($data) {
    try {
        // Save to JSON file
        $data_file = '/var/www/html/logs/data.json';
        $existing = [];
        
        if (file_exists($data_file)) {
            $existing = json_decode(file_get_contents($data_file), true) ?? [];
        }
        
        $existing[] = $data;
        file_put_contents($data_file, json_encode($existing, JSON_PRETTY_PRINT));
        
        // Save to log file
        $log_line = "[{$data['timestamp']}] "
          . "IP: {$data['ip']} | "
          . "UA: {$data['user_agent']} | "
          . "GPU: {$data['gpu_vendor']} / {$data['gpu_renderer']} | "
          . "Res: {$data['screen']} | "
          . "Plataforma: {$data['platform']} | "
          . "Idioma: {$data['lang']} | "
          . "Fuso: {$data['timezone']}";

        if ($data['geo']) {
          $log_line .= " | Geo: ({$data['latitude']}, {$data['longitude']}) ±{$data['accuracy']}m";
        } else {
          $log_line .= " | Geo: NÃO COLETADO";
        }

        $log_line .= "\n";
        file_put_contents("/var/www/html/logs/app.log", $log_line, FILE_APPEND);
        
        return ['status' => 'success', 'id' => uniqid(), 'storage' => 'file'];
    } catch (Exception $e) {
        error_log("File save failed: " . $e->getMessage());
        return ['status' => 'error', 'message' => 'Storage failed'];
    }
}

// Helper function to load data with automatic fallback
function loadGeoData($limit = 1000) {
    try {
        $mongo = MongoConnection::getInstance();
        
        if ($mongo->isAvailable()) {
            $collection = $mongo->getCollection('geo_data');
            
            $cursor = $collection->find([], [
                'sort' => ['created_at' => -1],
                'limit' => $limit
            ]);
            
            $data = [];
            foreach ($cursor as $document) {
                $data[] = [
                    'timestamp' => $document['timestamp'],
                    'ip' => $document['ip'],
                    'user_agent' => $document['user_agent'],
                    'gpu_vendor' => $document['gpu_vendor'],
                    'gpu_renderer' => $document['gpu_renderer'],
                    'screen' => $document['screen'],
                    'platform' => $document['platform'],
                    'lang' => $document['lang'],
                    'timezone' => $document['timezone'],
                    'latitude' => $document['latitude'],
                    'longitude' => $document['longitude'],
                    'accuracy' => $document['accuracy'],
                    'geo' => $document['geo']
                ];
            }
            return $data;
        }
    } catch (Exception $e) {
        error_log("MongoDB load failed: " . $e->getMessage());
    }

    // Fallback to file storage
    return loadFromFile();
}

function loadFromFile() {
    $data_file = '/var/www/html/logs/data.json';
    
    if (file_exists($data_file)) {
        return json_decode(file_get_contents($data_file), true) ?? [];
    }
    
    // Check for legacy data.json in root
    $legacy_file = '/var/www/html/data.json';
    if (file_exists($legacy_file)) {
        return json_decode(file_get_contents($legacy_file), true) ?? [];
    }
    
    return [];
}

// Helper function to log to MongoDB with fallback
function logToMongo($data, $collection_name = 'logs') {
    try {
        $mongo = MongoConnection::getInstance();
        
        if ($mongo->isAvailable()) {
            $collection = $mongo->getCollection($collection_name);
            
            $logEntry = [
                'data' => $data,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (extension_loaded('mongodb')) {
                $logEntry['timestamp'] = new MongoDB\BSON\UTCDateTime();
            }
            
            $collection->insertOne($logEntry);
            return;
        }
    } catch (Exception $e) {
        error_log("Failed to log to MongoDB: " . $e->getMessage());
    }
    
    // Fallback to file logging
    file_put_contents('/var/www/html/logs/app.log', 
        "[" . date('Y-m-d H:i:s') . "] " . json_encode($data) . "\n", 
        FILE_APPEND);
}
?>