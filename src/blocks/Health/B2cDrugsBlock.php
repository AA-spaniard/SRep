<?php

declare(strict_types=1);

namespace src\blocks\Health;

use src\AtlasReport;

class B2cDrugsBlock
{
    private const RECOMMENDATION_COLORS = [
        0 => 'black',
        1 => 'green',
        2 => 'orange',
        3 => 'red',
    ];

    private $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('drugs')) {
            $this->render();
        }
    }

    private function render(): void
    {
        $this->setupBookmark();
        $this->renderCover();
        $this->renderBlockDescription();

        $drugs = $this->prepareDrugsData();

        $this->renderOverallTable($drugs);
        $this->renderIndividualPages($drugs);

        $this->pdf->addon->set('drugs');
    }

    private function setupBookmark(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('pharmacogenetics_drug', 1);
    }

    private function renderCover(): void
    {
        $this->pdf->cover->set('bg/12.jpg');
        $this->pdf->coverTitle->set('pharmacogenetics_drug');
    }

    private function renderBlockDescription(): void
    {
        $this->pdf->AddPage();
        $this->pdf->sectionIntroTitle->set('pharmacogenetics_drug');
        $description = $this->pdf->getText('pharm_block_description');
        $this->pdf->pageDescription->setByParagraphs($description);
    }

    private function prepareDrugsData()
    {
        $drugs = $this->pdf->data['drugs'];
        \usort($drugs, function ($a, $b) {
            $recommendationLevelA = $a['chart']['val'] ?? 0;
            $recommendationLevelB = $b['chart']['val'] ?? 0;
            if ($recommendationLevelA === $recommendationLevelB) {
                return $a['name'] <=> $b['name'];
            }

            return $recommendationLevelA < $recommendationLevelB ? 1 : -1;
        });

        return $drugs;
    }

    private function renderOverallTable($drugs): void
    {
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('recs', false);

        $data = \array_map(function ($drug) {
            $recommendationLevel = $drug['chart']['val'] ?? 0;
            $color = self::RECOMMENDATION_COLORS[$recommendationLevel];

            return [
                'name' => $drug['name'],
                'rec' => $drug['short_recommendation'],
                'color' => $color,
            ];
        }, $drugs);

        $headers = [$this->pdf->getText('name'), $this->pdf->getText('recs')];
        $fieldsList = ['name', 'rec'];
        $sizeList = ['20%', '80%'];
        $colorList = [null, null];
        $alignList = ['left', 'left'];
        $highlightList = [null, 'color'];

        $this->pdf->table->set($headers, $data, $fieldsList, $sizeList, $colorList, $alignList, [], $highlightList);
    }

    private function renderIndividualPages($drugs): void
    {
        $drugsToRender = \array_filter($drugs, function ($drug) {
            $recommendationLevel = $drug['chart']['val'] ?? 0;

            return $recommendationLevel >= 2;
        });

        foreach ($drugsToRender as $drug) {
            $recommendationLevel = $drug['chart']['val'] ?? 0;
            $this->pdf->AddPage();
            $this->pdf->pageTitle->set($drug['name']);
            $this->pdf->pageShortDescription->set($drug['short_recommendation'], self::RECOMMENDATION_COLORS[$recommendationLevel]);
            $this->pdf->pageDescription->set($drug['definition']);

            $this->pdf->pageSubtitle->set('pharmacogenetics_drug', false);
            $this->pdf->pageDescription->set($drug['pharmacogenetics_recommendations']);

            $this->renderIndividualDrugGenetics($drug);
        }
    }

    private function renderIndividualDrugGenetics($drug): void
    {
        // prevent subtitle at the end of a page
        if ($this->pdf->GetY() > 650) {
            $this->pdf->AddPage();
        }
        $this->pdf->pageSubtitle->set('genetics_influence', false);
        $this->pdf->pageDescription->set($drug['genetics']['descr']);

        $headers = [$this->pdf->getText('Symptom'), $this->pdf->getText('status')];
        $fieldsList = ['trait', 'state_description'];
        $sizeList = ['50%', '50%'];
        $colorList = [null, null];
        $alignList = ['left', 'left'];

        // The 'genetics'->'table' field seems to always contain exactly one element. Hence "[0]"
        // This is an oddity of input data structure.
        $this->pdf->table->set($headers, $drug['genetics']['table'][0], $fieldsList, $sizeList, $colorList, $alignList);
    }
}
