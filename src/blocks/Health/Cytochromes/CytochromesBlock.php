<?php

declare(strict_types=1);

namespace src\blocks\Health\Cytochromes;

use src\AtlasReport;

class CytochromesBlock
{
    const ITEM_TYPE_DIPLOTYPE = 'ITEM_TYPE_DIPLOTYPE';
    const ITEM_TYPE_METABOLIZER = 'ITEM_TYPE_METABOLIZER';

    private $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('pharmacogeneticsCytochromes')) {
            $this->render();
        }
    }

    private function render(): void
    {
        $this->setupBookmark();
        $this->renderCover();
        $this->renderBlockDescription();

        $cytochromes = $this->getCytochromes();

        foreach ($cytochromes as $cytochrome) {
            $this->renderIndividualCytochrome($cytochrome);
        }

        $this->pdf->addon->set('pharmacogeneticsCytochromes');
    }

    private function setupBookmark(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('pharmacogenetics_cytochrome', 1);
    }

    private function renderCover(): void
    {
        $this->pdf->cover->set('emc' === $this->pdf->template_name ? 'bg/emc/emc_section.jpg' : 'bg/12.jpg');
        $this->pdf->coverTitle->set('pharmacogenetics_cytochrome');
    }

    private function renderBlockDescription(): void
    {
        $this->pdf->AddPage();
        $this->pdf->sectionIntroTitle->set('pharmacogenetics_cytochrome');
        $description = $this->pdf->getText('cytochromes_block_description');
        $this->pdf->pageDescription->setByParagraphs($description);
    }

    private function renderIndividualCytochrome($cytochrome): void
    {
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set($cytochrome['title'], true);
        $this->pdf->pageDescription->set($cytochrome['description'], true);

        CytochromesTable::renderWithDefaults($this->pdf, $cytochrome);
    }

    public function getCytochromes()
    {
        return \array_map(function ($cyto) {
            return \array_merge($cyto, [
                'diplotypes' => $this->prepareCytochrtomeItems($cyto['diplotypes'], self::ITEM_TYPE_DIPLOTYPE),
                'metabolizers' => $this->prepareCytochrtomeItems($cyto['metabolizers'], self::ITEM_TYPE_METABOLIZER),
            ]);
        }, $this->pdf->data['cytochromes']);
    }

    private function prepareCytochrtomeItems($itemsList, $itemType)
    {
        $BIGGEST_DIPLOTYPE_LIMIT = 0.85;
        $ITEMS_LIMIT = 5;

        \usort($itemsList, function ($a, $b) {
            return $b['probability'] <=> $a['probability'];
        });

        $isMaxBiggestItemHit = self::ITEM_TYPE_DIPLOTYPE === $itemType
            && \count($itemsList) > 0
            && $itemsList[0]['probability'] >= $BIGGEST_DIPLOTYPE_LIMIT;
        $numberOfItemsToShow = $isMaxBiggestItemHit ? 1 : $ITEMS_LIMIT;

        $itemsToShow = \array_slice($itemsList, 0, $numberOfItemsToShow);
        $result = \array_map(
            function ($item) {
                return [
                    'title' => $item['title'] ?? $item['diplotype'],
                    'probabilityText' => $this->renderPercent($item['probability']),
                ];
            },
            $itemsToShow
        );

        if (\count($itemsList) > $numberOfItemsToShow) {
            $others = \array_slice($itemsList, $numberOfItemsToShow);
            $othersProbability = \array_reduce(
                $others,
                function ($carry, $item) {
                    return $carry + $item['probability'];
                },
                0
            );
            $result[] = [
                'title' => self::ITEM_TYPE_DIPLOTYPE === $itemType
                  ? $this->pdf->getText('other_diplotype')
                  : $this->pdf->getText('other_metabolizer'),
                'probabilityText' => $this->renderPercent($othersProbability),
            ];
        }

        return $result;
    }

    private function renderPercent(float $probability): string
    {
        if ($probability < 0.01) {
            return '< 1%';
        }
        if ($probability >= 1.0) {
            return '100%';
        }
        if ($probability > 0.999) {
            return '99.9%';
        }

        return \round($probability * 100, 1).'%';
    }
}
