<?phprequire_once __DIR__ . "/../Models/Zone.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['zone'])) {
    $zoneName = $_POST['zone'];

    Zone::add($zoneName);

    header("Location: ../Views/Admin/zones.php");
    exit();
} else {
    header("Location: ../Views/Admin/zones.php");
    exit();
}