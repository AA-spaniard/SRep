<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:29 PM.
 */

namespace src\blocks\Sport;

use src\AtlasReport;

class SportBlock
{
    const TOPICS = [
        'physiologyAndMetabolism',
        'sportRisks',
    ];

    public $pdf;
    public $cover;
    public $template;

    //////////
    // Data //
    //////////

    public $sport;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('sport')) {
            $this->sport = $this->getSport();

            $this->setTemplate();
            $this->build();
        }

        $this->pdf->addon->set('sport');
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'cover-sport' => 'bg/60.jpg',
            'physiologyAndMetabolism' => 'bg/new_112.jpg',
            'metabolism' => 'bg/new_112.jpg',
            'endurance' => 'bg/new_104.jpg',
            'strengthAndMuscles' => 'bg/new_100.jpg',
            'sportRisks' => 'bg/new_119.jpg',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'cover-sport' => 'bg/emc/emc_section.jpg',
                'physiologyAndMetabolism' => 'bg/emc/emc_section.jpg',
                'metabolism' => 'bg/emc/emc_section.jpg',
                'endurance' => 'bg/emc/emc_section.jpg',
                'strengthAndMuscles' => 'bg/emc/emc_section.jpg',
                'sportRisks' => 'bg/emc/emc_section.jpg',
            ];
        }
    }

    public function build(): void
    {
        //block cover page
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('sport');
        $this->pdf->cover->set($this->template['cover-sport']);
        $this->pdf->coverTitle->set('sport');

        //block intro page
        $this->pdf->AddPage();
        $this->pdf->blockIntroTitle->set('sport', 'cover-intro/sport-cover.jpg', true);
        $this->pdf->pageDescription->set('geneticStudies', false);
        $this->pdf->pageDescription->set('sportInjury', false);

        foreach (self::TOPICS as $topicsGroup) {
            if (isset($this->sport[$topicsGroup])) {
                $group = $this->sport[$topicsGroup];
                //block cover page
                $this->pdf->serviceLocator->getBookmarks()->addBookmark($topicsGroup, 1);

                if ($this->pdf->template['show-subcovers']) {
                    $this->pdf->cover->set($this->template[$topicsGroup]);
                    $this->pdf->coverTitle->set($topicsGroup);
                }

                //show topics preview
                $this->pdf->topicsList->set($group);

                //show topics page
                if (!$this->pdf->is_short_sections) {
                    $this->pdf->topicsPages->set($group);
                }
            }
        }
    }

    //////////
    // DATA //
    //////////

    public function getSport()
    {
        $groups = [];

        foreach ($this->pdf->data['sport'] as $key => $topic) {
            $groups[$topic['type']][] = $topic;
        }

        foreach ($groups as $type => &$group) {
            \usort($group, function ($a, $b) {
                return $a['score'] <=> $b['score'];
            });
            if (!\in_array($type, self::TOPICS, true)) {
                throw new \Exception("Error in Sport block: topic \"$type\" not found.");
            }
        }

        return $groups;
    }
}
