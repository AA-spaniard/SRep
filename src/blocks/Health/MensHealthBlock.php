<?php

declare(strict_types=1);

namespace src\blocks\Health;

use src\AtlasReport;
use src\blocks\Health\components\Risk\Risk;
use src\blocks\Health\components\Risks\Risks;

class MensHealthBlock
{
    public $pdf;
    public $cover;
    public $template;

    //////////
    // Data //
    //////////

    public $risks;
    public $risksGroups;
    public $risksList;
    public $traits;

    public $maxDelta = 0;
    public $minDelta = 100;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('mensHealth')) {
            $this->risks = $this->getRisks();
            $this->risksGroups = $this->getRisksGroups();
            $this->risksList = $this->getRisksList();
            $this->traits = $this->getTraits();

            $this->setTemplate();
            $this->build();
        }
        $this->pdf->addon->set('mensHealth');
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'cover-menshealth' => 'bg/mensHealth.jpg',
            'cover-color' => 'black',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'cover-menshealth' => 'bg/emc/emc_section.jpg',
                'cover-color' => null,
            ];
        }
    }

    public function build(): void
    {
        //block cover page
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('menshealth');
        $this->pdf->cover->set($this->template['cover-menshealth']);
        $this->pdf->coverTitle->set('menshealth', $this->template['cover-color']);

        //risks
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('section_risks', 1);

        //risks table
        $risksList = new Risks($this->pdf, 'section_risks');
        $risksList->set($this->risksGroups);

        //risks pages
        if (!$this->pdf->is_short_sections) {
            $risksPages = new Risk($this->pdf);
            $risksPages->set($this->risksList);
        }

        //traits
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('section_traits', 1);

        //traits list
        $this->pdf->topicsList->set($this->traits);

        //show topics page
        if (!$this->pdf->is_short_sections) {
            $this->pdf->topicsPages->set($this->traits);
        }
    }

    //////////
    // DATA //
    //////////

    public function getRisks()
    {
        $allRisks = [];
        $risksGroupProperties = [
            3 => 'red',
            2 => 'orange-light',
            1 => 'green',
        ];

        $this->getHighRisksMargins();

        foreach ($this->pdf->data['mensHealth']['risks'] as $key => $risk) {
            $risk['delta'] = ($risk['you'] - $risk['P0']) * 100;
            $risk['tableYou'] = $this->pdf->utils->fixedPercent($risk['you'] * 100, true).'%';
            $risk['tableP0'] = $this->pdf->utils->fixedPercent($risk['P0'] * 100, true).'%';
            $risk['tableRatio'] = $this->pdf->utils->fixedPercent($risk['ratio'], true).'x';
            $risk['colorRatio'] = $risk['ratio'] > 1 ? $this->pdf->colors['red'] : $this->pdf->colors['black'];
            $risk['tableDelta'] = $this->pdf->utils->fixedPercent($risk['delta']);
            $risk['graphColor'] = $risksGroupProperties[$risk['group']];

            if ($risk['delta'] > 0) {
                $risk['tableDelta'] = '+'.$risk['tableDelta'];
            }

            //risk graph params
            $you = $this->pdf->utils->fixedPercent($risk['you'] * 100, true);
            $P0 = $this->pdf->utils->fixedPercent($risk['P0'] * 100, true);
            $maxGraphWidth = 35;
            $minGraphWidth = 0.5;
            $staticGraphWidth = 25;
            $risk['graphP0'] = $staticGraphWidth / $you * \abs($P0);
            if ($risk['graphP0'] > $maxGraphWidth) {
                $risk['graphP0'] = $maxGraphWidth;
            }
            if ($risk['graphP0'] < $minGraphWidth) {
                $risk['graphP0'] = $minGraphWidth;
            }
            $risk['graphTotal'] = $staticGraphWidth;

            //snip table params
            $snipsHigh = [];
            $snipsLow = [];
            foreach ($risk['genBar'] as $snipName => $snip) {
                if (0 === $snip['type']) {
                    $snip['rs'] = $snipName;
                    $snip['gene'] = $snip['gene'] ?: '-';
                    $snip['tableOR'] = $this->pdf->utils->fixedPercent($snip['OR']);
                    if ($snip['OR'] > 0) {
                        $snip['color'] = $this->pdf->colors['red'];
                        $snipsHigh[] = $snip;
                    } else {
                        $snip['color'] = $this->pdf->colors['green'];
                        $snipsLow[] = $snip;
                    }
                }
            }

            \usort($snipsHigh, function ($a, $b) {
                return $b['OR'] <=> $a['OR'];
            });

            \usort($snipsLow, function ($a, $b) {
                return $a['OR'] <=> $b['OR'];
            });

            $risk['snipsHigh'] = $snipsHigh;
            $risk['snipsLow'] = $snipsLow;

            if (\count($snipsHigh) || \count($snipsLow)) {
                $allRisks[] = $risk;
            }
        }

        \usort($allRisks, function ($a, $b) {
            return $b['delta'] <=> $a['delta'];
        });

        return $allRisks;
    }

    public function getRisksGroups()
    {
        $risksGroups = [];

        foreach ($this->risks as $key => $risk) {
            $risksGroups[$risk['group']][] = $risk;
        }

        foreach ($risksGroups as &$risksGroup) {
            \usort($risksGroup, function ($a, $b) {
                return $b['delta'] <=> $a['delta'];
            });
        }

        return $risksGroups;
    }

    public function getRisksList()
    {
        $risksList = [];

        $risksGroups = $this->risksGroups;
        \ksort($risksGroups);
        $risksGroups = \array_reverse($risksGroups);

        foreach ($risksGroups as $risksGroup) {
            foreach ($risksGroup as $risk) {
                $risksList[] = $risk;
            }
        }

        return $risksList;
    }

    public function getHighRisksMargins(): void
    {
        foreach ($this->pdf->data['mensHealth']['risks'] as $key => $risk) {
            $delta = ($risk['you'] - $risk['P0']) * 100;
            $this->minDelta = \min($this->minDelta, $delta);
            $this->maxDelta = \max($this->maxDelta, $delta);
        }
    }

    public function getTraits()
    {
        $traits = [];

        foreach ($this->pdf->data['mensHealth']['nutrition'] as $key => $topic) {
            $traits[] = $topic;
        }
        foreach ($this->pdf->data['mensHealth']['sport'] as $key => $topic) {
            $traits[] = $topic;
        }
        foreach ($this->pdf->data['mensHealth']['traits'] as $key => $topic) {
            $traits[] = $topic;
        }

        \usort($traits, function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        foreach ($traits as &$trait) {
            $trait['type'] = 'section_traits';
        }

        return $traits;
    }
}
