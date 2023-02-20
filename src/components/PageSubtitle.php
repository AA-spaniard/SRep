<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:26 PM.
 */

namespace src\components;

use src\AtlasReport;

class PageSubtitle
{
    public const FONT_SIZE = 20;

    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($pageSubtitle, $isCustom = true, $color = '#000000'): void
    {
        // чтобы не было заголовков в конце страницы
        if ($this->pdf->GetY() > 650) {
            $this->pdf->AddPage();
        }
        if (!$isCustom) {
            $pageSubtitle = $this->pdf->getText($pageSubtitle);
        }
        $textColor = $this->pdf->utils->hex2rgb($color);
        $this->pdf->SetTextColor($textColor[0], $textColor[1], $textColor[2]);
        $this->pdf->SetFont($this->pdf->font_regular, '', self::FONT_SIZE);
        $this->pdf->MultiCell(0, 0, $pageSubtitle, 0, 'L', 0, 1);
        $this->pdf->ln(20);

        //reset font style
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont($this->pdf->font_light, '', 13);
    }
}
