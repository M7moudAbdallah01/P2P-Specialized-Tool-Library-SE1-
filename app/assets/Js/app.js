/* ============================================================
   TOOL LIBRARY – ADMIN app.js
   Full access: Edit Tool · Add Cert · Add Maint · Update Battery
   + Interactive Activity Timeline with Add / Delete events
   ============================================================ */

/* ── TIMELINE DATA ── */
const timelineData = [
  { date: '10 May 2024', label: 'Tool Added',    type: 'added'       },
  { date: '10 May 2024', label: 'First Booking', type: 'booking'     },
  { date: '20 May 2024', label: 'Returned',       type: 'returned'    },
  { date: '15 Jun 2024', label: 'Maintenance',   type: 'maintenance' },
  { date: '01 Jul 2024', label: 'Booked Again',  type: 'booking'     },
  { date: '01 Aug 2024', label: 'Returned',       type: 'returned'    },
];

/* SVG icon paths per event type */
const TL_ICONS = {
  added:       '<path d="M12 5v14M5 12h14"/>',
  booking:     '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
  returned:    '<polyline points="20 6 9 17 4 12"/>',
  maintenance: '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
  custom:      '<circle cx="12" cy="12" r="4"/>',
};

/* ══════════════════════════════════════════════
   TIMELINE RENDER
══════════════════════════════════════════════ */
function renderTimeline() {
  const container = document.getElementById('timelineItems');
  if (!container) return;
  container.innerHTML = '';

  timelineData.forEach((item, idx) => {
    const icon = TL_ICONS[item.type] || TL_ICONS.custom;

    const el = document.createElement('div');
    el.className = 't-item';
    el.innerHTML = `
      <button class="t-delete" title="Remove event" onclick="removeTimelineEvent(${idx})">✕</button>
      <span class="t-date">${item.date}</span>
      <div class="t-dot">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2">${icon}</svg>
      </div>
      <span class="t-label">${item.label}</span>`;

    container.appendChild(el);
  });
}

function removeTimelineEvent(idx) {
  timelineData.splice(idx, 1);
  renderTimeline();
  showToast('Event removed');
}

/* ══════════════════════════════════════════════
   ADD TIMELINE EVENT
══════════════════════════════════════════════ */
function addTimelineEvent() {
  const dateVal  = document.getElementById('tlDate').value;
  const label    = document.getElementById('tlLabel').value.trim();
  const type     = document.getElementById('tlType').value;

  if (!dateVal || !label) { shake('timelineModal'); return; }

  const fmt = d => new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

  timelineData.push({ date: fmt(dateVal), label, type });
  renderTimeline();

  // Scroll timeline to end to reveal new event
  const wrap = document.querySelector('.timeline-scroll-wrap');
  if (wrap) setTimeout(() => { wrap.scrollLeft = wrap.scrollWidth; }, 60);

  closeModal('timelineModal');
  document.getElementById('tlDate').value  = '';
  document.getElementById('tlLabel').value = '';
  showToast('Event added to timeline');
}

/* ══════════════════════════════════════════════
   STAR RATING  (interactive – admin can rate)
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
    svg.style.cursor = 'pointer';

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

    svg.addEventListener('click',      () => { currentRating = i; renderStars(currentRating); });
    svg.addEventListener('mouseenter', () => renderStars(i));
    svg.addEventListener('mouseleave', () => renderStars(currentRating));
    container.appendChild(svg);
  }
}

/* ══════════════════════════════════════════════
   BATTERY GAUGE (Canvas arc)
══════════════════════════════════════════════ */
let batteryHealth = 87;

function drawGauge(pct) {
  const canvas = document.getElementById('batteryGauge');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  const W = canvas.width, H = canvas.height;
  const cx = W / 2, cy = H - 8;
  const R = 66;
  const startAngle = Math.PI, endAngle = 2 * Math.PI;
  const progress = startAngle + (pct / 100) * Math.PI;

  ctx.clearRect(0, 0, W, H);

  // Track
  ctx.beginPath();
  ctx.arc(cx, cy, R, startAngle, endAngle);
  ctx.strokeStyle = '#242424'; ctx.lineWidth = 13; ctx.lineCap = 'round';
  ctx.stroke();

  // Gradient fill
  const g = ctx.createLinearGradient(cx - R, cy, cx + R, cy);
  g.addColorStop(0, '#e63946'); g.addColorStop(.5, '#c0303c'); g.addColorStop(1, '#ff6b6b');
  ctx.beginPath();
  ctx.arc(cx, cy, R, startAngle, progress);
  ctx.strokeStyle = g; ctx.lineWidth = 13; ctx.lineCap = 'round';
  ctx.stroke();

  // Glow tip
  const tx = cx + R * Math.cos(progress), ty = cy + R * Math.sin(progress);
  ctx.beginPath();
  ctx.arc(tx, ty, 6, 0, 2 * Math.PI);
  ctx.fillStyle = '#e63946';
  ctx.shadowColor = '#e63946'; ctx.shadowBlur = 14;
  ctx.fill(); ctx.shadowBlur = 0;
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
   EDIT TOOL (admin only)
══════════════════════════════════════════════ */
function saveToolEdit() {
  const name      = document.getElementById('editName')?.value.trim();
  const cat       = document.getElementById('editCat')?.value.trim();
  const owner     = document.getElementById('editOwner')?.value.trim();
  const condition = document.getElementById('editCondition')?.value;
  const price     = document.getElementById('editPrice')?.value;
  const status    = document.getElementById('editStatus')?.value;
  const desc      = document.getElementById('editDesc')?.value.trim();

  if (!name) { shake('editToolModal'); return; }

  // Update hero fields live
  const n = document.querySelector('.tool-name'); if (n) n.textContent = name;
  const c = document.getElementById('heroCat');    if (c) c.textContent = cat;
  const o = document.getElementById('heroOwner');  if (o) o.textContent = owner;
  const co = document.getElementById('heroCondition'); if (co) co.textContent = condition;
  const p = document.getElementById('heroPrice');  if (p) p.textContent = `$${price}.00 / Day`;
  const d = document.getElementById('heroDesc');   if (d) d.textContent = desc;
  const s = document.getElementById('heroState');  if (s) s.textContent = condition;

  // Update status badge
  const badge = document.getElementById('statusBadge');
  if (badge) {
    badge.textContent = status;
    badge.className = status === 'Available' ? 'badge badge--available' : 'badge badge--unavailable';
  }

  // Add to timeline
  timelineData.push({ date: todayStr(), label: 'Tool Edited', type: 'custom' });
  renderTimeline();

  closeModal('editToolModal');
  showToast('Tool updated successfully');
}

/* ══════════════════════════════════════════════
   ADD CERTIFICATION
══════════════════════════════════════════════ */
function addCert() {
  const type   = document.getElementById('certType')?.value.trim();
  const issue  = document.getElementById('certIssue')?.value;
  const expiry = document.getElementById('certExpiry')?.value;
  const status = document.getElementById('certStatus')?.value;

  if (!type || !issue || !expiry) { shake('certModal'); return; }

  const tbody = document.getElementById('certTableBody');
  if (!tbody) return;

  const cls = `tag--${status.toLowerCase()}`;
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td>${type}</td>
    <td>${fmtDate(issue)}</td>
    <td>${fmtDate(expiry)}</td>
    <td><span class="tag ${cls}">${status}</span></td>
    <td class="row-actions">
      <button class="row-btn" onclick="editCertRow(this)" title="Edit">✎</button>
      <button class="row-btn del" onclick="deleteRow(this)" title="Delete">🗑</button>
    </td>`;

  tbody.insertBefore(tr, tbody.firstChild);
  flashRow(tr);

  clearFields(['certType','certIssue','certExpiry']);
  closeModal('certModal');
  showToast('Certification added');
}

function editCertRow(btn) {
  const cells = btn.closest('tr').querySelectorAll('td');
  const ti = document.getElementById('certType');
  if (ti) ti.value = cells[0].textContent;
  openModal('certModal');
}

function deleteRow(btn) {
  const tr = btn.closest('tr');
  tr.style.transition = 'opacity .3s, transform .3s';
  tr.style.opacity = '0'; tr.style.transform = 'translateX(16px)';
  setTimeout(() => tr.remove(), 310);
  showToast('Row deleted');
}

/* ══════════════════════════════════════════════
   ADD MAINTENANCE
══════════════════════════════════════════════ */
function addMaint() {
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

  // Push to timeline
  timelineData.push({ date: fmtDate(date), label: action, type: 'maintenance' });
  renderTimeline();

  clearFields(['maintDate','maintAction','maintTech','maintNotes']);
  closeModal('maintModal');
  showToast('Maintenance record added');
}

/* ══════════════════════════════════════════════
   UPDATE BATTERY
══════════════════════════════════════════════ */
function updateBattery() {
  const health  = parseInt(document.getElementById('battHealth')?.value, 10);
  const cycles  = document.getElementById('battCycles')?.value;
  const status  = document.getElementById('battStatus')?.value.trim();
  const checked = document.getElementById('battChecked')?.value;

  if (isNaN(health) || health < 0 || health > 100) { shake('battModal'); return; }

  batteryHealth = health;

  // Animate gauge
  let cur = 0;
  const step = () => {
    cur = Math.min(cur + 2, health);
    drawGauge(cur);
    const el = document.getElementById('gaugePct');
    if (el) el.textContent = cur + '%';
    if (cur < health) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);

  // Update stat fields
  if (cycles)  { const el = document.getElementById('bCycles');  if (el) el.textContent = cycles; }
  if (status)  { const el = document.getElementById('bStatus');  if (el) el.textContent = status; }
  if (checked) {
    const formatted = fmtDate(checked);
    const el = document.getElementById('bChecked'); if (el) el.textContent = formatted;
    // mirror in hero panel
    const h = document.getElementById('heroLastChecked'); if (h) h.textContent = formatted;
  }
  // Mirror hero health
  const hh = document.getElementById('heroHealthStatus'); if (hh) hh.textContent = `${status || 'Good'} (${health}%)`;
  const hc = document.getElementById('heroChargeCycles'); if (hc && cycles) hc.textContent = cycles;

  closeModal('battModal');
  showToast('Battery log updated');
}

/* ══════════════════════════════════════════════
   MODAL HELPERS
══════════════════════════════════════════════ */
function openModal(id) { const el = document.getElementById(id); if (el) el.classList.add('open'); }
function closeModal(id) { const el = document.getElementById(id); if (el) el.classList.remove('open'); }

document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

/* ══════════════════════════════════════════════
   SIDEBAR TOGGLE (mobile)
══════════════════════════════════════════════ */
function toggleSidebar() {
  const sb = document.getElementById('sidebar');
  if (sb) sb.classList.toggle('open');
}

/* ══════════════════════════════════════════════
   SEARCH – filters maintenance & cert tables
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  const si = document.getElementById('searchInput');
  if (si) {
    si.addEventListener('input', e => {
      const q = e.target.value.toLowerCase();
      ['certTableBody','maintTableBody'].forEach(id => {
        document.querySelectorAll(`#${id} tr`).forEach(tr => {
          tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
      });
    });
  }
});

/* ══════════════════════════════════════════════
   TOAST
══════════════════════════════════════════════ */
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = `toast ${type} show`;
  setTimeout(() => { t.className = 'toast'; }, 3000);
}

/* ══════════════════════════════════════════════
   UTILITIES
══════════════════════════════════════════════ */
function fmtDate(val) {
  if (!val) return '';
  return new Date(val).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
function todayStr() {
  return new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
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
  modal.style.animation = 'none'; modal.offsetHeight;
  modal.style.animation = 'shake .35s ease';
}

// Inject shake keyframes
const s = document.createElement('style');
s.textContent = `
  @keyframes shake{0%,100%{transform:translateX(0)}20%{transform:translateX(-8px)}40%{transform:translateX(8px)}60%{transform:translateX(-5px)}80%{transform:translateX(5px)}}
  .badge--unavailable{background:rgba(100,100,100,.15);color:#888;border:1px solid #333;}
`;
document.head.appendChild(s);

/* ══════════════════════════════════════════════
   INIT
══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  renderStars(currentRating);
  animateGauge(batteryHealth);
  renderTimeline();

  console.log('%c[Tool Library] Role: ADMIN — Full Access', 'color:#e63946;font-weight:bold;font-size:13px');
});
