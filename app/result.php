<?php
require_once 'mongo_helper.php';

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

$entry = [
    'timestamp'     => date("Y-m-d H:i:s"),
    'ip'            => $ip,
    'user_agent'    => $user_agent,
    'gpu_vendor'    => $data['gpu_vendor'] ?? 'N/A',
    'gpu_renderer'  => $data['gpu_renderer'] ?? 'N/A',
    'screen'        => $data['screen'] ?? 'N/A',
    'platform'      => $data['platform'] ?? 'N/A',
    'lang'          => $data['lang'] ?? 'N/A',
    'timezone'      => $data['timezone'] ?? 'N/A',
    'latitude'      => $data['lat'] ?? null,
    'longitude'     => $data['lon'] ?? null,
    'accuracy'      => $data['accuracy'] ?? null,
    'geo'           => $data['geo'] ?? false,
    'created_at'    => new MongoDB\BSON\UTCDateTime()
];

try {
    // Save to MongoDB
    $mongo = MongoConnection::getInstance();
    $collection = $mongo->getCollection('geo_data');
    $result = $collection->insertOne($entry);
    
    // Log success
    logToMongo([
        'action' => 'data_collected',
        'entry_id' => $result->getInsertedId(),
        'ip' => $ip,
        'geo_collected' => $entry['geo']
    ], 'app_logs');
    
    // Send success response
    http_response_code(200);
    echo json_encode(['status' => 'success', 'id' => (string)$result->getInsertedId()]);
    
} catch (Exception $e) {
    // Log error
    logToMongo([
        'action' => 'data_collection_error',
        'error' => $e->getMessage(),
        'ip' => $ip
    ], 'error_logs');
    
    // Fallback to file storage
    $log_line = "[{$entry['timestamp']}] "
      . "IP: {$entry['ip']} | "
      . "UA: {$entry['user_agent']} | "
      . "GPU: {$entry['gpu_vendor']} / {$entry['gpu_renderer']} | "
      . "Res: {$entry['screen']} | "
      . "Plataforma: {$entry['platform']} | "
      . "Idioma: {$entry['lang']} | "
      . "Fuso: {$entry['timezone']}";

    if ($entry['geo']) {
      $log_line .= " | Geo: ({$entry['latitude']}, {$entry['longitude']}) ±{$entry['accuracy']}m";
    } else {
      $log_line .= " | Geo: NÃO COLETADO";
    }

    $log_line .= "\n";
    file_put_contents("/var/www/html/logs/fallback.log", $log_line, FILE_APPEND);
    
    // Send error response
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database unavailable']);
}
?>