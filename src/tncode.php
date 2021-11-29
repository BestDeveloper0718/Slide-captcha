<?php

require_once __DIR__.'/../vendor/autoload.php';

$tn = new \Tncode\SlideCaptcha();
$tn->setLogoPath(__DIR__.'/logo/ky-logo.png');
$tn->make();
