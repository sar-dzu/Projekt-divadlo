<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once('parts/head.php');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';

use Classes\Database;
use Classes\Hra;

$db = new Database();
$hraObj = new Hra($db);
$predstavenia = $hraObj->getAllOrderedByDateLogic();


require_once 'classes/Hra.php';

$kategoria = $_GET['kategoria'] ?? null;

if ($kategoria) {
    $hry = $hraObj->getByCategory($kategoria);
} else {
    $hry = $hraObj->getAllOrderedByDateLogic();
}
$kategorie = $hraObj->getAllCategories();


?>

  <!-- ***** Header Area End ***** -->

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <span class="breadcrumb"><a href="index.php">Home</a> / Predstavenia</span>
          <h3>Predstavenia</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="section properties">
    <div class="container">
        <ul class="properties-filter">
            <li><a class="<?= !$kategoria ? 'is_active' : '' ?>" href="vsetky-hry.php">Zobraziť všetky</a></li>
            <?php foreach ($kategorie as $kat): ?>
                <?php
                $katSlug = strtolower(preg_replace('/\s+/', '-', $kat['nazov']));
                $isActive = ($kategoria === $kat['nazov']) ? 'is_active' : '';
                ?>
                <li>
                    <a class="<?= $isActive ?>" href="?kategoria=<?= urlencode($kat['nazov']) ?>" data-filter=".<?= $katSlug ?>">
                        <?= htmlspecialchars($kat['nazov']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="row properties-box">
            <?php foreach ($hry as $hra): ?>
                <?php
                // CSS triedy z kategórií
                $kategorieTriedy = '';
                if (!empty($hra['kategorie'])) {
                    $kategorieArray = array_map('trim', explode(',', $hra['kategorie']));
                    $kategorieTriedy = implode(' ', array_map(function ($k) {
                        return strtolower(preg_replace('/\s+/', '-', $k));
                    }, $kategorieArray));
                }
                ?>
                <div class="col-lg-4 col-md-6 align-self-center mb-30 properties-items <?= $kategorieTriedy ?>">
                    <div class="item">
                        <a href="detail-predstavenia.php?id=<?= $hra['id'] ?>">
                            <?php if (!empty($hra['hlavny_obrazok'])): ?>
                                <img src="assets/images/<?= htmlspecialchars($hra['hlavny_obrazok'], ENT_QUOTES, 'UTF-8') ?>"
                                     alt="<?= htmlspecialchars($hra['nazov'] ?? 'Neznámy názov', ENT_QUOTES, 'UTF-8') ?>"
                                     style="object-fit: cover; width: 100%; aspect-ratio: 1 / 1;">
                            <?php else: ?>
                                <img src="assets/images/featured1.jpg"
                                     alt="Bez obrázku"
                                     style="object-fit: cover; width: 100%; aspect-ratio: 1 / 1;">
                            <?php endif; ?>
                        </a>

                        <span class="category"><?= htmlspecialchars($hra['kategorie']) ?></span>

                        <ul>
                            <li>Vekové obmedzenie:
                                <span>
                                  <?= $hra['vekove_obmedzenie'] ? "od " . htmlspecialchars($hra['vekove_obmedzenie']) . " rokov" : "Bez obmedzenia" ?>
                                </span>
                            </li>
                            <li>Dĺžka predstavenia:
                                <span><?= $hra['trvanie'] ? htmlspecialchars($hra['trvanie']) . " minút" : "Neuvedené" ?></span>
                            </li>
                            <li>Vstupné: <span>Dobrovoľné</span></li>
                        </ul>
                        <div class="main-button">
                            <a href="detail-predstavenia.php?id=<?= $hra['id'] ?>">Zobraziť detaily</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
  </div>

<?php
$file_path = "parts/footer.php";
if (!include $file_path) {
    echo "Failed to include $file_path";
}
?>

