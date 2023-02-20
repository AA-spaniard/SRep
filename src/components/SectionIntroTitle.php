<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:24 PM.
 */

namespace src\components;

use src\AtlasReport;

class SectionIntroTitle
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($introTitle): void
    {
        $this->pdf->SetFont($this->pdf->font_medium);
        $html = '<h6 style="'.$this->pdf->h6_section_intro_title.'color:'.$this->pdf->template['template-color'].';">'.$this->pdf->getText($introTitle).'</h6>';
        $this->pdf->writeHTML($html, 0, false, true, false, '');
        $this->pdf->ln(110);
    }
}
