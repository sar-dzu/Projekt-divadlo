<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php'); // alebo prihlasenie.php
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

// Vymaž hru a jej obrázky
$conn = $database->getConnection();
$stmt = $conn->prepare("SELECT obrazok FROM hra_obrazky WHERE hra_id = :id");
$stmt->execute(['id' => $id]);
$obrazky = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($obrazky as $obrazok) {
    $path = "assets/images/" . $obrazok['obrazok'];
    if (file_exists($path)) {
        unlink($path);
    }
}

$conn->prepare("DELETE FROM hra_obrazky WHERE hra_id = :id")->execute(['id' => $id]);
$conn->prepare("DELETE FROM predstavenie_kategoria WHERE predstavenie_id = :id")->execute(['id' => $id]);
$hra->delete($id);

header("Location: zobrazit-hry.php?deleted=1");
exit();
