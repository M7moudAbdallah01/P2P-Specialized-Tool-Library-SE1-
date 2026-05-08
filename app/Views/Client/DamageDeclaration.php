<?php
session_start();
require_once __DIR__ . "/../../../Core/database.php";

$db   = Database::getInstance();
$conn = $db->getConnection();

$conn->query("
    CREATE TABLE IF NOT EXISTS damage_declarations (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        reservation_id  VARCHAR(100)  NOT NULL,
        tool_name       VARCHAR(255)  NOT NULL,
        damage_date     DATE          NOT NULL,
        location        VARCHAR(255)  NOT NULL,
        damage_type     VARCHAR(100)  NOT NULL,
        severity        ENUM('low','medium','high') NOT NULL,
        description     TEXT          DEFAULT NULL,
        witness         VARCHAR(255)  DEFAULT NULL,
        document_path   VARCHAR(500)  DEFAULT NULL,
        photos          TEXT          DEFAULT NULL,
        reference_no    VARCHAR(50)   NOT NULL,
        status          ENUM('pending','reviewing','resolved') DEFAULT 'pending',
        submitted_at    DATETIME      DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_damage') {

    $reservation_id = mysqli_real_escape_string($conn, $_POST['reservation_id'] ?? '');
    $tool_name      = mysqli_real_escape_string($conn, $_POST['tool_name']      ?? '');
    $damage_date    = mysqli_real_escape_string($conn, $_POST['damage_date']    ?? '');
    $location       = mysqli_real_escape_string($conn, $_POST['location']       ?? '');
    $damage_type    = mysqli_real_escape_string($conn, $_POST['damage_type']    ?? '');
    $severity       = mysqli_real_escape_string($conn, $_POST['severity']       ?? 'low');
    $description    = mysqli_real_escape_string($conn, $_POST['description']    ?? '');
    $witness        = mysqli_real_escape_string($conn, $_POST['witness']        ?? '');

    $reference_no = 'DMG-' . date('Y') . '-' . rand(1000, 9999);

    $document_path = '';
    if (!empty($_FILES['document']['name'])) {
        $doc_dir = "uploads/damage_docs/";
        if (!is_dir($doc_dir)) mkdir($doc_dir, 0777, true);

        $doc_ext   = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        $allowed   = ['pdf', 'doc', 'docx'];

        if (in_array($doc_ext, $allowed) && $_FILES['document']['size'] <= 5000000) {
            $doc_file = time() . '_' . basename($_FILES['document']['name']);
            if (move_uploaded_file($_FILES['document']['tmp_name'], $doc_dir . $doc_file)) {
                $document_path = $doc_dir . $doc_file;
            }
        }
    }

    $photo_paths = [];
    if (!empty($_FILES['photos']['name'][0])) {
        $photo_dir = "uploads/damage_photos/";
        if (!is_dir($photo_dir)) mkdir($photo_dir, 0777, true);

        $allowed_img = ['jpg', 'jpeg', 'png', 'webp'];
        $count = min(count($_FILES['photos']['name']), 5);

        for ($i = 0; $i < $count; $i++) {
            $img_ext = strtolower(pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION));
            if (in_array($img_ext, $allowed_img) && $_FILES['photos']['size'][$i] <= 5000000) {
                $img_file = time() . '_' . $i . '_' . basename($_FILES['photos']['name'][$i]);
                if (move_uploaded_file($_FILES['photos']['tmp_name'][$i], $photo_dir . $img_file)) {
                    $photo_paths[] = $photo_dir . $img_file;
                }
            }
        }
    }

    $photos_json    = mysqli_real_escape_string($conn, json_encode($photo_paths));
    $document_esc   = mysqli_real_escape_string($conn, $document_path);
    $reference_esc  = mysqli_real_escape_string($conn, $reference_no);

    $sql = "INSERT INTO damage_declarations
                (reservation_id, tool_name, damage_date, location, damage_type, severity, description, witness, document_path, photos, reference_no)
            VALUES
                ('$reservation_id','$tool_name','$damage_date','$location','$damage_type','$severity','$description','$witness','$document_esc','$photos_json','$reference_esc')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'reference' => $reference_no]);
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
    <title>Tool Hub - Damage Declaration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/Css/style.css">
    <link rel="stylesheet" href="../../assets/Css/admin.css">
    <link rel="stylesheet" href="../../assets/Css/DamageDeclaration.css">
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <img src="../../assets/images/logo.png" alt="Tool Hub Logo">
            </div>
            <div class="brand-text">TOOL HUB</div>
        </div>

        <div class="role-badge" style="background:#6366f1;color:#fff;">CLIENT</div>

        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-link"><i class="fa fa-gauge"></i> Dashboard</a>
            <a href="../Tools/tools.php" class="nav-link"><i class="fa fa-wrench"></i> Tools</a>
            <a href="ToolSpecification.php" class="nav-link"><i class="fa fa-plus"></i> Add Tool</a>
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
            <a href="chat.php" class="nav-link"><i class="fa fa-comments"></i> Chat</a>
            <!-- <a href="reports.php" class="nav-link"><i class="fa fa-scale-balanced"></i> Reports</a> -->
            <a href="ToolCompatibility.php" class="nav-link"><i class="fa fa-circle-check"></i> Compatibility Checker</a>
            <a href="DamageDeclaration.php" class="nav-link active"><i class="fa fa-triangle-exclamation"></i> Damage Report</a>
        </div>
    </div>

    <!-- LAYOUT RIGHT -->
    <div class="layout-right">

        <!-- TOPBAR -->
        <div class="topbar">
            <div class="topbar-title">Damage Declaration</div>
            <div class="topbar-right">
            <button class="avatar-btn" style="background:#6366f1;color:#fff;border:none;cursor:default;">
                <?= htmlspecialchars($_SESSION['name']) ?> <span>Client</span>
            </button>
                <a href="../Auth/login.php" class="icon-btn">
                    <i class="fa fa-right-from-bracket"></i>
                </a>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="damage-page">

                <!-- PROGRESS BAR -->
                <div class="progress-wrapper">
                    <div class="progress-steps">
                        <div class="step active" data-step="1">
                            <div class="step-circle"><i class="fa fa-toolbox"></i></div>
                            <span>Tool Info</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" data-step="2">
                            <div class="step-circle"><i class="fa fa-triangle-exclamation"></i></div>
                            <span>Damage Type</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" data-step="3">
                            <div class="step-circle"><i class="fa fa-camera"></i></div>
                            <span>Evidence</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step" data-step="4">
                            <div class="step-circle"><i class="fa fa-check"></i></div>
                            <span>Confirm</span>
                        </div>
                    </div>
                </div>

                <!-- FORM CONTAINER -->
                <div class="declaration-container">

                    <!-- STEP 1: Tool Info -->
                    <div class="form-step active" id="step-1">
                        <div class="step-header">
                            <div class="step-icon"><i class="fa fa-toolbox"></i></div>
                            <div>
                                <h2>Tool Information</h2>
                                <p class="step-sub">Select the tool and reservation related to the damage</p>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Reservation ID</label>
                            <div class="input-box">
                                <i class="fa fa-hashtag input-icon"></i>
                                <input type="text" name="reservation_id" placeholder="e.g. RES-2024-0045" required>
                            </div>
                        </div>

                        <div class="grid-inputs">
                            <div class="input-group">
                                <label>Tool Name</label>
                                <div class="input-box">
                                    <i class="fa fa-wrench input-icon"></i>
                                    <input type="text" name="tool_name" placeholder="e.g. Angle Grinder" required>
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Date of Damage</label>
                                <div class="input-box">
                                    <i class="fa fa-calendar input-icon"></i>
                                    <input type="date" name="damage_date" required>
                                </div>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Location of Incident</label>
                            <div class="input-box">
                                <i class="fa fa-location-dot input-icon"></i>
                                <input type="text" name="location" placeholder="Where did the damage occur?" required>
                            </div>
                        </div>

                        <div class="nav-btns">
                            <span></span>
                            <button class="next-btn" onclick="nextStep(1)">Next <i class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- STEP 2: Damage Type -->
                    <div class="form-step" id="step-2">
                        <div class="step-header">
                            <div class="step-icon warn"><i class="fa fa-triangle-exclamation"></i></div>
                            <div>
                                <h2>Damage Details</h2>
                                <p class="step-sub">Describe the type and severity of the damage</p>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Damage Type</label>
                            <div class="select-box">
                                <i class="fa fa-layer-group input-icon"></i>
                                <select name="damage_type">
                                    <option value="">-- Select Type --</option>
                                    <option value="physical">Physical / Structural</option>
                                    <option value="electrical">Electrical / Circuit</option>
                                    <option value="mechanical">Mechanical / Parts</option>
                                    <option value="cosmetic">Cosmetic / Surface</option>
                                    <option value="lost">Lost / Missing</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Severity Level</label>
                            <div class="severity-options">
                                <label class="severity-card low">
                                    <input type="radio" name="severity" value="low">
                                    <div class="sev-content">
                                        <i class="fa fa-circle-check"></i>
                                        <span>Minor</span>
                                        <small>Cosmetic only</small>
                                    </div>
                                </label>
                                <label class="severity-card medium">
                                    <input type="radio" name="severity" value="medium">
                                    <div class="sev-content">
                                        <i class="fa fa-circle-exclamation"></i>
                                        <span>Moderate</span>
                                        <small>Affects function</small>
                                    </div>
                                </label>
                                <label class="severity-card high">
                                    <input type="radio" name="severity" value="high">
                                    <div class="sev-content">
                                        <i class="fa fa-circle-xmark"></i>
                                        <span>Severe</span>
                                        <small>Tool unusable</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Damage Description</label>
                            <div class="input-box textarea-box">
                                <textarea name="description" rows="4"
                                    placeholder="Describe in detail what happened and how the damage occurred..."></textarea>
                            </div>
                        </div>

                        <div class="nav-btns">
                            <button class="back-btn" onclick="prevStep(2)"><i class="fa fa-arrow-left"></i> Back</button>
                            <button class="next-btn" onclick="nextStep(2)">Next <i class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- STEP 3: Evidence -->
                    <div class="form-step" id="step-3">
                        <div class="step-header">
                            <div class="step-icon"><i class="fa fa-camera"></i></div>
                            <div>
                                <h2>Upload Evidence</h2>
                                <p class="step-sub">Attach photos or documents to support your report</p>
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Damage Photos</label>
                            <div class="upload-zone" id="dropzone"
                                onclick="document.getElementById('photo-input').click()">
                                <input type="file" id="photo-input" name="photos[]" multiple accept="image/*"
                                    style="display:none" onchange="previewImages(event)">
                                <div class="upload-zone-inner">
                                    <i class="fa fa-cloud-arrow-up"></i>
                                    <p>Click or drag & drop photos here</p>
                                    <small>JPG, PNG, WEBP — max 5 files</small>
                                </div>
                            </div>
                            <div class="photo-preview" id="photo-preview"></div>
                        </div>

                        <div class="input-group">
                            <label>Supporting Document (Optional)</label>
                            <div class="input-box">
                                <i class="fa fa-file-pdf input-icon"></i>
                                <input type="file" name="document" accept=".pdf,.doc,.docx">
                            </div>
                        </div>

                        <div class="input-group">
                            <label>Witness Name (Optional)</label>
                            <div class="input-box">
                                <i class="fa fa-user input-icon"></i>
                                <input type="text" name="witness" placeholder="Name of any witness present">
                            </div>
                        </div>

                        <div class="nav-btns">
                            <button class="back-btn" onclick="prevStep(3)"><i class="fa fa-arrow-left"></i> Back</button>
                            <button class="next-btn" onclick="nextStep(3)">Review <i class="fa fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- STEP 4: Confirm -->
                    <div class="form-step" id="step-4">
                        <div class="step-header">
                            <div class="step-icon confirm"><i class="fa fa-shield-halved"></i></div>
                            <div>
                                <h2>Confirm & Submit</h2>
                                <p class="step-sub">Review your declaration before final submission</p>
                            </div>
                        </div>

                        <div class="summary-box">
                            <div class="summary-row">
                                <span><i class="fa fa-hashtag"></i> Reservation ID</span>
                                <strong id="s-reservation">—</strong>
                            </div>
                            <div class="summary-row">
                                <span><i class="fa fa-wrench"></i> Tool</span>
                                <strong id="s-tool">—</strong>
                            </div>
                            <div class="summary-row">
                                <span><i class="fa fa-calendar"></i> Date</span>
                                <strong id="s-date">—</strong>
                            </div>
                            <div class="summary-row">
                                <span><i class="fa fa-layer-group"></i> Damage Type</span>
                                <strong id="s-type">—</strong>
                            </div>
                            <div class="summary-row">
                                <span><i class="fa fa-circle-exclamation"></i> Severity</span>
                                <strong id="s-severity">—</strong>
                            </div>
                            <div class="summary-row">
                                <span><i class="fa fa-location-dot"></i> Location</span>
                                <strong id="s-location">—</strong>
                            </div>
                        </div>

                        <div class="disclaimer-box">
                            <i class="fa fa-circle-info"></i>
                            <p>By submitting this report, you confirm that the information provided is accurate and
                                truthful. False declarations may result in account suspension and legal liability.</p>
                        </div>

                        <label class="checkbox-label">
                            <input type="checkbox" id="agree-checkbox">
                            <span>I confirm the accuracy of this damage report</span>
                        </label>

                        <div class="nav-btns">
                            <button class="back-btn" onclick="prevStep(4)"><i class="fa fa-arrow-left"></i> Back</button>
                            <button class="submit-btn" id="submitBtn" onclick="submitReport()">
                                <i class="fa fa-paper-plane"></i> Submit Report
                            </button>
                        </div>
                    </div>

                </div><!-- end declaration-container -->

                <!-- SUCCESS MODAL -->
                <div class="modal-overlay" id="success-modal">
                    <div class="modal-box">
                        <div class="modal-icon"><i class="fa fa-circle-check"></i></div>
                        <h3>Report Submitted!</h3>
                        <p>Your damage declaration has been received. Our team will review it and contact you shortly.</p>
                        <div class="modal-ref">Reference: <strong id="modal-ref-id">DMG-2024-XXXX</strong></div>
                        <button class="modal-close-btn" onclick="closeModal()">Done</button>
                    </div>
                </div>

            </div><!-- end damage-page -->
        </div><!-- end main-content -->
    </div><!-- end layout-right -->

    <script>

        let currentStep = 1;
        const totalSteps = 4;

        function nextStep(from) {
            if (!validateStep(from)) return;
            goToStep(from + 1);
        }

        function prevStep(from) {
            goToStep(from - 1);
        }

        function goToStep(to) {
            document.getElementById(`step-${currentStep}`).classList.remove('active');
            updateStepper(to);
            currentStep = to;
            document.getElementById(`step-${currentStep}`).classList.add('active');
            if (to === 4) populateSummary();
        }

        function updateStepper(activeTo) {
            document.querySelectorAll('.step').forEach((el, i) => {
                const num = i + 1;
                el.classList.remove('active', 'done');
                if (num < activeTo) el.classList.add('done');
                if (num === activeTo) el.classList.add('active');
            });
            document.querySelectorAll('.step-line').forEach((el, i) => {
                el.classList.remove('done');
                if (i + 1 < activeTo) el.classList.add('done');
            });
        }

        function validateStep(step) {
            if (step === 1) {
                const resId   = document.querySelector('[name="reservation_id"]').value.trim();
                const toolNm  = document.querySelector('[name="tool_name"]').value.trim();
                const dmgDate = document.querySelector('[name="damage_date"]').value;
                const loc     = document.querySelector('[name="location"]').value.trim();
                if (!resId || !toolNm || !dmgDate || !loc) {
                    showToast('Please fill in all required fields.', 'error');
                    return false;
                }
            }
            if (step === 2) {
                const type = document.querySelector('[name="damage_type"]').value;
                const sev  = document.querySelector('[name="severity"]:checked');
                if (!type) { showToast('Please select a damage type.', 'error'); return false; }
                if (!sev)  { showToast('Please select a severity level.', 'error'); return false; }
            }
            return true;
        }

        function populateSummary() {
            const get = (name) => {
                const el = document.querySelector(`[name="${name}"]`);
                return el ? el.value.trim() || '—' : '—';
            };
            const sevEl = document.querySelector('[name="severity"]:checked');

            document.getElementById('s-reservation').textContent = get('reservation_id');
            document.getElementById('s-tool').textContent        = get('tool_name');
            document.getElementById('s-date').textContent        = formatDate(get('damage_date'));
            document.getElementById('s-type').textContent        = formatSelect(get('damage_type'));
            document.getElementById('s-severity').textContent    = sevEl ? capitalize(sevEl.value) : '—';
            document.getElementById('s-location').textContent    = get('location');
        }

        function formatDate(val) {
            if (!val || val === '—') return '—';
            const d = new Date(val);
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        }

        function formatSelect(val) {
            const map = {
                physical: 'Physical / Structural', electrical: 'Electrical / Circuit',
                mechanical: 'Mechanical / Parts',  cosmetic: 'Cosmetic / Surface',
                lost: 'Lost / Missing',             other: 'Other'
            };
            return map[val] || val;
        }

        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function submitReport() {
            if (!document.getElementById('agree-checkbox').checked) {
                showToast('Please confirm the accuracy of the report.', 'error');
                return;
            }

            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Submitting...';

            const formData = new FormData();
            formData.append('action',         'submit_damage');
            formData.append('reservation_id', document.querySelector('[name="reservation_id"]').value.trim());
            formData.append('tool_name',      document.querySelector('[name="tool_name"]').value.trim());
            formData.append('damage_date',    document.querySelector('[name="damage_date"]').value);
            formData.append('location',       document.querySelector('[name="location"]').value.trim());
            formData.append('damage_type',    document.querySelector('[name="damage_type"]').value);
            formData.append('severity',       (document.querySelector('[name="severity"]:checked') || {}).value || 'low');
            formData.append('description',    document.querySelector('[name="description"]').value.trim());
            formData.append('witness',        document.querySelector('[name="witness"]').value.trim());

            const photoInput = document.getElementById('photo-input');
            Array.from(photoInput.files).slice(0, 5).forEach(f => formData.append('photos[]', f));

            const docInput = document.querySelector('[name="document"]');
            if (docInput && docInput.files[0]) formData.append('document', docInput.files[0]);

            fetch(window.location.href, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        document.getElementById('modal-ref-id').textContent = json.reference;
                        document.getElementById('success-modal').classList.add('show');
                    } else {
                        showToast('Submission failed: ' + json.error, 'error');
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Report';
                    }
                })
                .catch(err => {
                    showToast('Network error. Please try again.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-paper-plane"></i> Submit Report';
                });
        }

        function closeModal() {
            document.getElementById('success-modal').classList.remove('show');
        }

        function previewImages(event) {
            const preview = document.getElementById('photo-preview');
            preview.innerHTML = '';
            Array.from(event.target.files).slice(0, 5).forEach(file => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        const dropzone = document.getElementById('dropzone');
        if (dropzone) {
            dropzone.addEventListener('dragover', (e) => { e.preventDefault(); dropzone.style.borderColor = '#ff2e2e'; });
            dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = '#333'; });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.style.borderColor = '#333';
                const input = document.getElementById('photo-input');
                const dt = new DataTransfer();
                Array.from(e.dataTransfer.files).slice(0, 5).forEach(f => dt.items.add(f));
                input.files = dt.files;
                previewImages({ target: input });
            });
        }

        function showToast(msg, type = 'info') {
            const existing = document.querySelector('.toast-msg');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'toast-msg';
            toast.innerHTML = `<i class="fa fa-${type === 'error' ? 'circle-xmark' : 'circle-info'}"></i> ${msg}`;

            Object.assign(toast.style, {
                position: 'fixed', bottom: '30px', right: '30px',
                background: type === 'error' ? '#ff2e2e' : '#6366f1',
                color: '#fff', padding: '12px 20px', borderRadius: '10px',
                fontSize: '13px', fontFamily: "'Poppins', sans-serif", fontWeight: '500',
                display: 'flex', alignItems: 'center', gap: '8px',
                boxShadow: '0 4px 20px rgba(0,0,0,0.4)', zIndex: '99999',
                animation: 'slideInToast 0.3s ease'
            });

            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.4s';
                setTimeout(() => toast.remove(), 400);
            }, 3000);
        }

        const style = document.createElement('style');
        style.textContent = `
@keyframes slideInToast {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}`;
        document.head.appendChild(style);
    </script>

</body>
</html>