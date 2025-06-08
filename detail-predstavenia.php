<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once('parts/head.php');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';
require_once 'classes/Faq.php';
require_once 'classes/Repriza.php';
require_once 'classes/Obrazok.php';


use Classes\Faq;
use Classes\Database;
use Classes\Hra;
use Classes\Repriza;
use Classes\Obrazok;


$db = new Database();
$hra = new Hra($db);
$repriza = new Repriza($db);
$obrazok = new Obrazok($db);


$faq = new Faq($db);
$questions = $faq->getAll();


// Získaj ID z URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Získaj údaje o predstavení podľa ID
$predstavenie = $hra->getHraById($id);
$reprizy = $repriza->getReprizy($id);
if (!$predstavenie) {
    die('Predstavenie nebolo nájdené.');
}

$obrazky = $obrazok->getObrazkyByHraId($id);
if (empty($obrazky)) {
    $obrazky = ['featured1.jpg']; // fallback, ak nemá obrázky
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repriza_id'])) {
    $reprizaId = (int)$_POST['repriza_id'];
    if ($repriza->buyTicket($reprizaId)) {
        $message = "Lístok bol úspešne zakúpený.";
        // Obnovíme reprízy, aby sa aktualizovala kapacita
        $reprizy = $repriza->getReprizy($id);
    } else {
        $message = "Lístok sa nepodarilo zakúpiť (možno už nie sú voľné miesta).";
    }
}

// odporucane
$idAktualneho = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$odporucane = $hra->getRecommendedHra($idAktualneho);

?>

  <!-- ***** Header Area End ***** -->

    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <span class="breadcrumb"><a href="index.php">Home</a>  / <a href="predstavenia.php">Predstavenia</a> /  <?php echo htmlspecialchars($predstavenie['nazov']); ?></span>
                    <h3><?php echo htmlspecialchars($predstavenie['nazov']); ?></h3>
                </div>
            </div>
        </div>
    </div>


<div class="single-property section">
    <div class="container">
        <div class="row">
            <div class="row mb-4">
                <!-- Carousel obrázkov -->
                <div class="col-12 col-md-8">
                    <div class="main-image" style="position: relative; text-align: center;">
                        <!-- LEFT BUTTON -->
                        <button id="prevBtn"
                                style="position: absolute; top: 50%; left: 0; transform: translateY(-50%); z-index: 10;
                       background: rgba(255,255,255,0.7); border: none; font-size: 24px; padding: 8px 12px;
                       cursor: pointer; border-radius: 50%;">
                            &#10094;
                        </button>

                        <!-- IMAGE -->
                        <img id="carouselImage"
                             src="assets/images/<?php echo htmlspecialchars($obrazky[0]); ?>"
                             alt="Obrázok predstavenia"
                             style="
                               display: block;
                               margin-left: auto;
                               margin-right: auto;
                               max-height: 500px;
                               height: auto;
                               max-width: 100%;
                               width: auto;
                               border-radius: 8px;
                             ">

                        <!-- RIGHT BUTTON -->
                        <button id="nextBtn"
                                style="position: absolute; top: 50%; right: 0; transform: translateY(-50%); z-index: 10;
                       background: rgba(255,255,255,0.7); border: none; font-size: 24px; padding: 8px 12px;
                       cursor: pointer; border-radius: 50%;">
                            &#10095;
                        </button>
                    </div>
                </div>

                <!-- Info tabuľka -->
                <div class="col-12 col-md-4">
                    <div class="info-table">
                        <ul>
                            <li>
                                <h4>Vekové obmedzenie<br>
                                    <span>
                        <?= !empty($predstavenie['vekove_obmedzenie']) ?
                            "Nie je vhodné pre divákov mladších ako " . htmlspecialchars($predstavenie['vekove_obmedzenie']) . " rokov"
                            : "Bez obmedzenia" ?>
                        </span>
                                </h4>
                            </li>
                            <li>
                                <h4>Trvanie<br>
                                    <span>
                        <?= !empty($predstavenie['trvanie']) ?
                            htmlspecialchars($predstavenie['trvanie']) . " minút"
                            : "Neuvedené" ?>
                        </span>
                                </h4>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Zvyšný obsah pod tým -->
            <div class="main-content">
                <?php if ($message): ?>
                    <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 1rem; border-radius: 5px;">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <span class="category"><?= htmlspecialchars($predstavenie['kategorie']); ?></span>


                <?php if (!empty($reprizy)): ?>
                    <h4>Nadchádzajúce predstavenia:</h4>
                    <ul>
                        <?php foreach ($reprizy as $r): ?>
                            <li style="margin-bottom: 1rem;">
                                <h5><?= date('d.m.Y H:i', strtotime($r['datum_cas'])); ?></h5>
                                (Voľné miesta: <?= htmlspecialchars($r['kapacita']) ?>)
                                <form method="POST" style="display:inline;"
                                      onsubmit="return confirm('Naozaj chcete kúpiť lístok na tento dátum?');">
                                    <input type="hidden" name="repriza_id" value="<?= $r['id'] ?>">
                                    <button type="submit" <?= ($r['kapacita'] <= 0) ? 'disabled' : '' ?>>Kúpiť lístok</button>
                                </form>
                                <?php if ($r['kapacita'] <= 0): ?>
                                    <span style="color: red;">(Vypredané)</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif (!empty($predstavenie['koniec_hrania'])): ?>
                    <h4>Posledné predstavenie: <?= date('d.m.Y', strtotime($predstavenie['koniec_hrania'])); ?></h4>
                <?php else: ?>
                    <h4>Zatiaľ nie sú nadchádzajúce reprízy, pre prípadný záujem <a href="contact.php">nás kontaktujte</a>.</h4>
                <?php endif; ?>


                <p><?= nl2br(htmlspecialchars($predstavenie['popis'])); ?></p>

                <!-- Accordion -->
                <div class="accordion" id="accordionExample">
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>"
                                        aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                    <?= htmlspecialchars($q['otazka']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>"
                                 class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                                 aria-labelledby="heading<?= $index ?>" data-bs-parent="#accordionExample">
                                <div class="accordion-body">
                                    <?= nl2br(htmlspecialchars($q['odpoved'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
      </div>
    </div>


    <div class="section best-deal">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-heading">
                        <h6>| Odporúčame</h6>
                        <h2>Pozrite si aj toto predstavenie</h2>
                    </div>
                </div>

                <?php if ($odporucane): ?>
                    <div class="col-lg-4">
                        <img src="assets/images/<?= htmlspecialchars($odporucane['hlavny_obrazok'] ?? 'featured1.jpg') ?>" alt="<?= htmlspecialchars($odporucane['nazov']) ?>" class="img-fluid">
                    </div>
                    <div class="col-lg-8">
                        <h4 style="margin-bottom: 1rem;"><?= htmlspecialchars($odporucane['nazov']) ?></h4>
                        <ul style="margin-bottom: 1rem;">
                            <li>Dátum: <span><?= date('d.m.Y H:i', strtotime($odporucane['datum_cas'])) ?></span></li>
                            <li>Dĺžka predstavenia: <span><?= htmlspecialchars($odporucane['trvanie']) ?> min</span></li>
                            <li>Vekové obmedzenie:
                                <span><?= $odporucane['vekove_obmedzenie'] ? 'od ' . htmlspecialchars($odporucane['vekove_obmedzenie']) . ' rokov' : 'Bez obmedzenia' ?></span>
                            </li>
                        </ul>
                        <p><?= htmlspecialchars($odporucane['popis']) ?></p>
                        <div class="icon-button">
                            <a href="detail-predstavenia.php?id=<?= $odporucane['id'] ?>"><i class="fa fa-calendar"></i> Zobraziť detail</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="col-lg-12">
                        <p>Momentálne nemáme iné odporúčané predstavenie.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


<?php
$file_path = "parts/footer.php";
if (!include $file_path) {
    echo "Failed to include $file_path";
}
?>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const images = <?php echo json_encode($obrazky); ?>;
        let currentIndex = 0;

        const imgElement = document.getElementById('carouselImage');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');

        prevBtn.addEventListener('click', () => {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            imgElement.src = "assets/images/" + images[currentIndex];
        });

        nextBtn.addEventListener('click', () => {
            currentIndex = (currentIndex + 1) % images.length;
            imgElement.src = "assets/images/" + images[currentIndex];
        });
    });
</script>