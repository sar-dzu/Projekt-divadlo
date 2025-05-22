<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db/config.php";
require_once 'classes/Database.php';

$database = new \Classes\Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['predstavenie_id'];
    $datetime = $_POST['datum_cas'];

    $zvolenyCas = strtotime($datetime);
    $teraz = time();

    if ($zvolenyCas === false || $zvolenyCas < $teraz) {
        $error = "Zvolený dátum a čas musí byť v budúcnosti.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reprizy (predstavenie_id, datum_cas) VALUES (:id, :cas)");
        $stmt->execute([
            'id' => $id,
            'cas' => $datetime
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
        <button type="submit">Pridať reprízu</button>
    </form>
</div>
<?php if (!empty($error)): ?>
    <div style="color: red; margin: 1rem;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
<?php require_once "parts/footer.php";?>