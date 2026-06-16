<?php
require_once 'mongo_helper.php';

function inferLocationType(array $entry): string {
    if (!empty($entry['location_type'])) {
        return $entry['location_type'];
    }
    if (($entry['geo'] ?? false) && is_numeric($entry['latitude'] ?? null) && is_numeric($entry['longitude'] ?? null)) {
        return 'precise';
    }
    return 'none';
}

function locationTypeLabel(string $type): string {
    return match ($type) {
        'precise' => 'Precisa',
        'approximate' => 'Aproximada',
        'none' => 'Nenhuma',
        default => 'Desconhecida',
    };
}

$data = [];
$api_key = $_ENV['GOOGLE_MAPS_API_KEY'] ?? 'your_google_maps_api_key_here';
$storage_type = 'file'; // Default fallback

try {
    // Get data using the helper function (automatically handles MongoDB/file fallback)
    $data = loadGeoData(1000);
    
    // Determine storage type based on MongoDB availability
    $mongo = MongoConnection::getInstance();
    $storage_type = $mongo->isAvailable() ? 'mongodb' : 'file';
    
} catch (Exception $e) {
    error_log("Failed to load data: " . $e->getMessage());
    $data = [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>GEOakim - Relatório de Coletas</title>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <!-- Toast CSS -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
  <style>
    :root {
      --bg: #f0f2f5;
      --card: #ffffff;
      --accent: #0078d7;
      --text: #333;
      --subtext: #666;
      --border: #ddd;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: var(--bg);
      margin: 0;
      padding: 20px;
      color: var(--text);
    }

    h1 {
      text-align: center;
      margin-bottom: 30px;
    }

    .stats-container {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      justify-content: center;
      flex-wrap: wrap;
    }

    .stat-card {
      background: var(--card);
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      text-align: center;
      min-width: 150px;
    }

    .stat-number {
      font-size: 2rem;
      font-weight: bold;
      color: var(--accent);
    }

    .stat-label {
      color: var(--subtext);
      font-size: 0.9rem;
    }

    #map {
      width: 100%;
      height: 450px;
      margin-bottom: 30px;
      border-radius: 8px;
      border: 1px solid var(--border);
    }

    table.dataTable {
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      background-color: var(--card);
    }

    table.dataTable th {
      background-color: var(--accent);
      color: white;
      font-weight: 500;
      text-align: left;
    }

    table.dataTable tbody tr:hover {
      background-color: #e6f0ff;
      cursor: pointer;
    }

    .geo-icon {
      font-size: 14px;
      font-weight: 600;
    }

    .location-precise {
      color: #2d7d2d;
    }

    .location-approximate {
      color: #b86e00;
    }

    .location-none {
      color: #c41e3a;
    }

    small {
      color: var(--subtext);
      font-size: 11px;
    }

    #exportCsv {
      padding: 8px 16px;
      font-size: 14px;
      background: #0078d7;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      float: right;
      margin-bottom: 15px;
    }

    .status-indicator {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
    }

    .status-online {
      background: #e7f5e7;
      color: #2d7d2d;
    }

    .status-offline {
      background: #ffeaea;
      color: #c41e3a;
    }
  </style>
</head>
<body>

  <h1>GEOakim - Relatório de Coletas</h1>
  
  <div class="stats-container">
    <div class="stat-card">
      <div class="stat-number"><?= count($data) ?></div>
      <div class="stat-label">Total de Coletas</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= count(array_filter($data, fn($d) => inferLocationType($d) === 'precise')) ?></div>
      <div class="stat-label">Precisa (GPS)</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= count(array_filter($data, fn($d) => inferLocationType($d) === 'approximate')) ?></div>
      <div class="stat-label">Aproximada (IP)</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= count(array_filter($data, fn($d) => inferLocationType($d) === 'none')) ?></div>
      <div class="stat-label">Sem Localização</div>
    </div>
    <div class="stat-card">
      <div class="stat-number"><?= count(array_unique(array_column($data, 'ip'))) ?></div>
      <div class="stat-label">IPs Únicos</div>
    </div>
    <div class="stat-card">
      <div class="stat-number">
        <span class="status-indicator <?= $storage_type === 'mongodb' ? 'status-online' : 'status-offline' ?>">
          <?= ucfirst($storage_type) ?>
        </span>
      </div>
      <div class="stat-label">Storage Type</div>
    </div>
  </div>

  <button id="exportCsv">📥 Exportar CSV</button>
  <div style="clear: both;"></div>

  <div id="map"></div>

  <table id="coletaTable" class="display" style="width:100%">
    <thead>
      <tr>
        <th>Data/Hora</th>
        <th>IP</th>
        <th>Localização</th>
        <th>GPU</th>
        <th>SO/Navegador</th>
        <th>Resolução</th>
        <th>Idioma</th>
        <th>Fuso</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $markers = [];
        foreach(array_reverse($data) as $i => $entry):
          $locationType = inferLocationType($entry);
          $lat = $entry['latitude'] ?? null;
          $lon = $entry['longitude'] ?? null;
          $hasCoords = is_numeric($lat) && is_numeric($lon);
          $markerId = $hasCoords ? "m" . $i : null;
          $locationSource = $entry['location_source'] ?? '';
          $locationLabel = $entry['location_label'] ?? '';
          $accuracy = $entry['accuracy'] ?? null;

          if ($hasCoords) {
            $info = "IP: {$entry['ip']}<br>Data: {$entry['timestamp']}<br>Tipo: " . locationTypeLabel($locationType);
            if ($locationType === 'approximate') {
              $info .= "<br>Localização aproximada (IP)";
            }
            if ($locationLabel !== '') {
              $info .= "<br>{$locationLabel}";
            }

            $markers[] = [
              'id' => $markerId,
              'lat' => floatval($lat),
              'lng' => floatval($lon),
              'type' => $locationType,
              'info' => $info
            ];
          }

          $locationClass = match ($locationType) {
            'precise' => 'location-precise',
            'approximate' => 'location-approximate',
            default => 'location-none',
          };
          $locationDetails = locationTypeLabel($locationType);
          if ($locationSource !== '') {
            $locationDetails .= ' (' . strtoupper($locationSource) . ')';
          }
          if ($accuracy !== null && $accuracy !== '') {
            $locationDetails .= '<br><small>±' . htmlspecialchars((string) $accuracy) . 'm</small>';
          }
          if ($locationLabel !== '') {
            $locationDetails .= '<br><small>' . htmlspecialchars($locationLabel) . '</small>';
          }
      ?>
      <tr data-marker="<?= $markerId ?? '' ?>"
          data-location-type="<?= htmlspecialchars($locationType) ?>"
          data-location-source="<?= htmlspecialchars($locationSource) ?>"
          data-accuracy="<?= htmlspecialchars((string) ($accuracy ?? '')) ?>"
          data-latitude="<?= htmlspecialchars((string) ($lat ?? '')) ?>"
          data-longitude="<?= htmlspecialchars((string) ($lon ?? '')) ?>">
        <td><?= $entry['timestamp'] ?></td>
        <td><?= $entry['ip'] ?></td>
        <td class="geo-icon <?= $locationClass ?>"><?= $locationDetails ?></td>
        <td><?= $entry['gpu_vendor'] ?> - <?= $entry['gpu_renderer'] ?></td>
        <td><?= $entry['platform'] ?><br><small><?= $entry['user_agent'] ?></small></td>
        <td><?= $entry['screen'] ?></td>
        <td><?= $entry['lang'] ?></td>
        <td><?= $entry['timezone'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- JS libs -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <!-- Toast JS -->
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

  <script>
    let map, markers = {};

    $(document).ready(function () {
      const table = $('#coletaTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 20
      });

      $('#coletaTable tbody').on('click', 'tr', function () {
        const markerId = $(this).data('marker');
        if (markerId) {
          focusMarker(markerId);
        }
      });

      $('#exportCsv').on('click', function () {
        let csv = 'Data/Hora,IP,location_type,location_source,accuracy,latitude,longitude,GPU,SO/Navegador,Resolução,Idioma,Fuso\n';

        $('#coletaTable tbody tr').each(function () {
          const row = $(this);
          const cells = row.find('td');
          const cleanCell = (cell) => {
            const tmp = document.createElement("div");
            tmp.innerHTML = cell;
            return '"' + tmp.textContent.trim().replace(/\n/g, ' ') + '"';
          };

          const rowData = [
            cleanCell(cells.eq(0).html()),
            cleanCell(cells.eq(1).html()),
            '"' + (row.data('location-type') || '') + '"',
            '"' + (row.data('location-source') || '') + '"',
            '"' + (row.data('accuracy') || '') + '"',
            '"' + (row.data('latitude') || '') + '"',
            '"' + (row.data('longitude') || '') + '"',
            cleanCell(cells.eq(3).html()),
            cleanCell(cells.eq(4).html()),
            cleanCell(cells.eq(5).html()),
            cleanCell(cells.eq(6).html()),
            cleanCell(cells.eq(7).html())
          ];

          csv += rowData.join(',') + '\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'relatorio_coletas.csv';
        a.click();
        URL.revokeObjectURL(url);
        
        // Show success toast
        Toastify({
          text: "CSV exportado com sucesso!",
          duration: 3000,
          gravity: "top",
          position: "right",
          backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)"
        }).showToast();
      });
    });

    function initMap() {
      map = new google.maps.Map(document.getElementById("map"), {
        zoom: 2,
        center: { lat: 0, lng: 0 },
        mapTypeControl: false,
        streetViewControl: false
      });

      const markerData = <?= json_encode($markers) ?>;

      markerData.forEach(m => {
        const isApproximate = m.type === 'approximate';
        const marker = new google.maps.Marker({
          position: { lat: m.lat, lng: m.lng },
          map: map,
          title: m.info,
          icon: isApproximate ? {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: '#f7971e',
            fillOpacity: 0.9,
            strokeColor: '#ffffff',
            strokeWeight: 2
          } : undefined
        });

        const infoWindow = new google.maps.InfoWindow({ content: m.info });

        marker.addListener("click", () => infoWindow.open(map, marker));

        markers[m.id] = {
          marker: marker,
          infoWindow: infoWindow
        };
      });
    }

    function focusMarker(id) {
      const m = markers[id];
      if (m) {
        map.setZoom(14);
        map.panTo(m.marker.getPosition());
        m.infoWindow.open(map, m.marker);
        
        // Show toast notification
        Toastify({
          text: "Marcador selecionado no mapa",
          duration: 2000,
          gravity: "top",
          position: "right",
          backgroundColor: "linear-gradient(to right, #0078d7, #4fc3f7)"
        }).showToast();
      }
    }
  </script>

  <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= $api_key ?>&callback=initMap"></script>
</body>
</html>