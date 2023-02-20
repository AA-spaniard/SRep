<?php

declare(strict_types=1);

namespace src\blocks\Health;

use src\AtlasReport;

class OtherHealthRelatedTraitsBlock
{
    private $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('otherHealthRelatedTraits')) {
            $this->build();
            $this->pdf->addon->set('otherHealthRelatedTraits');
        }
    }

    private function build(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('otherHealthRelatedTraits', 1);
        $coverImage = 'emc' !== $this->pdf->template_name
            ? 'bg/other_health_traits_cover.png'
            : 'bg/emc/emc_section.jpg';
        $this->pdf->cover->set($coverImage);
        $this->pdf->coverTitle->set('otherHealthRelatedTraits');

        $this->pdf->AddPage();
        $this->pdf->sectionIntroTitle->set('otherHealthRelatedTraits');
        $description = $this->pdf->getText('health_related_traits_block_description');
        $this->pdf->pageDescription->setByParagraphs($description);

        $topics = $this->pdf->data['otherHealthRelatedTraits'];
        $topicsWithGoodColors = \array_map(function ($topic) {
            return \array_merge($topic, ['color' => $this->getGoodColor($topic['color'])]);
        }, $topics);
        $this->pdf->topicsPages->set($topicsWithGoodColors);
    }

    private function getGoodColor($badColor)
    {
        $scheme = [
            'red' => '#FE965E', // orange
            'orange' => '#97C7FF', // blue
            'green' => '#A957C1', // purple
        ];

        return $scheme[$badColor] ?? $badColor;
    }
}
