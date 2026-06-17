<?php
$title = 'Painel - GEOakim';
ob_start();
?>
<div class="card" style="margin-bottom: 1.5rem;">
  <h2 style="margin-top:0;">Gerar link</h2>
  <form id="createLinkForm" class="form-grid">
    <div>
      <label for="name">Nome</label>
      <input id="name" name="name" required>
    </div>
    <div>
      <label for="expires_at">Expiração</label>
      <input id="expires_at" name="expires_at" type="datetime-local" required>
    </div>
    <div style="grid-column: 1 / -1;">
      <label for="meeting_url">URL da reunião</label>
      <input id="meeting_url" name="meeting_url" type="url" required placeholder="https://teams.microsoft.com/l/meetup-join/...">
    </div>
    <div>
      <button class="btn" type="submit">Gerar</button>
    </div>
  </form>
</div>

<div class="card table-wrap">
  <h2 style="margin-top:0;">Links gerados</h2>
  <table id="linksTable">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Link</th>
        <th>Expiração</th>
        <th>Status</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($links as $link): ?>
        <tr data-uid="<?= htmlspecialchars($link['uid']) ?>"
            data-name="<?= htmlspecialchars($link['name']) ?>"
            data-meeting-url="<?= htmlspecialchars($link['meeting_url']) ?>"
            data-expires-at="<?= htmlspecialchars($link['expires_at']) ?>">
          <td><?= htmlspecialchars($link['name']) ?></td>
          <td>
            <a href="<?= htmlspecialchars($link['public_url']) ?>" target="_blank"><?= htmlspecialchars($link['public_url']) ?></a>
            <button class="btn btn-secondary btn-sm copy-btn" type="button" data-url="<?= htmlspecialchars($link['public_url']) ?>">Copiar</button>
          </td>
          <td><?= htmlspecialchars($link['expires_at']) ?></td>
          <td><span class="badge badge-<?= htmlspecialchars($link['status']) ?>"><?= htmlspecialchars(ucfirst($link['status'])) ?></span></td>
          <td>
            <button class="btn btn-secondary btn-sm edit-btn" type="button">Editar</button>
            <?php if ($link['status'] !== 'revoked'): ?>
              <button class="btn btn-danger btn-sm revoke-btn" type="button">Revogar</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal" id="editModal">
  <div class="modal-content">
    <h3>Editar link</h3>
    <form id="editLinkForm" class="form-grid">
      <input type="hidden" id="edit_uid">
      <div>
        <label for="edit_name">Nome</label>
        <input id="edit_name" required>
      </div>
      <div>
        <label for="edit_expires_at">Expiração</label>
        <input id="edit_expires_at" type="datetime-local" required>
      </div>
      <div style="grid-column: 1 / -1;">
        <label for="edit_meeting_url">URL da reunião</label>
        <input id="edit_meeting_url" type="url" required>
      </div>
      <div style="display:flex; gap:0.5rem;">
        <button class="btn" type="submit">Salvar</button>
        <button class="btn btn-secondary" type="button" id="closeEditModal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
