<?php

declare(strict_types=1);

namespace src\blocks\Health\components\Monogen;

use src\AtlasReport;
use src\components\CancerSnipTable;

class Cancer
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($cancers): void
    {
        foreach ($cancers as $cancer) {
            if ($cancer['status'] > 1) {
                $this->pdf->AddPage();
                $this->pdf->pageTitle->set($cancer['title']);
                $this->pdf->pageSubtitle->set($cancer['short']);

                $this->pdf->pageDescription->setByParagraphs($cancer['description']);

                $headerList = [
                    $this->pdf->getText('gen'),
                    $this->pdf->getText('variant'),
                    $this->pdf->getText('mutation'),
                    $this->pdf->getText('genotypeYou'),
                ];
                $fieldsList = ['gene', 'snp', 'mutation', 'genotype'];
                $sizeList = [19, 25, 27, 29];
                $alignList = ['center', 'center', 'center', 'center'];
                $colorList = [null, null, null, null];

                (new CancerSnipTable($this->pdf))
                    ->set($cancer, $headerList, $fieldsList, $sizeList, $colorList, $alignList, 'variants');
            }
        }
    }
}
