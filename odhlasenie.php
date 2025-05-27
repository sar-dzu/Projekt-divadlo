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

$admin->logout();

header('Location: index.php');
exit;