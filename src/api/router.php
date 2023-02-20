<?php

declare(strict_types=1);

$url = $_SERVER['REQUEST_URI'];

const EXAMPLES_LIST = [
    'dna_en',
    'dna_ru',
    'dna_jp',
    'biota_en',
    'biota_ru',
    'biota_jp',
    'b2b_dna_wgs_ru',
    'b2c_dna_wgs_ru',
    'dna_legacy_snps',
];

if (\preg_match('/^\/api\/v1\/input-example\/list/', $url)) {
    require 'inputExample/list.php';
    exit();
}

if (\preg_match('/^\/api\/v1\/input-example\/get/', $url)) {
    require 'inputExample/get.php';
    exit();
}

if (\preg_match('/^\/api\/v1\/openapi/', $url)) {
    require 'openApi.php';
    exit();
}

if (\preg_match('/^\/api\/v1\/redoc/', $url)) {
    require 'redoc.php';
    exit();
}

\http_response_code(404);
echo 'Not Found';
