<?php

declare(strict_types=1);

if (!isset($_GET['name'])) {
    \http_response_code(400);
    echo 'query-parameter "name" is required';
    exit();
}
$name = $_GET['name'];
if (!\in_array($name, EXAMPLES_LIST)) {
    \http_response_code(400);
    echo 'name has to be one of: '.\implode(',', EXAMPLES_LIST);
    exit();
}

$data = \json_decode(\file_get_contents(__DIR__."/../../../dict/$name.json"), true);
\header("Content-disposition: attachment; filename=$name.json");
\header('Content-Type: application/json');
echo \json_encode($data, \JSON_PRETTY_PRINT);
