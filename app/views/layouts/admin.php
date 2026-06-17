<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'GEOakim Admin') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div>
        <strong>GEOakim Admin</strong>
        <?php if (!empty($user)): ?>
          <div style="color: var(--subtext); font-size: 0.9rem;"><?= htmlspecialchars($user['display_name']) ?></div>
        <?php endif; ?>
      </div>
      <nav>
        <a href="/admin">Links</a>
        <a href="/relatorio">Relatório</a>
        <form method="post" action="/admin/logout" style="display:inline;">
          <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
          <button class="btn btn-secondary btn-sm" type="submit">Sair</button>
        </form>
      </nav>
    </div>
    <?= $content ?? '' ?>
  </div>
  <script>window.CSRF_TOKEN = <?= json_encode($csrf ?? '') ?>;</script>
  <script src="/assets/js/admin.js"></script>
</body>
</html>
