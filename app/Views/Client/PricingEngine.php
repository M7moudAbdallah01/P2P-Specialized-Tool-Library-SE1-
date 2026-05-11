<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

$db   = Database::getInstance();
$conn = $db->getConnection();

/* =========================================================
   بيانات اليوزر
========================================================= */
$user_id = $_SESSION['user_id'];

/* =========================================================
   بيانات الأداة
========================================================= */
$tool       = null;
$tool_id    = intval($_GET['tool_id'] ?? 0);
$start_date = $_GET['start_date'] ?? '';
$end_date   = $_GET['end_date'] ?? '';

if ($tool_id > 0) {

    // مهم: اسم الجدول categories مش category
    $stmt = $conn->prepare("
        SELECT t.*, c.name AS category_name
        FROM tools t
        LEFT JOIN category c 
            ON t.category_id = c.category_id
        WHERE t.tool_id = ?
    ");

    $stmt->bind_param("i", $tool_id);
    $stmt->execute();

    $tool = $stmt->get_result()->fetch_assoc();
}

/* =========================================================
   Membership
========================================================= */
$membership = 'basic';
$u = $conn->prepare("
    SELECT membership_tier
    FROM users
    WHERE user_id = ?
");

$u->bind_param("i", $user_id);
$u->execute();

$urow = $u->get_result()->fetch_assoc();

if ($urow) {
    $membership = strtolower($urow['membership_tier'] ?? 'standard');
}

/* =========================================================
   الخصومات
========================================================= */
$membership_discounts = [
    'basic'   => 0,
    'premium' => 15,
    'vip'     => 30
];

$user_discount = $membership_discounts[$membership] ?? 0;

/* =========================================================
   POST REQUESTS
========================================================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    header('Content-Type: application/json');

    /* =====================================================
       CALCULATE
    ===================================================== */
    if (isset($_POST['action']) && $_POST['action'] === 'calculate') {

        $tool_id_post = intval($_POST['tool_id'] ?? 0);
        $tier         = $_POST['tier'] ?? 'daily';
        $quantity     = max(1, intval($_POST['quantity'] ?? 1));

        if ($tool_id_post <= 0) {
            echo json_encode([
                "error" => "Invalid tool"
            ]);
            exit();
        }

        $t = $conn->prepare("
            SELECT name, base_price
            FROM tools
            WHERE tool_id = ?
        ");

        $t->bind_param("i", $tool_id_post);
        $t->execute();

        $toolData = $t->get_result()->fetch_assoc();

        if (!$toolData) {
            echo json_encode([
                "error" => "Tool not found"
            ]);
            exit();
        }

        $base_price = floatval($toolData['base_price']);

        $rates = [
            "hourly" => round($base_price / 8, 2),
            "daily"  => $base_price,
            "weekly" => round($base_price * 6, 2)
        ];

        $unit_rate = $rates[$tier] ?? $base_price;

        $base_total = $unit_rate * $quantity;

        $discount_amount = $base_total * ($user_discount / 100);

        $final_total = $base_total - $discount_amount;

        echo json_encode([
            "success"         => true,
            "tool"            => $toolData['name'],
            "tier"            => $tier,
            "quantity"        => $quantity,
            "unit_rate"       => $unit_rate,
            "base_total"      => $base_total,
            "discount"        => $discount_amount,
            "final_total"     => $final_total
        ]);

        exit();
    }

    /* =====================================================
       CONFIRM RESERVATION
    ===================================================== */
    if (isset($_POST['action']) && $_POST['action'] === 'confirm_reservation') {

        $tool_id_c   = intval($_POST['tool_id'] ?? 0);
        $start       = $_POST['start_date'] ?? '';
        $end         = $_POST['end_date'] ?? '';
        $final_price = floatval($_POST['final_price'] ?? 0);

        if (
            $tool_id_c <= 0 ||
            empty($start) ||
            empty($end)
        ) {

            echo json_encode([
                "success" => false,
                "error"   => "Missing required data"
            ]);

            exit();
        }

        /* =================================================
           INSERT RESERVATION
        ================================================= */
        $r = $conn->prepare("
            INSERT INTO reservations
            (
                user_id,
                tool_id,
                start_date,
                end_date,
                status
            )
            VALUES
            (
                ?, ?, ?, ?, 'pending'
            )
        ");

        $r->bind_param(
            "iiss",
            $user_id,
            $tool_id_c,
            $start,
            $end
        );

        if (!$r->execute()) {

            echo json_encode([
                "success" => false,
                "error"   => $conn->error
            ]);

            exit();
        }

        $reservation_id = $conn->insert_id;

        /* =================================================
           INSERT RENTAL
        ================================================= */
        $tier      = $_POST['tier'] ?? 'daily';
        $quantity  = intval($_POST['quantity'] ?? 1);

        $duration = $quantity . ' ' . $tier;

        $discount_applied = floatval($_POST['discount_amount'] ?? 0);

        $rl = $conn->prepare("
            INSERT INTO rentals
            (
                reservation_id,
                tool_id,
                renter_id,
                duration,
                final_price,
                discount_applied
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?
            )
        ");

        $rl->bind_param(
            "iiisdd",
            $reservation_id,
            $tool_id_c,
            $user_id,
            $duration,
            $final_price,
            $discount_applied
        );

        $rl->execute();

        $_SESSION['rental_id'] = $conn->insert_id;

        echo json_encode([
            "success"        => true,
            "reservation_id" => $reservation_id
        ]);

        exit();
    }
}

/* =========================================================
   حساب الأيام تلقائي
========================================================= */
$auto_days = 1;

if ($start_date && $end_date) {

    $diff = (
        strtotime($end_date) -
        strtotime($start_date)
    ) / 86400;

    if ($diff > 0) {
        $auto_days = (int)$diff;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tool Hub - Pricing Engine</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/Css/style.css">
    <link rel="stylesheet" href="../../assets/Css/admin.css">
    <link rel="stylesheet" href="../../assets/Css/PricingEngine.css">
</head>
<body>

<!-- ===================== SIDEBAR ===================== -->
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><img src="../../assets/images/logo.png" alt="Tool Hub Logo"></div>
        <div class="brand-text">TOOL HUB</div>
    </div>
    <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>
    <div class="sidebar-nav">
        <a href="dashboard.php"             class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
        <a href="../Tools/tools.php" class="nav-link"><i class="fa fa-wrench"></i> Tools</a>
        <a href="PricingEngine.php"         class="nav-link active"><i class="fa fa-tags"></i> Pricing</a>
    </div>
</div>

<!-- ===================== LAYOUT RIGHT ===================== -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Pricing Engine</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <span><?= htmlspecialchars($_SESSION['name'] ?? 'Client') ?></span>
            </button>
            <a href="../Auth/login.php" class="icon-btn"><i class="fa fa-right-from-bracket"></i></a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="pricing-page">
            <div class="pricing-container">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <h1><i class="fa fa-tags" style="color:#ff2e2e;margin-right:10px;"></i>Multi-Tier Pricing Engine</h1>
                    <span><i class="fa fa-circle" style="color:#4ade80;font-size:8px;margin-right:6px;"></i>Live Calculator</span>
                </div>

                <?php if ($tool): ?>
                <!-- ===== بانر بيانات الأداة (لو جاية من tool_details) ===== -->
                <div class="pricing-card" style="border-color:rgba(99,102,241,0.4);background:rgba(99,102,241,0.05);">
                    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                        <?php if (!empty($tool['image_path'])): ?>
                            <img src="../Client/<?= htmlspecialchars($tool['image_path']) ?>"
                                 style="width:70px;height:70px;object-fit:cover;border-radius:10px;border:1px solid var(--border);">
                        <?php endif; ?>
                        <div>
                            <div style="font-size:1.1rem;font-weight:700;color:var(--text);">
                                <?= htmlspecialchars($tool['name']) ?>
                            </div>
                            <div style="font-size:.82rem;color:var(--text-muted);margin-top:4px;">
                                <i class="fa fa-tag" style="color:#6366f1;margin-right:4px;"></i>
                                <?= htmlspecialchars($tool['category_name'] ?? '—') ?>
                                &nbsp;|&nbsp;
                                <i class="fa fa-dollar-sign" style="color:#4ade80;margin-right:4px;"></i>
                                $<?= number_format($tool['base_price'], 2) ?>/day
                                &nbsp;|&nbsp;
                                <i class="fa fa-circle" style="color:<?= $tool['availability'] ? '#4ade80' : '#e63946' ?>;font-size:8px;"></i>
                                <?= $tool['availability'] ? 'Available' : 'Unavailable' ?>
                            </div>
                            <?php if ($start_date && $end_date): ?>
                            <div style="font-size:.82rem;color:var(--text-muted);margin-top:4px;">
                                <i class="fa fa-calendar" style="color:#ff2e2e;margin-right:4px;"></i>
                                <?= date('d M Y', strtotime($start_date)) ?>
                                <span style="margin:0 6px;">→</span>
                                <?= date('d M Y', strtotime($end_date)) ?>
                                <span style="margin-left:8px;color:#4ade80;">(<?= $auto_days ?> day<?= $auto_days > 1 ? 's' : '' ?>)</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- ========== CARD 1: TOOL SELECTION ========== -->
                <div class="pricing-card">
                    <h2><i class="fa fa-wrench"></i> Select Tool</h2>
                    <div class="tool-selector-grid">
                        <div class="input-group">
                            <label>Tool</label>
                            <div class="input-box">
                                <select id="toolSelect" onchange="onToolChange()">
                                    <option value="">-- Choose a tool --</option>
                                    <?php
                                    // الأدوات الـ hardcoded + الأداة من DB لو موجودة
                                    // $hardcoded = [
                                    //     ["val"=>"drill",       "label"=>"Power Drill",     "h"=>15,  "d"=>80,  "w"=>400],
                                    //     ["val"=>"saw",         "label"=>"Circular Saw",    "h"=>20,  "d"=>110, "w"=>550],
                                    //     ["val"=>"compressor",  "label"=>"Air Compressor",  "h"=>25,  "d"=>130, "w"=>650],
                                    //     ["val"=>"welder",      "label"=>"Arc Welder",      "h"=>35,  "d"=>180, "w"=>900],
                                    //     ["val"=>"excavator",   "label"=>"Mini Excavator",  "h"=>120, "d"=>600, "w"=>2800],
                                    //     ["val"=>"generator",   "label"=>"Generator 5kW",   "h"=>30,  "d"=>160, "w"=>750],
                                    //     ["val"=>"scaffolding", "label"=>"Scaffolding Set", "h"=>18,  "d"=>95,  "w"=>460],
                                    // ];

                                    // لو في أداة من DB، حطها أول
                                    if ($tool) {
                                        $bp = floatval($tool['base_price']);
                                        $h  = round($bp / 8, 2);
                                        $w  = round($bp * 6, 2);
                                        $selected = 'selected';
                                        echo "<option value=\"db_{$tool['tool_id']}\"
                                                    data-hourly=\"{$h}\"
                                                    data-daily=\"{$bp}\"
                                                    data-weekly=\"{$w}\"
                                                    data-dbid=\"{$tool['tool_id']}\"
                                                    {$selected}>
                                                    {$tool['name']}
                                              </option>";
                                    }

                                    foreach ($hardcoded as $t) {
                                        echo "<option value=\"{$t['val']}\"
                                                    data-hourly=\"{$t['h']}\"
                                                    data-daily=\"{$t['d']}\"
                                                    data-weekly=\"{$t['w']}\">
                                                    {$t['label']}
                                              </option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Category</label>
                            <div class="input-box">
                                <select id="categoryDisplay" disabled>
                                    <option><?= $tool ? htmlspecialchars($tool['category_name'] ?? 'General') : 'Select a tool first' ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Rate Badges -->
                    <div class="rate-info-row" id="rateInfoRow" style="display:<?= $tool ? 'flex' : 'none' ?>;">
                        <div class="rate-badge">
                            <div class="rate-val" id="rBadgeHourly">
                                <?php if ($tool) echo '$' . round(floatval($tool['base_price']) / 8, 2); else echo '—'; ?>
                            </div>
                            <div class="rate-lbl">per Hour</div>
                        </div>
                        <div class="rate-badge">
                            <div class="rate-val" id="rBadgeDaily">
                                <?php if ($tool) echo '$' . $tool['base_price']; else echo '—'; ?>
                            </div>
                            <div class="rate-lbl">per Day</div>
                        </div>
                        <div class="rate-badge">
                            <div class="rate-val" id="rBadgeWeekly">
                                <?php if ($tool) echo '$' . round(floatval($tool['base_price']) * 6, 2); else echo '—'; ?>
                            </div>
                            <div class="rate-lbl">per Week</div>
                        </div>
                        <div class="rate-badge" style="border-color:rgba(255,46,46,0.3);">
                            <div class="rate-val" id="rBadgeBest" style="color:#4ade80;">Daily</div>
                            <div class="rate-lbl">Best Rate</div>
                        </div>
                    </div>
                </div>

                <!-- ========== CARD 2: DURATION ========== -->
                <div class="pricing-card">
                    <h2><i class="fa fa-clock"></i> Rental Duration</h2>
                    <div class="duration-tabs">
                        <button class="tab-btn" id="tab-hourly" onclick="switchTier('hourly', this)">
                            <i class="fa fa-hourglass-half"></i> Hourly
                        </button>
                        <button class="tab-btn active" id="tab-daily" onclick="switchTier('daily', this)">
                            <i class="fa fa-sun"></i> Daily
                        </button>
                        <button class="tab-btn" id="tab-weekly" onclick="switchTier('weekly', this)">
                            <i class="fa fa-calendar-week"></i> Weekly
                        </button>
                    </div>

                    <!-- Hourly -->
                    <div id="panel-hourly" class="duration-input-row" style="display:none;">
                        <div class="input-group">
                            <label>Number of Hours</label>
                            <div class="input-box">
                                <input type="number" id="inp-hours" min="1" max="23" value="1">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Start Date</label>
                            <div class="input-box">
                                <input type="date" id="inp-hours-date"
                                       value="<?= $start_date ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Daily -->
                    <div id="panel-daily" class="duration-input-row" style="display:grid;">
                        <div class="input-group">
                            <label>Number of Days</label>
                            <div class="input-box">
                                <input type="number" id="inp-days" min="1" max="30"
                                       value="<?= $auto_days ?>">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Start Date</label>
                            <div class="input-box">
                                <input type="date" id="inp-days-date"
                                       value="<?= $start_date ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Weekly -->
                    <div id="panel-weekly" class="duration-input-row" style="display:none;">
                        <div class="input-group">
                            <label>Number of Weeks</label>
                            <div class="input-box">
                                <input type="number" id="inp-weeks" min="1" max="52" value="1">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Start Date</label>
                            <div class="input-box">
                                <input type="date" id="inp-weeks-date"
                                       value="<?= $start_date ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ========== CARD 3: MEMBERSHIP ========== -->
                <div class="pricing-card">
                    <h2><i class="fa fa-crown"></i> Membership Tier</h2>
                            
                        <div class="membership-display-card">
                            <div class="membership-card selected" style="cursor:default;pointer-events:none;">
                                <div class="membership-badge badge-<?= htmlspecialchars($membership) ?>">
                                    <?= ucfirst($membership) ?>
                                </div>

                                <div class="discount-pct">
                                    <?= $user_discount ?>%
                                </div>

                                <div class="discount-label">
                                    Your Membership Tier
                                </div>

                                <div style="font-size:.75rem;color:#6366f1;margin-top:6px;">
                                    <i class="fa fa-lock"></i> Membership locked by system
                                </div>
                            </div>
                        </div>

                </div>

                <!-- ========== CARD 4: COST BREAKDOWN ========== -->
                <div class="pricing-card">
                    <h2><i class="fa fa-receipt"></i> Cost Breakdown</h2>
                    <div id="breakdownContent">
                        <div class="empty-state">
                            <i class="fa fa-calculator"></i>
                            <?= $tool ? 'Tool loaded! Click Calculate to see pricing.' : 'Select a tool and fill in the details, then click Calculate.' ?>
                        </div>
                    </div>
                    <button class="calculate-btn" onclick="calculate()">
                        <i class="fa fa-calculator"></i> Calculate Cost
                    </button>
                </div>

            </div><!-- /pricing-container -->
        </div><!-- /pricing-page -->
    </div><!-- /main-content -->
</div><!-- /layout-right -->

<!-- ===================== JAVASCRIPT ===================== -->
<script>
/* ---- State ---- */
let currentTier       = 'daily';
let currentDiscount   = <?= $user_discount ?>;
let currentMembership = '<?= $membership ?>';

// بيانات الأداة من PHP
const toolFromDB = <?= $tool ? json_encode([
    'id'         => $tool['tool_id'],
    'name'       => $tool['name'],
    'base_price' => floatval($tool['base_price']),
    'category'   => $tool['category_name'] ?? 'General',
    'hourly'     => round(floatval($tool['base_price']) / 8, 2),
    'daily'      => floatval($tool['base_price']),
    'weekly'     => round(floatval($tool['base_price']) * 6, 2),
]) : 'null' ?>;

const startDateFromDB = '<?= $start_date ?>';
const endDateFromDB   = '<?= $end_date ?>';
const autoDays        = <?= $auto_days ?>;

const toolCategories = {
    drill:'Power Tools', saw:'Power Tools', compressor:'Power Tools',
    welder:'Construction', excavator:'Heavy Machinery',
    generator:'Power Tools', scaffolding:'Construction'
};

/* ---- لما الصفحة تتحمّل، لو في أداة من DB ---- */
window.addEventListener('DOMContentLoaded', () => {
    if (toolFromDB) {
        document.getElementById('rBadgeHourly').textContent = '$' + toolFromDB.hourly;
        document.getElementById('rBadgeDaily').textContent  = '$' + toolFromDB.daily;
        document.getElementById('rBadgeWeekly').textContent = '$' + toolFromDB.weekly;
        document.getElementById('rBadgeBest').textContent   = 'Daily';
        document.getElementById('rateInfoRow').style.display = 'flex';

        // لو في تواريخ، حوّل للـ daily تلقائي
        if (autoDays > 0) {
            document.getElementById('inp-days').value = autoDays;
        }
    }
});

/* ---- Tool Changed ---- */
function onToolChange() {
    const sel = document.getElementById('toolSelect');
    const opt = sel.options[sel.selectedIndex];
    const val = sel.value;

    if (!val) {
        document.getElementById('rateInfoRow').style.display = 'none';
        document.getElementById('categoryDisplay').innerHTML = '<option>Select a tool first</option>';
        return;
    }

    const hourly = parseFloat(opt.dataset.hourly);
    const daily  = parseFloat(opt.dataset.daily);
    const weekly = parseFloat(opt.dataset.weekly);

    document.getElementById('rBadgeHourly').textContent = '$' + hourly;
    document.getElementById('rBadgeDaily').textContent  = '$' + daily;
    document.getElementById('rBadgeWeekly').textContent = '$' + weekly;

    const hPerHour = hourly;
    const dPerHour = daily / 8;
    const wPerHour = weekly / 40;
    let bestLabel = 'Hourly';
    if (dPerHour < hPerHour && dPerHour <= wPerHour) bestLabel = 'Daily';
    if (wPerHour < hPerHour && wPerHour < dPerHour)  bestLabel = 'Weekly';
    document.getElementById('rBadgeBest').textContent = bestLabel;

    const cat = toolCategories[val] || (toolFromDB && val.startsWith('db_') ? toolFromDB.category : 'General');
    document.getElementById('categoryDisplay').innerHTML = `<option>${cat}</option>`;
    document.getElementById('rateInfoRow').style.display = 'flex';
}

/* ---- Switch Tier ---- */
function switchTier(tier, btn) {
    currentTier = tier;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    ['hourly','daily','weekly'].forEach(t => {
        document.getElementById('panel-' + t).style.display = (t === tier) ? 'grid' : 'none';
    });
}
/* ---- Calculate ---- */
function calculate() {
    const sel = document.getElementById('toolSelect');
    const opt = sel.options[sel.selectedIndex];

    if (!sel.value) { showError('Please select a tool first.'); return; }

    let qty = 1;
    if      (currentTier === 'hourly') qty = document.getElementById('inp-hours').value;
    else if (currentTier === 'daily')  qty = document.getElementById('inp-days').value;
    else                               qty = document.getElementById('inp-weeks').value;

    // هل الأداة من DB؟
    const dbId = opt.dataset.dbid || (toolFromDB ? toolFromDB.id : '');

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:
            `action=calculate` +
            `&tool_name=${encodeURIComponent(opt.text)}` +
            `&tier=${currentTier}` +
            `&quantity=${qty}` +
            `&membership=${currentMembership}` +
            `&tool_id=${dbId}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) { showError(data.error); return; }

        // خزّن البيانات للـ confirm
        window._lastCalc = {
            tool_id:       dbId,
            tier:          currentTier,
            quantity:      qty,
            final_price:   data.final_total,
            discount_amount: data.discount,
            start_date:    startDateFromDB,
            end_date:      endDateFromDB
        };

        // حدّد end_date من الـ inputs لو مش موجود
        if (!window._lastCalc.start_date) {
            if (currentTier === 'hourly') window._lastCalc.start_date = document.getElementById('inp-hours-date').value;
            if (currentTier === 'daily')  window._lastCalc.start_date = document.getElementById('inp-days-date').value;
            if (currentTier === 'weekly') window._lastCalc.start_date = document.getElementById('inp-weeks-date').value;
        }

        document.getElementById('breakdownContent').innerHTML = `
            <table class="breakdown-table">
                <tr><td>Tool</td><td>${data.tool}</td></tr>
                <tr><td>Duration</td><td>${data.quantity} ${data.tier}</td></tr>
                <tr><td>Rate</td><td>$${data.unit_rate}</td></tr>
                <tr><td>Base Cost</td><td>$${data.base_total}</td></tr>
                <tr><td>Discount (${currentMembership})</td><td style="color:#4ade80;">-$${data.discount.toFixed(2)}</td></tr>
                <tr class="row-total">
                    <td>Total Due</td>
                    <td>$${data.final_total.toFixed(2)}</td>
                </tr>
            </table>
            ${dbId ? `
            <button class="calculate-btn" style="background:#4ade80;color:#000;margin-top:14px;" onclick="confirmReservation()">
                <i class="fa fa-calendar-check"></i> Confirm Reservation
            </button>` : ''}
        `;
    });
}

/* ---- Confirm Reservation ---- */
function confirmReservation() {
    const c = window._lastCalc;

    if (!c || !c.tool_id) {
        showError('Please calculate first.');
        return;
    }

    // تحديد start date
    let startDate = c.start_date;
    if (!startDate) {
        if (currentTier === 'hourly') startDate = document.getElementById('inp-hours-date').value;
        if (currentTier === 'daily')  startDate = document.getElementById('inp-days-date').value;
        if (currentTier === 'weekly') startDate = document.getElementById('inp-weeks-date').value;
    }

    if (!startDate) {
        showError('Start date is required.');
        return;
    }

    // حساب end date
    let endDate = c.end_date;

    if (!endDate) {
        let start = new Date(startDate);

        if (currentTier === 'hourly') {
            start.setDate(start.getDate() + 1);
        } 
        else if (currentTier === 'daily') {
            start.setDate(start.getDate() + parseInt(c.quantity));
        } 
        else if (currentTier === 'weekly') {
            start.setDate(start.getDate() + (parseInt(c.quantity) * 7));
        }

        endDate = start.toISOString().split('T')[0];
    }

    fetch(window.location.href, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:
            `action=confirm_reservation` +
            `&tool_id=${c.tool_id}` +
            `&start_date=${startDate}` +
            `&end_date=${endDate}` +
            `&final_price=${c.final_price}` +
            `&tier=${c.tier}` +
            `&quantity=${c.quantity}` +
            `&discount_amount=${c.discount_amount}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = `my-reservations.php?success=1&id=${data.reservation_id}`;
        } else {
            showError(data.error || 'Reservation failed.');
        }
    })
    .catch(err => {
        console.error(err);
        showError('Server error occurred.');
    });
}
/* ---- Error ---- */
function showError(msg) {
    document.getElementById('breakdownContent').innerHTML = `
        <div class="empty-state" style="color:#ff2e2e;">
            <i class="fa fa-circle-exclamation" style="color:#ff2e2e;"></i> ${msg}
        </div>`;
}
</script>
</body>
</html>