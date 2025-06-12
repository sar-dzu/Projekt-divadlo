<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');


require_once 'db/config.php';
require_once 'classes/Database.php';
require_once 'classes/Admin.php';

use Classes\Admin;
use Classes\Database;

$database = new Database();
$admin = new Admin($database);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $heslo = $_POST['heslo'] ?? '';

    if ($admin->login($email, $heslo)) {
        header('Location: zobrazit-hry.php'); // alebo kamkoľvek po prihlásení
        exit();
    } else {
        $error = 'Nesprávne prihlasovacie údaje.';
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <title>Admin Prihlásenie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-form {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<form method="POST" class="login-form">
    <h2 class="text-center mb-4">Prihlásenie pre administrátora</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="email" class="form-label">Emailová adresa</label>
        <input type="email" class="form-control" id="email" name="email" required placeholder="napr. admin@example.com">
    </div>
    <div class="mb-3">
        <label for="heslo" class="form-label">Heslo</label>
        <input type="password" class="form-control" id="heslo" name="heslo" required placeholder="Zadajte heslo">
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-primary">Prihlásiť sa</button>
    </div>

    <div class="text-center mt-3">
        <a href="index.php">← Naspäť na hlavnú stránku</a>
    </div>
</form>

</body>
</html>
