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
$hraObj = new Hra($database);
$kategoria = $_GET['kategoria'] ?? null;

if ($kategoria) {
    $hry = $hraObj->getByCategory($kategoria);
} else {
    $hry = $hraObj->getAllOrderedByDateLogic();
}
$kategorie = $hraObj->getAllCategories();
?>

<?php require_once 'parts/head.php'?>
<div class="properties">
    <ul class="properties-filter">
        <li>
            <a class="<?= !isset($_GET['kategoria']) ? 'is_active' : '' ?>" href="zobrazit-hry.php">Zobraziť všetky</a>
        </li>
        <?php foreach ($kategorie as $kat):
            $nazov = $kat['nazov'];
            ?>
            <li>
                <a class="<?= (isset($_GET['kategoria']) && $_GET['kategoria'] === $nazov) ? 'is_active' : '' ?>"
                   href="zobrazit-hry.php?kategoria=<?= urlencode($nazov) ?>">
                    <?= htmlspecialchars($nazov, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

</div>
<div class="container">
    <div style="margin: 1rem 0;">
        <a href="pridat-hra.php" class="btn btn-primary" style="background-color: #6c4a4a; border: none; padding: 10px 20px; border-radius: 6px; color: white; text-decoration: none;">
            + Pridať inscenáciu
        </a>
    </div>
    <h1 style="margin-bottom: 2rem;">Repertoár divadla</h1>
    <?php if (isset($_GET['kategoria']) && !empty($_GET['kategoria'])): ?>
        <h4 style="margin-bottom: 1rem;">Filtrované podľa: <?= htmlspecialchars($_GET['kategoria'], ENT_QUOTES, 'UTF-8') ?></h4>
    <?php endif; ?>
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
            $reprizy = $hraObj->getReprizy($hra['id']);
            ?>

            <div class="hra-box <?= $katClasses ?>">
                <div class="hra-nazov">
                    <h2><?= htmlspecialchars($hra['nazov'] ?? 'Neznámy názov', ENT_QUOTES, 'UTF-8') ?></h2>
                </div>
                <?php if (!empty($hra['hlavny_obrazok'])): ?>
                    <img src="assets/images/<?= htmlspecialchars($hra['hlavny_obrazok'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($hra['nazov'] ?? 'Neznámy názov', ENT_QUOTES, 'UTF-8') ?>"
                         style="display: block; margin: 10px 0; max-width: 200px; height: auto;">
                <?php else: ?>
                    <img src="assets/images/featured1.jpg" alt="Bez obrázku" style="display: block; margin: 10px 0; max-width: 200px; height: auto;">
                <?php endif; ?>

                <div class="hra-info">
                    <p><?= htmlspecialchars($hra['popis'] ?? 'Žiadny popis k dispozícii.', ENT_QUOTES, 'UTF-8') ?></p>
                    <p><strong>Kategórie:</strong> <?= !empty($hra['kategorie']) ? htmlspecialchars($hra['kategorie'], ENT_QUOTES, 'UTF-8') : 'N/A' ?></p>

                    <p>
                        <?php if (!empty($hra['koniec_hrania'])): ?>
                            <strong>Derniéra:</strong> <?= date('d.m.Y', strtotime($hra['koniec_hrania'])) ?>
                        <?php elseif (!empty($hra['triedenie'])): ?>
                            <strong>Najbližšie:</strong> <?= date('d.m.Y', strtotime($hra['triedenie'])) ?>
                        <?php else: ?>
                            <em>Nemá dátum derniéry</em>
                        <?php endif; ?>
                    </p>

                    <a href="upravit-hra.php?id=<?= $hra['id'] ?>" class="btn btn-secondary">Upraviť</a>
                    <a href="vymazat-hra.php?id=<?= $hra['id'] ?>" class="btn btn-danger" onclick="return confirm('Naozaj chceš vymazať toto predstavenie?')">Vymazať</a>
                </div>
                <div style="margin-bottom: 2rem;">
                    <?php if (!empty($reprizy)): ?>
                        <div class="reprizy">
                            <strong>Nadchádzajúce reprízy:</strong>
                            <ul>
                                <?php foreach ($reprizy as $r): ?>
                                    <li style="margin: 0.3rem 0;">
                                        <?= date('d.m.Y H:i', strtotime($r['datum_cas'])) ?>
                                        <a href="vymazat-reprizu.php?id=<?= $r['id'] ?>" onclick="return confirm('Naozaj chceš vymazať túto reprízu?')" style="color: red; margin-left: 10px;">&times;</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <?php
                    $koniec = $hra['koniec_hrania'] ?? null;
                    $mozePridat = empty($koniec) || strtotime($koniec) > time();
                    ?>

                    <?php if ($mozePridat): ?>
                        <a href="pridat-reprizu.php?predstavenie_id=<?= $hra['id'] ?>" class="btn btn-dark">Pridať reprízu</a>
                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>
    </div>
</div>
<?php require_once "parts/footer.php"; ?>
