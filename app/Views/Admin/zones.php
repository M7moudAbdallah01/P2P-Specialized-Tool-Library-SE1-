<?php
require_once "../../Models/Zone.php";
$zones = Zone::getAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Zones</title>
    <link rel="stylesheet" href="../../assets/Css/zones.css">
</head>
<body>

<h2>Add Zone</h2>

<form method="POST" action="../../Controllers/ZoneController.php">
    <input type="text" name="zone" placeholder="Zone Name" required>
    <button>Add</button>
</form>

<hr>

<h3>All Zones:</h3>

<?php while($row = mysqli_fetch_assoc($zones)){ ?>
    <p><?php echo $row['name']; ?></p>
<?php } ?>

</body>
</html>