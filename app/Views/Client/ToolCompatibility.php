<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

$db   = Database::getInstance();
$conn = $db->getConnection();
$uid  = intval($_SESSION['user_id']);
$r = $conn->query("SELECT COUNT(*) AS cnt FROM messages WHERE receiver_id = $uid AND is_read = 0");
$unread_msgs = $r->fetch_assoc()['cnt'];
$conn->query("
    CREATE TABLE IF NOT EXISTS tool_compatibility_checks (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        tool_name       VARCHAR(255)  NOT NULL,
        tool_model      VARCHAR(255)  DEFAULT NULL,
        tool_accessory  VARCHAR(255)  DEFAULT NULL,
        project_type    VARCHAR(100)  NOT NULL,
        material        VARCHAR(255)  NOT NULL,
        material_size   VARCHAR(100)  DEFAULT NULL,
        notes           TEXT          DEFAULT NULL,
        result_status   ENUM('compatible','warning','incomplete') NOT NULL,
        result_summary  TEXT          DEFAULT NULL,
        checked_at      DATETIME      DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_check') {

    $tool_name      = mysqli_real_escape_string($conn, $_POST['tool_name']      ?? '');
    $tool_model     = mysqli_real_escape_string($conn, $_POST['tool_model']     ?? '');
    $tool_accessory = mysqli_real_escape_string($conn, $_POST['tool_accessory'] ?? '');
    $project_type   = mysqli_real_escape_string($conn, $_POST['project_type']   ?? '');
    $material       = mysqli_real_escape_string($conn, $_POST['material']       ?? '');
    $material_size  = mysqli_real_escape_string($conn, $_POST['material_size']  ?? '');
    $notes          = mysqli_real_escape_string($conn, $_POST['notes']          ?? '');
    $result_status  = mysqli_real_escape_string($conn, $_POST['result_status']  ?? 'incomplete');
    $result_summary = mysqli_real_escape_string($conn, $_POST['result_summary'] ?? '');

    $sql = "INSERT INTO tool_compatibility_checks
                (tool_name, tool_model, tool_accessory, project_type, material, material_size, notes, result_status, result_summary)
            VALUES
                ('$tool_name','$tool_model','$tool_accessory','$project_type','$material','$material_size','$notes','$result_status','$result_summary')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
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

        <a href="../Tools/tools.php" class="nav-link">
            <i class="fa fa-wrench"></i> Tools
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
        <a href="my-reservations.php" class="nav-link">
            <i class="fa fa-calendar-check"></i> My Reservations
        </a>
        <a href="chat.php" class="nav-link">
            <i class="fa fa-comments"></i> Chat
            <?php if ($unread_msgs > 0): ?>
                <span class="nav-count"><?= $unread_msgs ?></span>
            <?php endif; ?>
        </a>
        <a href="ToolCompatibility.php" class="nav-link active">
            <i class="fa fa-circle-check"></i> Compatibility Checker
        </a>

        <a href="DamageDeclaration.php" class="nav-link">
            <i class="fa fa-triangle-exclamation"></i> Damage Report
         </a>
    </div>
</div>


<div class="layout-right">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="topbar-title">Tool Compatibility Checker</div>
        <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Client</span>
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

    if (!toolName || !projectType || !material) {
        showResult('warning', [
            { icon: 'fa-triangle-exclamation', label: 'Missing Information', value: 'Please fill in Tool Name, Project Type, and Material to get accurate results.' }
        ], 'incomplete');
        return;
    }

    const result = evaluateCompatibility(toolName, toolAccessory, projectType, material, materialSize);

    showResult(result.status, result.details, result.status);

    const summary = result.details[0]?.value ?? '';
    saveToDatabase({
        tool_name:      toolName,
        tool_model:     toolModel,
        tool_accessory: toolAccessory,
        project_type:   projectType,
        material:       material,
        material_size:  materialSize,
        notes:          notes,
        result_status:  result.status,
        result_summary: summary
    });
}

function saveToDatabase(data) {
    const formData = new FormData();
    formData.append('action', 'save_check');
    for (const [key, value] of Object.entries(data)) {
        formData.append(key, value);
    }

    fetch(window.location.href, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(json => {
            if (!json.success) {
                console.warn('DB save failed:', json.error);
            }
        })
        .catch(err => console.warn('Fetch error:', err));
}

function evaluateCompatibility(tool, accessory, project, material, size) {
    const t = tool.toLowerCase();
    const m = material.toLowerCase();
    const p = project;
    const a = accessory.toLowerCase();

    let status  = 'compatible';
    let details = [];

    if ((t.includes('saw') || t.includes('circular')) && (m.includes('wood') || m.includes('pine') || m.includes('plywood'))) {
        details.push({ icon: 'fa-check', label: 'Tool–Material Match', value: 'Circular saw is excellent for cutting wood materials.' });
        if (a.includes('blade')) {
            details.push({ icon: 'fa-check', label: 'Accessory Fit', value: 'The specified blade is suitable for wood cutting operations.' });
        }
    }
    else if (t.includes('saw') && (m.includes('metal') || m.includes('steel') || m.includes('iron'))) {
        status = 'warning';
        details.push({ icon: 'fa-triangle-exclamation', label: 'Material Mismatch Risk', value: 'Standard saw blades are not rated for metal. You need a metal-cutting blade or an angle grinder.' });
        details.push({ icon: 'fa-lightbulb', label: 'Recommendation', value: 'Switch to a metal-rated carbide blade or use an angle grinder with a cutting disc.' });
    }
    else if (t.includes('drill') && (m.includes('concrete') || m.includes('masonry') || m.includes('brick'))) {
        if (a.includes('masonry') || a.includes('hammer')) {
            details.push({ icon: 'fa-check', label: 'Accessory Fit', value: 'Masonry/hammer drill bit confirmed — suitable for concrete and brick.' });
        } else {
            status = 'warning';
            details.push({ icon: 'fa-triangle-exclamation', label: 'Wrong Bit Type', value: 'Standard drill bits will not penetrate concrete effectively. Use a masonry or SDS bit.' });
            details.push({ icon: 'fa-lightbulb', label: 'Recommendation', value: 'Attach a carbide-tipped masonry bit or switch to a rotary hammer drill.' });
        }
    }
    else if (t.includes('sander') && p === 'painting') {
        details.push({ icon: 'fa-check', label: 'Project Fit', value: 'Sanders are ideal for surface preparation before painting.' });
        details.push({ icon: 'fa-info', label: 'Tip', value: 'Use fine-grit sandpaper (120–220) for finishing; medium-grit (80–100) for material removal.' });
    }
    else {
        details.push({ icon: 'fa-check', label: 'Tool–Project Alignment', value: `${tool} appears suitable for ${p} work on ${material}.` });
    }

    if (size) {
        details.push({ icon: 'fa-ruler', label: 'Size / Thickness Note', value: `Noted material size: ${size}. Ensure your tool's capacity or accessory rating covers this dimension.` });
    }

    const summaries = {
        compatible: { icon: 'fa-circle-check', label: 'Overall Verdict', value: 'This tool and accessory combination is compatible with your project requirements.' },
        warning:    { icon: 'fa-triangle-exclamation', label: 'Overall Verdict', value: 'Compatibility issues detected. Review the recommendations above before proceeding.' }
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