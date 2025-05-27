<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php'); // alebo prihlasenie.php
    exit;
}
require_once 'db/config.php';
require_once 'classes/Database.php';

$database = new \Classes\Database();
$conn = $database->getConnection();



if (isset($_GET['id'])) {
    $stmt = $conn->prepare("DELETE FROM reprizy WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
}

header('Location: zobrazit-hry.php');
exit();