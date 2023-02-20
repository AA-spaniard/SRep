<?php

declare(strict_types=1);

namespace src\blocks\Health;

use src\AtlasReport;
use src\blocks\Health\components\Monogen\Monogen;
use src\blocks\Health\components\Monogens\Monogens;
use src\blocks\Health\components\Risk\Risk;
use src\blocks\Health\components\Risks\Risks;
use src\blocks\Health\Cytochromes\CytochromesBlock;
use src\helpers\ArrayHelper;

class HealthBlock
{
    private const MONOGEN_ICONS = [
        'autosomeCompoundHeterozygote',
        'yHemizygote',
        'autosomeUnaffected',
        'mtUnaffected',
        'xHemizygote',
        'autosomeHeterozygote',
        'mtHemizygote',
        'xyUnaffected',
        'autosomeHomozygote',
    ];

    public $pdf;
    public $cover;
    public $template;

    public $risks;
    public $risksGroups;
    public $risksList;
    public $monogens;

    public $maxDelta = 0;
    public $minDelta = 100;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        $risksTemplate = $this->pdf->isSectionNeeded('risks');
        $monogensTemplate = $this->pdf->isSectionNeeded('monogens');

        if ($risksTemplate || $monogensTemplate) {
            if ($risksTemplate) {
                $this->risks = $this->getRisks();
                $this->risksGroups = $this->getRisksGroups();
                $this->risksList = $this->getRisksList();
            }

            if ($monogensTemplate) {
                $data = $this->pdf->data;
                $this->monogens = $this->getMonogens($data['monogens'], $data['monogen']);
            }
        }

        if ($this->getIsBlockNeeded()) {
            $this->setTemplate();
            $this->build();
        }
    }

    private function getIsBlockNeeded()
    {
        $sections = [
            'risks',
            'monogens',
            'hereditaryCancers',
            'pharmacogeneticsDrugs', // b2b version
            'pharmacogeneticsCytochromes',
            'drugs', // b2c version
        ];
        foreach ($sections as $section) {
            if ($this->pdf->isSectionNeeded($section)) {
                return true;
            }
        }

        return false;
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'cover-health' => 'bg/23.jpg',
            'cover-risks' => 'bg/25.jpg',
            'cover-monogens' => 'bg/32.jpg',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'cover-health' => 'bg/emc/emc_section.jpg',
                'cover-risks' => 'bg/emc/emc_section.jpg',
                'cover-monogens' => 'bg/emc/emc_section.jpg',
            ];
        }
    }

    public function build(): void
    {
        // block cover page
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('health');
        $this->pdf->cover->set($this->template['cover-health']);
        $this->pdf->coverTitle->set('health');
        $this->pdf->coverSubtitle->set('healthDescr');

        // block intro page
        $this->pdf->AddPage();
        $this->pdf->blockIntroTitle->set('geneticRole', 'cover-intro/gen-cover.jpg');
        $this->pdf->pageDescription->set('dnaInfo', false);
        $this->pdf->pageDescription->set('dnaInfo2', false);

        // section cover page: risks
        if ($this->pdf->isSectionNeeded('risks')) {
            $this->pdf->serviceLocator->getBookmarks()->addBookmark('multiDisease', 1);
            $this->pdf->cover->set($this->template['cover-risks']);
            $this->pdf->coverTitle->set('multiDisease');

            // section intro page: risks
            $this->pdf->AddPage();
            $this->pdf->sectionIntroTitle->set('multiDisease');
            $this->pdf->pageDescription->set('riskLife', false);
            $this->pdf->pageDescription->set('riskLife2', false);
            $this->pdf->pageDescription->set('riskLife3', false);

            // risks table
            $risksList = new Risks($this->pdf);
            $risksList->set($this->risksGroups);

            // risks pages
            if (!$this->pdf->is_short_sections) {
                $risksPages = new Risk($this->pdf);
                $risksPages->set($this->risksList);
            }
        }

        $this->pdf->addon->set('risks');

        // section cover page: monogens
        if ($this->pdf->isSectionNeeded('monogens')) {
            $this->pdf->serviceLocator->getBookmarks()->addBookmark('monoDisease', 1);
            $this->pdf->cover->set($this->template['cover-monogens']);
            $this->pdf->coverTitle->set('monoDisease');

            // section intro page: monogens
            $this->pdf->AddPage();
            $this->pdf->sectionIntroTitle->set('monoDisease');
            $this->pdf->pageDescription->set('dnaMono', false);
            $this->pdf->pageDescription->set('monogenDescr', false);

            // monogens table
            $monogensList = new Monogens($this->pdf);
            $monogensList->set($this->monogens);

            // monogens pages
            if (!$this->pdf->is_short_sections) {
                $monogensPages = new Monogen($this->pdf);
                $monogensPages->set($this->monogens);
            }
        }

        $this->pdf->addon->set('monogens');

        new CancersBlock($this->pdf, $this);
        new PharmBlock($this->pdf);
        new CytochromesBlock($this->pdf);
        new B2cDrugsBlock($this->pdf);
        new OtherHealthRelatedTraitsBlock($this->pdf);
    }

    public function getRisks()
    {
        $allRisks = [];
        $risksGroupProperties = [
            3 => 'red',
            2 => 'orange-light',
            1 => 'green',
        ];

        $this->getHighRisksMargins();

        foreach ($this->pdf->data['risks'] as $key => $risk) {
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

            // risk graph params
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

            // snip table params
            $snipsHigh = [];
            $snipsLow = [];
            $hasLdUsages = false;
            foreach ($risk['genBar'] as $snipName => $snip) {
                if (0 === $snip['type']) {
                    $isCalculatedUsingLd = ($snip['is_calculated_using_ld'] ?? false) === true;
                    $hasLdUsages = $hasLdUsages || $isCalculatedUsingLd;
                    $snip['rs'] = $snipName.($isCalculatedUsingLd ? '*' : '');
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
            $risk['hasLdUsages'] = $hasLdUsages;

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
        foreach ($this->pdf->data['risks'] as $key => $risk) {
            $delta = ($risk['you'] - $risk['P0']) * 100;
            $this->minDelta = \min($this->minDelta, $delta);
            $this->maxDelta = \max($this->maxDelta, $delta);
        }
    }

    public function getMonogens($monogensFullList, $detectedMonogens)
    {
        $allMonogens = [];

        foreach ($monogensFullList as $monogen) {
            if (!isset($monogen['status']) || null === $monogen['status']) {
                $monogen['status'] = 1;
            }
            $monogen['statusTitle'] = $this->getMonogenStatusTitle($monogen['status']);
            $monogen['color'] = $monogen['status'] > 1 ? 'orange' : 'black';
            $monogen['icon'] = $this->getMonogenIcon($monogen);

            $detectedDetailsKey = ArrayHelper::findKey($detectedMonogens, function ($detected) use ($monogen) {
                return $detected['topic_search_id'] === $monogen['id'];
            });
            if (null !== $detectedDetailsKey) {
                $detectedDetails = $detectedMonogens[$detectedDetailsKey];
                $monogen['description'] = $detectedDetails['descr'];
                $monogen['family'] = $detectedDetails['family'];
                $monogen['system'] = $detectedDetails['organ_system'];

                $snips = [];
                foreach ($detectedDetails['snips'] as $snipName => $snip) {
                    $snip['snp'] = $snipName;
                    $snip['mutation'] = $this->shortenAllele($snip['normalAllele']).' -> '.$this->shortenAllele($snip['diseaseAllele']);
                    $userAlleles = \explode('/', $snip['userGenotype']);
                    $match = \array_search($snip['diseaseAllele'], $userAlleles, true);
                    if (false !== $match) {
                        $coloredAlleles = \array_map(function ($allele) use ($snip) {
                            return $allele === $snip['diseaseAllele']
                                ? '<span style="color: '.$this->pdf->colors['red'].'">'.$this->shortenAllele($allele).'</span>'
                                : $this->shortenAllele($allele);
                        }, $userAlleles);
                        $snip['genotype'] = \implode('/', $coloredAlleles);
                        $snips[] = $snip;
                    }
                }
                $monogen['snips'] = $snips;
            }

            $allMonogens[] = $monogen;
        }

        return $allMonogens;
    }

    private function getMonogenIcon(array $monogen): string
    {
        if (!isset($monogen['illustration_id'])) {
            $this->pdf->serviceLocator->getLogger()->warn('illustration_id for monogen not set. Assuming autosomeUnaffected', [
                'monogenId' => $monogen['id'] ?? '',
                'monogenTitle' => $monogen['title'] ?? '',
            ]);
        }
        $illustrationId = $monogen['illustration_id'] ?? 'autosomeUnaffected';
        if (!\in_array($illustrationId, self::MONOGEN_ICONS, true)) {
            throw new \Exception("Unknown illustrationId $illustrationId");
        }

        return "monogen/$illustrationId.jpg";
    }

    private function shortenAllele(string $allele): string
    {
        $LETTERS_TO_TAKE = 4;
        $SHORTEN_FROM = 10;
        $length = \mb_strlen($allele);
        if ($length < $SHORTEN_FROM) {
            return $allele;
        }

        $beginning = \mb_substr($allele, 0, $LETTERS_TO_TAKE);
        $ending = \mb_substr($allele, $length - $LETTERS_TO_TAKE, $LETTERS_TO_TAKE);

        return "$beginning...$ending";
    }

    public function getMonogenStatusTitle($status)
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
