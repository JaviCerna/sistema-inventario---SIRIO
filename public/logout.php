<?php
// public/logout.php
require_once __DIR__ . '/../src/Auth.php';
$auth = new Auth();
$auth->logout();
header('Location: index.php');
exit;
