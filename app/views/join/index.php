<?php
$title = 'Reunião do Microsoft Teams';
ob_start();
?>
<div class="join-card">
  <h2>Reunião do Microsoft Teams</h2>
  <p>Clique abaixo para entrar na reunião</p>
  <button class="btn" id="joinBtn" type="button">Entrar na reunião</button>
  <div class="spinner" id="spinner"></div>
  <div class="progress-bar" id="progressBar"><div class="progress-bar-fill" id="progressBarFill"></div></div>
  <p id="loadingText" style="display:none;">Aguarde, conectando...</p>
</div>
<?php
$content = ob_get_clean();
$sessionMeta = [
    'u' => $link_uid,
    'r' => $meeting_url,
];
require __DIR__ . '/../layouts/public.php';
