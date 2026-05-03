/* ============================================================
   TOOL LIBRARY – SHARED APP.JS
   Role-aware: reads data-role from <body>
   Roles: "admin" | "technician" | "client"
   ============================================================ */

const ROLE = document.body.dataset.role || 'client';

/* ══════════════════════════════════════════════
   PERMISSION HELPERS
══════════════════════════════════════════════ */
const CAN = {
  editTool:          () => ROLE === 'admin',
  addCertification:  () => ROLE === 'admin',
  deleteCertification: () => ROLE === 'admin',
  addMaintenance:    () => ROLE === 'admin' || ROLE === 'technician',
  updateBattery:     () => ROLE === 'admin' || ROLE === 'technician',
};

/* ══════════════════════════════════════════════
   STAR RATING
══════════════════════════════════════════════ */
let currentRating = 4.5;

function renderStars(rating) {
  const container = document.getElementById('starsDisplay');
  if (!container) return;
  container.innerHTML = '';

  for (let i = 1; i <= 5; i++) {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('width', '15');
    svg.setAttribute('height', '15');

    const filled = i <= Math.floor(rating);
    const half   = !filled && i === Math.ceil(rating) && rating % 1 >= 0.5;

    if (half) {
      svg.innerHTML = `
        <defs>
          <linearGradient id="hg${i}" x1="0" x2="1" y1="0" y2="0">
            <stop offset="50%" stop-color="#e63946"/>
            <stop offset="50%" stop-color="#2a2a2a"/>
          </linearGradient>
        </defs>
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"
          fill="url(#hg${i})" stroke="#e63946" stroke-width="1.5"/>`;
    } else {
      svg.innerHTML = `
        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"
          fill="${filled ? '#e63946' : '#2a2a2a'}"
          stroke="${filled ? '#e63946' : '#444'}" stroke-width="1.5"/>`;
    }

    // Stars are interactive only for admin
    if (ROLE === 'admin') {
      svg.style.cursor = 'pointer';
      svg.addEventListener('click',      () => { currentRating = i; renderStars(currentRating); });
      svg.addEventListener('mouseenter', () => renderStars(i));
      svg.addEventListener('mouseleave', () => renderStars(currentRating));
    }

    container.appendChild(svg);
  }
}

/* ══════════════════════════════════════════════
   BATTERY GAUGE (Canvas)
══════════════════════════════════════════════ */
let batteryHealth = 87;

function drawGauge(pct) {
  const canvas = document.getElementById('batteryGauge');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const cx = W / 2, cy = H - 8;
  const R = 66;
  const startAngle = Math.PI;
  const endAngle   = 2 * Math.PI;
  const progress   = startAngle + (pct / 100) * Math.PI;

  ctx.clearRect(0, 0, W, H);

  // Track
  ctx.beginPath();
  ctx.arc(cx, cy, R, startAngle, endAngle);
  ctx.strokeStyle = '#252525';
  ctx.lineWidth = 13;
  ctx.lineCap = 'round';
  ctx.stroke();

  // Fill
  const grad = ctx.createLinearGradient(cx - R, cy, cx + R, cy);
  grad.addColorStop(0,   '#e63946');
  grad.addColorStop(0.5, '#c0303c');
  grad.addColorStop(1,   '#ff6b6b');
  ctx.beginPath();
  ctx.arc(cx, cy, R, startAngle, progress);
  ctx.strokeStyle = grad;
  ctx.lineWidth = 13;
  ctx.lineCap = 'round';
  ctx.stroke();

  // Tip glow dot
  const tipX = cx + R * Math.cos(progress);
  const tipY = cy + R * Math.sin(progress);
  ctx.beginPath();
  ctx.arc(tipX, tipY, 6, 0, 2 * Math.PI);
  ctx.fillStyle = '#e63946';
  ctx.shadowColor = '#e63946';
  ctx.shadowBlur = 14;
  ctx.fill();
  ctx.shadowBlur = 0;
}

function animateGauge(target) {
  let cur = 0;
  const step = () => {
    cur = Math.min(cur + 2, target);
    drawGauge(cur);
    const el = document.getElementById('gaugePct');
    if (el) el.textContent = cur + '%';
    if (cur < target) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

/* ══════════════════════════════════════════════
   MODAL HELPERS
══════════════════════════════════════════════ */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

// Click overlay to close
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

/* ══════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = `toast ${type} show`;
  setTimeout(() => { t.className = 'toast'; }, 3200);
}

/* ══════════════════════════════════════════════
   DATE FORMAT
══════════════════════════════════════════════ */
function fmtDate(val) {
  if (!val) return '';
  return new Date(val).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

/* ══════════════════════════════════════════════
   ADD CERTIFICATION (Admin only)
══════════════════════════════════════════════ */
function addCert() {
  if (!CAN.addCertification()) { showToast('Permission denied', 'error'); return; }

  const type   = document.getElementById('certType')?.value.trim();
  const issue  = document.getElementById('certIssue')?.value;
  const expiry = document.getElementById('certExpiry')?.value;
  const status = document.getElementById('certStatus')?.value;

  if (!type || !issue || !expiry) { shake('certModal'); return; }

  const tbody = document.getElementById('certTableBody');
  if (!tbody) return;

  const tagClass = `tag--${status.toLowerCase()}`;
  const actionsCell = CAN.deleteCertification()
    ? `<td class="row-actions"><button class="row-btn" onclick="editCertRow(this)">✎</button><button class="row-btn del" onclick="deleteRow(this)">🗑</button></td>`
    : '';
  const actionsHeader = tbody.closest('table').querySelector('th:last-child');

  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${type}</td>
    <td>${fmtDate(issue)}</td>
    <td>${fmtDate(expiry)}</td>
    <td><span class="tag ${tagClass}">${status}</span></td>
    ${actionsCell}`;

  tbody.insertBefore(tr, tbody.firstChild);
  flashRow(tr);
  closeModal('certModal');
  clearFields(['certType', 'certIssue', 'certExpiry']);
  showToast('Certification added');
}

/* ══════════════════════════════════════════════
   ADD MAINTENANCE (Admin + Technician)
══════════════════════════════════════════════ */
function addMaint() {
  if (!CAN.addMaintenance()) { showToast('Permission denied', 'error'); return; }

  const date   = document.getElementById('maintDate')?.value;
  const action = document.getElementById('maintAction')?.value.trim();
  const tech   = document.getElementById('maintTech')?.value.trim();
  const notes  = document.getElementById('maintNotes')?.value.trim();

  if (!date || !action || !tech) { shake('maintModal'); return; }

  const tbody = document.getElementById('maintTableBody');
  if (!tbody) return;

  const tr = document.createElement('tr');
  tr.innerHTML = `<td>${fmtDate(date)}</td><td>${action}</td><td>${tech}</td><td>${notes || '—'}</td>`;
  tbody.insertBefore(tr, tbody.firstChild);
  flashRow(tr);
  closeModal('maintModal');
  clearFields(['maintDate', 'maintAction', 'maintTech', 'maintNotes']);
  showToast('Maintenance record added');
}

/* ══════════════════════════════════════════════
   UPDATE BATTERY (Admin + Technician)
══════════════════════════════════════════════ */
function updateBattery() {
  if (!CAN.updateBattery()) { showToast('Permission denied', 'error'); return; }

  const health  = parseInt(document.getElementById('battHealth')?.value, 10);
  const cycles  = document.getElementById('battCycles')?.value;
  const status  = document.getElementById('battStatus')?.value.trim();
  const checked = document.getElementById('battChecked')?.value;

  if (isNaN(health) || health < 0 || health > 100) { shake('battModal'); return; }

  batteryHealth = health;

  // Animate gauge to new value
  const canvas = document.getElementById('batteryGauge');
  if (canvas) {
    let cur = 0;
    const step = () => {
      cur = Math.min(cur + 2, health);
      drawGauge(cur);
      const el = document.getElementById('gaugePct');
      if (el) el.textContent = cur + '%';
      if (cur < health) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  }

  if (cycles) { const el = document.getElementById('bCycles'); if (el) el.textContent = cycles; }
  if (status) { const el = document.getElementById('bStatus'); if (el) el.textContent = status; }
  if (checked){ const el = document.getElementById('bChecked'); if (el) el.textContent = fmtDate(checked); }

  closeModal('battModal');
  showToast('Battery log updated');
}

/* ══════════════════════════════════════════════
   ROW ACTIONS (Admin delete/edit)
══════════════════════════════════════════════ */
function deleteRow(btn) {
  if (!CAN.deleteCertification()) { showToast('Permission denied', 'error'); return; }
  const tr = btn.closest('tr');
  tr.style.transition = 'opacity .3s, transform .3s';
  tr.style.opacity = '0';
  tr.style.transform = 'translateX(20px)';
  setTimeout(() => tr.remove(), 300);
  showToast('Row deleted');
}

function editCertRow(btn) {
  if (!CAN.editTool()) { showToast('Permission denied', 'error'); return; }
  const tr    = btn.closest('tr');
  const cells = tr.querySelectorAll('td');
  const type  = cells[0].textContent;
  const ti = document.getElementById('certType');
  if (ti) ti.value = type;
  openModal('certModal');
}

/* ══════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════ */
function clearFields(ids) {
  ids.forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
}

function flashRow(tr) {
  tr.style.transition = 'background .5s';
  tr.style.background = 'rgba(230,57,70,.1)';
  setTimeout(() => { tr.style.background = ''; }, 700);
}

function shake(modalId) {
  const modal = document.querySelector(`#${modalId} .modal`);
  if (!modal) return;
  modal.style.animation = 'none';
  modal.offsetHeight;
  modal.style.animation = 'shake .35s ease';
}

const shakeStyle = document.createElement('style');
shakeStyle.textContent = `@keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-8px)}40%{transform:translateX(8px)}60%{transform:translateX(-5px)}80%{transform:translateX(5px)}}`;
document.head.appendChild(shakeStyle);

/* ══════════════════════════════════════════════
   CLIENT: disable all interactive elements
══════════════════════════════════════════════ */
function lockClientView() {
  // Already no buttons in client HTML, but extra safety net
  document.querySelectorAll('.add-btn, .btn, .row-btn').forEach(el => {
    el.style.pointerEvents = 'none';
    el.style.opacity = '0.3';
  });
}

/* ══════════════════════════════════════════════
   INIT
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  renderStars(currentRating);
  animateGauge(87);

  if (ROLE === 'client') lockClientView();

  // Log role to console for clarity during development
  const colors = { admin: '#e63946', technician: '#ffa500', client: '#6ab4ff' };
  console.log(`%c[Tool Library] Role: ${ROLE.toUpperCase()}`, `color:${colors[ROLE]};font-weight:bold;font-size:13px`);
});
