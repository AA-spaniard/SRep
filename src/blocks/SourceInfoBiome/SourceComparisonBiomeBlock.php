<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:29 PM.
 */

namespace src\blocks\SourceInfoBiome;

use src\AtlasReport;

class SourceComparisonBiomeBlock
{
    public $pdf;
    public $cover;

    //////////
    // Data //
    //////////

    public $source;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('microbiomeReportsComparison')) {
            $this->source = $this->getSource();

            $this->build();
        }
        $this->pdf->addon->set('microbiomeReportsComparison');
    }

    public function build(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('comparison');
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('comparison', false);
        $this->pdf->ln(20);

        $headerList = [
            $this->pdf->getText('barcode'),
            $this->pdf->getText('sampled_at'),
            $this->pdf->getText('ready_at'),
            $this->pdf->getText('alpha_diversity_score'),
            $this->pdf->getText('probiotics_score_short'),
            $this->pdf->getText('fibers_score'),
            $this->pdf->getText('scfa_score'),
            $this->pdf->getText('is_abnormal'),
        ];
        $fieldsList = [
            'barcode',
            'sampled_at',
            'ready_at',
            'alpha_diversity_score',
            'probiotics_score',
            'fibers_score',
            'scfa_score',
            'is_abnormal',
        ];
        $sizeList = ['15%', '12.5%', '12.5%', '12.5%', '12.5%', '10%', '10%', '15%'];
        $alignList = ['center', 'center', 'center', 'center', 'center', 'center', 'center', 'center'];
        $colorList = [null, null, null, null, null, null, null, null];

        $this->pdf->table->set($headerList, $this->source, $fieldsList, $sizeList, $colorList, $alignList, [], false, 5, [], false, 9);
    }

    //////////
    // DATA //
    //////////

    public function getSource()
    {
        $source = $this->pdf->data['microbiomeReportsComparison'];

        foreach ($source as &$item) {
            $item['is_abnormal'] = $item['is_abnormal'] ? $this->pdf->getText('yes') : $this->pdf->getText('no');
            $item['ts'] = null === $item['sampled_at'] ? 0 : \strtotime($item['sampled_at']);
            $item['sampled_at'] = null !== $item['sampled_at']
                ? $this->pdf->renderLocalizedDate(new \DateTimeImmutable($item['sampled_at']))
                : '-';
            $item['ready_at'] = null !== $item['ready_at']
                ? $this->pdf->renderLocalizedDate(new \DateTimeImmutable($item['ready_at']))
                : '-';
        }

        \usort($source, function ($a, $b) {
            return $a['ts'] <=> $b['ts'];
        });

        return $source;
    }
}
