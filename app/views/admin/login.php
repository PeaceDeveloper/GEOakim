<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - GEOakim</title>
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
  <div class="container">
    <div class="card" style="max-width:420px; margin: 4rem auto;">
      <h1 style="margin-top:0;">Administração</h1>
      <p style="color: var(--subtext);">Entre com seu usuário do AD</p>
      <?php if (!empty($error)): ?>
        <p style="color: var(--danger);">Credenciais inválidas ou sessão expirada.</p>
      <?php endif; ?>
      <form method="post" action="/admin/login">
        <?= \App\Support\Csrf::field() ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect ?? '/admin') ?>">
        <div style="margin-bottom: 1rem;">
          <label for="username">Usuário</label>
          <input id="username" name="username" required autocomplete="username" placeholder="joao.silva">
        </div>
        <div style="margin-bottom: 1rem;">
          <label for="password">Senha</label>
          <input id="password" type="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn" type="submit" style="width:100%;">Entrar</button>
      </form>
    </div>
  </div>
</body>
</html>
