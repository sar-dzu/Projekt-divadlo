<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once('parts/head.php');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';
require_once 'classes/Faq.php';

use Classes\Database;
use Classes\Hra;
use Classes\Faq;

$db = new Database();
$hra = new Hra($db);

$najnovsiePredstavenie = $hra->getLatestPredstavenie();
$reprizyNove = $hra->getReprizy($najnovsiePredstavenie['id']);

$faq = new Faq($db);
$questions = $faq->getAll();

$reprizy = $hra->getUpcomingUniqueReprizy();

?>

<!-- ***** Header Area End ***** -->

<div class="main-banner" >
    <div class="owl-carousel owl-banner">
        <?php foreach ($reprizy as $index => $repriza): ?>
            <?php $obrazky = $hra->getObrazkyByHraId($repriza['predstavenie_id']); ?>
            <div class="item">
                <div class="header-text" style="margin-left: 300px;">
                  <span class="category">
                    <em><?php echo date('d.m.Y H:i', strtotime($repriza['najblizsia_repriza'])); ?></em>
                  </span>
                    <h3>Príďte sa pozrieť!</h3>
                    <h2 style="color: #2c0b0e;white-space: nowrap;">
                        <a href="detail-predstavenia.php?id=<?php echo urlencode($repriza['predstavenie_id']); ?>" style="color: inherit; text-decoration: none;">
                            <?php echo htmlspecialchars($repriza['nazov']); ?>
                        </a>
                    </h2>
                </div>
                <div class="d-flex flex-wrap gap-2 justify-content-center">
                    <?php if (!empty($obrazky)): ?>
                        <?php foreach (array_slice($obrazky, 0, 3) as $obrazok): ?>
                            <a href="detail-predstavenia.php?id=<?= urlencode($repriza['predstavenie_id']) ?>">
                                <img src="assets/images/<?= htmlspecialchars($obrazok) ?>"
                                     alt="Obrázok predstavenia"
                                     style="height: 400px; width: auto; border-radius: 8px;">
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <img src="assets/images/featured1.jpg" alt="Bez obrázku"
                             style="height: 400px; margin: 0 300px; border-radius: 8px; object-fit: cover;">
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
                    <img src="assets/images/dsdk_logo.png" alt="">
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
            <h6>| Properties</h6>
            <h2>We Provide The Best Property You Like</h2>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-4 col-md-6">
          <div class="item">
            <a href="detail-predstavenia.php"><img src="assets/images/property-01.jpg" alt=""></a>
            <span class="category">Luxury Villa</span>
            <h6>$2.264.000</h6>
            <h4><a href="detail-predstavenia.php">18 New Street Miami, OR 97219</a></h4>
            <ul>
              <li>Bedrooms: <span>8</span></li>
              <li>Bathrooms: <span>8</span></li>
              <li>Area: <span>545m2</span></li>
              <li>Floor: <span>3</span></li>
              <li>Parking: <span>6 spots</span></li>
            </ul>
            <div class="main-button">
              <a href="detail-predstavenia.php">Schedule a visit</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="item">
            <a href="detail-predstavenia.php"><img src="assets/images/property-02.jpg" alt=""></a>
            <span class="category">Luxury Villa</span>
            <h6>$1.180.000</h6>
            <h4><a href="detail-predstavenia.php">54 Mid Street Florida, OR 27001</a></h4>
            <ul>
              <li>Bedrooms: <span>6</span></li>
              <li>Bathrooms: <span>5</span></li>
              <li>Area: <span>450m2</span></li>
              <li>Floor: <span>3</span></li>
              <li>Parking: <span>8 spots</span></li>
            </ul>
            <div class="main-button">
              <a href="detail-predstavenia.php">Schedule a visit</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="item">
            <a href="detail-predstavenia.php"><img src="assets/images/property-03.jpg" alt=""></a>
            <span class="category">Luxury Villa</span>
            <h6>$1.460.000</h6>
            <h4><a href="detail-predstavenia.php">26 Old Street Miami, OR 38540</a></h4>
            <ul>
              <li>Bedrooms: <span>5</span></li>
              <li>Bathrooms: <span>4</span></li>
              <li>Area: <span>225m2</span></li>
              <li>Floor: <span>3</span></li>
              <li>Parking: <span>10 spots</span></li>
            </ul>
            <div class="main-button">
              <a href="detail-predstavenia.php">Schedule a visit</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="item">
            <a href="detail-predstavenia.php"><img src="assets/images/property-04.jpg" alt=""></a>
            <span class="category">Apartment</span>
            <h6>$584.500</h6>
            <h4><a href="detail-predstavenia.php">12 New Street Miami, OR 12650</a></h4>
            <ul>
              <li>Bedrooms: <span>4</span></li>
              <li>Bathrooms: <span>3</span></li>
              <li>Area: <span>125m2</span></li>
              <li>Floor: <span>25th</span></li>
              <li>Parking: <span>2 cars</span></li>
            </ul>
            <div class="main-button">
              <a href="detail-predstavenia.php">Schedule a visit</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="item">
            <a href="detail-predstavenia.php"><img src="assets/images/property-05.jpg" alt=""></a>
            <span class="category">Penthouse</span>
            <h6>$925.600</h6>
            <h4><a href="detail-predstavenia.php">34 Beach Street Miami, OR 42680</a></h4>
            <ul>
              <li>Bedrooms: <span>4</span></li>
              <li>Bathrooms: <span>4</span></li>
              <li>Area: <span>180m2</span></li>
              <li>Floor: <span>38th</span></li>
              <li>Parking: <span>2 cars</span></li>
            </ul>
            <div class="main-button">
              <a href="detail-predstavenia.php">Schedule a visit</a>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
          <div class="item">
            <a href="detail-predstavenia.php"><img src="assets/images/property-06.jpg" alt=""></a>
            <span class="category">Modern Condo</span>
            <h6>$450.000</h6>
            <h4><a href="detail-predstavenia.php">22 New Street Portland, OR 16540</a></h4>
            <ul>
              <li>Bedrooms: <span>3</span></li>
              <li>Bathrooms: <span>2</span></li>
              <li>Area: <span>165m2</span></li>
              <li>Floor: <span>26th</span></li>
              <li>Parking: <span>3 cars</span></li>
            </ul>
            <div class="main-button">
              <a href="detail-predstavenia.php">Schedule a visit</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="contact section">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 offset-lg-4">
          <div class="section-heading text-center">
            <h6>| Contact Us</h6>
            <h2>Get In Touch With Our Agents</h2>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="contact-content">
    <div class="container">
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
                  <label for="name">Full Name</label>
                  <input type="name" name="name" id="name" placeholder="Your Name..." autocomplete="on" required>
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <label for="email">Email Address</label>
                  <input type="text" name="email" id="email" pattern="[^ @]*@[^ @]*" placeholder="Your E-mail..." required="">
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <label for="subject">Subject</label>
                  <input type="subject" name="subject" id="subject" placeholder="Subject..." autocomplete="on" >
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <label for="message">Message</label>
                  <textarea name="message" id="message" placeholder="Your Message"></textarea>
                </fieldset>
              </div>
              <div class="col-lg-12">
                <fieldset>
                  <button type="submit" id="form-submit" class="orange-button">Send Message</button>
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
