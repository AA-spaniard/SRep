<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:27 PM.
 */

namespace src\components;

use src\AtlasReport;

class TaxonTable
{
    public $pdf;
    public $headerList;
    public $fieldsList;
    public $sizeList;
    public $colorList;
    public $alignList;

    public $leftSpacerWidth = 1;

    public $bgColor = '#F4F4F4';

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($topic, $headerList, $fieldsList, $sizeList, $colorList, $alignList, $variantsText): void
    {
        $this->headerList = $headerList;
        $this->fieldsList = $fieldsList;
        $this->sizeList = $sizeList;
        $this->colorList = $colorList;
        $this->alignList = $alignList;

        $this->pdf->SetFont($this->pdf->font_regular);

        $table = '<style>
                    .snips{
                        font-family: '.$this->pdf->font_light.';
                    }
                    .snips-line{
                        height: 20px;
                        line-height:20px;
                        font-size: 11px;
                    }
                    .snips-header{
                        height: 20px;
                        line-height:13px;
                        font-size: 9px;
                    }
                    .snips-title{
                        font-size:'.PageSubtitle::FONT_SIZE.'px;
                        font-family: '.$this->pdf->font_regular.';
                    }
                    .snips-title-ln{
                        height: 20px;
                    }
                    </style>';

        $table .= '
            <table nobr="true" border="0" cellpadding="0" cellspacing="0">

            <tr>
                <td colspan="9" class="snips-title">'.$this->pdf->getText($variantsText).'</td>
            </tr>
            <tr>
                <td colspan="9" class="snips-title-ln"></td>
            </tr>
            <tr class="snips-header">';

        $table = $this->formTableHeaderCols($table);
        $table .= '</tr>';

        foreach ($topic['taxons'] as $index => $taxon) {
            $table .= '<tr nobr="true" class="snips-line">';
            $table = $this->formTableCols($table, $taxon, $index);
            $table .= '</tr>';
        }

        $table .= '</table>';

        $this->pdf->writeHTML($table, true, false, true, false, '');
    }

    public function formTableHeaderCols($table)
    {
        $table .= '<td
            width="'.$this->leftSpacerWidth.'%"
            class="left-spacer"
            style="border-bottom:1px solid #f8f8f8;"></td>';
        foreach ($this->headerList as $index => $header) {
            $table .= '<td
            width="'.$this->getCellWidth($this->sizeList[$index]).'"
            align="'.$this->alignList[$index].'"
            style="border-bottom:1px solid #f8f8f8;">'.$header.'</td>';
        }

        return $table;
    }

    public function formTableCols($table, $item, $rowIndex)
    {
        $bgColor = 0 === $rowIndex % 2 ? $this->bgColor : '';
        $table .= '<td bgcolor="'.$bgColor.'" width="'.$this->leftSpacerWidth.'%" class="left-spacer"></td>';

        foreach ($this->fieldsList as $index => $field) {
            $color = $this->colorList[$index] ? $item[$this->colorList[$index]] : 'black';
            $value = null != $field ? $item[$field] : '';

            $table .= '<td bgcolor="'.$bgColor.'" width="'.$this->getCellWidth($this->sizeList[$index]).'"
                            align="'.$this->alignList[$index].'"
                            style="color: '.$color.'">'.$value.'</td>';
        }

        return $table;
    }

    public function getCellWidth($cellAbsPercent)
    {
        return ((100 - $this->leftSpacerWidth) / 100 * $cellAbsPercent).'%';
    }
}
