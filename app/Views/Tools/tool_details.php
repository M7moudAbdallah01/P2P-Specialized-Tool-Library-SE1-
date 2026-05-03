<?php
session_start();

$isAdmin = ($_SESSION['role'] ?? '') === 'admin';
$isTech  = ($_SESSION['role'] ?? '') === 'technical';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tool Library – Admin View</title>
    <link rel="stylesheet" href="../../assets/Css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
</head>

<body data-role="admin">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" />
                </svg>
            </div>
            <span class="brand-text">TOOL HUB</span>
        </div>
        <nav class="sidebar-nav">
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7" />
                    <rect x="14" y="3" width="7" height="7" />
                    <rect x="3" y="14" width="7" height="7" />
                    <rect x="14" y="14" width="7" height="7" />
                </svg>Dashboard</a>
            <a href="#" class="nav-link active"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                </svg>Tools</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16v4H4z" />
                    <path d="M4 12h16v4H4z" />
                    <path d="M4 20h16" />
                </svg>Categories</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>Users</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>Bookings</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                </svg>Maintenance</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>Certifications</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="18" height="10" rx="2" />
                    <line x1="22" y1="11" x2="22" y2="13" />
                </svg>Battery Logs</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                </svg>Reports</a>
            <a href="#" class="nav-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                </svg>Settings</a>
        </nav>
        <?php if ($isAdmin): ?>
            <div class="role-badge role-admin">ADMIN</div>
        <?php elseif ($isTech): ?>
            <div class="role-badge role-tech">TECHNICIAN</div>
        <?php else: ?>
            <div class="role-badge role-client">CLIENT</div>
        <?php endif; ?>
    </aside>

    <div class="layout-right">
        <!-- TOPBAR -->
        <header class="topbar">
            <button class="hamburger"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6" />
                    <line x1="3" y1="12" x2="21" y2="12" />
                    <line x1="3" y1="18" x2="21" y2="18" />
                </svg></button>
            <h1 class="topbar-title">Tool Details</h1>
            <div class="topbar-right">
                <button class="icon-btn"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg></button>
                <button class="icon-btn notif-wrap"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                    </svg><span class="notif-dot">3</span></button>
                <button class="avatar-btn admin-avatar">A <span>Admin</span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="6 9 12 15 18 9" />
                    </svg></button>
            </div>
        </header>

        <!-- BREADCRUMB + ACTION BUTTONS -->
        <div class="subbar">
            <div class="breadcrumb"><a href="#">Dashboard</a><span>›</span><a href="#">Tools</a><span>›</span><span class="bc-active">Tool Details</span></div>
            <div class="action-btns">
                <?php if ($isAdmin): ?>
                    <button class="btn btn-outline" onclick="openModal('editToolModal')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit Tool
                    </button>
                <?php endif; ?>
                <?php if ($isAdmin || $isTech): ?>
                    <button class="btn btn-solid" onclick="openModal('maintModal')">Add Maintenance</button>
                    <button class="btn btn-solid" onclick="openModal('battModal')">Update Battery</button>
                <?php endif; ?>
                <?php if ($isAdmin): ?>
                    <button class="btn btn-solid" onclick="openModal('certModal')">Add Certification</button>
                <?php endif; ?>

            </div>
        </div>

        <main class="main-content">
            <!-- HERO -->
            <section class="hero-card card">
                <div class="hero-image-wrap">
                    <img src="https://myturn-prod-images-out.s3.amazonaws.com/0/30/item/---/image/image-A2DEE8F4-C32B-AF17-0953-6CB7CE8E48DB-small@2x.jpg"
                        alt="Milwaukee M18 Fuel Drill" class="hero-image"
                        onerror="this.src='https://placehold.co/200x200/1a1a1a/e63946?text=M18'" />
                </div>
                <div class="hero-info">
                    <div class="hero-title-row">
                        <h2 class="tool-name">Milwaukee M18 Fuel Drill</h2>
                        <span class="badge badge--available">Available</span>
                    </div>
                    <p class="tool-id">Tool ID: TL-2024-00125</p>
                    <div class="info-grid">
                        <div class="info-row"><span class="info-label">Tool ID:</span><span>TL-2024-00125</span></div>
                        <div class="info-row"><span class="info-label">Base Price:</span><span>$65.00 / Day</span></div>
                        <div class="info-row"><span class="info-label">Category:</span><span>Power Tools</span></div>
                        <div class="info-row"><span class="info-label">Warranty Expiry:</span><span class="accent">12 Nov 2025</span></div>
                        <div class="info-row"><span class="info-label">Owner:</span><span>Ahmed Darwish</span></div>
                        <div class="info-row"><span class="info-label">Added On:</span><span>10 May 2024</span></div>
                        <div class="info-row"><span class="info-label">Condition:</span><span>Good</span></div>
                        <div class="info-row"><span class="info-label">Price:</span><span>$65.00 / Day</span></div>
                    </div>
                    <div class="desc-block"><span class="desc-label">Description:</span>
                        <p>High-performance cordless drill with brushless motor, ideal for heavy-duty applications.</p>
                    </div>
                </div>
                <div class="hero-stats">
                    <div class="stat-row"><span class="stat-label">Tool State</span><span class="stat-val">Good</span></div>
                    <div class="stat-row"><span class="stat-label">Total Bookings</span><span class="stat-val">12</span></div>
                    <div class="stat-row rating-row"><span class="stat-label">Rating</span>
                        <div class="stars" id="starsDisplay"></div>
                    </div>
                    <div class="stat-row"><span class="stat-label">Charge Cycles</span><span class="stat-val">45</span></div>
                    <div class="stat-row"><span class="stat-label">Health Status</span><span class="stat-val">Good (87%)</span></div>
                    <div class="stat-row"><span class="stat-label">Last Checked</span><span class="stat-val">01 May 2024</span></div>
                </div>
            </section>

            <!-- PANELS -->
            <div class="panels-grid">
                <!-- Certifications -->
                <div class="card panel">
                    <div class="panel-header">
                        <div class="panel-title-wrap"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            <h3 class="panel-title">Certifications</h3>
                        </div>
                        <?php if ($isAdmin): ?>
                            <button class="add-btn" onclick="openModal('certModal')">+ Add Certification</button>
                        <?php endif; ?>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="certTableBody">
                            <tr>
                                <td>CE Certification</td>
                                <td>10 Jan 2024</td>
                                <td>10 Jan 2026</td>
                                <td><span class="tag tag--valid">Valid</span></td>
                                <td class="row-actions"><button class="row-btn" onclick="editCertRow(this)">✎</button><button class="row-btn del" onclick="deleteRow(this)">🗑</button></td>
                            </tr>
                            <tr>
                                <td>ISO 9001</td>
                                <td>15 Mar 2023</td>
                                <td>15 Mar 2025</td>
                                <td><span class="tag tag--valid">Valid</span></td>
                                <td class="row-actions"><button class="row-btn" onclick="editCertRow(this)">✎</button><button class="row-btn del" onclick="deleteRow(this)">🗑</button></td>
                            </tr>
                            <tr>
                                <td>Safety Approved</td>
                                <td>05 Feb 2024</td>
                                <td>05 Feb 2026</td>
                                <td><span class="tag tag--valid">Valid</span></td>
                                <td class="row-actions"><button class="row-btn" onclick="editCertRow(this)">✎</button><button class="row-btn del" onclick="deleteRow(this)">🗑</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Maintenance History -->
                <div class="card panel">
                    <div class="panel-header">
                        <div class="panel-title-wrap"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                            </svg>
                            <h3 class="panel-title">Maintenance History</h3>
                        </div>
                        <?php if ($isAdmin || $isTech): ?>
                            <button class="add-btn" onclick="openModal('maintModal')">+ Add Maintenance</button>
                        <?php endif; ?>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Technician</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody id="maintTableBody">
                            <tr>
                                <td>01 May 2024</td>
                                <td>General Checkup</td>
                                <td>Mohamed Ali</td>
                                <td>All good</td>
                            </tr>
                            <tr>
                                <td>15 Mar 2024</td>
                                <td>Battery Replace</td>
                                <td>Omar Hassan</td>
                                <td>New battery</td>
                            </tr>
                            <tr>
                                <td>20 Feb 2024</td>
                                <td>Cleaning</td>
                                <td>Mohamed Ali</td>
                                <td>Dust cleaned</td>
                            </tr>
                            <tr>
                                <td>10 Jan 2024</td>
                                <td>Inspection</td>
                                <td>Sara Ahmed</td>
                                <td>Everything OK</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Battery Logs -->
                <div class="card panel">
                    <div class="panel-header">
                        <div class="panel-title-wrap"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="18" height="10" rx="2" />
                                <line x1="22" y1="11" x2="22" y2="13" />
                            </svg>
                            <h3 class="panel-title">Battery Logs</h3>
                        </div>
                        <?php if ($isAdmin || $isTech): ?>
                            <button class="add-btn" onclick="openModal('battModal')">+ Update</button>
                        <?php endif; ?>
                    </div>
                    <div class="battery-body">
                        <div class="gauge-wrap">
                            <canvas id="batteryGauge" width="160" height="100"></canvas>
                            <div class="gauge-label"><span class="gauge-pct" id="gaugePct">87%</span><span class="gauge-sub">Health</span></div>
                        </div>
                        <div class="battery-stats">
                            <div class="bstat-row"><span class="bstat-label">Charge Cycles</span><span class="bstat-val" id="bCycles">45</span></div>
                            <div class="bstat-row"><span class="bstat-label">Health Status</span><span class="bstat-val" id="bStatus">Good</span></div>
                            <div class="bstat-row"><span class="bstat-label">Last Checked</span><span class="bstat-val" id="bChecked">01 May 2024</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL: Edit Tool (Admin only) -->
    <div class="modal-overlay" id="editToolModal">
        <div class="modal">
            <div class="modal-header">
                <?php if ($isAdmin): ?>
                    <h3>Edit Tool</h3><button class="modal-close" onclick="closeModal('editToolModal')">✕</button>
                <?php endif; ?>
            </div>
            <div class="modal-body">
                <label>Tool Name<input type="text" value="Milwaukee M18 Fuel Drill" /></label>
                <label>Category<input type="text" value="Power Tools" /></label>
                <label>Owner<input type="text" value="Ahmed Darwish" /></label>
                <label>Condition
                    <select>
                        <option>Good</option>
                        <option>Fair</option>
                        <option>Poor</option>
                    </select>
                </label>
                <label>Base Price / Day<input type="number" value="65" /></label>
                <label>Status
                    <select>
                        <option>Available</option>
                        <option>Unavailable</option>
                        <option>Under Maintenance</option>
                    </select>
                </label>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal('editToolModal')">Cancel</button>
                <button class="btn-save" onclick="closeModal('editToolModal'); showToast('Tool updated successfully')">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Add Certification -->
    <div class="modal-overlay" id="certModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Certification</h3><button class="modal-close" onclick="closeModal('certModal')">✕</button>
            </div>
            <div class="modal-body">
                <label>Type<input type="text" id="certType" placeholder="e.g. ISO 9001" /></label>
                <label>Issue Date<input type="date" id="certIssue" /></label>
                <label>Expiry Date<input type="date" id="certExpiry" /></label>
                <label>Status<select id="certStatus">
                        <option>Valid</option>
                        <option>Expired</option>
                        <option>Pending</option>
                    </select></label>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal('certModal')">Cancel</button>
                <button class="btn-save" onclick="addCert()">Save</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Add Maintenance -->
    <div class="modal-overlay" id="maintModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Add Maintenance Record</h3><button class="modal-close" onclick="closeModal('maintModal')">✕</button>
            </div>
            <div class="modal-body">
                <label>Date<input type="date" id="maintDate" /></label>
                <label>Action<input type="text" id="maintAction" placeholder="e.g. Inspection" /></label>
                <label>Technician<input type="text" id="maintTech" placeholder="Name" /></label>
                <label>Notes<input type="text" id="maintNotes" placeholder="Short notes" /></label>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal('maintModal')">Cancel</button>
                <button class="btn-save" onclick="addMaint()">Save</button>
            </div>
        </div>
    </div>

    <!-- MODAL: Update Battery -->
    <div class="modal-overlay" id="battModal">
        <div class="modal">
            <?php if ($isAdmin || $isTech): ?>
                <div class="modal-header">
                    <h3>Update Battery Log</h3><button class="modal-close" onclick="closeModal('battModal')">✕</button>
                </div>
            <?php endif; ?>
            <div class="modal-body">
                <label>Health % (0-100)<input type="number" id="battHealth" min="0" max="100" value="87" /></label>
                <label>Charge Cycles<input type="number" id="battCycles" value="45" /></label>
                <label>Health Status<input type="text" id="battStatus" value="Good" /></label>
                <label>Last Checked<input type="date" id="battChecked" /></label>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel" onclick="closeModal('battModal')">Cancel</button>
                <button class="btn-save" onclick="updateBattery()">Save</button>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="toast" id="toast"></div>

    <script src="../../assets/Js/app.js"></script>
</body>

</html>