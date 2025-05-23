<?php
include_once "functions.php";

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
                        <?php printMenu($menu);?>
                        <li><a href="#"><i class="fa fa-calendar"></i> Navštívte nás</a></li>
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