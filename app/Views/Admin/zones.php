<?php
require_once "../../Models/Zone.php";
$zones = Zone::getAll();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إدارة المناطق | Zones Management</title>
    <!-- ربط ملف الـ CSS -->
    <link rel="stylesheet" href="../../assets/Css/zones.css">
</head>
<body>

<div class="container">
    <div class="header-section">
        <h2>Add New Zone</h2>
        <form method="POST" action="../../Controllers/ZoneController.php" class="add-form">
            <input type="text" name="zone" placeholder="Enter Zone Name..." required>
            <button type="submit">Add Zone</button>
        </form>
    </div>

    <hr>

    <div class="list-section">
        <h3>All Registered Zones</h3>
        <table class="zones-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Zone Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($zones)): ?>
                    <?php foreach($zones as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td>
                                <button class="btn-edit">Edit</button>
                                <button class="btn-delete">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No zones found in database.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>