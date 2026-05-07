<?php
// 1. الاتصال بقاعدة البيانات (Single File Logic)
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'tool_library';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // ضبط جلب البيانات لتكون مصفوفة مرتبة
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// 2. جلب البيانات من جدول الصيانة
// هنستخدم الأعمدة اللي شفناها في الداتا بيز عندك (tool_id, issue, action, notes, date)
$stmt = $conn->query("SELECT * FROM maintenance_logs WHERE action LIKE '%External%' OR action LIKE '%Sent%'");
$repairs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <style>
        /* تنسيق سريع عشان الصفحة متبقاش بيضاء */
        :root { --red: #e63946; --bg: #0f0f0f; --surface: #1a1a1a; --text: #fff; }
        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', sans-serif; padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .card { background: var(--surface); border: 1px solid #333; border-radius: 8px; padding: 20px; position: relative; }
        .card::before { content: ""; position: absolute; left: 0; top: 0; height: 100%; width: 4px; background: var(--red); }
        .tool-id { color: var(--red); font-weight: bold; margin-bottom: 15px; display: block; }
        .detail { margin-bottom: 10px; font-size: 14px; display: flex; justify-content: space-between; }
        .label { color: #888; text-transform: uppercase; font-size: 11px; }
        .value { text-align: right; font-weight: 500; }
        .status-badge { background: rgba(230, 57, 70, 0.1); color: var(--red); padding: 2px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

    <h2 style="letter-spacing: 1px;">EXTERNAL WORKFLOW MANAGEMENT</h2>
    <p style="color: #888; font-size: 12px; margin-bottom: 30px;">REAL-TIME THIRD-PARTY TRACKING</p>

    <div class="grid">
        <?php if (!empty($repairs)): ?>
            <?php foreach ($repairs as $r): ?>
                <div class="card">
                    <span class="tool-id">TOOL #<?= $r['tool_id']; ?></span>
                    
                    <div class="detail">
                        <span class="label">Diagnosed Issue:</span>
                        <span class="value"><?= $r['issue']; ?></span>
                    </div>

                    <div class="detail">
                        <span class="label">Current Workflow:</span>
                        <span class="value"><span class="status-badge"><?= $r['action']; ?></span></span>
                    </div>

                    <div class="detail">
                        <span class="label">Service Shop:</span>
                        <span class="value"><?= $r['notes']; ?></span>
                    </div>

                    <div class="detail" style="margin-top: 15px; border-top: 1px solid #333; padding-top: 10px;">
                        <span class="label">Dispatch Date:</span>
                        <span class="value" style="color: #666;"><?= $r['date']; ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; grid-column: 1/-1; padding: 50px; border: 1px dashed #333;">
                <p>No tools are currently at external shops.</p>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
