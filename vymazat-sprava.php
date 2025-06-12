<?php
session_start();
require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Formular.php';

use Classes\Database;
use Classes\Formular;

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $database = new Database();
    $sprava = new Formular($database);
    $sprava->delete((int)$_POST['id']);
}

header('Location: zobrazit-spravy.php');
exit;