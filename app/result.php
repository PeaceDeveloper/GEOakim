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
    'geo'           => $data['geo'] ?? false
];

try {
    // Save data using the helper function (automatically handles MongoDB/file fallback)
    $result = saveGeoData($entry);
    
    // Log success
    logToMongo([
        'action' => 'data_collected',
        'entry_id' => $result['id'],
        'ip' => $ip,
        'geo_collected' => $entry['geo'],
        'storage_used' => $result['storage']
    ], 'app_logs');
    
    // Send success response
    http_response_code(200);
    echo json_encode($result);
    
} catch (Exception $e) {
    // Log error
    logToMongo([
        'action' => 'data_collection_error',
        'error' => $e->getMessage(),
        'ip' => $ip
    ], 'error_logs');
    
    // Send error response
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Storage failed']);
}
?>