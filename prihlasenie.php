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

<!-- HTML FORMULÁR -->
<form method="POST">
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="heslo" required placeholder="Heslo">
    <button type="submit">Prihlásiť sa</button>
</form>

<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
