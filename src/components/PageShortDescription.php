<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:23 PM.
 */

namespace src\components;

use src\AtlasReport;

class PageShortDescription
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($pageShortDescription, $color = null, $isCustom = true, $font = false, $pageShortTitle = false): void
    {
        if (!$isCustom) {
            $pageShortDescription = $this->pdf->getText($pageShortDescription);
        }
        $font = $font ?: $this->pdf->font_regular;
        $color = $this->pdf->lookupColor($color);

        $rowsCount = $pageShortTitle ? 5 : 3;

        $this->pdf->SetFont($font, '', 13);
        $table = '<style>
                    .bar{
                        background-color: '.$color.';
                    }
                    .padding{
                        font-family: '.$this->pdf->font_light.';
                        line-height: 8px;
                        height: 8px;
                    }
                    .bold{
                        font-family: '.$this->pdf->font_bold.';
                        color: '.$color.';
                        font-size: 14px;
                        line-height: 20px;
                    }
                    .text{
                        font-family: '.$font.';
                        color: '.$color.';
                        font-size: 14px;
                    }
                    </style>';

        $table .= '
            <table nobr="true" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td rowspan="'.$rowsCount.'" class="bar" width="3"></td>
                <td class="padding" width="480"></td>
            </tr>';
        if ($pageShortTitle) {
            $table .= '
            <tr>
                <td width="10"></td>
                <td class="bold" width="470">'.$pageShortTitle.'</td>
            </tr>';
        }
        if ($pageShortTitle && $pageShortDescription) {
            $table .= '
            <tr>
                <td class="padding" width="480"></td>
            </tr>';
        }
        if ($pageShortDescription) {
            $table .= '
            <tr>
                <td width="10"></td>
                <td class="text" width="470">'.$pageShortDescription.'</td>
            </tr>';
        }
        $table .= '
            <tr>
                <td class="padding" width="480"></td>
            </tr>';

        $table .= '</table>';

        $this->pdf->writeHTML($table, true, false, true, false, '');
    }
}
