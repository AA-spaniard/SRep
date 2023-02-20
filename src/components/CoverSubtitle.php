<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:24 PM.
 */

namespace src\components;

use src\AtlasReport;

class CoverSubtitle
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($coverSubtitle): void
    {
        $this->pdf->SetFont($this->pdf->font_light, '', 12);
        $html = '<h6 style="'.$this->pdf->h6_cover_subtitle.'">'.$this->pdf->getText($coverSubtitle).'</h6>';
//        $this->pdf->writeHTML($html, true, false, true, false, '');
//        $this->pdf->MultiCell(300, 0, $coverSubtitle);
        $this->pdf->writeHTMLCell($this->pdf->getWidthPercent($this->pdf->template['cover-text-max-width']), 0, '', '', $html);
    }
}
