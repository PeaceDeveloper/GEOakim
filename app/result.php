<?php
require_once 'mongo_helper.php';
require_once 'ip_geolocation.php';

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

$input = file_get_contents('php://input');
$data = json_decode($input, true) ?? [];

$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';

$location = resolveLocationFields($ip, $data);

$entry = [
    'timestamp'       => date("Y-m-d H:i:s"),
    'ip'              => $ip,
    'user_agent'      => $user_agent,
    'gpu_vendor'      => $data['gpu_vendor'] ?? 'N/A',
    'gpu_renderer'    => $data['gpu_renderer'] ?? 'N/A',
    'screen'          => $data['screen'] ?? 'N/A',
    'platform'        => $data['platform'] ?? 'N/A',
    'lang'            => $data['lang'] ?? 'N/A',
    'timezone'        => $data['timezone'] ?? 'N/A',
    'latitude'        => $location['latitude'],
    'longitude'       => $location['longitude'],
    'accuracy'        => $location['accuracy'],
    'location_type'   => $location['location_type'],
    'location_source' => $location['location_source'],
    'location_label'  => $location['location_label'],
    'geo'             => $location['geo']
];

try {
    $result = saveGeoData($entry);

    if (($result['status'] ?? '') !== 'success') {
        http_response_code(500);
        echo json_encode($result);
        exit;
    }

    logToMongo([
        'action' => 'data_collected',
        'entry_id' => $result['id'],
        'ip' => $ip,
        'geo_collected' => $entry['geo'],
        'location_type' => $entry['location_type'],
        'location_source' => $entry['location_source'],
        'storage_used' => $result['storage']
    ], 'app_logs');

    http_response_code(200);
    echo json_encode($result);

} catch (Exception $e) {
    logToMongo([
        'action' => 'data_collection_error',
        'error' => $e->getMessage(),
        'ip' => $ip
    ], 'error_logs');

    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Storage failed']);
}
?>
