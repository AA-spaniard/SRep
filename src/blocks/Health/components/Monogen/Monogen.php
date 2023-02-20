<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:24 PM.
 */

namespace src\blocks\Health\components\Monogen;

use src\AtlasReport;
use src\blocks\Health\components\Monogen\components\FamilyChart;

class Monogen
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($monogens): void
    {
        foreach ($monogens as $monogen) {
            if ($monogen['status'] > 1) {
                $this->pdf->AddPage();
                $this->pdf->pageTitle->set($monogen['title']);
                $this->pdf->pageSubtitle->set($monogen['short']);
                $this->pdf->pageDescription->setByParagraphs($monogen['description']);

                //monogen snips table
                $headerList = [
                    $this->pdf->getText('gen'),
                    $this->pdf->getText('variant'),
                    $this->pdf->getText('mutation'),
                    $this->pdf->getText('genotypeYou'),
                ];
                $fieldsList = ['gene', 'snp', 'mutation', 'genotype'];
                $sizeList = [27, 33.33, 16.66, 23];
                $alignList = ['center', 'center', 'center', 'center'];
                $colorList = [null, null, null, null];

                $this->pdf->snipTable->set($monogen, $headerList, $fieldsList, $sizeList, $colorList, $alignList, 'variants');

                //monogen family
                $this->pdf->AddPage();
                $this->pdf->pageSubtitle->set('family', false);
                $this->pdf->pageDescription->set('familyInfo', false);

                $familyChart = new FamilyChart($this->pdf);
                $familyChart->set($monogen);

                $this->pdf->pageSubtitle->set('resume', false);
                $this->pdf->pageDescription->set($monogen['descr']);

                $this->pdf->pageSubtitle->set('rec', false);
                $this->pdf->pageDescription->set('resumeInfo', false);
            }
        }
    }
}
