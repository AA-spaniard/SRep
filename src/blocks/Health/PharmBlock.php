<?php

declare(strict_types=1);

namespace src\blocks\Health;

use src\AtlasReport;

class PharmBlock
{
    public $pdf;
    public $cover;
    public $template;

    //////////
    // Data //
    //////////

    public $pharms;
    public $sections;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('pharmacogeneticsDrugs')) {
            $this->pharms = $this->getPharm();
            $this->sections = $this->getPharmSections();

            $this->setTemplate();
            $this->build();
            $this->pdf->addon->set('pharmacogeneticsDrugs');
        }
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'cover-pharmacogenetics_drug' => 'bg/12.jpg',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'cover-pharmacogenetics_drug' => 'bg/emc/emc_section.jpg',
            ];
        }
    }

    public function build(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('pharmacogenetics_drug', 1);
        $this->pdf->cover->set($this->template['cover-pharmacogenetics_drug']);
        $this->pdf->coverTitle->set('pharmacogenetics_drug');

        $this->pdf->AddPage();
        $this->pdf->sectionIntroTitle->set('pharmacogenetics_drug');
        $description = $this->pdf->getText('pharm_block_description');
        $this->pdf->pageDescription->setByParagraphs($description);

        foreach ($this->pharms as $key => $group) {
            $this->pdf->AddPage();
            $this->pdf->pageTitle->set($this->sections[$group[0]['section_id']], true);

            $headerList = [
                $this->pdf->getText('name'),
                $this->pdf->getText('evidence'),
                $this->pdf->getText('rec'),
            ];
            $fieldsList = ['title', 'level', 'rec'];
            $sizeList = ['33%', '25%', '42%'];
            $alignList = ['left', 'left', 'left'];
            $colorList = [null, null, null];

            $this->pdf->table->set($headerList, $group, $fieldsList, $sizeList, $colorList, $alignList, null, false);
        }
    }

    //////////
    // DATA //
    //////////
    public function getPharm()
    {
        $groups = [];

        foreach ($this->pdf->data['drugs'] as $key => $topic) {
            $topic['rec'] = $topic['recommendation']['title'];
            $topic['level'] = $topic['recommendation']['level'];
            $groups[$topic['section_id']][] = $topic;
        }

        foreach ($groups as &$group) {
            \usort($group, function ($a, $b) {
                return $a['title'] <=> $b['title'];
            });
        }

        \ksort($groups);
        if (\array_key_exists('other', $groups)) {
            $otherGroup = $groups['other'];
            unset($groups['other']);
            $groups['other'] = $otherGroup;
        }

        return $groups;
    }

    public function getPharmSections()
    {
        $sections = [];

        foreach ($this->pdf->data['drug_sections'] as $section) {
            $sections[$section['id']] = $section['title'];
        }

        return $sections;
    }
}
