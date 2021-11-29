<?php

require_once __DIR__.'/../vendor/autoload.php';

$tn = new \Tncode\SlideCaptcha();
$tn->setLogoPath(__DIR__.'/logo/xf-logo.png');
$tn->make();
