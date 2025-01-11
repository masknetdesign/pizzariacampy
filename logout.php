<?php
session_start();
require_once "config/database.php";
require_once "classes/Auth.php";

$database = new Database();
$db = $database->getConnection();
$auth = new Auth($db);

$auth->logout();
header("Location: index.php");
exit;
?>
