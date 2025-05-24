<?php
include_once('parts/head.php');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';
require_once 'classes/Faq.php';

use Classes\Faq;
use Classes\Database;
use Classes\Hra;

$db = new Database();
$hra = new Hra($db);


$faq = new Faq($db);
$questions = $faq->getAll();


// Získaj ID z URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Získaj údaje o predstavení podľa ID
$predstavenie = $hra->getHraById($id);
$reprizy = $hra->getReprizy($id);
if (!$predstavenie) {
    die('Predstavenie nebolo nájdené.');
}

$obrazky = $hra->getObrazkyByHraId($id);
if (empty($obrazky)) {
    $obrazky = ['featured1.jpg']; // fallback, ak nemá obrázky
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repriza_id'])) {
    $reprizaId = (int)$_POST['repriza_id'];
    if ($hra->buyTicket($reprizaId)) {
        $message = "Lístok bol úspešne zakúpený.";
        // Obnovíme reprízy, aby sa aktualizovala kapacita
        $reprizy = $hra->getReprizy($id);
    } else {
        $message = "Lístok sa nepodarilo zakúpiť (možno už nie sú voľné miesta).";
    }
}

?>

  <!-- ***** Header Area End ***** -->

    <div class="page-heading header-text">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <span class="breadcrumb"><a href="index.php">Home</a>  /  <?php echo htmlspecialchars($predstavenie['nazov']); ?></span>
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
                <?php else: ?>
                    <h4>Posledné predstavenie: <?= date('d.m.Y', strtotime($predstavenie['koniec_hrania'])); ?></h4>
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
  </div>

  <div class="section best-deal">
    <div class="container">
      <div class="row">
        <div class="col-lg-4">
          <div class="section-heading">
            <h6>| Best Deal</h6>
            <h2>Find Your Best Deal Right Now!</h2>
          </div>
        </div>
        <div class="col-lg-12">
          <div class="tabs-content">
            <div class="row">
              <div class="nav-wrapper ">
                <ul class="nav nav-tabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="appartment-tab" data-bs-toggle="tab" data-bs-target="#appartment" type="button" role="tab" aria-controls="appartment" aria-selected="true">Appartment</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="villa-tab" data-bs-toggle="tab" data-bs-target="#villa" type="button" role="tab" aria-controls="villa" aria-selected="false">Villa House</button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="penthouse-tab" data-bs-toggle="tab" data-bs-target="#penthouse" type="button" role="tab" aria-controls="penthouse" aria-selected="false">Penthouse</button>
                  </li>
                </ul>
              </div>              
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="appartment" role="tabpanel" aria-labelledby="appartment-tab">
                  <div class="row">
                    <div class="col-lg-3">
                      <div class="info-table">
                        <ul>
                          <li>Total Flat Space <span>540 m2</span></li>
                          <li>Floor number <span>3</span></li>
                          <li>Number of rooms <span>8</span></li>
                          <li>Parking Available <span>Yes</span></li>
                          <li>Payment Process <span>Bank</span></li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <img src="assets/images/deal-01.jpg" alt="">
                    </div>
                    <div class="col-lg-3">
                      <h4>All Info About Apartment</h4>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, do eiusmod tempor pack incididunt ut labore et dolore magna aliqua quised ipsum suspendisse. <br><br>Swag fanny pack lyft blog twee. JOMO ethical copper mug, succulents typewriter shaman DIY kitsch twee taiyaki fixie hella venmo after messenger poutine next level humblebrag swag franzen.</p>
                      <div class="icon-button">
                        <a href="#"><i class="fa fa-calendar"></i> Schedule a visit</a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="villa" role="tabpanel" aria-labelledby="villa-tab">
                  <div class="row">
                    <div class="col-lg-3">
                      <div class="info-table">
                        <ul>
                          <li>Total Flat Space <span>250 m2</span></li>
                          <li>Floor number <span>26th</span></li>
                          <li>Number of rooms <span>5</span></li>
                          <li>Parking Available <span>Yes</span></li>
                          <li>Payment Process <span>Bank</span></li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <img src="assets/images/deal-02.jpg" alt="">
                    </div>
                    <div class="col-lg-3">
                      <h4>Detail Info About New Villa</h4>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, do eiusmod tempor pack incididunt ut labore et dolore magna aliqua quised ipsum suspendisse. <br><br>Swag fanny pack lyft blog twee. JOMO ethical copper mug, succulents typewriter shaman DIY kitsch twee taiyaki fixie hella venmo after messenger poutine next level humblebrag swag franzen.</p>
                      <div class="icon-button">
                        <a href="#"><i class="fa fa-calendar"></i> Schedule a visit</a>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="tab-pane fade" id="penthouse" role="tabpanel" aria-labelledby="penthouse-tab">
                  <div class="row">
                    <div class="col-lg-3">
                      <div class="info-table">
                        <ul>
                          <li>Total Flat Space <span>320 m2</span></li>
                          <li>Floor number <span>34th</span></li>
                          <li>Number of rooms <span>6</span></li>
                          <li>Parking Available <span>Yes</span></li>
                          <li>Payment Process <span>Bank</span></li>
                        </ul>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <img src="assets/images/deal-03.jpg" alt="">
                    </div>
                    <div class="col-lg-3">
                      <h4>Extra Info About Penthouse</h4>
                      <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, do eiusmod tempor pack incididunt ut Kinfolk tonx seitan crucifix 3 wolf moon bicycle rights keffiyeh snackwave wolf same vice, chillwave vexillologistlabore et dolore magna aliqua quised ipsum suspendisse. <br><br>Swag fanny pack lyft blog twee. JOMO ethical copper mug, succulents typewriter shaman DIY kitsch twee taiyaki fixie hella venmo after messenger poutine next level humblebrag swag franzen.</p>
                      <div class="icon-button">
                        <a href="#"><i class="fa fa-calendar"></i> Schedule a visit</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
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