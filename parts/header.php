<?php
include_once "functions.php";

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Admin.php';

use Classes\Admin;
use Classes\Database;

$database = new Database();
$admin = new Admin($database);


$menu = getMenuData("header");
?>

<header class="header-area header-sticky">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav class="main-nav">
                    <!-- ***** Logo Start ***** -->
                    <a href="<?php echo $menu['home']['path']?>">
                        <img alt="img" src="assets/images/dsdk_logo.png" style="margin: 10px; max-height: 60px;width: auto;">
                    </a>
                    <!-- ***** Logo End ***** -->
                    <!-- ***** Menu Start ***** -->
                    <ul class="nav">
                        <?php printMenu($menu); ?>
                        <li><a href="index.php#nextShows"><i class="fa fa-calendar"></i> Najbližšie predstavenia</a></li>

                        <?php if ($admin->isLoggedIn()): ?>
                            <li><a href="odhlasenie.php"><i class="fa fa-sign-out"></i> Odhlásiť sa</a></li>
                        <?php else: ?>
                            <li><a href="prihlasenie.php"><i class="fa fa-sign-in"></i> Prihlásiť sa</a></li>
                        <?php endif; ?>
                    </ul>
                    <a class='menu-trigger'>
                        <span>Menu</span>
                    </a>
                    <!-- ***** Menu End ***** -->
                </nav>
            </div>
        </div>
    </div>
</header>