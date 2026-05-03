<?php
// تفعيل الأخطاء عشان لو فيه حاجة وقفت نعرفها فوراً
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../../Models/Zone.php";
// التأكد من استدعاء البيانات باستخدام الميثود اللي صلحناها
$zones = Zone::getAll();

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/P2P-Specialized-Tool-Library-SE1-";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zones Management | Tool Hub</title>
    
    <!-- الروابط الأصلية بتاعتك عشان الـ CSS يفضل مظبوط -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/app/assets/Css/admin.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/app/assets/Css/zone.css?v=<?php echo time(); ?>">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="welcome">
        <h1>Zone Management</h1>
        <p>Add and organize service delivery areas for your library.</p>
    </div>

    <div class="card">
        <!-- تأكد إن اسم الـ input هو zone_name عشان يطابق الـ Controller الجديد -->
        <form method="POST" action="../../Controllers/ZoneController.php" class="filters">
            <input type="text" name="zone_name" placeholder="Enter zone name (e.g., Cairo, Giza)..." required>
            <input type="hidden" name="add_zone" value="1">
            <button type="submit" class="btn btn-red">Add Zone</button>
        </form>
    </div>

    <div class="card">
        <h3 style="margin-top: 0; margin-bottom: 20px;">All Registered Zones</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Zone Name</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($zones) && is_array($zones)): ?>
                    <?php foreach($zones as $zone): ?>
                        <tr>
                            <!-- التعديل هنا: استخدمنا الأسماء الجديدة للأعمدة zone_id و zone_name -->
                            <td><?php echo htmlspecialchars($zone['zone_id']); ?></td>
                            <td><?php echo htmlspecialchars($zone['zone_name']); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button class="btn btn-ghost btn-sm" type="button">Edit</button>
                                    <button class="btn btn-red btn-sm" type="button" 
                                            style="background: rgba(230,57,70,0.1); color: #e63946; border: 1px solid rgba(230,57,70,0.2);">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding: 40px;" class="tool-sub">No zones found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>