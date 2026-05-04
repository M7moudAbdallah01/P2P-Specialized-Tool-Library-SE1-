<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Tool Hub - Pricing Engine</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../../assets/Css/style.css">
    <link rel="stylesheet" href="../../../assets/Css/admin.css">
    <link rel="stylesheet" href="../../../assets/Css/PricingEngine.css">
</head>

<body>

    <!-- ===================== SIDEBAR ===================== -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Tool Hub Logo">
            </div>
            <div class="brand-text">TOOL HUB</div>
        </div>

        <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>

        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="fa fa-gauge"></i> Dashboard
            </a>
            <a href="my-tools.php" class="nav-link">
                <i class="fa fa-wrench"></i> My Tools
            </a>
            <a href="ToolSpecification.php" class="nav-link">
                <i class="fa fa-plus"></i> Add Tool
            </a>
            <a href="../Tools/categories.php" class="nav-link">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    style="flex-shrink:0">
                    <rect x="2" y="3" width="20" height="14" rx="2" />
                    <line x1="8" y1="21" x2="16" y2="21" />
                    <line x1="12" y1="17" x2="12" y2="21" />
                </svg> Categories
            </a>
            <a href="pricing.php" class="nav-link active">
                <i class="fa fa-tags"></i> Pricing
            </a>
            <a href="reservations.php" class="nav-link">
                <i class="fa fa-calendar"></i> Reservations
            </a>
            <a href="chat.php" class="nav-link">
                <i class="fa fa-comments"></i> Messages
            </a>
            <a href="reports.php" class="nav-link">
                <i class="fa fa-scale-balanced"></i> Reports
            </a>
        </div>
    </div>

    <!-- ===================== LAYOUT RIGHT ===================== -->
    <div class="layout-right">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">Pricing Engine</div>
            <div class="topbar-right">
                <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                    <span>Client</span>
                </button>
                <a href="../Auth/login.php" class="icon-btn">
                    <i class="fa fa-right-from-bracket"></i>
                </a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="pricing-page">
                <div class="pricing-container">

                    <!-- PAGE HEADER -->
                    <div class="page-header">
                        <h1><i class="fa fa-tags" style="color:#ff2e2e;margin-right:10px;"></i>Multi-Tier Pricing Engine
                        </h1>
                        <span><i class="fa fa-circle" style="color:#4ade80;font-size:8px;margin-right:6px;"></i>Live
                            Calculator</span>
                    </div>

                    <!-- ========== CARD 1: TOOL SELECTION ========== -->
                    <div class="pricing-card">
                        <h2><i class="fa fa-wrench"></i> Select Tool</h2>
                        <div class="tool-selector-grid">
                            <div class="input-group">
                                <label>Tool</label>
                                <div class="input-box">
                                    <select id="toolSelect" onchange="onToolChange()">
                                        <option value="">-- Choose a tool --</option>
                                        <option value="drill" data-hourly="15" data-daily="80" data-weekly="400">Power
                                            Drill</option>
                                        <option value="saw" data-hourly="20" data-daily="110" data-weekly="550">Circular
                                            Saw</option>
                                        <option value="compressor" data-hourly="25" data-daily="130" data-weekly="650">
                                            Air Compressor</option>
                                        <option value="welder" data-hourly="35" data-daily="180" data-weekly="900">Arc
                                            Welder</option>
                                        <option value="excavator" data-hourly="120" data-daily="600" data-weekly="2800">
                                            Mini Excavator</option>
                                        <option value="generator" data-hourly="30" data-daily="160" data-weekly="750">
                                            Generator 5kW</option>
                                        <option value="scaffolding" data-hourly="18" data-daily="95" data-weekly="460">
                                            Scaffolding Set</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Category</label>
                                <div class="input-box">
                                    <select id="categoryDisplay" disabled>
                                        <option>Select a tool first</option>
                                        <option value="power">Power Tools</option>
                                        <option value="heavy">Heavy Machinery</option>
                                        <option value="construction">Construction</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Rate Info Badges -->
                        <div class="rate-info-row" id="rateInfoRow" style="display:none;">
                            <div class="rate-badge">
                                <div class="rate-val" id="rBadgeHourly">—</div>
                                <div class="rate-lbl">per Hour</div>
                            </div>
                            <div class="rate-badge">
                                <div class="rate-val" id="rBadgeDaily">—</div>
                                <div class="rate-lbl">per Day</div>
                            </div>
                            <div class="rate-badge">
                                <div class="rate-val" id="rBadgeWeekly">—</div>
                                <div class="rate-lbl">per Week</div>
                            </div>
                            <div class="rate-badge" style="border-color:rgba(255,46,46,0.3);">
                                <div class="rate-val" id="rBadgeBest" style="color:#4ade80;">—</div>
                                <div class="rate-lbl">Best Rate</div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== CARD 2: DURATION ========== -->
                    <div class="pricing-card">
                        <h2><i class="fa fa-clock"></i> Rental Duration</h2>

                        <!-- Tier Tabs -->
                        <div class="duration-tabs">
                            <button class="tab-btn active" onclick="switchTier('hourly', this)">
                                <i class="fa fa-hourglass-half"></i> Hourly
                            </button>
                            <button class="tab-btn" onclick="switchTier('daily', this)">
                                <i class="fa fa-sun"></i> Daily
                            </button>
                            <button class="tab-btn" onclick="switchTier('weekly', this)">
                                <i class="fa fa-calendar-week"></i> Weekly
                            </button>
                        </div>

                        <!-- Hourly Panel -->
                        <div id="panel-hourly" class="duration-input-row">
                            <div class="input-group">
                                <label>Number of Hours</label>
                                <div class="input-box">
                                    <input type="number" id="inp-hours" min="1" max="23" value="1" placeholder="e.g. 4">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Start Date</label>
                                <div class="input-box">
                                    <input type="text" id="inp-hours-date" placeholder="DD/MM/YYYY">
                                </div>
                            </div>
                        </div>

                        <!-- Daily Panel -->
                        <div id="panel-daily" class="duration-input-row" style="display:none;">
                            <div class="input-group">
                                <label>Number of Days</label>
                                <div class="input-box">
                                    <input type="number" id="inp-days" min="1" max="30" value="1" placeholder="e.g. 3">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Start Date</label>
                                <div class="input-box">
                                    <input type="text" id="inp-days-date" placeholder="DD/MM/YYYY">
                                </div>
                            </div>
                        </div>

                        <!-- Weekly Panel -->
                        <div id="panel-weekly" class="duration-input-row" style="display:none;">
                            <div class="input-group">
                                <label>Number of Weeks</label>
                                <div class="input-box">
                                    <input type="number" id="inp-weeks" min="1" max="52" value="1" placeholder="e.g. 2">
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Start Date</label>
                                <div class="input-box">
                                    <input type="text" id="inp-weeks-date" placeholder="DD/MM/YYYY">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========== CARD 3: MEMBERSHIP ========== -->
                    <div class="pricing-card">
                        <h2><i class="fa fa-crown"></i> Membership Tier</h2>
                        <div class="membership-grid">

                            <div class="membership-card selected" onclick="selectMembership(this, 0, 'standard')">
                                <div class="membership-badge badge-standard">Standard</div>
                                <div class="discount-pct">0%</div>
                                <div class="discount-label">No Discount</div>
                            </div>

                            <div class="membership-card" onclick="selectMembership(this, 10, 'silver')">
                                <div class="membership-badge badge-silver">Silver</div>
                                <div class="discount-pct">10%</div>
                                <div class="discount-label">Members Save More</div>
                            </div>

                            <div class="membership-card" onclick="selectMembership(this, 20, 'gold')">
                                <div class="membership-badge badge-gold">Gold</div>
                                <div class="discount-pct">20%</div>
                                <div class="discount-label">Popular Choice</div>
                            </div>

                            <div class="membership-card" onclick="selectMembership(this, 35, 'platinum')">
                                <div class="membership-badge badge-platinum">Platinum</div>
                                <div class="discount-pct">35%</div>
                                <div class="discount-label">Maximum Savings</div>
                            </div>

                        </div>
                    </div>

                    <!-- ========== CARD 4: COST BREAKDOWN ========== -->
                    <div class="pricing-card">
                        <h2><i class="fa fa-receipt"></i> Cost Breakdown</h2>

                        <div id="breakdownContent">
                            <div class="empty-state">
                                <i class="fa fa-calculator"></i>
                                Select a tool and fill in the details, then click Calculate.
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
        let currentTier = 'hourly';
        let currentDiscount = 0;
        let currentMembership = 'standard';

        const toolCategories = {
            drill: { cat: 'power', catLabel: 'Power Tools' },
            saw: { cat: 'power', catLabel: 'Power Tools' },
            compressor: { cat: 'power', catLabel: 'Power Tools' },
            welder: { cat: 'construction', catLabel: 'Construction' },
            excavator: { cat: 'heavy', catLabel: 'Heavy Machinery' },
            generator: { cat: 'power', catLabel: 'Power Tools' },
            scaffolding: { cat: 'construction', catLabel: 'Construction' },
        };

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
            const daily = parseFloat(opt.dataset.daily);
            const weekly = parseFloat(opt.dataset.weekly);

            // Rate badges
            document.getElementById('rBadgeHourly').textContent = '$' + hourly;
            document.getElementById('rBadgeDaily').textContent = '$' + daily;
            document.getElementById('rBadgeWeekly').textContent = '$' + weekly;

            // Best rate per hour comparison
            const hPerHour = hourly;
            const dPerHour = daily / 8;
            const wPerHour = weekly / 40;
            let bestLabel = 'Hourly';
            if (dPerHour < hPerHour && dPerHour <= wPerHour) bestLabel = 'Daily';
            if (wPerHour < hPerHour && wPerHour <= dPerHour) bestLabel = 'Weekly';
            document.getElementById('rBadgeBest').textContent = bestLabel;

            // Category dropdown
            const catInfo = toolCategories[val] || { catLabel: 'General' };
            document.getElementById('categoryDisplay').innerHTML =
                `<option selected>${catInfo.catLabel}</option>`;

            document.getElementById('rateInfoRow').style.display = 'flex';
        }

        /* ---- Switch Duration Tier Tab ---- */
        function switchTier(tier, btn) {
            currentTier = tier;

            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            ['hourly', 'daily', 'weekly'].forEach(t => {
                document.getElementById('panel-' + t).style.display = (t === tier) ? 'grid' : 'none';
            });
        }

        /* ---- Select Membership ---- */
        function selectMembership(card, discount, tier) {
            document.querySelectorAll('.membership-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            currentDiscount = discount;
            currentMembership = tier;
        }

        /* ---- Calculate ---- */
        function calculate() {
            const sel = document.getElementById('toolSelect');
            const opt = sel.options[sel.selectedIndex];

            if (!sel.value) {
                showError('Please select a tool first.');
                return;
            }

            const toolName = opt.text;
            const hourlyRate = parseFloat(opt.dataset.hourly);
            const dailyRate = parseFloat(opt.dataset.daily);
            const weeklyRate = parseFloat(opt.dataset.weekly);

            let baseTotal = 0;
            let durationDesc = '';
            let unitRate = 0;
            let qty = 0;

            if (currentTier === 'hourly') {
                qty = parseInt(document.getElementById('inp-hours').value) || 1;
                qty = Math.max(1, qty);
                baseTotal = hourlyRate * qty;
                unitRate = hourlyRate;
                durationDesc = qty + ' Hour' + (qty > 1 ? 's' : '');
            } else if (currentTier === 'daily') {
                qty = parseInt(document.getElementById('inp-days').value) || 1;
                qty = Math.max(1, qty);
                baseTotal = dailyRate * qty;
                unitRate = dailyRate;
                durationDesc = qty + ' Day' + (qty > 1 ? 's' : '');
            } else {
                qty = parseInt(document.getElementById('inp-weeks').value) || 1;
                qty = Math.max(1, qty);
                baseTotal = weeklyRate * qty;
                unitRate = weeklyRate;
                durationDesc = qty + ' Week' + (qty > 1 ? 's' : '');
            }

            const discountAmt = baseTotal * (currentDiscount / 100);
            const finalTotal = baseTotal - discountAmt;

            // Membership label
            const tierLabels = {
                standard: 'Standard',
                silver: 'Silver Member',
                gold: 'Gold Member',
                platinum: 'Platinum Member'
            };

            const html = `
        <table class="breakdown-table">
            <tr>
                <td>Tool</td>
                <td><strong style="color:#eee;">${toolName}</strong></td>
            </tr>
            <tr class="highlight-row">
                <td>Duration</td>
                <td>${durationDesc} (${currentTier.charAt(0).toUpperCase() + currentTier.slice(1)} rate)</td>
            </tr>
            <tr>
                <td>Rate</td>
                <td>$${unitRate.toFixed(2)} / ${currentTier === 'hourly' ? 'hr' : currentTier === 'daily' ? 'day' : 'wk'}</td>
            </tr>
            <tr>
                <td>Base Cost</td>
                <td>$${baseTotal.toFixed(2)}</td>
            </tr>
            <tr>
                <td>Membership</td>
                <td>${tierLabels[currentMembership]}</td>
            </tr>
            ${currentDiscount > 0 ? `
            <tr class="row-savings">
                <td>Membership Discount (${currentDiscount}%)</td>
                <td style="color:#4ade80;">− $${discountAmt.toFixed(2)}</td>
            </tr>` : ''}
            <tr class="row-total">
                <td><i class="fa fa-dollar-sign" style="margin-right:6px;"></i>Total Due</td>
                <td>$${finalTotal.toFixed(2)}</td>
            </tr>
            ${currentDiscount > 0 ? `
            <tr>
                <td colspan="2" style="text-align:center;padding-top:10px;">
                    <span style="background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.3);
                        color:#4ade80;border-radius:6px;padding:5px 16px;font-size:12px;">
                        🎉 You save $${discountAmt.toFixed(2)} with ${tierLabels[currentMembership]}!
                    </span>
                </td>
            </tr>` : ''}
        </table>
    `;

            document.getElementById('breakdownContent').innerHTML = html;
        }

        /* ---- Error Helper ---- */
        function showError(msg) {
            document.getElementById('breakdownContent').innerHTML = `
        <div class="empty-state" style="color:#ff2e2e;">
            <i class="fa fa-circle-exclamation" style="color:#ff2e2e;"></i>
            ${msg}
        </div>`;
        }

    </script>
</body>

</html>