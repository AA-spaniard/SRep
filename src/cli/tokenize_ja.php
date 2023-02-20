<?php

/**
 * This script will split Japanese texts in static dictionary by words, i.e. tokenize.
 * This helps proper lines wrapping.
 */

declare(strict_types=1);

namespace src\cli;

require './vendor/autoload.php';

use Atlas\TinySegmenter\TinySegmenter;

const PUNCTUATION_CHARS = ['.', '!', '?', ',', ':', ';', '、', '。', '？', '・', '，', '』', '」', '〟', '〜', '：', '！'];

function implodeWithZeroWidthSpaces(array $tokens): string
{
    $result = '';
    foreach ($tokens as $token) {
        if (!\in_array($token, PUNCTUATION_CHARS, true) && $result) {
            $result .= "\u{200b}";
        }
        $result .= $token;
    }

    return $result;
}

echo "Tokenizing Japanese language\n";

$segmenter = new TinySegmenter();

$dictionary = \json_decode(\file_get_contents(__DIR__.'/../../dict/general.json'), true);

foreach ($dictionary as $key => $values) {
    $text = $values['ja'];
    $tokens = $segmenter->tokenize($text);
    $tokenizedText = implodeWithZeroWidthSpaces($tokens);
    $dictionary[$key]['ja'] = $tokenizedText;
}

$json = \json_encode($dictionary, \JSON_UNESCAPED_UNICODE);
\file_put_contents(__DIR__.'/../../dict/dist/general_tokenized.json', $json);

echo "Tokenizing Japanese language: Success\n";
