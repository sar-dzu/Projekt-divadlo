<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

include_once('parts/head.php');

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Formular.php';

use Classes\Database;
use Classes\Formular;

$db = new Database();

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

  <div class="page-heading header-text">
    <div class="container">
      <div class="row">
        <div class="col-lg-12">
          <span class="breadcrumb"><a href="index.php">Home</a>  /  Kontakt</span>
          <h3>Kontakt</h3>
        </div>
      </div>
    </div>
  </div>

  <div class="contact-page section">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="section-heading">
            <h6>| Kontaktujte nás</h6>
            <h2>Napíš nám správu</h2>
          </div>
          <p>Či už máte pozitívne, negatívne ohlasy na naše predstavenia, alebo akékoľvek nezodpovedané otázky, sme tu pre Vás.</p>
          <div class="row">
            <div class="col-lg-12">
              <div class="item phone">
                <img src="assets/images/phone-icon.png" alt="" style="max-width: 52px;">
                <h6>010-020-0340<br><span>Phone Number</span></h6>
              </div>
            </div>
            <div class="col-lg-12">
              <div class="item email">
                <img src="assets/images/email-icon.png" alt="" style="max-width: 52px;">
                <h6>info@villa.co<br><span>Business Email</span></h6>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
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
        <div class="col-lg-12">
          <div id="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12469.776493332698!2d-80.14036379941481!3d25.907788681148624!2m3!1f357.26927939317244!2f20.870722720054623!3f0!3m2!1i1024!2i768!4f35!3m3!1m2!1s0x88d9add4b4ac788f%3A0xe77469d09480fcdb!2sSunny%20Isles%20Beach!5e1!3m2!1sen!2sth!4v1642869952544!5m2!1sen!2sth" width="100%" height="500px" frameborder="0" style="border:0; border-radius: 10px; box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.15);" allowfullscreen=""></iframe>
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

