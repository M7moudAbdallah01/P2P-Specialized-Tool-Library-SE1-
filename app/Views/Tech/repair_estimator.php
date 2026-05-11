<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technical') {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);


$r = $conn->query("SELECT COUNT(*) AS c FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['c'];

$r = $conn->query("SELECT COUNT(*) AS c FROM repair_requests WHERE status = 'pending'");
$pending_damage = $r->fetch_assoc()['c'];
function tableExists($conn, $table) {
    $t = $conn->real_escape_string($table);
    $r = $conn->query("SHOW TABLES LIKE '$t'");
    return $r && $r->num_rows > 0;
}

$parts = [];
if (tableExists($conn, 'parts')) {
    $parts_result = $conn->query("SELECT * FROM parts ORDER BY name");
    if ($parts_result) while ($row = $parts_result->fetch_assoc()) $parts[] = $row;
}

$faults = [];
if (tableExists($conn, 'fault_templates')) {
    $faults_result = $conn->query("SELECT * FROM fault_templates ORDER BY fault_name");
    if ($faults_result) while ($row = $faults_result->fetch_assoc()) $faults[] = $row;
}

$tools_result = $conn->query("SELECT tool_id, name FROM tools ORDER BY name");
$tools = [];
if ($tools_result) {
    while ($row = $tools_result->fetch_assoc()) $tools[] = $row;
}

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_estimate'])) {
    $tool_id     = intval($_POST['tool_id'] ?? 0);
    $fault       = $conn->real_escape_string(trim($_POST['fault_description'] ?? ''));
    $parts_cost  = floatval($_POST['parts_cost'] ?? 0);
    $labor_hours = floatval($_POST['labor_hours'] ?? 0);
    $labor_rate  = floatval($_POST['labor_rate'] ?? 50);
    $notes       = $conn->real_escape_string(trim($_POST['notes'] ?? ''));
    $total       = $parts_cost + ($labor_hours * $labor_rate);

    if ($tool_id && $fault) {
        $saved = false;
        if (tableExists($conn, 'repair_estimates')) {
            $stmt = $conn->prepare("
                INSERT INTO repair_estimates (tool_id, fault_description, parts_cost, labor_hours, labor_rate, total_cost, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            if ($stmt) {
                $stmt->bind_param("isddddsi", $tool_id, $fault, $parts_cost, $labor_hours, $labor_rate, $total, $notes, $uid);
                if ($stmt->execute()) { $success = "Estimate saved successfully!"; $saved = true; }
                else { $error = $stmt->error; }
                $stmt->close();
            }
        }
        if (!$saved && !$error) {
            $action_text = $conn->real_escape_string("Repair Estimate — $fault (Total: " . number_format($total,2) . " EGP)");
            $chk = $conn->query("SHOW COLUMNS FROM maintenance_logs");
            $ml_c = [];
            if ($chk) while ($cc = $chk->fetch_assoc()) $ml_c[] = $cc['Field'];

            if (in_array('action', $ml_c) && in_array('date', $ml_c)) {
                $conn->query("INSERT INTO maintenance_logs (tool_id, action, date) VALUES ($tool_id, '$action_text', NOW())");
            } elseif (in_array('description', $ml_c)) {
                $conn->query("INSERT INTO maintenance_logs (tool_id, description, date) VALUES ($tool_id, '$action_text', NOW())");
            } else {
                $conn->query("INSERT INTO maintenance_logs (tool_id) VALUES ($tool_id)");
            }
            $success = "Estimate recorded in maintenance logs.";
        }
    } else {
        $error = "Please select a tool and describe the fault.";
    }
}

$estimates = [];
if (tableExists($conn, 'repair_estimates')) {
    $res = $conn->query("
        SELECT re.*, t.name AS tool_name
        FROM repair_estimates re
        LEFT JOIN tools t ON re.tool_id = t.tool_id
        ORDER BY re.created_at DESC LIMIT 8
    ");
    if ($res) while ($row = $res->fetch_assoc()) $estimates[] = $row;
}
if (empty($estimates)) {
    $ml_cols_res = $conn->query("SHOW COLUMNS FROM maintenance_logs");
    $ml_cols = [];
    if ($ml_cols_res) while ($c = $ml_cols_res->fetch_assoc()) $ml_cols[] = $c['Field'];

    $sel_action = in_array('action', $ml_cols)   ? 'ml.action' : "''";
    $sel_date   = in_array('date', $ml_cols)      ? 'ml.date'   : (in_array('created_at', $ml_cols) ? 'ml.created_at' : 'NULL');
    $where_part = in_array('action', $ml_cols)    ? "WHERE ml.action LIKE '%Estimate%'" : '';

    $res = $conn->query("
        SELECT ml.*, $sel_action AS fault_description, $sel_date AS created_at,
               0 AS total_cost, t.name AS tool_name
        FROM maintenance_logs ml
        LEFT JOIN tools t ON ml.tool_id = t.tool_id
        $where_part
        ORDER BY ml.id DESC LIMIT 8
    ");
    if ($res) while ($row = $res->fetch_assoc()) $estimates[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tool Hub - Repair Estimator</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/tech.css">

</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><img src="../../assets/images/logo.png" alt="Tool Hub Logo"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>
    <div class="role-badge" style="background:#0ea5e9;color:#fff;">TECH</div>
    <div class="sidebar-nav">
        <a href="dashboard.php" class="nav-link "><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="repair_estimator.php" class="nav-link active"><i class="fa fa-calculator"></i> Repair Estimator</a>
        <a href="external_repair.php" class="nav-link"><i class="fa fa-arrow-up-right-from-square"></i> External Repairs</a>
        <a href="reports.php" class="nav-link ">
            <i class="fa fa-screwdriver-wrench"></i> Repair Requests
            <?php if ($pending_damage > 0): ?>
                <span class="nav-count"><?= $pending_damage ?></span>
            <?php endif; ?>
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?><span class="nav-count"><?= $unread_msgs ?></span><?php endif; ?>
        </a>
    </div>
</div>

<!-- RIGHT -->
<div class="layout-right">
    <div class="topbar">
        <button class="hamburger"><i class="fa fa-bars"></i></button>
        <div class="topbar-title">Repair Estimator</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#0ea5e9;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Tech</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <div class="main-content">

        <div class="page-header">
            <div>
                <h1><i class="fa fa-calculator" style="color:var(--red);margin-right:8px;"></i>Repair Cost Estimator</h1>
                <p>Calculate and record repair cost estimates for tool faults.</p>
            </div>
        </div>

        <?php if ($success): ?><div class="alert alert-success"><i class="fa fa-circle-check"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-error"><i class="fa fa-circle-xmark"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="two-col">

            <!-- ESTIMATOR FORM -->
            <div>
                <!-- FAULT TEMPLATES -->
                <?php if (!empty($faults)): ?>
                <div class="card" style="margin-bottom:20px;">
                    <div class="card-title"><i class="fa fa-list-check" style="color:var(--red);"></i> Common Fault Templates</div>
                    <div class="fault-grid">
                        <?php foreach ($faults as $f): ?>
                        <div class="fault-chip" onclick="applyFault(<?= htmlspecialchars(json_encode($f)) ?>)">
                            <div class="fc-name"><?= htmlspecialchars($f['fault_name']) ?></div>
                            <div class="fc-cost">≈ <?= number_format($f['estimated_cost'], 0) ?> EGP
                                <?php if ($f['labor_hours'] ?? null): ?> · <?= $f['labor_hours'] ?>h labor<?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p style="font-size:.75rem;color:var(--text-muted);margin-top:8px;">Click a template to auto-fill the form</p>
                </div>
                <?php endif; ?>

                <!-- FORM -->
                <div class="card">
                    <div class="card-title"><i class="fa fa-pen-to-square" style="color:var(--red);"></i> New Estimate</div>
                    <form method="POST">
                        <div class="form-group">
                            <label>Tool</label>
                            <select name="tool_id" id="tool_id" class="form-control" required>
                                <option value="">— Select Tool —</option>
                                <?php foreach ($tools as $t): ?>
                                <option value="<?= $t['tool_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Fault Description</label>
                            <textarea name="fault_description" id="fault_desc" class="form-control" placeholder="Describe the problem…" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Parts Cost (EGP)</label>
                                <input type="number" name="parts_cost" id="parts_cost" class="form-control" placeholder="0.00" min="0" step="0.01" value="0" oninput="updateTotal()">
                            </div>
                            <div class="form-group">
                                <label>Labor Hours</label>
                                <input type="number" name="labor_hours" id="labor_hours" class="form-control" placeholder="0" min="0" step="0.5" value="0" oninput="updateTotal()">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Labor Rate / Hour (EGP)</label>
                            <input type="number" name="labor_rate" id="labor_rate" class="form-control" placeholder="50" min="0" step="1" value="50" oninput="updateTotal()">
                        </div>

                        <!-- LIVE COST PREVIEW -->
                        <div class="cost-preview">
                            <div class="cost-line">
                                <span>Parts Cost</span>
                                <span class="cost-val" id="prev_parts">0.00 EGP</span>
                            </div>
                            <div class="cost-line">
                                <span>Labor Cost <small id="prev_labor_detail" style="color:var(--text-muted);"></small></span>
                                <span class="cost-val" id="prev_labor">0.00 EGP</span>
                            </div>
                            <div class="cost-line total">
                                <span>TOTAL ESTIMATE</span>
                                <span class="cost-val" id="prev_total">0.00 EGP</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea name="notes" class="form-control" placeholder="Optional notes…" rows="2"></textarea>
                        </div>

                        <div style="display:flex;gap:10px;">
                            <button type="submit" name="save_estimate" class="btn btn-red">
                                <i class="fa fa-floppy-disk"></i> Save Estimate
                            </button>
                            <button type="reset" class="btn btn-outline" onclick="resetCalc()">
                                <i class="fa fa-rotate-left"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- RECENT ESTIMATES -->
            <div class="card">
                <div class="card-title"><i class="fa fa-clock-rotate-left" style="color:var(--red);"></i> Recent Estimates</div>
                <?php if (empty($estimates)): ?>
                    <div style="text-align:center;padding:40px;color:var(--text-muted);">
                        <i class="fa fa-file-invoice" style="font-size:2.5rem;opacity:.2;"></i>
                        <p style="margin-top:12px;">No estimates recorded yet.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="est-table">
                            <thead>
                                <tr>
                                    <th>Tool</th>
                                    <th>Fault</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estimates as $e): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($e['tool_name'] ?? 'N/A') ?></strong></td>
                                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($e['fault_description'] ?? '') ?>">
                                        <?= htmlspecialchars($e['fault_description'] ?? '—') ?>
                                    </td>
                                    <td><span class="badge badge-blue"><?= number_format($e['total_cost'] ?? 0, 0) ?> EGP</span></td>
                                    <td style="color:var(--text-muted);"><?= $e['created_at'] ? date('M d, Y', strtotime($e['created_at'])) : '—' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- PARTS QUICK REFERENCE -->
                <?php if (!empty($parts)): ?>
                <div style="margin-top:24px;">
                    <div class="card-title" style="margin-top:0;"><i class="fa fa-coins" style="color:var(--red);"></i> Parts Quick Ref</div>
                    <div style="overflow-x:auto;">
                        <table class="est-table">
                            <thead><tr><th>Part</th><th>Category</th><th>Unit Cost</th></tr></thead>
                            <tbody>
                                <?php foreach (array_slice($parts, 0, 8) as $p): ?>
                                <tr style="cursor:pointer;" onclick="addPartCost(<?= floatval($p['unit_cost']) ?>)" title="Click to add to parts cost">
                                    <td><?= htmlspecialchars($p['name']) ?></td>
                                    <td style="color:var(--text-muted);"><?= htmlspecialchars($p['category'] ?? '—') ?></td>
                                    <td><span class="badge badge-blue"><?= number_format($p['unit_cost'], 0) ?> EGP</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php if (count($parts) > 8): ?>
                            <p style="font-size:.75rem;color:var(--text-muted);margin-top:8px;">+<?= count($parts)-8 ?> more — <a href="cost-of-parts.php" style="color:var(--red);">View all</a></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
function updateTotal() {
    const parts  = parseFloat(document.getElementById('parts_cost').value)  || 0;
    const hours  = parseFloat(document.getElementById('labor_hours').value)  || 0;
    const rate   = parseFloat(document.getElementById('labor_rate').value)   || 0;
    const labor  = hours * rate;
    const total  = parts + labor;
    const fmt = n => n.toLocaleString('en-EG', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' EGP';
    document.getElementById('prev_parts').textContent  = fmt(parts);
    document.getElementById('prev_labor').textContent  = fmt(labor);
    document.getElementById('prev_total').textContent  = fmt(total);
    document.getElementById('prev_labor_detail').textContent = hours > 0 ? `(${hours}h × ${rate} EGP)` : '';
}

function resetCalc() {
    setTimeout(() => {
        ['parts_cost','labor_hours'].forEach(id => document.getElementById(id).value = 0);
        document.getElementById('labor_rate').value = 50;
        updateTotal();
    }, 10);
}

function addPartCost(cost) {
    const el = document.getElementById('parts_cost');
    el.value = (parseFloat(el.value)||0) + cost;
    updateTotal();
    el.style.borderColor = 'var(--red)';
    setTimeout(() => el.style.borderColor = '', 600);
}

function applyFault(f) {
    if (f.fault_name) document.getElementById('fault_desc').value = f.fault_name + (f.notes ? '\n' + f.notes : '');
    if (f.estimated_cost) { document.getElementById('parts_cost').value = f.estimated_cost; }
    if (f.labor_hours)    { document.getElementById('labor_hours').value = f.labor_hours; }
    updateTotal();
}

document.querySelector('.hamburger')?.addEventListener('click', () => {
    document.querySelector('.sidebar').classList.toggle('open');
});

updateTotal();
</script>
</body>
</html>