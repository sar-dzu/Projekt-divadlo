<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';

use Classes\Database;
use Classes\Hra;

session_start();
$_SESSION['admin'] = true;
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: ../index.php');
    exit();
}

$database = new Database();
$hra = new Hra($database);

$kategorie = $hra->getAllCategories();

// ID hry
if (!isset($_GET['id'])){
    echo "Chyba ID hry.";
    exit();
}
$id = (int) $_GET['id'];

// data o hre
$stmt = $database->getConnection()->prepare("SELECT * FROM predstavenia WHERE id = :id");
$stmt->execute(['id' => $id]);
$hraData = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$hraData) {
    echo "Hra neexistuje.";
    exit();
}

// priradene kategorie
$stmt = $database->getConnection()->prepare("SELECT kategoria_id FROM predstavenie_kategoria WHERE predstavenie_id = :id");
$stmt->execute(['id' => $id]);
$priradeneKategorie = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'kategoria_id');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazov = $_POST['nazov'];
    $popis = $_POST['popis'];
    $zaciatok = $_POST['zaciatok'];
    $koniec = $_POST['koniec'];
    $koniec = $koniec === '' ? null : $koniec;
    $trvanie = (int) $_POST['trvanie'];
    $vek = (int) $_POST['vek'];

    if (!empty($nazov) && !empty($popis) && !empty($zaciatok) && $trvanie > 0 && $vek >= 0) {
        try {
            // Predpokladám, že máte metódu na aktualizáciu údajov v Hra triede
            $success = $hra->update($id, $nazov, $popis, $zaciatok, $koniec, $trvanie, $vek);
            if ($success) {
                // Úspešná aktualizácia
                if (!empty($_POST['kategorie'])) {
                    $hra->updateCategories($id, $_POST['kategorie']);
                }

                // Spracovanie obrázkov
                if(!empty($_FILES['obrazky']['name']) && is_array($_FILES['obrazky']['name'])) {
                    foreach ($_FILES['obrazky']['tmp_name'] as $key => $tmp_name) {
                        $obrazok_nazov = basename($_FILES['obrazky']['name'][$key]);
                        $target = "assets/images/" . $obrazok_nazov;
                        if (move_uploaded_file($tmp_name, $target)) {
                            $hra->addImage($id, $obrazok_nazov);
                        }
                    }
                }

                $msg = "Predstavenie bolo úspešne upravené.";
                header("Location: upravit-hra.php?id=$id&success=1");
                exit();
            } else {
                $error = "Nepodarilo sa upravit predstavenie.";
            }
        } catch (Exception $e) {
            $error = "Chyba: " . $e->getMessage();
        }
    } else {
        $error = "Vyplň všetky povinné polia správne!";
    }
}

?>

<?php require_once 'parts/head.php'?>

    <div class="container">
        <h1>Upraviť predstavenie</h1>

        <?php if (isset($msg)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="upravit-hra.php?id=<?= $id ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nazov">Názov hry</label>
                <input type="text" name="nazov" id="nazov" class="form-control" required value="<?= htmlspecialchars($hraData['nazov']) ?>">
            </div>

            <div class="form-group">
                <label for="popis">Popis hry</label>
                <textarea name="popis" id="popis" class="form-control" required><?= htmlspecialchars($hraData['popis']) ?></textarea>
            </div>

            <div class="form-group">
                <label>Kategórie</label><br>
                <?php foreach ($kategorie as $kategoria): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="kategorie[]" value="<?= $kategoria['id'] ?>"
                               id="kategoria<?= $kategoria['id'] ?>"
                            <?= in_array($kategoria['id'], $priradeneKategorie) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="kategoria<?= $kategoria['id'] ?>">
                            <?= htmlspecialchars($kategoria['nazov']) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label for="zaciatok">Dátum premiéry</label>
                <input type="date" name="zaciatok" id="zaciatok" class="form-control" required value="<?= htmlspecialchars($hraData['zaciatok_hrania']) ?>">
            </div>

            <div class="form-group">
                <label for="koniec">Dátum derniéry</label>
                <input type="date" name="koniec" id="koniec" class="form-control" value="<?= htmlspecialchars($hraData['koniec_hrania']) ?>">
            </div>

            <div class="form-group">
                <label for="trvanie">Dĺžka predstavenia</label>
                <input type="number" name="trvanie" id="trvanie" class="form-control" required value="<?= htmlspecialchars($hraData['trvanie']) ?>">
            </div>

            <div class="form-group">
                <label for="vek">Vekové obmedzenie</label>
                <input type="number" name="vek" id="vek" class="form-control" required value="<?= htmlspecialchars($hraData['vekove_obmedzenie']) ?>">
            </div>

            <button type="submit" class="btn btn-primary mt-3">Uložiť zmeny</button>
        </form>
    </div>

<?php require_once "parts/footer.php"; ?>