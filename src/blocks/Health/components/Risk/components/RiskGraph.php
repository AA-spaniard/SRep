<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:24 PM.
 */

namespace src\blocks\Health\components\Risk\components;

use src\AtlasReport;

class RiskGraph
{
    public $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;
    }

    public function set($risk): void
    {
        $gray = $this->pdf->colors['gray'];
        $grayDark = $this->pdf->colors['gray-dark'];
        $this->pdf->SetFont($this->pdf->font_regular);

        $valuePadding = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        $table = '<style>
                    .risk-graph{
                        font-family: '.$this->pdf->font_light.';
                    }
                    td{
                        max-width: 10%;
                        height: 40pt;
                        line-height:40px;
                    }
                    .risk-graph-avg-title{
                        font-size:16px;
                    }
                    .risk-graph-avg-value{
                        font-size:12px;
                        color: '.$grayDark.';
                        font-family: '.$this->pdf->font_regular.';
                    }
                    .risk-graph-you-title{
                        font-size:16px;
                        font-family: '.$this->pdf->font_medium.';
                    }
                    .risk-graph-you-value{
                        font-size:12px;
                        font-family: '.$this->pdf->font_regular.';
                    }
                    </style>
                    <table class="risk-graph" nobr="true" border="0" cellpadding="0" cellspacing="0">
                        <tr nobr="true" valign="middle">
                            <td width="25%" valign="middle" align="left" class="risk-graph-avg-title">&nbsp;'.$this->pdf->getText('riskAvg').'</td>
                            <td width="'.$risk['graphP0'].'%" valign="middle" align="left" bgcolor="'.$gray.'" style=""></td>
                            <td width="30%" valign="middle" align="left" class="risk-graph-avg-value">'.$valuePadding.$risk['tableP0'].'</td>
                        </tr>
                        <tr nobr="true" valign="middle">
                            <td width="25%" valign="middle" align="left" class="risk-graph-you-title">&nbsp;'.$this->pdf->getText('riskYou').'</td>
                            <td width="25%" valign="middle" align="left" bgcolor="'.$this->pdf->colors[$risk['graphColor']].'" style=""></td>
                            <td width="25%" valign="middle" align="left" class="risk-graph-you-value" style="color: '.$risk['graphColor'].';">'.$valuePadding.$risk['tableYou'].'</td>
                        </tr>
                    </table>';

        $this->pdf->writeHTML($table, true, false, false, false, '');
    }
}
