<?php

declare(strict_types=1);

namespace src\blocks\Health\components\Risks;

use src\AtlasReport;

class Risks
{
    public $pdf;
    public $splitListView = false;

    private $risksTitle = false;

    public function __construct(AtlasReport $pdf, $risksTitle = 'allRiscs')
    {
        $this->pdf = $pdf;
        $this->risksTitle = $risksTitle;
    }

    public function set($risks): void
    {
        $risksGroupProperties = [
            3 => [
                'title' => 'risksHigh',
                'color' => 'red',
            ],
            2 => [
                'title' => 'risksTypical',
                'color' => 'orange-light',
            ],
            1 => [
                'title' => 'risksLow',
                'color' => 'green',
            ],
        ];

        $headerList = [
            $this->pdf->getText('name'),
            $this->pdf->getText('riskYou'),
            $this->pdf->getText('riskAvg'),
            $this->pdf->getText('riskRelative'),
            $this->pdf->getText('comparedAvg'),
        ];
        $fieldsList = ['title', 'tableYou', 'tableP0', 'tableRatio', 'tableDelta'];
        $sizeList = ['37%', '11%', '16%', '17%', '16%'];
        $alignList = ['left', 'right', 'right', 'right', 'right'];
        $colorList = [null, null, null, 'colorRatio', null];

        if (!$this->splitListView) {
            $this->pdf->AddPage();
            $this->pdf->pageTitle->set($this->risksTitle, false);
        }

        $risksGroupCount = 0;
        foreach ($risksGroupProperties as $risksGroup => $riskGroupProperty) {
            if (isset($risks[$risksGroup])) {
                if ($this->splitListView) {
                    $this->pdf->AddPage();
                    if (0 === $risksGroupCount) {
                        $this->pdf->pageTitle->set('allRiscs', false);
                    }
                } else {
                    if ($this->pdf->GetY() > 700) {
                        $this->pdf->AddPage();
                    }
                }
                $this->pdf->pageSubtitle->set($riskGroupProperty['title'],
                    false,
                    $this->pdf->colors[$riskGroupProperty['color']]);
                $this->pdf->table->set($headerList, $risks[$risksGroup], $fieldsList, $sizeList, $colorList, $alignList);
                ++$risksGroupCount;
            }
        }
    }
}
