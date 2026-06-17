<?php
$title = 'Link indisponível';
ob_start();
?>
<div class="join-card">
  <h2>Link indisponível</h2>
  <p>Este link expirou, foi revogado ou não existe.</p>
</div>
<?php
$content = ob_get_clean();
$joinConfig = [];
require __DIR__ . '/../layouts/public.php';
