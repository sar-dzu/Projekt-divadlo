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
$hry = $hra->getAllOrderedByDateLogic();
$kategorie = $hra->getAllCategories();
?>

<?php require_once 'parts/head.php'?>
<ul class="properties-filter">
    <li>
        <a class="is_active" href="#!" data-filter="*">Zobraziť všetky</a>
    </li>
    <?php if (!empty($kategorie)): ?>
        <?php foreach ($kategorie as $kat): ?>
            <?php if (!empty($kat)): ?>
                <li>
                    <a href="#!" data-filter=".<?= strtolower(preg_replace('/\s+/', '-', $kat)) ?>">
                        <?= htmlspecialchars($kat ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</ul>
<div class="container">
    <h1>Repertoár divadla</h1>
    <div class="hry-grid">
        <?php foreach ($hry as $hra): ?>
            <?php
            $katClasses = '';
            // Kontrola, či existuje kľúč kategorie a nie je prázdny
            if (isset($hra['kategorie']) && !empty($hra['kategorie'])) {
                $katArray = array_map('trim', explode(',', $hra['kategorie']));
                $katClasses = implode(' ', array_map(function ($k) {
                    return strtolower(preg_replace('/\s+/', '-', $k));
                }, $katArray));
            }
            ?>

            <div class="hra-box <?= $katClasses ?>">
                <?php if (!empty($hra['hlavny_obrazok'])): ?>
                    <img src="../assets/images/<?= htmlspecialchars($hra['hlavny_obrazok'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($hra['nazov'] ?? 'Neznámy názov', ENT_QUOTES, 'UTF-8') ?>">
                <?php else: ?>
                    <img src="../images/featured.jpg" alt="Bez obrázku">
                <?php endif; ?>

                <div class="hra-info">
                    <h2><?= htmlspecialchars($hra['nazov'] ?? 'Neznámy názov', ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($hra['popis'] ?? 'Žiadny popis k dispozícii.', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Kategórie:</strong> <?= !empty($hra['kategorie']) ? htmlspecialchars($hra['kategorie'], ENT_QUOTES, 'UTF-8') : 'N/A' ?></p>

                    <p>
                        <?php if (!empty($hra['koniec_hrania'])): ?>
                            <strong>Derniéra:</strong> <?= date('d.m.Y', strtotime($hra['koniec_hrania'])) ?>
                        <?php elseif (!empty($hra['triedenie'])): ?>
                            <strong>Najbližšie:</strong> <?= date('d.m.Y', strtotime($hra['triedenie'])) ?>
                        <?php else: ?>
                            <em>Zatiaľ bez termínu</em>
                        <?php endif; ?>
                    </p>

                    <a href="hra-detail.php?id=<?= $hra['id'] ?>" class="btn">Zobraziť</a>
                    <a href="upravit-hra.php?id=<?= $hra['id'] ?>" class="btn btn-edit">Upraviť</a>
                    <button class="btn btn-delete" onclick="confirmDelete(<?= $hra['id'] ?>)">Vymazať</button>
                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>
<?php require_once "parts/footer.php"; ?>
