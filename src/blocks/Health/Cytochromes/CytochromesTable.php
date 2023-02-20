<?php

declare(strict_types=1);

namespace src\blocks\Health\Cytochromes;

use src\AtlasReport;

class CytochromesTable
{
    private const PADDING_LEFT = '&nbsp;&nbsp;';
    private const PADDING_RIGHT = '&nbsp;&nbsp;&nbsp;&nbsp;';

    public $pdf;
    public $separatorWidth = 5;
    public $headerList;
    public $fieldsList;
    public $sizeList;
    public $colorList;
    public $alignList;

    public $bgColor = '#F4F4F4';

    public static function renderWithDefaults($pdf, $cytochromeData): void
    {
        $headerList = [
            $pdf->getText('poly'),
            $pdf->getText('probability'),
            $pdf->getText('metabolizer'),
            $pdf->getText('probability'),
        ];
        $fieldsList = ['title', 'probabilityText', 'title', 'probabilityText'];
        $sizeList = [70, 30];
        $alignList = ['left', 'left'];
        $colorList = [null, null];

        $snipTable = new self($pdf);
        $snipTable->set($cytochromeData, $headerList, $fieldsList, $sizeList, $colorList, $alignList);
    }

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($cyto, $headerList, $fieldsList, $sizeList, $colorList, $alignList): void
    {
        $this->headerList = $headerList;
        $this->fieldsList = $fieldsList;
        $this->sizeList = $sizeList;
        $this->colorList = $colorList;
        $this->alignList = $alignList;

        $this->pdf->SetFont($this->pdf->font_regular);

        $dataListHigh = $cyto['diplotypes'];
        $dataListLow = $cyto['metabolizers'];
        $highCount = \count($dataListHigh);
        $lowCount = \count($dataListLow);
        $maxElements = \max($lowCount, $highCount);

        $table = '<style>
                    .cyto{
                        font-family: '.$this->pdf->font_light.';
                    }
                    .cyto-line{
                        font-size: 9px;
                    }
                    .cyto-header{
                        height: 20px;
                        line-height:20px;
                        font-size: 9px;
                    }
                    .cyto-title-ln{
                        height: 20px;
                    }
                    .cyto-subtitle{
                        font-size:11px;
                        font-family: '.$this->pdf->font_medium.';
                        height: 25px;
                    }
                    </style>';

        $table .= '
            <table nobr="true" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td colspan="9" class="cyto-title-ln"></td>
            </tr>
            <tr>';

        $subtitleWidth = (100 - $this->separatorWidth) / 2;
        if ($highCount) {
            $leftSubtitle = $this->pdf->getText('diplotype');
            $table .= '<td width="'.$subtitleWidth.'%" colspan="2" class="cyto-subtitle">'.self::PADDING_LEFT.$leftSubtitle.self::PADDING_RIGHT.'</td>';
            $table .= '<td width="'.$this->separatorWidth.'%"></td>';
        }
        if ($lowCount) {
            $rightSubtitle = $this->pdf->getText('phenotype');
            $table .= '<td width="'.$subtitleWidth.'%" colspan="2" class="cyto-subtitle">'.self::PADDING_LEFT.$rightSubtitle.self::PADDING_RIGHT.'</td>';
        }

        $table .= '</tr>
            <tr class="cyto-header">';

        if ($highCount) {
            $table = $this->formTableHeaderCols($table, [$headerList[0], $headerList[1]]);
            $table .= '<td width="'.$this->separatorWidth.'%"></td>';
        }
        if ($lowCount) {
            $table = $this->formTableHeaderCols($table, [$headerList[2], $headerList[3]]);
        }
        $table .= '</tr>
        ';

        for ($i = 0; $i < $maxElements; ++$i) {
            $table .= '<tr class="cyto-line">';

            if ($highCount) {
                $table = $this->formTableCols($table, $dataListHigh, $i, [$fieldsList[0], $fieldsList[1]]);
                $table .= '<td width="'.$this->separatorWidth.'%"></td>';
            }
            if ($lowCount) {
                $table = $this->formTableCols($table, $dataListLow, $i, [$fieldsList[2], $fieldsList[3]]);
            }

            $table .= '</tr>';
        }

        $table .= '</table>';

        $this->pdf->writeHTML($table, true, false, true, false, '');
    }

    public function formTableHeaderCols($table, $headers)
    {
        foreach ($headers as $index => $header) {
            $paddingStart = 0 === $index ? self::PADDING_LEFT : '';
            $paddingEnd = $index === \count($this->headerList) - 1 ? self::PADDING_RIGHT : '';

            $table .= '<td
            width="'.$this->getCellWidth($this->sizeList[$index]).'"
            align="'.$this->alignList[$index].'"
            style="border-bottom:1px solid #f8f8f8;">'.$paddingStart.$header.$paddingEnd.'</td>';
        }

        return $table;
    }

    public function formTableCols($table, $values, $rowIndex, $fields)
    {
        foreach ($fields as $index => $field) {
            if (isset($values[$rowIndex])) {
                $paddingStart = 0 === $index ? self::PADDING_LEFT : '';
                $paddingEnd = $index === \count($this->fieldsList) - 1 ? self::PADDING_RIGHT : '';
                $item = $values[$rowIndex];
                $color = $this->colorList[$index] ? $item[$this->colorList[$index]] : 'black';
                $bgColor = 0 === $rowIndex % 2 ? $this->bgColor : '';
                $value = null != $field ? $item[$field] : '';

                $table .= '<td bgcolor="'.$bgColor.'" width="'.$this->getCellWidth($this->sizeList[$index]).'"
                                align="'.$this->alignList[$index].'"
                                style="color: '.$color.'">'.$paddingStart.$value.$paddingEnd.'</td>';
            } else {
                $table .= '<td></td>';
            }
        }

        return $table;
    }

    public function getCellWidth($cellAbsPercent)
    {
        return ((100 - $this->separatorWidth) / 2) / 100 * $cellAbsPercent.'%';
    }
}
