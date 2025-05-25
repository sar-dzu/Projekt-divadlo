<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once('parts/head.php');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';
require_once 'classes/Faq.php';
require_once 'classes/Formular.php';

use Classes\Database;
use Classes\Hra;
use Classes\Faq;
use Classes\Formular;

$db = new Database();
$hra = new Hra($db);

$najnovsiePredstavenie = $hra->getLatestPredstavenie();
$reprizyNove = $hra->getReprizy($najnovsiePredstavenie['id']);

$faq = new Faq($db);
$questions = $faq->getAll();

$reprizy = $hra->getUpcomingUniqueReprizy();
$sestRepriz = $hra->getUpcomingReprizy();

$formular = new Formular($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $meno = $_POST['meno'] ?? '';
    $email = $_POST['email'] ?? '';
    $predmet = $_POST['predmet'] ?? '';
    $sprava = $_POST['sprava'] ?? '';

    if (!empty($meno) && !empty($email) && !empty($sprava)) {
        $ulozene = $formular->saveMessage($meno, $email, $predmet, $sprava);
        if ($ulozene) {
            header("Location: contact.php?success=1");
        } else {
            header("Location: contact.php?error=db");
        }
    } else {
        header("Location: contact.php?error=1");
    }
    exit;
}
?>

<!-- ***** Header Area End ***** -->

<div class="main-banner" >
    <div class="owl-carousel owl-banner">
        <?php foreach ($reprizy as $index => $repriza): ?>
            <?php $obrazky = $hra->getObrazkyByHraId($repriza['predstavenie_id']); ?>
            <div class="item">
                <div class="header-textx">
                  <span class="category">
                    <em><?php echo date('d.m.Y H:i', strtotime($repriza['najblizsia_repriza'])); ?></em>
                  </span>
                    <h3>Príďte sa pozrieť!</h3>
                    <h2 style="color: #2c0b0e;">
                        <a href="detail-predstavenia.php?id=<?php echo urlencode($repriza['predstavenie_id']); ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($repriza['nazov']); ?>
                        </a>
                    </h2>
                </div>
                <div class="obrazky-wrapper">
                    <?php if (!empty($obrazky)): ?>
                        <?php foreach (array_slice($obrazky, 0, 3) as $obrazok): ?>
                            <a href="detail-predstavenia.php?id=<?= urlencode($repriza['predstavenie_id']) ?>">
                                <img src="assets/images/<?= htmlspecialchars($obrazok) ?>"
                                     alt="Obrázok predstavenia">
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <img src="assets/images/featured1.jpg" alt="Bez obrázku" class="obrazok-default">
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<div class="featured section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="left-image">
                    <img src="assets/images/dsdk_logo.png" alt="" style="max-width: 400px;height: auto;">
                </div>
            </div>

            <div class="col-lg-8">
                <div class="section-heading">
                    <h2>Často kladené otázky:</h2>
                </div>
                <div class="accordion" id="accordionExample">
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading<?= $index ?>">
                                <button class="accordion-button <?= $index !== 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                    <?= htmlspecialchars($q['otazka']) ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-bs-parent="#accordionExample">
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


<div class="video section">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 offset-lg-4">
          <div class="section-heading text-center">
            <h2>Reportáž o inscenácií</h2>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="video-content">
    <div class="container">
      <div class="row">
        <div class="col-lg-10 offset-lg-1">
          <div class="video-frame">
            <img src="assets/images/katarina.jpg" alt="">
            <a href="https://www.youtube.com/watch?v=jSeuYOZMBRA&t=72s" target="_blank"><i class="fa fa-play"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="fun-facts">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <div class="wrapper">
            <div class="row">
              <div class="col-lg-4">
                <div class="counter">
                  <h2 class="timer count-title count-number" data-to="95" data-speed="1000"></h2>
                   <p class="count-text ">Odohraných<br>predstavení</p>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="counter">
                    <?php
                    $rokNajstarsie = $hra->getOldestYear();
                    $aktualnyRok = date('Y');
                    $pocetRokov = $rokNajstarsie ? $aktualnyRok - $rokNajstarsie : 0;
                    ?>
                  <h2 class="timer count-title count-number" data-to="<?= $pocetRokov ?>" data-speed="1000"></h2>
                  <p class="count-text ">Rokov<br>sme na scéne</p>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="counter">
                  <h2 class="timer count-title count-number" data-to="55" data-speed="1000"></h2>
                  <p class="count-text ">Rodinám v núdzi<br>sme pomohli</p>
                </div>
              </div>
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
                    <h6>| Najnovšie predstavenie</h6>
                    <h2><?= htmlspecialchars($najnovsiePredstavenie['nazov']) ?></h2>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="tabs-content">
                    <div class="row">
                        <div class="tab-pane fade show active">
                            <div class="row">
                                <div class="col-lg-3">
                                    <h3 style="margin-bottom: 1rem;">Hráme:</h3>
                                    <div class="info-table">
                                        <ul>
                                            <?php
                                            $daysJsonPath = __DIR__ . '/data/days.json';

                                            $daysJson = file_get_contents($daysJsonPath);
                                            $daysOfWeek = json_decode($daysJson, true);

                                            // Príklad použitia v slučke, kde máš dátum reprízy
                                            foreach ($reprizyNove as $repriza) {
                                                $datum = $repriza['datum_cas'] ?? null;
                                                if ($datum && strtotime($datum)) {
                                                    $englishDay = date('l', strtotime($datum));
                                                    $slovakDay = $daysOfWeek[$englishDay] ?? $englishDay; // fallback na angličtinu
                                                    echo '<li>' . htmlspecialchars($slovakDay) . ' <span>' . date('d.m.Y H:i', strtotime($datum)) . '</span></li>';
                                                } else {
                                                    echo '<li>Neznámy deň <span>Neznámy dátum</span></li>';
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <img src="assets/images/<?= htmlspecialchars($najnovsiePredstavenie['hlavny_obrazok'] ?? 'featured1.jpg') ?>" alt="">
                                </div>
                                <div class="col-lg-3">
                                    <h4>O predstavení</h4>
                                    <p><?= nl2br(htmlspecialchars($najnovsiePredstavenie['popis'])) ?></p>
                                    <div class="icon-button">
                                        <a href="detail-predstavenia.php?id=<?= $najnovsiePredstavenie['id'] ?>"><i class="fa fa-calendar"></i> Zobraziť detail</a>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- .tab-pane -->
                    </div> <!-- .row -->
                </div> <!-- .tabs-content -->
            </div>
        </div>
    </div>
</div>

<div class="properties section">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 offset-lg-4">
                <div class="section-heading text-center">
                    <h6>| Nadchádzajúce predstavenia</h6>
                    <h2>Vyber si niečo, čo ťa zaujme</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <?php foreach ($sestRepriz as $r): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="item">
                        <a href="detail-predstavenia.php?id=<?= $r['predstavenie_id'] ?>">
                            <img src="assets/images/<?= htmlspecialchars($r['obrazok'] ?? 'featured1.jpg') ?>"
                                 alt=""
                                 style="object-fit: cover; width: 100%; aspect-ratio: 1 / 1;">
                        </a>
                        <span class="category"><?= date('d.m.Y H:i', strtotime($r['datum_cas'])) ?></span>
                        <h4>
                            <a href="detail-predstavenia.php?id=<?= $r['predstavenie_id'] ?>">
                                <?= htmlspecialchars($r['nazov']) ?>
                            </a>
                        </h4>
                        <ul>
                            <li>Vekové obmedzenie:
                                <span>
                                  <?= $r['vekove_obmedzenie'] ? "od " . htmlspecialchars($r['vekove_obmedzenie']) . " rokov" : "Bez obmedzenia" ?>
                                </span>
                            </li>
                            <li>Dĺžka predstavenia:
                                <span><?= $r['trvanie'] ? htmlspecialchars($r['trvanie']) . " minút" : "Neuvedené" ?></span>
                            </li>
                            <li>Vstupné: <span>Dobrovoľné</span></li>
                        </ul>
                        <div class="main-button">
                            <a href="detail-predstavenia.php?id=<?= $r['predstavenie_id'] ?>">Viac info</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


  <div class="contact-content">
    <div class="container">
        <div class="section-heading text-center">
            <h6>| Sídlo</h6>
            <h2>Tu nás nájdeš, alebo nás kokntaktuj</h2>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Správa bola úspešne odoslaná.</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_GET['error'] === '1' ? 'Vyplň prosím všetky povinné polia.' : 'Nastala chyba pri ukladaní správy.' ?>
                </div>
            <?php endif; ?>

        </div>
      <div class="row">
        <div class="col-lg-7">
          <div id="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12469.776493332698!2d-80.14036379941481!3d25.907788681148624!2m3!1f357.26927939317244!2f20.870722720054623!3f0!3m2!1i1024!2i768!4f35!3m3!1m2!1s0x88d9add4b4ac788f%3A0xe77469d09480fcdb!2sSunny%20Isles%20Beach!5e1!3m2!1sen!2sth!4v1642869952544!5m2!1sen!2sth" width="100%" height="500px" frameborder="0" style="border:0; border-radius: 10px; box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.15);" allowfullscreen=""></iframe>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="item phone">
                <img src="assets/images/phone-icon.png" alt="" style="max-width: 52px;">
                <h6>010-020-0340<br><span>Phone Number</span></h6>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="item email">
                <img src="assets/images/email-icon.png" alt="" style="max-width: 52px;">
                <h6>info@villa.co<br><span>Business Email</span></h6>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
            <form id="contact-form" action="" method="post">
                <div class="row">
                    <div class="col-lg-12">
                        <fieldset>
                            <label for="meno">Meno</label>
                            <input type="text" name="meno" id="meno" placeholder="Tvoje meno..." required>
                        </fieldset>
                    </div>
                    <div class="col-lg-12">
                        <fieldset>
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email" placeholder="Tvoj e-mail..." required>
                        </fieldset>
                    </div>
                    <div class="col-lg-12">
                        <fieldset>
                            <label for="predmet">Predmet</label>
                            <input type="text" name="predmet" id="predmet" placeholder="Predmet správy...">
                        </fieldset>
                    </div>
                    <div class="col-lg-12">
                        <fieldset>
                            <label for="sprava">Správa</label>
                            <textarea name="sprava" id="sprava" placeholder="Tvoja správa..." required></textarea>
                        </fieldset>
                    </div>
                    <div class="col-lg-12">
                        <fieldset>
                            <button type="submit" class="orange-button">Odoslať správu</button>
                        </fieldset>
                    </div>
                </div>
            </form>

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

  <!-- Scripts -->
  <!-- Bootstrap core JavaScript -->
