<?php

declare(strict_types=1);

namespace src\blocks\Frontpage;

use src\AtlasReport;

class FrontpageBlock
{
    private const LOCALE_EN = 'en';
    private const LOCALE_RU = 'ru';
    private const LOCALE_IT = 'it';
    private const LOCALE_DA = 'da';
    private const LOCALE_TR = 'tr';
    private const LOCALE_JA = 'ja';

    private const SUPPORTED_LOCALES = [
        self::LOCALE_RU,
        self::LOCALE_EN,
        self::LOCALE_IT,
        self::LOCALE_DA,
        self::LOCALE_TR,
        self::LOCALE_JA,
    ];
    private const DEFAULT_LOCALE = self::LOCALE_EN;

    private $pdf;
    private $template;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('frontPage')) {
            $this->template = [
                'cover' => $this->chooseCover(),
            ];
            $this->build();
        }
        $this->pdf->addon->set('frontPage', false);
    }

    private function build(): void
    {
        $this->pdf->cover->set($this->template['cover']);
    }

    private function chooseCover(): string
    {
        $userLocale = $this->pdf->data['profile']['locale'];
        $displayLocale = \in_array($userLocale, self::SUPPORTED_LOCALES, true)
            ? $userLocale
            : self::DEFAULT_LOCALE;

        $isWgs = AtlasReport::TYPE_DNA_WGS === $this->pdf->data['source']['type'];
        if ($isWgs) {
            if ($this->pdf->is_full_custom_frontpage) {
                return 'bg/b2b_dna_wgs/'.$this->pdf->partner_id.'.jpg';
            }

            if ($this->pdf->is_custom_frontpage) {
                throw new \Exception('WGS does not support custom front page cover');
            }

            if (self::LOCALE_EN === $displayLocale) {
                return $this->pdf->isB2c ? 'bg/front_dna_wgs_en_b2c.png' : 'bg/front_dna_wgs_en_b2b.png';
            }
            if (self::LOCALE_RU === $displayLocale) {
                return $this->pdf->isB2c ? 'bg/front_dna_wgs_ru_b2c.png' : 'bg/front_dna_wgs_ru_b2b.png';
            }

            throw new \Exception('Only ru and en locales are supported for WGS');
        }

        if ($this->pdf->is_custom_frontpage) {
            return 'bg/b2b_dna/'.$this->pdf->partner_id.'.jpg';
        }

        return 'bg/front_dna_'.$displayLocale.'.jpg';
    }
}
