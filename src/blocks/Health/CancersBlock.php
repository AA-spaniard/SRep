<?php

declare(strict_types=1);

namespace src\blocks\Health;

use src\AtlasReport;
use src\blocks\Health\components\Monogen\Cancer;
use src\blocks\Health\components\Monogens\Cancers;

class CancersBlock
{
    public $pdf;
    public $cover;
    public $template;
    public $healthBlock;

    //////////
    // Data //
    //////////

    public $cancers;

    public function __construct(AtlasReport $pdf, HealthBlock $healthBlock)
    {
        $this->pdf = $pdf;
        $this->healthBlock = $healthBlock;

        if ($this->pdf->isSectionNeeded('hereditaryCancers')) {
            $data = $this->pdf->data;
            $this->cancers = $healthBlock->getMonogens($data['hereditary_cancers_list'], $data['hereditary_cancers_detected_list']);

            $this->setTemplate();
            $this->build();
        }
        $this->pdf->addon->set('hereditaryCancers');
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'cover-hereditary_cancers' => 'bg/39.jpg',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'cover-hereditary_cancers' => 'bg/emc/emc_section.jpg',
            ];
        }
    }

    public function build(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('hereditary_cancers', 1);
        $this->pdf->cover->set($this->template['cover-hereditary_cancers']);
        $this->pdf->coverTitle->set('hereditary_cancers');

        $this->pdf->AddPage();
        $this->pdf->sectionIntroTitle->set('hereditary_cancers');
        $description = $this->pdf->getText('hereditary_cancers_block_description');
        $this->pdf->pageDescription->setByParagraphs($description);

        //cancers table
        $cancersList = new Cancers($this->pdf);
        $cancersList->set($this->cancers);

        //cancers pages
        if (!$this->pdf->is_short_sections) {
            $cancersPages = new Cancer($this->pdf);
            $cancersPages->set($this->cancers);
        }
    }

    public function getCancersStatusTitle($status)
    {
        $statusList = [
            1 => $this->pdf->getText('noIdentifiedMutations'),
            2 => $this->pdf->getText('carrier'),
            3 => $this->pdf->getText('unknown'),
            4 => $this->pdf->getText('illness'),
        ];

        return $statusList[$status];
    }
}
