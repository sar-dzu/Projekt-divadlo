<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Hra.php';

use Classes\Database;
use Classes\Hra;

if (!isset($_GET['id'])) {
    die("Chýba ID hry.");
}

$id = (int)$_GET['id'];
$database = new Database();
$hra = new Hra($database);
$hra->delete($id);

header("Location: zobrazit-hry.php?deleted=1");
exit;

