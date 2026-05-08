<?php

require_once __DIR__ . "/../../../Core/database.php";
require_once "../../Controllers/toolDetailsController.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$role     = $_SESSION['role']; // 'admin' | 'technical' | 'client'
$isAdmin  = ($role === 'admin');
$isTech   = ($role === 'technical');
$isClient = ($role === 'client');
$certifications = $certifications ? $certifications->fetch_all(MYSQLI_ASSOC) : [];
$maintenance    = $maintenance ? $maintenance->fetch_all(MYSQLI_ASSOC) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // EDIT TOOL
    if (isset($_POST['edit_tool'])) {
        $id    = intval($_POST['tool_id']);
        $name  = $_POST['name'];
        $state = $_POST['state'];
        $price = floatval($_POST['base_price']);
        $avail = intval($_POST['availability']);
        $desc  = $_POST['description'];

        $stmt = $conn->prepare("
            UPDATE tools 
            SET name=?, state=?, base_price=?, availability=?, description=? 
            WHERE tool_id=?
        ");
        $stmt->bind_param("ssdisi", $name, $state, $price, $avail, $desc, $id);
        $stmt->execute();
    }

    // ADD CERTIFICATION
    if (isset($_POST['add_cert'])) {
        $stmt = $conn->prepare("
            INSERT INTO certifications (tool_id, type, issue_date, expiry_date)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isss",
            $_POST['tool_id'],
            $_POST['type'],
            $_POST['issue_date'],
            $_POST['expiry_date']
        );
        $stmt->execute();
    }

    // DELETE CERT
    if (isset($_POST['delete_cert'])) {
        $stmt = $conn->prepare("DELETE FROM certifications WHERE id=?");
        $stmt->bind_param("i", $_POST['delete_cert']);
        $stmt->execute();
    }

    // ADD MAINTENANCE
    if (isset($_POST['add_maintenance'])) {
        $stmt = $conn->prepare("
            INSERT INTO maintenance_logs (tool_id, action, notes, date)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "isss",
            $_POST['tool_id'],
            $_POST['action'],
            $_POST['notes'],
            $_POST['date']
        );
        $stmt->execute();
    }

    // UPDATE BATTERY
    if (isset($_POST['update_battery'])) {
        $stmt = $conn->prepare("
            INSERT INTO battery_logs (tool_id, charge_cycles, health_status, last_checked)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiss",
            $_POST['tool_id'],
            $_POST['charge_cycles'],
            $_POST['health_status'],
            $_POST['last_checked']
        );
        $stmt->execute();
    }

    // refresh الصفحة عشان يظهر التحديث
    header("Location: tool_details.php?id=" . $_POST['tool_id']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub – Tool Details</title>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/tool_details.css">
</head>
<body data-role="<?= $role ?>">

<!-- ═══════════════════════════════
     SIDEBAR
═══════════════════════════════ -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
        </div>
        <div class="brand-text">TOOL HUB</div>
    </div>

    <?php if ($isAdmin): ?>
        <div class="role-badge role-admin">ADMIN</div>
    <?php elseif ($isTech): ?>
        <div class="role-badge" style="background:rgba(245,158,11,.15);color:#f59e0b;border:1px solid rgba(245,158,11,.3);">TECHNICAL</div>
    <?php else: ?>
        <div class="role-badge" style="background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.3);">CLIENT</div>
    <?php endif; ?>

    <nav class="sidebar-nav">
        <?php if ($isAdmin): ?>
            <a href="../Admin/dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <?php elseif ($isTech): ?>
            <a href="../Tech/dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <?php else: ?>
            <a href="../Client/client_dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <?php endif; ?>

        <a href="tools.php" class="nav-link active"><i class="fa fa-wrench"></i> Tools</a>

        <?php if ($isClient): ?>
            <a href="../Client/ToolSpecification.php" class="nav-link">
                <i class="fa fa-plus"></i> Add Tool
            </a>
        <?php endif; ?>


        <a href="categories.php" class="nav-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
        </a>


        <?php if ($isAdmin): ?>
            <a href="../Admin/members.php"      class="nav-link"><i class="fa fa-users"></i> Members</a>
            <a href="../Admin/reservations.php" class="nav-link"><i class="fa fa-calendar"></i> Reservations</a>
            <a href="../Admin/chat.php"         class="nav-link"><i class="fa fa-comments"></i> Chat</a>
            <a href="../Admin/reports.php"      class="nav-link"><i class="fa fa-scale-balanced"></i> Disputes &amp; Reports</a>
        <?php elseif ($isTech): ?>
            <a href="../Tech/reservations.php"  class="nav-link"><i class="fa fa-calendar"></i> Reservations</a>
            <a href="../Admin/reports.php"      class="nav-link"><i class="fa fa-scale-balanced"></i> Reports</a>
        <?php else: ?>
            <a href="../Client/chat.php"          class="nav-link"><i class="fa fa-comments"></i> Chat</a>


        <a href="../Client/ToolCompatibility.php" class="nav-link">
            <i class="fa fa-circle-check"></i> Compatibility Checker
        </a>

        <a href="../Client/DamageDeclaration.php" class="nav-link">
            <i class="fa fa-triangle-exclamation"></i> Damage Report
         </a>
        <?php endif; ?>
    </nav>
</aside>

<!-- ═══════════════════════════════
     RIGHT SIDE
═══════════════════════════════ -->
<div class="layout-right">

    <!-- TOPBAR -->
    <header class="topbar">
        <button class="hamburger" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="fa fa-bars"></i>
        </button>
        <div class="topbar-title">Tool Details</div>
        <div class="topbar-right">
            <div class="search-wrap">
                <input type="text" class="search-input" placeholder="Search tools…">
                <i class="fa fa-search search-icon" style="font-size:12px;"></i>
            </div>
            <button class="avatar-btn admin-avatar">
                <?= htmlspecialchars($_SESSION['name']) ?>
                <span><?= ucfirst($role) ?></span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </header>

    <!-- SUBBAR -->
    <div class="subbar">
        <div class="breadcrumb">
            <a href="<?= $isAdmin ? '../Admin/dashboard.php' : ($isTech ? '../Tech/dashboard.php' : '../Client/client_dashboard.php') ?>">Dashboard</a>
            <span>›</span>
            <a href="tools.php">Tools</a>
            <span>›</span>
            <span class="bc-active"><?= htmlspecialchars($tool['name'] ?? 'Tool Details') ?></span>
        </div>

        <div class="action-btns">

            <?php if ($isAdmin): ?>
                <button class="btn btn-outline"
                        onclick="openModal('editToolModal', <?= $tool['tool_id'] ?>)">
                    <i class="fa fa-pen"></i> Edit Tool
                </button>

                <button class="btn btn-solid"
                        onclick="openModal('certModal', <?= $tool['tool_id'] ?>)">
                    <i class="fa fa-certificate"></i> Add Certification
                </button>
            <?php endif; ?>

            <?php if ($isAdmin || $isTech): ?>
                <button class="btn btn-solid"
                        onclick="openModal('maintModal', <?= $tool['tool_id'] ?>)">
                    <i class="fa fa-screwdriver-wrench"></i> Add Maintenance
                </button>

                <button class="btn btn-solid"
                        onclick="openModal('battModal', <?= $tool['tool_id'] ?>)">
                    <i class="fa fa-battery-half"></i> Update Battery
                </button>
            <?php endif; ?>

        </div>
    </div>

    <!-- MAIN -->
    <main class="main-content">

    <!-- ── HERO CARD ── -->
    <section class="hero-card card">

    <img src="<?= !empty($tool['image_path']) 
        ? '../Client/' . htmlspecialchars($tool['image_path']) 
        : '../../assets/img/default-tool.png' ?>" 
        alt="Tool Image" style="width: 170px;">

        <!-- Info -->
        <div class="hero-info">
            <div class="hero-title-row">
                <h2 class="tool-name-lg"><?= htmlspecialchars($tool['name'] ?? '—') ?></h2>

                <?php
                $avail     = $tool['availability'] ?? 0;
                $state     = strtolower($tool['state'] ?? '');
                $badge_cls = $avail ? 'badge--available' : 'badge--unavailable';
                $badge_txt = $avail ? 'Available' : 'Unavailable';

                if (str_contains($state,'maintenance')){
                    $badge_cls='badge--maintenance';
                    $badge_txt='Maintenance';
                }
                ?>

                <span class="badge <?= $badge_cls ?>"><?= $badge_txt ?></span>
            </div>

            <p class="tool-id-sub">Tool ID: #<?= $tool['tool_id'] ?? '—' ?></p>

            <div class="info-grid">
                <div class="info-row">
                    <span class="info-label">Category:</span>
                    <span><?= htmlspecialchars($tool['category_name'] ?? '—') ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Base Price:</span>
                    <span class="accent">$<?= $tool['base_price'] ?? '—' ?> / day</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Owner:</span>
                    <span><?= htmlspecialchars($tool['owner_name'] ?? '—') ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Condition:</span>
                    <span><?= htmlspecialchars($tool['state'] ?? '—') ?></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Added On:</span>
                    <span><?= isset($tool['created_at']) ? date('d M Y', strtotime($tool['created_at'])) : '—' ?></span>
                </div>

                <?php if (!empty($tool['warranty'])): ?>
                <div class="info-row">
                    <span class="info-label">Warranty:</span>
                    <span class="accent"><?= htmlspecialchars($tool['warranty']) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="desc-block">
                <span class="desc-label">Description</span>
                <p class="desc-text"><?= nl2br(htmlspecialchars($tool['description'] ?? 'No description provided.')) ?></p>
            </div>
        </div>

        <!-- Stats -->
        <div class="hero-stats">

        <?php if ($isClient && $tool['owner_id'] != $_SESSION['user_id']): ?>
            <div style="margin-top:auto; padding-top:12px; border-top:1px solid var(--border);">
                <button class="btn btn-solid"
                        style="width:100%;"
                        onclick="openModal('reserveModal', <?= $tool['tool_id'] ?>)">
                    <i class="fa fa-calendar-plus"></i> Reserve
                </button>
            </div>
        <?php endif; ?>

        </div>
    </section>

    <!-- ── PANELS ── -->
    <div class="panels-grid">

        <!-- CERTIFICATIONS -->
        <div class="card panel">
            <div class="panel-header">
                <div class="panel-title-wrap">
                    <i class="fa fa-certificate" style="color:var(--red);"></i>
                    <h3 class="panel-title">Certifications</h3>
                </div>

                <?php if ($isAdmin): ?>
                    <button class="add-btn"
                            onclick="openModal('certModal', <?= $tool['tool_id'] ?>)">+ Add</button>
                <?php endif; ?>
            </div>

            <table class="data-table">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Issue</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <?php if ($isAdmin): ?><th></th><?php endif; ?>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($certifications)): ?>
                    <tr><td colspan="<?= $isAdmin ? 5 : 4 ?>" class="empty-td">No certifications yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($certifications as $cert):
                        $exp_ts = strtotime($cert['expiry_date']);
                        $is_exp = $exp_ts < time();
                        $tag_cls = $is_exp ? 'tag--expired' : 'tag--valid';
                        $tag_txt = $is_exp ? 'Expired' : 'Valid';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($cert['type']) ?></td>
                        <td><?= date('d M Y', strtotime($cert['issue_date'])) ?></td>
                        <td><?= date('d M Y', $exp_ts) ?></td>
                        <td><span class="tag <?= $tag_cls ?>"><?= $tag_txt ?></span></td>

                        <?php if ($isAdmin): ?>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_cert" value="<?= $cert['id'] ?>">
                                <input type="hidden" name="tool_id" value="<?= $tool['tool_id'] ?>">
                                <button type="submit" class="row-btn del"
                                        onclick="return confirm('Delete?')">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- MAINTENANCE -->
        <div class="card panel">
            <div class="panel-header">
                <div class="panel-title-wrap">
                    <i class="fa fa-screwdriver-wrench" style="color:var(--red);"></i>
                    <h3 class="panel-title">Maintenance History</h3>
                </div>

                <?php if ($isAdmin || $isTech): ?>
                    <button class="add-btn"
                            onclick="openModal('maintModal', <?= $tool['tool_id'] ?>)">+ Add</button>
                <?php endif; ?>
            </div>

            <table class="data-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Action</th>
                    <th>Notes</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($maintenance)): ?>
                    <tr><td colspan="3" class="empty-td">No maintenance records yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($maintenance as $m): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($m['date'])) ?></td>
                        <td><?= htmlspecialchars($m['action']) ?></td>
                        <td style="color:var(--text-dim);"><?= htmlspecialchars($m['notes']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- BATTERY -->
        <div class="card panel">
            <div class="panel-header">
                <div class="panel-title-wrap">
                    <i class="fa fa-battery-half" style="color:var(--red);"></i>
                    <h3 class="panel-title">Battery Status</h3>
                </div>

                <?php if ($isAdmin || $isTech): ?>
                    <button class="add-btn"
                            onclick="openModal('battModal', <?= $tool['tool_id'] ?>)">+ Update</button>
                <?php endif; ?>
            </div>

            <?php if (empty($battery)): ?>
                <p class="empty-td">No battery data yet.</p>
            <?php else: ?>
                <div class="battery-body">
                    <div class="battery-stats">
                        <div class="bstat-row">
                            <span class="bstat-label">Charge Cycles</span>
                            <span class="bstat-val"><?= htmlspecialchars($battery['charge_cycles'] ?? '0') ?></span>
                        </div>

                        <div class="bstat-row">
                            <span class="bstat-label">Health Status</span>
                            <span class="bstat-val"
                                  style="color:<?= $battery['health_status'] === 'Good' ? '#2dbe6c' : 'var(--red)' ?>">
                                <?= htmlspecialchars($battery['health_status'] ?? '—') ?>
                            </span>
                        </div>

                        <div class="bstat-row">
                            <span class="bstat-label">Last Checked</span>
                            <span class="bstat-val">
                                <?= isset($battery['last_checked']) ? date('d M Y', strtotime($battery['last_checked'])) : '—' ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>

</main>
</div><!-- /.layout-right -->

<!-- ═══════════════════════════════
     MODALS
═══════════════════════════════ -->

<!-- Edit Tool — admin only -->
<?php if ($isAdmin && !empty($tool)): ?>
<div class="modal-overlay" id="editToolModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit Tool</h3>
            <button type="button" class="modal-close" onclick="closeModal('editToolModal')">✕</button>
        </div>

        <form method="POST">
            <input type="hidden" name="edit_tool" value="1">
            <input type="hidden" name="tool_id" value="<?= intval($tool['tool_id']) ?>">

            <div class="modal-body">

                <!-- Tool Name -->
                <label>Tool Name
                    <input type="text"
                           name="name"
                           value="<?= htmlspecialchars($tool['name'] ?? '') ?>"
                           required>
                </label>

                <!-- Condition (IMPORTANT: match DB ENUM) -->
                <label>Condition
                    <select name="state" required>
                        <?php
                        $states = ['good','needs_maintenance','broken','unavailable'];
                        foreach ($states as $s):
                        ?>
                            <option value="<?= $s ?>"
                                <?= ($tool['state'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= ucfirst(str_replace('_',' ', $s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <!-- Price -->
                <label>Base Price / Day ($)
                    <input type="number"
                           name="base_price"
                           value="<?= floatval($tool['base_price'] ?? 0) ?>"
                           step="0.01"
                           min="0"
                           required>
                </label>

                <!-- Availability -->
                <label>Availability
                    <select name="availability" required>
                        <option value="1" <?= !empty($tool['availability']) ? 'selected' : '' ?>>
                            Available
                        </option>
                        <option value="0" <?= empty($tool['availability']) ? 'selected' : '' ?>>
                            Unavailable
                        </option>
                    </select>
                </label>

                <!-- Description -->
                <label>Description
                    <textarea name="description" rows="3"><?= htmlspecialchars($tool['description'] ?? '') ?></textarea>
                </label>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('editToolModal')">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>


<!-- Add Certification — admin only -->
<?php if ($isAdmin && !empty($tool)): ?>
<div class="modal-overlay" id="certModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Certification</h3>
            <button type="button" class="modal-close" onclick="closeModal('certModal')">✕</button>
        </div>

        <form method="POST">
            <input type="hidden" name="add_cert" value="1">
            <input type="hidden" name="tool_id" value="<?= intval($tool['tool_id']) ?>">

            <div class="modal-body">

                <!-- Type -->
                <label>Type
                    <input type="text"
                           name="type"
                           placeholder="e.g. ISO 9001"
                           required>
                </label>

                <!-- Issue Date -->
                <label>Issue Date
                    <input type="date"
                           name="issue_date"
                           max="<?= date('Y-m-d') ?>"
                           required>
                </label>

                <!-- Expiry Date -->
                <label>Expiry Date
                    <input type="date"
                           name="expiry_date"
                           required>
                </label>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('certModal')">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>


<!-- Add Maintenance — admin + technical -->
<?php if (($isAdmin || $isTech) && !empty($tool)): ?>
<div class="modal-overlay" id="maintModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Add Maintenance Record</h3>
            <button type="button" class="modal-close" onclick="closeModal('maintModal')">✕</button>
        </div>

        <form method="POST">
            <input type="hidden" name="add_maintenance" value="1">
            <input type="hidden" name="tool_id" value="<?= intval($tool['tool_id']) ?>">

            <div class="modal-body">

                <!-- Date -->
                <label>Date
                    <input type="date"
                           name="date"
                           max="<?= date('Y-m-d') ?>"
                           required>
                </label>

                <!-- Action -->
                <label>Action
                    <input type="text"
                           name="action"
                           placeholder="e.g. Inspection"
                           required>
                </label>

                <!-- Notes -->
                <label>Notes
                    <input type="text"
                           name="notes"
                           placeholder="Short notes">
                </label>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('maintModal')">Cancel</button>
                <button type="submit" class="btn-save">Save</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Update Battery — admin + technical -->
<?php if ($isAdmin || $isTech): ?>
<div class="modal-overlay" id="battModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Update Battery Log</h3>
            <button type="button" class="modal-close" onclick="closeModal('battModal')">✕</button>
        </div>

        <form method="POST">
            <input type="hidden" name="update_battery" value="1">
            <input type="hidden" name="tool_id" value="<?= intval($tool['tool_id'] ?? 0) ?>">

            <div class="modal-body">

                <!-- Charge Cycles -->
                <label>
                    Charge Cycles
                    <input type="number"
                           name="charge_cycles"
                           value="<?= htmlspecialchars($battery['charge_cycles'] ?? 0) ?>"
                           min="0"
                           required>
                </label>

                <!-- Health Status -->
                <label>
                    Health Status
                    <select name="health_status" required>
                        <?php 
                        $currentStatus = $battery['health_status'] ?? '';
                        foreach(['Good','Fair','Poor','Critical'] as $hs): 
                        ?>
                            <option value="<?= $hs ?>" <?= $currentStatus === $hs ? 'selected' : '' ?>>
                                <?= $hs ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <!-- Last Checked -->
                <label>
                    Last Checked
                    <input type="date"
                           name="last_checked"
                           value="<?= !empty($battery['last_checked']) ? date('Y-m-d', strtotime($battery['last_checked'])) : date('Y-m-d') ?>"
                           required>
                </label>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('battModal')">
                    Cancel
                </button>
                <button type="submit" class="btn-save">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
<?php $display = ($tool['owner_id'] == $_SESSION['user_id']) ? 'none' : 'block'; ?>
<!-- Reserve Tool — client only -->
<?php if ($isClient && !empty($tool) && $tool['owner_id'] != $_SESSION['user_id']): ?>
<div class="modal-overlay" id="reserveModal">
    <div class="modal">
        <div class="modal-header">
            <h3 style="display: <?= $display ?>;">Reserve Tool</h3>
            <button type="button" class="modal-close" onclick="closeModal('reserveModal')">✕</button>
        </div>

        <form method="POST" action="../Client/reserve.php">
            <input type="hidden" name="tool_id" value="<?= intval($tool['tool_id']) ?>">

            <div class="modal-body">

                <!-- Start Date -->
                <label>Start Date
                    <input type="date"
                           name="start_date"
                           min="<?= date('Y-m-d') ?>"
                           required>
                </label>

                <!-- End Date -->
                <label>End Date
                    <input type="date"
                           name="end_date"
                           min="<?= date('Y-m-d') ?>"
                           required>
                </label>

                <!-- PRICE BOX -->
                <div style="
                    background:var(--surface2);
                    border:1px solid var(--border2);
                    border-radius:8px;
                    padding:12px;
                    font-size:12px;
                    color:var(--text-muted);
                ">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span>Price per day</span>
                        <span style="color:var(--text);">
                            $<?= number_format(floatval($tool['base_price'] ?? 0), 2) ?>
                        </span>
                    </div>

                    <div style="display:flex;justify-content:space-between;">
                        <span>Total</span>
                        <span style="color:var(--red);font-weight:700;" id="totalPrice">—</span>
                    </div>
                </div>

                <!-- TOOL NOT AVAILABLE WARNING -->
                <?php if (empty($tool['availability'])): ?>
                    <p style="color:var(--red);font-size:12px;margin-top:8px;">
                        This tool is currently unavailable for reservation.
                    </p>
                <?php endif; ?>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('reserveModal')">
                    Cancel
                </button>

                <button type="submit"
                        class="btn-save"
                        <?= empty($tool['availability']) ? 'disabled' : '' ?>>
                    Confirm Reservation
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<script>
/* ── Modal helpers ── */
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(el =>
    el.addEventListener('click', e => { if (e.target === el) el.classList.remove('open'); })
);

/* ── Battery gauge ── */
(function(){
    const canvas = document.getElementById('batteryGauge');
    if (!canvas) return;

    <?php
    $health_pct = 0;
    if (!empty($battery)) {
        $health_pct = match($battery['health_status']) {
            'Good'     => 87,
            'Fair'     => 55,
            'Poor'     => 30,
            'Critical' => 10,
            default    => 50
        };
    }
    ?>
    const pct = <?= $health_pct ?>;
    document.getElementById('gaugePct').textContent = pct + '%';

    const ctx = canvas.getContext('2d');
    const cx = 80, cy = 90, r = 70;
    const startAngle = Math.PI;
    const endAngle   = Math.PI * 2;
    const fillAngle  = startAngle + (pct / 100) * Math.PI;
    const color      = pct > 60 ? '#2dbe6c' : pct > 30 ? '#fbbf24' : '#e63946';

    ctx.clearRect(0, 0, 160, 100);

    // track
    ctx.beginPath();
    ctx.arc(cx, cy, r, startAngle, endAngle);
    ctx.strokeStyle = '#252525';
    ctx.lineWidth   = 10;
    ctx.stroke();

    // fill
    ctx.beginPath();
    ctx.arc(cx, cy, r, startAngle, fillAngle);
    ctx.strokeStyle = color;
    ctx.lineWidth   = 10;
    ctx.lineCap     = 'round';
    ctx.stroke();
})();

/* ── Reserve price calc ── */
<?php if ($isClient): ?>
(function(){
    const price    = <?= floatval($tool['base_price'] ?? 0) ?>;
    const startEl  = document.querySelector('#reserveModal [name=start_date]');
    const endEl    = document.querySelector('#reserveModal [name=end_date]');
    const totalEl  = document.getElementById('totalPrice');
    if (!startEl || !endEl) return;

    function calc(){
        const s = new Date(startEl.value), e = new Date(endEl.value);
        if (s && e && e > s) {
            const days = Math.ceil((e - s) / 86400000);
            totalEl.textContent = '$' + (days * price).toFixed(2) + ' (' + days + ' days)';
        } else {
            totalEl.textContent = '—';
        }
    }
    startEl.addEventListener('change', calc);
    endEl.addEventListener('change', calc);
})();
<?php endif; ?>
</script>

</body>
</html>