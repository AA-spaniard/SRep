<?php

declare(strict_types=1);

\header('Access-Control-Allow-Origin: *');
echo file_get_contents(__DIR__.'/../../openapi/dist/openapi.yaml');
