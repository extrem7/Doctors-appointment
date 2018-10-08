<?php

$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root . '/wp-load.php')) {
    require_once $root . '/wp-load.php';
}

if (isset($_POST['hash']) && !empty($_POST['hash'])) {
    $DA->verifyComment($_POST['hash']);
    header('Location: /Doctors-appointment/?thanks=1');//todo
    exit;
}