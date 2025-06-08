<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once "db/config.php";
require_once 'classes/Database.php';
require_once 'classes/Repriza.php';

use Classes\Database;
use Classes\Repriza;

$database = new Database();
$repriza = new Repriza($database);
$conn = $database->getConnection();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['predstavenie_id'];
    $datetime = $_POST['datum_cas'];
    $kapacita = (int)$_POST['kapacita'];

    $zvolenyCas = strtotime($datetime);
    $teraz = time();

    if (!filter_var($kapacita, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
        $error = "Kapacita musí byť celé kladné číslo.";
    } elseif ($zvolenyCas === false || $zvolenyCas < $teraz) {
        $error = "Zvolený dátum a čas musí byť v budúcnosti.";
    } else {
        if ($repriza->addRepriza($id, $datetime, $kapacita)) {
            header('Location: zobrazit-hry.php');
            exit();
        } else {
            $error = "Nepodarilo sa pridať reprízu.";
        }
    }
}

$predstavenie_id = $_GET['predstavenie_id'] ?? '';
$nazov = '';

if ($predstavenie_id) {
    $stmt = $conn->prepare("SELECT nazov FROM predstavenia WHERE id = :id");
    $stmt->execute(['id' => $predstavenie_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $nazov = $result['nazov'];
    }
}
?>

<?php require_once 'parts/head.php'; ?>
<div class="container">
    <h1 style="margin-bottom: 2rem;">Pridať reprízu pre: <?= htmlspecialchars($nazov) ?></h1>
    <form method="POST">
        <input type="hidden" name="predstavenie_id" value="<?= htmlspecialchars($predstavenie_id) ?>">

        <label for="datum_cas">Dátum a čas reprízy:</label>
        <input type="datetime-local" name="datum_cas" required>

        <label for="kapacita" style="margin-top: 1rem;">Kapacita (počet miest):</label>
        <input type="number" name="kapacita" min="0" required>

        <button type="submit" style="margin-top: 1rem;">Pridať reprízu</button>
    </form>
</div>
<?php if (!empty($error)): ?>
    <div style="color: red; margin: 1rem;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
<?php require_once "parts/footer.php";?>
