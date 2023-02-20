<?php

declare(strict_types=1);

namespace src\blocks\Health\components\Risk;

use src\AtlasReport;
use src\blocks\Health\components\Risk\components\RiskGraph;
use src\blocks\Health\components\Risk\components\RiskSnipTable;

class Risk
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($risks, $onlyHigh = false): void
    {
        $highGroups = [5, 4];

        foreach ($risks as $risk) {
            if (!$onlyHigh || ($onlyHigh && \in_array($risk['group'], $highGroups))) {
                $this->pdf->AddPage();
                $this->pdf->pageTitle->set($risk['title']);
                $this->pdf->Bookmark($risk['topic_id'], 0, 0, '', 'B', [128, 0, 255], -1, '*utf8test.txt');

                //risk graph
                $graph = new RiskGraph($this->pdf);
                $graph->set($risk);

                //risk description
                $this->pdf->pageDescription->setByParagraphs($risk['descr']);

                //risk snips table
                $headerList = [
                    $this->pdf->getText('gen'),
                    $this->pdf->getText('variant'),
                    $this->pdf->getText('genotype'),
                    $this->pdf->getText('contrib'),
                ];
                $fieldsList = ['gene', 'rs', 'genotype', 'tableOR'];
                $sizeList = [26, 28, 23, 23];
                $alignList = ['left', 'center', 'center', 'right'];
                $colorList = [null, null, null, 'color'];

                $snipTable = new RiskSnipTable($this->pdf);
                $snipTable->set($risk, $headerList, $fieldsList, $sizeList, $colorList, $alignList);
            }
        }
    }
}
