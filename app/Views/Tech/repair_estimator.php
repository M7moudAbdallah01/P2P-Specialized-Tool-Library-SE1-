<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'tool_library';
$success_msg = ""; // متغير لتخزين رسالة النجاح

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database Connection Failed"); }

// 1. جلب الأدوات مع أسمائها من قاعدة البيانات
try {
    $tools_stmt = $conn->query("SELECT id, name FROM tools"); 
    $tools = $tools_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $tools = [['id' => 1, 'name' => 'Default Tool']]; 
}

$labor_cost = 50.00;
$issues = [
    ['name' => 'Bearing Replacement', 'cost' => 85.00],
    ['name' => 'Motor Brush Change', 'cost' => 30.00],
    ['name' => 'Switch Repair', 'cost' => 20.00],
    ['name' => 'General Maintenance', 'cost' => 15.00]
];

// 2. منطق الحفظ والبقاء في نفس الصفحة
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_estimate'])) {
    $t_id = $_POST['tool_id'];
    $issue_name = $_POST['issue'];
    
    $parts_cost = 0;
    foreach($issues as $i) {
        if($i['name'] == $issue_name) { $parts_cost = $i['cost']; break; }
    }
    
    $total = $labor_cost + $parts_cost;
    $notes = "Automated Estimate: Labor $$labor_cost + Parts $$parts_cost = Total $$total";

    try {
        $sql = "INSERT INTO maintenance_logs (tool_id, issue, action, notes, date) 
                VALUES (:tid, :iss, 'Estimate Generated', :notes, CURDATE())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute(['tid' => $t_id, 'iss' => $issue_name, 'notes' => $notes]);

        // تعيين رسالة النجاح بدلاً من الـ Header Location
        $success_msg = "✅ Estimate saved successfully to database!";
    } catch(PDOException $e) {
        $error_msg = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <style>
        body { background-color: #0f0f0f; color: white; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; }
        .function-card { background: #1a1a1a; padding: 30px; border-radius: 12px; max-width: 700px; margin: auto; border: 1px solid #333; position: relative; }
        .brand { color: #e63946; text-transform: uppercase; border-bottom: 2px solid #e63946; display: inline-block; margin-bottom: 20px; font-size: 24px; }
        
        /* استايل رسالة النجاح */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 6px; text-align: center; font-weight: bold; }
        .alert-success { background: #1b4332; color: #74c69d; border: 1px solid #2d6a4f; }
        .alert-error { background: #5a1212; color: #ffb3b3; border: 1px solid #800000; }

        label { display: block; margin-top: 15px; color: #888; font-size: 12px; text-transform: uppercase; }
        select { width: 100%; padding: 12px; background: #252525; border: 1px solid #444; color: white; border-radius: 5px; margin-top: 8px; outline: none; transition: 0.3s; }
        select:focus { border-color: #e63946; }
        .summary { background: #212121; padding: 20px; border-radius: 8px; margin: 25px 0; border-left: 4px solid #e63946; }
        .summary p { margin: 5px 0; font-size: 14px; display: flex; justify-content: space-between; }
        .btn { width: 100%; padding: 16px; background: #e63946; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 16px; transition: 0.3s; }
        .btn:hover { background: #ff4d5a; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(230, 57, 70, 0.3); }
    </style>
</head>
<body>

<div class="function-card">
    <h2 class="brand">Repair Cost Estimator</h2>

    <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    
    <?php if (isset($error_msg)): ?>
        <div class="alert alert-error"><?= $error_msg ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Tool Identification (From Database)</label>
        <select name="tool_id" required>
            <option value="">-- Select Tool --</option>
            <?php foreach($tools as $t): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (#<?= $t['id'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <label>Diagnosed Issue</label>
        <select name="issue" id="issue_select" required>
            <option value="" data-price="0">-- Select Issue --</option>
            <?php foreach($issues as $i): ?>
                <option value="<?= $i['name'] ?>" data-price="<?= $i['cost'] ?>"><?= $i['name'] ?></option>
            <?php endforeach; ?>
        </select>

        <div class="summary">
            <p><span>Fixed Labor Cost:</span> <span>$<?= number_format($labor_cost, 2) ?></span></p>
            <p><span>Estimated Parts:</span> <span id="parts_disp">$0.00</span></p>
            <hr style="border: 0; border-top: 1px solid #333; margin: 15px 0;">
            <p style="font-size: 18px; font-weight: bold;">
                <span>Grand Total:</span> <span id="total_disp" style="color:#e63946;">$50.00</span>
            </p>
        </div>

        <button type="submit" name="save_estimate" class="btn"> SAVE Estimate </button>
    </form>
</div>

<script>
// تحديث الأسعار لحظياً في الواجهة
document.getElementById('issue_select').addEventListener('change', function() {
    const labor = <?= $labor_cost ?>;
    const parts = parseFloat(this.options[this.selectedIndex].getAttribute('data-price')) || 0;
    
    document.getElementById('parts_disp').innerText = '$' + parts.toFixed(2);
    document.getElementById('total_disp').innerText = '$' + (labor + parts).toFixed(2);
});

// إخفاء رسالة النجاح تلقائياً بعد 4 ثوانٍ
setTimeout(function() {
    let alert = document.querySelector('.alert-success');
    if(alert) alert.style.display = 'none';
}, 4000);
</script>

</body>
</html>
