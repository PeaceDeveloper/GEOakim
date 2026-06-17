function api(path, options = {}) {
  const headers = Object.assign({
    'Content-Type': 'application/json',
    'X-CSRF-Token': window.CSRF_TOKEN || ''
  }, options.headers || {});

  return fetch(path, Object.assign({}, options, { headers }))
    .then(async (response) => {
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        throw new Error(data.error || 'Erro na requisição');
      }
      return data;
    });
}

function toLocalInputValue(dateString) {
  if (!dateString) return '';
  const date = new Date(dateString.replace(' ', 'T'));
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function toApiDateTime(value) {
  const date = new Date(value);
  const pad = (n) => String(n).padStart(2, '0');
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:00`;
}

document.addEventListener('DOMContentLoaded', () => {
  const createForm = document.getElementById('createLinkForm');
  const editForm = document.getElementById('editLinkForm');
  const editModal = document.getElementById('editModal');
  const closeEditModal = document.getElementById('closeEditModal');

  if (createForm) {
    createForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(createForm);
      api('/api/admin/links', {
        method: 'POST',
        body: JSON.stringify({
          name: formData.get('name'),
          meeting_url: formData.get('meeting_url'),
          expires_at: toApiDateTime(formData.get('expires_at'))
        })
      }).then(() => window.location.reload())
        .catch((error) => alert(error.message));
    });
  }

  document.querySelectorAll('.copy-btn').forEach((button) => {
    button.addEventListener('click', () => {
      navigator.clipboard.writeText(button.dataset.url || '');
    });
  });

  document.querySelectorAll('.edit-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const row = button.closest('tr');
      document.getElementById('edit_uid').value = row.dataset.uid;
      document.getElementById('edit_name').value = row.dataset.name;
      document.getElementById('edit_meeting_url').value = row.dataset.meetingUrl;
      document.getElementById('edit_expires_at').value = toLocalInputValue(row.dataset.expiresAt);
      editModal.classList.add('open');
    });
  });

  document.querySelectorAll('.revoke-btn').forEach((button) => {
    button.addEventListener('click', () => {
      const row = button.closest('tr');
      if (!confirm('Revogar este link?')) return;
      api(`/api/admin/links/${row.dataset.uid}`, { method: 'DELETE' })
        .then(() => window.location.reload())
        .catch((error) => alert(error.message));
    });
  });

  if (closeEditModal) {
    closeEditModal.addEventListener('click', () => editModal.classList.remove('open'));
  }

  if (editForm) {
    editForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const uid = document.getElementById('edit_uid').value;
      api(`/api/admin/links/${uid}`, {
        method: 'PATCH',
        body: JSON.stringify({
          name: document.getElementById('edit_name').value,
          meeting_url: document.getElementById('edit_meeting_url').value,
          expires_at: toApiDateTime(document.getElementById('edit_expires_at').value)
        })
      }).then(() => window.location.reload())
        .catch((error) => alert(error.message));
    });
  }
});
