<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../../Models/Zone.php";
$zones = Zone::getAll();

$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/P2P-Specialized-Tool-Library-SE1-";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zones Management | Tool Hub</title>
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

    <!-- فورم الإضافة -->
    <div class="card">
        <form method="POST" action="../../Controllers/ZoneController.php" class="filters">
            <input type="text" name="zone_name" placeholder="Enter zone name (e.g., Cairo, Giza)..." required>
            <input type="hidden" name="action" value="add">
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
                            <td><?php echo htmlspecialchars($zone['zone_id']); ?></td>
                            <td><?php echo htmlspecialchars($zone['zone_name']); ?></td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    
                                    <!-- زر التعديل: بيفتح صفحة تعديل ويبعت الـ ID -->
                                    <a href="edit_zone.php?id=<?php echo $zone['zone_id']; ?>" 
                                       class="btn btn-ghost btn-sm" 
                                       style="text-decoration: none; line-height: 2;">Edit</a>
                                    
                                    <!-- زر الحذف: بيبعت طلب حذف مباشر للكنترولر مع ID المنطقة -->
                                    <a href="../../Controllers/ZoneController.php?action=delete&id=<?php echo $zone['zone_id']; ?>" 
                                       class="btn btn-red btn-sm" 
                                       style="background: rgba(230,57,70,0.1); color: #e63946; border: 1px solid rgba(230,57,70,0.2); text-decoration: none; line-height: 2;"
                                       onclick="return confirm('Are you sure you want to delete this zone?')">
                                        Delete
                                    </a>

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