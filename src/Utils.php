<?php

declare(strict_types=1);

namespace src;

class Utils
{
    public function getGradientColor($color1, $color2, $percent)
    {
        return $this->makeGradientColor($this->hexToRgb($color1), $this->hexToRgb($color2), $percent);
    }

    public function hexToRgb($hex)
    {
        $color['r'] = \hexdec(\substr($hex, 0, 2));
        $color['g'] = \hexdec(\substr($hex, 2, 2));
        $color['b'] = \hexdec(\substr($hex, 4, 2));

        return $color;
    }

    public function makeGradientColor($color1, $color2, $percent)
    {
        $r = $this->makeChannel($color1['r'], $color2['r'], $percent);
        $g = $this->makeChannel($color1['g'], $color2['g'], $percent);
        $b = $this->makeChannel($color1['b'], $color2['b'], $percent);

        return $this->makeColorPiece($r).$this->makeColorPiece($g).$this->makeColorPiece($b);
    }

    public function makeChannel($a, $b, $per)
    {
        return $a + \round(($b - $a) * ($per));
    }

    public function makeColorPiece($num)
    {
        $num = \min($num, 255);
        $num = \max($num, 0);
        $str = \dechex($num); // PARSE TO HEX FROM DEC
        if (\strlen($str) < 2) {
            return $str = '0'.$str;
        } else {
            return $str;
        }
    }

    public function hex2rgb($hexColor)
    {
        list($r, $g, $b) = \sscanf($hexColor, '#%02x%02x%02x');

        return [$r, $g, $b];
    }

    public function toPrecision($number, $precision)
    {
        if (0 == $number) {
            return 0;
        }
        $exponent = \floor(\log10(\abs($number)) + 1);
        $significand =
            \round(
                ($number / 10 ** $exponent)
                * 10 ** $precision
            )
            / 10 ** $precision;

        return $significand * 10 ** $exponent;
    }

    public function fixedPercent($value, $toNumber = false, $precision = 2)
    {
        $per = $value;
        if ($per > 0) {
            if ($per < 1) {
                $per = $this->toPrecision($per, $precision);
                if (!$toNumber) {
                    if ($per < 0.01) {
                        $per = '0.01%';
                    } else {
                        $per = $per.'%';
                    }
                }
            } else {
                $per = \round($per, $precision);
                if (!$toNumber) {
                    $per = $per.'%';
                }
            }
        } else {
            if ($per > -1) {
                $per = $this->toPrecision($per, $precision);
                if (!$toNumber) {
                    if ($per > -0.01) {
                        $per = '-0.01%';
                    } else {
                        $per = $per.'%';
                    }
                }
            } else {
                if ($per) {
                    $per = \round($per, $precision);
                    if (!$toNumber) {
                        $per = $per.'%';
                    }
                } else {
                    $per = null;
                }
            }
        }

        if (!$toNumber) {
            return $per;
        } else {
            return (float) $per;
        }
    }

    public static function logDataSource(string $dataSource): void
    {
        $datetime = (new \DateTime())->format('Y-m-d_H.i.s.u');
        $randomChars = \substr(\md5((string) \mt_rand()), 0, 5);
        $name = "{$datetime}_{$randomChars}.json";
        $path = "/var/log/sources_log/$name";
        \file_put_contents($path, $dataSource);
    }
}
