<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Repriza.php';

use Classes\Database;
use Classes\Repriza;

$database = new Database();
$repriza = new Repriza($database);

if (isset($_GET['id'])) {
    $repriza->deleteRepriza((int)$_GET['id']);
}

header('Location: zobrazit-hry.php');
exit;
