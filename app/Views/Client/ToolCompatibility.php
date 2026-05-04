<html>
<meta charset="UTF-8">
<title>Tool Hub - Tool Compatibility Checker</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../../assets/Css/style.css">
<link rel="stylesheet" href="../../assets/Css/admin.css">
<link rel="stylesheet" href="../../assets/Css/ToolCompatibility.css">
</head>

<body>
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
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
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                <rect x="2" y="3" width="20" height="14" rx="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
            </svg> Categories
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

        <a href="ToolCompatibility.php" class="nav-link active">
            <i class="fa fa-circle-check"></i> Compatibility Checker
        </a>

    </div>
</div>

<!-- =========================================================
   LAYOUT RIGHT
========================================================= -->
<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Tool Compatibility Checker</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                 <span>Client</span>
            </button>
            <a href="../Auth/login.php" class="icon-btn">
                <i class="fa fa-right-from-bracket"></i>
            </a>
        </div>
    </div>

    <div class="main-content">

        <div class="compat-page">

            <div class="compat-container">

                <div class="compat-header">
                    <div class="compat-icon">
                        <i class="fa fa-circle-check"></i>
                    </div>
                    <div>
                        <h2>Tool Compatibility Checker</h2>
                        <p class="compat-subtitle">Ensure the right tool fits your project before you borrow</p>
                    </div>
                </div>

                <!-- FORM SECTION -->
                <div class="compat-form-section">
                    <h3 class="section-title"><i class="fa fa-screwdriver-wrench"></i> Tool Details</h3>

                    <div class="input-group">
                        <label>Tool Name / Type</label>
                        <div class="input-box">
                            <input type="text" id="toolName" placeholder="e.g., Circular Saw, Drill, Sander...">
                        </div>
                    </div>

                    <div class="grid-inputs">
                        <div class="input-group">
                            <label>Tool Brand / Model</label>
                            <div class="input-box">
                                <input type="text" id="toolModel" placeholder="e.g., DeWalt DWE575">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Accessory / Attachment</label>
                            <div class="input-box">
                                <input type="text" id="toolAccessory" placeholder="e.g., 7-1/4 inch blade, 12mm bit...">
                            </div>
                        </div>
                    </div>

                    <h3 class="section-title" style="margin-top:28px;"><i class="fa fa-hard-hat"></i> Project Details</h3>

                    <div class="input-group">
                        <label>Project Type</label>
                        <div class="select-box">
                            <select id="projectType">
                                <option value="">-- Select Project Type --</option>
                                <option value="woodworking">Woodworking</option>
                                <option value="metalworking">Metalworking</option>
                                <option value="masonry">Masonry / Concrete</option>
                                <option value="electrical">Electrical Work</option>
                                <option value="plumbing">Plumbing</option>
                                <option value="painting">Painting / Finishing</option>
                                <option value="landscaping">Landscaping / Outdoor</option>
                                <option value="other">Other</option>
                            </select>
                            <i class="fa fa-chevron-down select-arrow"></i>
                        </div>
                    </div>

                    <div class="grid-inputs">
                        <div class="input-group">
                            <label>Material to Work On</label>
                            <div class="input-box">
                                <input type="text" id="material" placeholder="e.g., Pine wood, Steel, Concrete...">
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Material Thickness / Size</label>
                            <div class="input-box">
                                <input type="text" id="materialSize" placeholder="e.g., 2 inches, 10mm...">
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Additional Notes / Requirements</label>
                        <div class="input-box">
                            <textarea id="notes" rows="3" placeholder="Describe any specific requirements, constraints, or concerns about your project..."></textarea>
                        </div>
                    </div>

                    <button class="check-btn" id="checkBtn" onclick="checkCompatibility()">
                        <i class="fa fa-magnifying-glass"></i> Check Compatibility
                    </button>
                </div>

                <!-- RESULT SECTION -->
                <div class="result-section" id="resultSection">
                    <div class="result-header">
                        <i class="fa fa-clipboard-list"></i> Compatibility Result
                    </div>

                    <div class="result-body" id="resultBody">
                        <!-- Filled by JS -->
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
function checkCompatibility() {
    const toolName      = document.getElementById('toolName').value.trim();
    const toolModel     = document.getElementById('toolModel').value.trim();
    const toolAccessory = document.getElementById('toolAccessory').value.trim();
    const projectType   = document.getElementById('projectType').value;
    const material      = document.getElementById('material').value.trim();
    const materialSize  = document.getElementById('materialSize').value.trim();
    const notes         = document.getElementById('notes').value.trim();

    // Basic validation
    if (!toolName || !projectType || !material) {
        showResult('warning', [
            { icon: 'fa-triangle-exclamation', label: 'Missing Information', value: 'Please fill in Tool Name, Project Type, and Material to get accurate results.' }
        ], 'incomplete');
        return;
    }

    // Simulate compatibility logic
    const result = evaluateCompatibility(toolName, toolAccessory, projectType, material, materialSize);

    showResult(result.status, result.details, result.status);
}

function evaluateCompatibility(tool, accessory, project, material, size) {
    const t = tool.toLowerCase();
    const m = material.toLowerCase();
    const p = project;
    const a = accessory.toLowerCase();

    let status = 'compatible';
    let details = [];

    // --- Rule engine (simple demo logic) ---

    // Circular saw + wood
    if ((t.includes('saw') || t.includes('circular')) && (m.includes('wood') || m.includes('pine') || m.includes('plywood'))) {
        details.push({ icon: 'fa-check', label: 'Tool–Material Match', value: 'Circular saw is excellent for cutting wood materials.' });
        if (a.includes('blade')) {
            details.push({ icon: 'fa-check', label: 'Accessory Fit', value: 'The specified blade is suitable for wood cutting operations.' });
        }
    }

    // Saw on metal — warning
    else if (t.includes('saw') && (m.includes('metal') || m.includes('steel') || m.includes('iron'))) {
        status = 'warning';
        details.push({ icon: 'fa-triangle-exclamation', label: 'Material Mismatch Risk', value: 'Standard saw blades are not rated for metal. You need a metal-cutting blade or an angle grinder.' });
        details.push({ icon: 'fa-lightbulb', label: 'Recommendation', value: 'Switch to a metal-rated carbide blade or use an angle grinder with a cutting disc.' });
    }

    // Drill + masonry
    else if (t.includes('drill') && (m.includes('concrete') || m.includes('masonry') || m.includes('brick'))) {
        if (a.includes('masonry') || a.includes('hammer')) {
            details.push({ icon: 'fa-check', label: 'Accessory Fit', value: 'Masonry/hammer drill bit confirmed — suitable for concrete and brick.' });
        } else {
            status = 'warning';
            details.push({ icon: 'fa-triangle-exclamation', label: 'Wrong Bit Type', value: 'Standard drill bits will not penetrate concrete effectively. Use a masonry or SDS bit.' });
            details.push({ icon: 'fa-lightbulb', label: 'Recommendation', value: 'Attach a carbide-tipped masonry bit or switch to a rotary hammer drill.' });
        }
    }

    // Sander + painting project
    else if (t.includes('sander') && p === 'painting') {
        details.push({ icon: 'fa-check', label: 'Project Fit', value: 'Sanders are ideal for surface preparation before painting.' });
        details.push({ icon: 'fa-info', label: 'Tip', value: 'Use fine-grit sandpaper (120–220) for finishing; medium-grit (80–100) for material removal.' });
    }

    // Default: generic positive check
    else {
        details.push({ icon: 'fa-check', label: 'Tool–Project Alignment', value: `${tool} appears suitable for ${p} work on ${material}.` });
    }

    // Size check
    if (size) {
        details.push({ icon: 'fa-ruler', label: 'Size / Thickness Note', value: `Noted material size: ${size}. Ensure your tool's capacity or accessory rating covers this dimension.` });
    }

    // Summary line
    const summaries = {
        compatible: { icon: 'fa-circle-check', label: 'Overall Verdict', value: 'This tool and accessory combination is compatible with your project requirements.' },
        warning:    { icon: 'fa-triangle-exclamation', label: 'Overall Verdict', value: 'Compatibility issues detected. Review the recommendations above before proceeding.' },
        incomplete: { icon: 'fa-circle-info', label: 'Status', value: 'Incomplete form — please provide all required fields.' }
    };
    details.unshift(summaries[status]);

    return { status, details };
}

function showResult(status, details, type) {
    const section = document.getElementById('resultSection');
    const body    = document.getElementById('resultBody');

    const iconMap = {
        compatible: 'result-compatible',
        warning:    'result-warning',
        incomplete: 'result-incomplete'
    };

    body.innerHTML = details.map(d => `
        <div class="result-item ${iconMap[type] || ''}">
            <div class="result-item-icon"><i class="fa ${d.icon}"></i></div>
            <div class="result-item-text">
                <span class="result-label">${d.label}</span>
                <span class="result-value">${d.value}</span>
            </div>
        </div>
    `).join('');

    section.classList.add('visible');
    section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>

</body>
</html>