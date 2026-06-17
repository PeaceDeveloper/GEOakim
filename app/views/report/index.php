<?php

function inferLocationType(array $entry): string
{
    if (!empty($entry['location_type'])) {
        return $entry['location_type'] === 'approximate' ? 'none' : $entry['location_type'];
    }
    if (($entry['geo'] ?? false) && is_numeric($entry['latitude'] ?? null) && is_numeric($entry['longitude'] ?? null)) {
        return 'precise';
    }
    return 'none';
}

$preciseCount = count(array_filter($entries, fn($entry) => inferLocationType($entry) === 'precise'));
$noneCount = count(array_filter($entries, fn($entry) => inferLocationType($entry) === 'none'));
$title = 'Relatório - GEOakim';

ob_start();
?>
<div class="card" style="margin-bottom: 1rem;">
  <form method="get" action="/relatorio" class="form-grid">
    <div>
      <label for="link_uid">Filtrar por link</label>
      <select id="link_uid" name="link_uid">
        <option value="">Todos</option>
        <?php foreach ($links as $link): ?>
          <option value="<?= htmlspecialchars($link['uid']) ?>" <?= ($selected_link_uid === $link['uid']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($link['name']) ?> (<?= htmlspecialchars($link['uid']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="align-self:end;">
      <button class="btn" type="submit">Filtrar</button>
    </div>
  </form>
</div>

<div class="stats">
  <div class="stat-card"><div class="stat-number"><?= count($entries) ?></div><div>Total</div></div>
  <div class="stat-card"><div class="stat-number"><?= $preciseCount ?></div><div>Precisa (GPS)</div></div>
  <div class="stat-card"><div class="stat-number"><?= $noneCount ?></div><div>Sem Localização</div></div>
</div>

<div id="map" style="width:100%;height:450px;margin-bottom:1.5rem;border-radius:8px;border:1px solid var(--border);"></div>

<div class="card table-wrap">
  <table id="coletaTable">
    <thead>
      <tr>
        <th>Data/Hora</th>
        <th>Link</th>
        <th>IP</th>
        <th>Localização</th>
        <th>Mapa</th>
        <th>GPU</th>
        <th>SO/Navegador</th>
        <th>Resolução</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $markers = [];
      foreach (array_reverse($entries) as $i => $entry):
        $locationType = inferLocationType($entry);
        $lat = $entry['latitude'] ?? null;
        $lon = $entry['longitude'] ?? null;
        $hasPrecise = $locationType === 'precise' && is_numeric($lat) && is_numeric($lon);
        $markerId = $hasPrecise ? 'm' . $i : '';
        if ($hasPrecise) {
          $markers[] = [
            'id' => $markerId,
            'lat' => (float) $lat,
            'lng' => (float) $lon,
            'info' => 'IP: ' . $entry['ip'] . '<br>Link: ' . ($entry['link_name'] ?? '-'),
          ];
        }
        $mapsUrl = $hasPrecise ? 'https://www.google.com/maps?q=' . urlencode($lat . ',' . $lon) : '';
      ?>
      <tr data-marker="<?= htmlspecialchars($markerId) ?>">
        <td><?= htmlspecialchars($entry['timestamp']) ?></td>
        <td><?= htmlspecialchars($entry['link_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($entry['ip']) ?></td>
        <td><?= $hasPrecise ? 'Precisa (GPS)' : 'Nenhuma' ?></td>
        <td><?= $hasPrecise ? '<a href="' . htmlspecialchars($mapsUrl) . '" target="_blank" rel="noopener">Abrir no Maps</a>' : '-' ?></td>
        <td><?= htmlspecialchars(($entry['gpu_vendor'] ?? '') . ' - ' . ($entry['gpu_renderer'] ?? '')) ?></td>
        <td><?= htmlspecialchars($entry['platform'] ?? '') ?></td>
        <td><?= htmlspecialchars($entry['screen'] ?? '') ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
  const markerData = <?= json_encode($markers) ?>;
  function initMap() {
    const map = new google.maps.Map(document.getElementById('map'), {
      zoom: 2,
      center: { lat: 0, lng: 0 },
      mapTypeControl: false,
      streetViewControl: false
    });
    markerData.forEach((m) => {
      const marker = new google.maps.Marker({
        position: { lat: m.lat, lng: m.lng },
        map,
        title: m.info
      });
      const infoWindow = new google.maps.InfoWindow({ content: m.info });
      marker.addListener('click', () => infoWindow.open(map, marker));
    });
  }
</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars($api_key) ?>&callback=initMap"></script>
<?php
$content = ob_get_clean();
$csrf = \App\Support\Csrf::token();
require __DIR__ . '/../layouts/admin.php';
