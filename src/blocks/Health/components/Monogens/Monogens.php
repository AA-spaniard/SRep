<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:24 PM.
 */

namespace src\blocks\Health\components\Monogens;

use src\AtlasReport;

class Monogens
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($monogens): void
    {
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('allMono', false);

        $headerList = [
            $this->pdf->getText('name'),
            '',
            $this->pdf->getText('status'),
        ];
        $fieldsList = ['title', null, 'statusTitle'];
        $sizeList = ['64.66%', '5.33%', '30%'];
        $alignList = ['left', 'left', 'left'];
        $colorList = [null, null, 'color'];
        $iconList = [null, 'icon', null];

        $this->pdf->table->set($headerList, $monogens, $fieldsList, $sizeList, $colorList, $alignList, $iconList);
    }
}
