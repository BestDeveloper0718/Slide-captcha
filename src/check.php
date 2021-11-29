<?php
require_once __DIR__ . '/../vendor/autoload.php';

$tn = new \Tncode\SlideCaptcha();
$tn->setBgNum(12);
if ($tn->check()) {
    $_SESSION['tncode_check'] = 'ok';
    echo "ok";
} else {
    $_SESSION['tncode_check'] = 'error';
    echo "error";
}

?>
