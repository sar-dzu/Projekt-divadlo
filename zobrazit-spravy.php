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

$database = new Database();
$sprava = new Formular($database);
$spravy = $sprava->getAll();
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Kontaktné správy</title>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
</head>
<body class="container py-4">
<h2>Prijaté správy z formulára</h2>
<table class="table table-bordered table-striped mt-3">
    <thead class="thead-dark">
    <tr>
        <th>Meno</th>
        <th>Email</th>
        <th>Predmet</th>
        <th>Správa</th>
        <th>Dátum</th>
        <th>Akcia</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($spravy as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['meno']) ?></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= htmlspecialchars($s['predmet']) ?></td>
            <td><?= nl2br(htmlspecialchars($s['sprava'])) ?></td>
            <td><?= date('d.m.Y H:i', strtotime($s['datum'])) ?></td>
            <td>
                <form method="POST" action="vymazat-sprava.php" onsubmit="return confirm('Naozaj chcete správu zmazať?');">
                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Zmazať</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
