<?php
require_once "../../Models/Zone.php";
$id = $_GET['id'] ?? null;
$zones = Zone::getAll();
$currentZone = null;
foreach($zones as $z) {
    if($z['zone_id'] == $id) { $currentZone = $z; break; }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Zone | Tool Hub</title>
    <link rel="stylesheet" href="../../assets/Css/admin.css">
</head>
<body style="background: #0f0f0f; color: white; padding: 50px;">
    <div class="card" style="max-width: 500px; margin: auto;">
        <h2>Edit Zone Name</h2>
        <form method="POST" action="../../Controllers/ZoneController.php">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="zone_id" value="<?php echo $currentZone['zone_id']; ?>">
            <input type="text" name="new_name" value="<?php echo $currentZone['zone_name']; ?>" 
                   style="width: 100%; padding: 10px; margin: 20px 0; background: #222; border: 1px solid #444; color: white;">
            <button type="submit" class="btn btn-red" style="width: 100%;">Update Zone</button>
        </form>
    </div>
</body>
</html>