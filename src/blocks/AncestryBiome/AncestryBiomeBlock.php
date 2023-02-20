<?php

namespace src\blocks\AncestryBiome;

use src\AtlasReport;

class AncestryBiomeBlock
{
    public $pdf;
    public $cover;

    //////////
    // Data //
    //////////

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('ancestry')) {
            $this->ancestry = $this->getData();

            $this->build();
        }
        $this->pdf->addon->set('ancestry');
    }

    public function build(): void
    {
        //block cover page
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('citizenship_short');
//        $this->pdf->cover->set('bg/70.jpg');
//        $this->pdf->coverTitle->set('citizenship_short');

        $this->pdf->AddPage();
        $this->pdf->pageTitle->set($this->pdf->getText('citizenship').': '.$this->ancestry[0]['region']);
        $this->pdf->pageShortDescription->set($this->ancestry[0]['text'], 'black', true);
        $this->pdf->pageDescription->set('citizenship_info', false);

        $headerList = [
            '',
            '',
        ];
        $fieldsList = ['region', 'value'];
        $sizeList = ['50%', '50%'];
        $alignList = ['left', 'right'];
        $colorList = [null, null];
        $iconList = [null, null];

        $this->pdf->table->set($headerList, $this->ancestry, $fieldsList, $sizeList, $colorList, $alignList, $iconList);
    }

    //////////
    // DATA //
    //////////

    public function getData()
    {
        $ancestry = $this->pdf->data['citizenship'];
        foreach ($ancestry as &$line) {
            $line['region'] = $this->pdf->getText($line['region']);
            $line['value'] = $line['percent'].'%';
        }

        \usort($ancestry, function ($a, $b) {
            return $b['percent'] <=> $a['percent'];
        });

        return $ancestry;
    }
}
