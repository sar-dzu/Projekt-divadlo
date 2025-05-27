<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php'); // alebo prihlasenie.php
    exit;
}
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db/config.php";
require_once 'classes/Database.php';

$database = new \Classes\Database();
$conn = $database->getConnection();


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['predstavenie_id'];
    $datetime = $_POST['datum_cas'];
    $kapacita = $_POST['kapacita'];

    $zvolenyCas = strtotime($datetime);
    $teraz = time();

    // Validacia kapacity
    if (!filter_var($kapacita, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]])) {
        $error = "Kapacita musí byť celé kladné číslo.";
    } elseif ($zvolenyCas === false || $zvolenyCas < $teraz) {
        $error = "Zvolený dátum a čas musí byť v budúcnosti.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reprizy (predstavenie_id, datum_cas, kapacita) VALUES (:id, :cas, :kapacita)");
        $stmt->execute([
            'id' => $id,
            'cas' => $datetime,
            'kapacita' => $kapacita
        ]);
        header('Location: zobrazit-hry.php');
        exit();
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
