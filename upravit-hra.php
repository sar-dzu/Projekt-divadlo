<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';
require_once 'classes/Repriza.php';
require_once 'classes/Obrazok.php';
require_once 'classes/Kategoria.php';

use Classes\Database;
use Classes\Hra;
use Classes\Repriza;
use Classes\Obrazok;
use Classes\Kategoria;

$database = new Database();
$hra = new Hra($database);
$repriza = new Repriza($database);
$obrazok = new Obrazok($database);
$kategoria = new Kategoria($database);

// Všetky kategórie
$kategorie = $kategoria->getAllCategories();

// ID hry
if (!isset($_GET['id'])){
    echo "Chyba ID hry.";
    exit();
}
$id = (int) $_GET['id'];

// Získanie údajov o hre
$hraData = $hra->getHraById($id);
if (!$hraData) {
    echo "Hra neexistuje.";
    exit();
}

// Získanie obrázkov a kategórií
$obrazky = $obrazok->getObrazkyWithIdsByHraId($id);
$priradeneKategorie = $kategoria->getAssignedCategoryIds($id);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazov = $_POST['nazov'];
    $popis = $_POST['popis'];
    $zaciatok = $_POST['zaciatok'];
    $koniec = $_POST['koniec'] === '' ? null : $_POST['koniec'];
    $trvanie = (int) $_POST['trvanie'];
    $vek = (int) $_POST['vek'];

    if (!empty($nazov) && !empty($popis) && !empty($zaciatok) && $trvanie > 0 && $vek >= 0) {
        try {
            $success = $hra->update($id, $nazov, $popis, $zaciatok, $koniec, $trvanie, $vek);
            if ($success) {
                // Vymazanie obrázkov
                if (!empty($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $obrazokId) {
                        $obrazok->deleteImageById($obrazokId, $id);
                    }
                }

                // Aktualizácia kategórií
                if (!empty($_POST['kategorie'])) {
                    $kategoria->updateCategories($id, $_POST['kategorie']);
                }

                // Pridanie nových obrázkov
                if(!empty($_FILES['obrazky']['name']) && is_array($_FILES['obrazky']['name'])) {
                    foreach ($_FILES['obrazky']['tmp_name'] as $key => $tmp_name) {
                        $obrazok_nazov = basename($_FILES['obrazky']['name'][$key]);
                        $target = "assets/images/" . $obrazok_nazov;
                        if (move_uploaded_file($tmp_name, $target)) {
                            $obrazok->addImage($id, $obrazok_nazov);
                        }
                    }
                }

                header("Location: zobrazit-hry.php?updated=1");
                exit();
            } else {
                $error = "Nepodarilo sa upraviť predstavenie.";
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
                <?php foreach ($kategorie as $k): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="kategorie[]" value="<?= $k['id'] ?>"
                               id="kategoria<?= $k['id'] ?>"
                            <?= in_array($k['id'], $priradeneKategorie) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="kategoria<?= $k['id'] ?>">
                            <?= htmlspecialchars($k['nazov']) ?>
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
                <label for="trvanie">Dĺžka predstavenia (v minútach)</label>
                <input type="number" name="trvanie" id="trvanie" class="form-control" required min="20" max="240">
            </div>
            <div class="form-group">
                <label for="vek">Vekové obmedzenie</label>
                <input type="number" name="vek" id="vek" class="form-control" required min="0" max="18">
            </div>
            <?php if (!empty($obrazky)): ?>
                <div class="form-group">
                    <label>Existujúce obrázky</label>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <?php foreach ($obrazky as $o): ?>
                            <div style="position: relative;">
                                <img src="assets/images/<?= htmlspecialchars($o['obrazok']) ?>" style="width: 150px; height: auto; object-fit: cover;">
                                <label style="position: absolute; top: 5px; right: 5px; background: red; color: white; padding: 2px 6px; cursor: pointer;">
                                    <input type="checkbox" name="delete_images[]" value="<?= $o['id'] ?>" style="display: none;">
                                    X
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div>
                <label for="obrazky">Vyberte obrázky:</label>
                <input type="file" name="obrazky[]" multiple>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Uložiť zmeny</button>
        </form>
    </div>

<?php require_once "parts/footer.php"; ?>