<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Microsoft Teams') ?></title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="join-body">
  <?= $content ?? '' ?>
  <script>
    window._M = <?= json_encode($sessionMeta ?? [], JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="/assets/js/join.js"></script>
</body>
</html>
