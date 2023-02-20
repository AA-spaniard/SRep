<?php

declare(strict_types=1);

namespace src\helpers;

class ArrayHelper
{
    public static function findKey(array $heystack, \Closure $needle)
    {
        foreach ($heystack as $key => $element) {
            if ($needle($element)) {
                return $key;
            }
        }

        return null;
    }
}
