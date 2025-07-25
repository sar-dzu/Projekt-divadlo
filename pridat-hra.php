<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Získanie všetkých kategórií pre formulár
$kategorie = $kategoria->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nazov = $_POST['nazov'] ?? '';
    $popis = $_POST['popis'] ?? '';
    $zaciatok = $_POST['zaciatok'] ?? '';
    $koniec = $_POST['koniec'] ?? '';
    $koniec = $koniec === '' ? null : $koniec;
    $trvanie = (int) ($_POST['trvanie'] ?? 0);
    $vek = (int) ($_POST['vek'] ?? 0);

    if (!empty($nazov) && !empty($popis) && !empty($zaciatok) && $trvanie > 0 && $vek >= 0) {
        try {
            $success = $hra->create($nazov, $popis, $zaciatok, $koniec, $trvanie, $vek);
            if ($success) {
                $hra_id = $hra->getLastInsertedId();

                // Priradenie kategórií
                if (!empty($_POST['kategorie'])) {
                    $kategoria->addCategories($hra_id, $_POST['kategorie']);
                }

                // Pridanie obrázkov
                if (!empty($_FILES['obrazky']['name']) && is_array($_FILES['obrazky']['name'])) {
                    foreach ($_FILES['obrazky']['tmp_name'] as $key => $tmp_name) {
                        $obrazok_nazov = basename($_FILES['obrazky']['name'][$key]);
                        $target = "assets/images/" . $obrazok_nazov;
                        if (move_uploaded_file($tmp_name, $target)) {
                            $obrazok->addImage($hra_id, $obrazok_nazov);
                        }
                    }
                }

                header("Location: pridat-hra.php?success=1");
                exit;
            } else {
                $error = "Nepodarilo sa pridať predstavenie.";
            }
        } catch (Exception $e) {
            $error = "Chyba: " . $e->getMessage();
        }
    } else {
        $error = "Vyplň všetky povinné polia správne!";
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $msg = "Predstavenie bolo úspešne pridané!";
}
?>

<?php require_once 'parts/head.php'; ?>


<div class="container">
    <h1>Pridať predstavenie</h1>
    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="pridat-hra.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="nazov">Názov hry</label>
            <input type="text" name="nazov" id="nazov" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="popis">Popis hry</label>
            <textarea name="popis" id="popis" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label>Kategórie</label><br>
            <?php foreach ($kategorie as $kategoria): ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="kategorie[]" value="<?= htmlspecialchars($kategoria['id']) ?>" id="kategoria<?= $kategoria['id'] ?>">
                    <label class="form-check-label" for="kategoria<?= $kategoria['id'] ?>">
                        <?= htmlspecialchars($kategoria['nazov']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label for="zaciatok">Dátum premiéry</label>
            <input type="date" name="zaciatok" id="zaciatok" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="koniec">Dátum derniéry</label>
            <input type="date" name="koniec" id="koniec" class="form-control">
        </div>

        <div class="form-group">
            <label for="trvanie">Dĺžka predstavenia (v minútach)</label>
            <input type="number" name="trvanie" id="trvanie" class="form-control" required min="20" max="240">
        </div>
        <div class="form-group">
            <label for="vek">Vekové obmedzenie</label>
            <input type="number" name="vek" id="vek" class="form-control" required min="0" max="18">
        </div>
        <div>
            <label for="obrazky">Vyberte obrázky:</label>
            <input type="file" name="obrazky[]" multiple>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Pridať predstavenie</button>
    </form>
</div>

<?php require_once "parts/footer.php";?>