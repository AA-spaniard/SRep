<?php

namespace src\blocks\FrontpageBiome;

use src\AtlasReport;

class FrontpageBiomeBlock
{
    private $pdf;
    private $template;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('frontPage')) {
            $this->setTemplate();
            $this->build();
        }
        $this->pdf->addon->set('frontPage', false);
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $supported_langs = [
            'ru',
            'en',
            'it',
            'da',
            'tr',
            'ja',
        ];

        $user_locale = $this->pdf->data['profile']['locale'];
        $display_locale = \in_array($user_locale, $supported_langs) ? $user_locale : 'en';

        $this->template = [
            'cover' => 'bg/front_biome_'.$display_locale.'.jpg',
        ];

        //@TODO refactor this
        if (153 === $this->pdf->partner_id) {
            $this->template = [
                'cover' => 'bg/b2b_biome/153.png',
            ];
        }
        if (371 === $this->pdf->partner_id) {
            $this->template = [
                'cover' => 'bg/b2b_biome/371.png',
            ];
        }

        if (523 === $this->pdf->partner_id) {
            $this->template = [
                'cover' => 'bg/b2b_biome/523.jpg',
            ];
        }

        if (606 === $this->pdf->partner_id) {
            $this->template = [
                'cover' => 'bg/b2b_biome/606.jpg',
            ];
        }

        if (752 === $this->pdf->partner_id) {
            $this->template = [
                'cover' => 'bg/b2b_biome/752.jpg',
            ];
        }

        if ('emc' === $template_name) {
            $this->template = [
                'cover' => 'bg/emc/front_cover.png',
            ];
        } elseif ('cg' === $template_name) {
            $this->template = [
                'cover' => 'bg/cg/front_cover.jpg',
            ];
        }
    }

    private function build(): void
    {
        $this->pdf->cover->set($this->template['cover']);
    }
}
