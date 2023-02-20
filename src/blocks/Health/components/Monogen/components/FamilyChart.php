<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:24 PM.
 */

namespace src\blocks\Health\components\Monogen\components;

use src\AtlasReport;

class FamilyChart
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($monogen): void
    {
        $table = '<style>
                    .family-description{
                        font-size:12px;
                        font-family: '.$this->pdf->font_light.';
                    }
                    </style>';

        $table .= '<table nobr="true" border="0" cellpadding="0" cellspacing="0">';

        foreach ($monogen['family'] as $family) {
            $circleImage = '<img src="'.$this->getFamilyChart($family['percent'], $family['color']).'" width="70px" height="70px" />';
            $table .= '<tr>
                <td width="16.66%">'.$circleImage.'</td>
                <td width="83.33%" class="family-description" valign="middle">'.$family['text'].'</td>
            </tr>';
        }
        $table .= '</table>';

        $this->pdf->writeHTML($table, true, false, true, false, '');
    }

    public function getFamilyChart($percent, $color)
    {
        $xml = new \SimpleXMLElement(\file_get_contents('images/svg/circle.svg'));
        $xml->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');

        $fillerPercent = $percent;
        if (false !== \strpos($fillerPercent, '-')) {
            $fillerPercent = \explode('-', $fillerPercent)[0];
        }

        $dashLength = 1000;
        $fillerPercent = $dashLength + ($dashLength / 100 * (int) $fillerPercent);
        if (2000 === $fillerPercent) {
            $fillerPercent = 0;
        }
        $filler = $xml->xpath("//svg:path[@id='filler']")[0];
        // These assignments mutate $xml.
        // Some code inspection tools may deem them as "unused" but that's not so.
        $filler['stroke'] = $this->pdf->colors[$color];
        $filler['style'] = 'stroke-dashoffset: '.$fillerPercent.';';

        $value = $xml->xpath("//svg:text[@id='value']")[0];
        $value[0] = $percent.'%';
        $value['style'] = 'font-size: 60px; font-family: '.$this->pdf->font_regular.'; fill: '.$this->pdf->colors[$color].';';

        $map_data = $xml->asXML();

        $im = new \Imagick();
        $im->setSize(360, 360);
        $im->readImageBlob($map_data);
        $im->setImageFormat('png32');
        $image = 'data:image/png;base64,'.\base64_encode((string) $im);
        $im->clear();
        $im->destroy();

        $imageContent = \file_get_contents($image);
        $path = PATH_IMAGES.'tmp/'.$this->generateRandomString().'.png';

        \file_put_contents($path, $imageContent);

        return $path;
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = \strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[\rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
