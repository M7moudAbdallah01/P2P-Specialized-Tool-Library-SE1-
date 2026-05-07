<?php
// إظهار الأخطاء
ini_set('display_errors', 1);
error_reporting(E_ALL);

// اتصال قاعدة البيانات
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'tool_library';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Database Error: " . $e->getMessage()); }

// الفانكشن بتاعة الزرار
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'restock') {
    $id = $_POST['item_id'];
    $stmt = $conn->prepare("UPDATE consumables SET stock = stock + 10 WHERE id = :id");
    if($stmt->execute(['id' => $id])) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// جلب البيانات
$items = $conn->query("SELECT * FROM consumables")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Consumables</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: #fff; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border-bottom: 1px solid #eee; padding: 12px; text-align: left; }
        .status-pill { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .low { background: #ffebee; color: #c62828; }
        .stable { background: #e8f5e9; color: #2e7d32; }
        .restock-btn { color: #1a73e8; background: none; border: none; cursor: pointer; text-decoration: underline; font-weight: bold; }
    </style>
</head>
<body>
    <h2>CONSUMABLES INVENTORY</h2>
    <table>
        <thead>
            <tr>
                <th>CONSUMABLE ITEM</th>
                <th>LINKED TOOL</th>
                <th>STOCK</th>
                <th>STATUS</th>
                <th>ACTION</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items): foreach ($items as $item): ?>
            <tr>
                <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                <td><?= htmlspecialchars($item['linked_tool']) ?></td>
                <td><?= $item['stock'] ?> <?= $item['unit'] ?></td>
                <td>
                    <?php if ($item['stock'] <= 0): ?>
                        <span class="status-pill low">OUT OF STOCK</span>
                    <?php elseif ($item['stock'] <= $item['min_limit']): ?>
                        <span class="status-pill low">LOW STOCK</span>
                    <?php else: ?>
                        <span class="status-pill stable">STABLE</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="action" value="restock">
                        <button type="submit" class="restock-btn">Restock</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5">No items found. Run the SQL code first!</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
