<?php

declare(strict_types=1);

namespace src\blocks\Health\components\Risk\components;

use src\AtlasReport;
use src\components\PageSubtitle;

class RiskSnipTable
{
    public $pdf;
    public $separatorWidth = 5;
    public $sideSpacerWidth = 1;
    public $headerList;
    public $fieldsList;
    public $sizeList;
    public $colorList;
    public $alignList;

    public $bgColor = '#F4F4F4';

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($risk, $headerList, $fieldsList, $sizeList, $colorList, $alignList): void
    {
        $this->headerList = $headerList;
        $this->fieldsList = $fieldsList;
        $this->sizeList = $sizeList;
        $this->colorList = $colorList;
        $this->alignList = $alignList;

        $this->pdf->SetFont($this->pdf->font_regular);

        $dataListHigh = $risk['snipsHigh'];
        $dataListLow = $risk['snipsLow'];
        $hasLdUsages = $risk['hasLdUsages'] ?? false;
        $highCount = \count($dataListHigh);
        $lowCount = \count($dataListLow);
        $maxElements = \max($lowCount, $highCount);

        $table = '<style>
                    .risk-snips{
                        font-family: '.$this->pdf->font_light.';
                    }
                    .risk-snips-line{
                        font-size: 9px;
                    }
                    .risk-snips-header{
                        height: 20px;
                        line-height:20px;
                        font-size: 9px;
                    }
                    .risk-snips-title{
                        font-size:'.PageSubtitle::FONT_SIZE.'px;
                        font-family: '.$this->pdf->font_regular.';
                    }
                    .risk-snips-title-ln{
                        height: 20px;
                    }
                    .risk-snips-subtitle{
                        font-size:11px;
                        font-family: '.$this->pdf->font_medium.';
                        height: 25px;
                    }
                    .ld-usage-note {
                        font-size: 9px;
                        font-family: '.$this->pdf->font_regular.';
                        color: #666666;
                    }
                    </style>';

        $table .= '
            <table nobr="true" border="0" cellpadding="0" cellspacing="0">

            <tr>
                <td colspan="13" class="risk-snips-title">'.$this->pdf->getText('riskGen').' '.$risk['title_gen'].'</td>
            </tr>
            <tr>
                <td colspan="13" class="risk-snips-title-ln"></td>
            </tr>
            <tr>';

        $subtitleWidth = (100 - $this->separatorWidth - $this->sideSpacerWidth * 4) / 2;
        if ($highCount) {
            $table .= '<td width="'.$this->sideSpacerWidth.'%"></td>';
            $table .= '<td width="'.$subtitleWidth.'%" colspan="4" class="risk-snips-subtitle">'.$this->pdf->getText('riskHigher').'</td>';
            $table .= '<td width="'.$this->separatorWidth.'%"></td>';

            $table .= '<td width="'.$this->sideSpacerWidth.'%"></td>';
        }
        if ($lowCount) {
            $table .= '<td width="'.$this->sideSpacerWidth.'%"></td>';
            $table .= '<td width="'.$subtitleWidth.'%" colspan="4" class="risk-snips-subtitle">'.$this->pdf->getText('riskLower').'</td>';
            $table .= '<td width="'.$this->sideSpacerWidth.'%"></td>';
        }

        $table .= '</tr>
            <tr class="risk-snips-header">';

        if ($highCount) {
            $table = $this->formTableHeaderCols($table);
            $table .= '<td width="'.$this->separatorWidth.'%"></td>';
        }
        if ($lowCount) {
            $table = $this->formTableHeaderCols($table);
        }
        $table .= '</tr>
        ';

        for ($i = 0; $i < $maxElements; ++$i) {
            $table .= '<tr class="risk-snips-line">';

            if ($highCount) {
                $table = $this->formTableCols($table, $dataListHigh, $i);
                $table .= '<td width="'.$this->separatorWidth.'%"></td>';
            }
            if ($lowCount) {
                $table = $this->formTableCols($table, $dataListLow, $i);
            }

            $table .= '</tr>';
        }

        if ($hasLdUsages) {
            $ldNote = $this->pdf->getText('is_imputed');
            $table .= '<tr><td colspan="4" class="ld-usage-note"><div><br>'.$ldNote.'</div></td></tr>';
        }

        $table .= '</table>';

        $this->pdf->writeHTML($table, true, false, true, false, '');
    }

    public function formTableHeaderCols($table)
    {
        $table .= '<td width="'.$this->sideSpacerWidth.'%"></td>';
        foreach ($this->headerList as $index => $header) {
            $table .= '<td
            width="'.$this->getCellWidth($this->sizeList[$index]).'"
            align="'.$this->alignList[$index].'">'.$header.'</td>';
        }
        $table .= '<td width="'.$this->sideSpacerWidth.'%"></td>';

        return $table;
    }

    public function formTableCols($table, $values, $rowIndex)
    {
        if (!isset($values[$rowIndex])) {
            $table .= '<td colspan="6"></td>';

            return $table;
        }

        $bgColor = 0 === $rowIndex % 2 ? $this->bgColor : '';
        $table .= '<td bgcolor="'.$bgColor.'" width="'.$this->sideSpacerWidth.'%"></td>';
        foreach ($this->fieldsList as $index => $field) {
            $item = $values[$rowIndex];
            $color = $this->colorList[$index] ? $item[$this->colorList[$index]] : 'black';
            $value = null != $field ? $item[$field] : '';

            $table .= '<td bgcolor="'.$bgColor.'" width="'.$this->getCellWidth($this->sizeList[$index]).'"
                            align="'.$this->alignList[$index].'"
                            style="color: '.$color.'">'.$value.'</td>';
        }
        $table .= '<td bgcolor="'.$bgColor.'" width="'.$this->sideSpacerWidth.'%"></td>';

        return $table;
    }

    public function getCellWidth($cellAbsPercent)
    {
        return ((100 - $this->separatorWidth - $this->sideSpacerWidth * 4) / 2) / 100 * $cellAbsPercent.'%';
    }
}
