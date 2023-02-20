<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:29 PM.
 */

namespace src\blocks\Glossary;

use src\AtlasReport;

class GlossaryBlock
{
    public $pdf;
    public $cover;
    public $template;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('glossary')) {
            $this->source = $this->getSource();

            $this->setTemplate();
            $this->build();
        }
        $this->pdf->addon->set('glossary');
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'title-color' => '#00BDF0',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'title-color' => $this->pdf->template['template-color'],
            ];
        }
    }

    public function build(): void
    {
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('likbez');
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('likbez', false);

        $glossaryData = [
            [
                'img' => 'likbez-man',
                'title' => $this->pdf->getText('body'),
                'subtitle' => $this->pdf->getText('bodyShort'),
                'description' => $this->pdf->getText('bodyFull'),
            ],
            [
                'img' => 'likbez-organ',
                'title' => $this->pdf->getText('organ'),
                'subtitle' => $this->pdf->getText('organShort'),
                'description' => $this->pdf->getText('organFull'),
            ],
            [
                'img' => 'likbez-tisue',
                'title' => $this->pdf->getText('tissue'),
                'subtitle' => $this->pdf->getText('tissueShort'),
                'description' => $this->pdf->getText('tissueFull'),
            ],
            [
                'img' => 'likbez-cell',
                'title' => $this->pdf->getText('cell'),
                'subtitle' => $this->pdf->getText('cellShort'),
                'description' => $this->pdf->getText('cellFull'),
            ],
            [
                'img' => 'likbez-core',
                'title' => $this->pdf->getText('core'),
                'subtitle' => $this->pdf->getText('coreShort'),
                'description' => $this->pdf->getText('coreFull'),
            ],
            [
                'img' => 'likbez-chrome',
                'title' => $this->pdf->getText('chromo'),
                'subtitle' => $this->pdf->getText('chromoShort'),
                'description' => $this->pdf->getText('chromoFull'),
            ],
            [
                'img' => 'likbez-dna',
                'title' => $this->pdf->getText('dna'),
                'subtitle' => $this->pdf->getText('dnaShort'),
                'description' => $this->pdf->getText('dnaFull'),
            ],
            [
                'img' => 'likbez-gen',
                'title' => $this->pdf->getText('gen'),
                'subtitle' => $this->pdf->getText('genShort'),
                'description' => $this->pdf->getText('genFull'),
            ],
            [
                'img' => 'likbez-poli',
                'title' => $this->pdf->getText('poly'),
                'subtitle' => $this->pdf->getText('polyShort'),
                'description' => $this->pdf->getText('polyFull'),
            ],
        ];
        $this->addGlossaryTable($glossaryData);

        $this->pdf->AddPage();
        $this->pdf->pageSubtitle->set('riskAbout', false);
        $this->pdf->pageDescription->set('riskDescr', false);

        $this->pdf->pageSubtitle->set('riskImportance', false);
        $this->pdf->pageDescription->set('riskImportanceInfo', false);

        $this->pdf->AddPage();
        $this->pdf->pageSubtitle->set('inheritanceAbout', false);
        $this->pdf->pageDescription->set('inheritanceAboutInfo', false);

        $this->pdf->pageSubtitle->set('inheritanceHow', false);
        $this->pdf->pageDescription->set('inheritanceHowInfo', false);
    }

    public function getSource()
    {
        return $this->pdf->data['source'];
    }

    public function addGlossaryTable($glossary): void
    {
        $table = '<style>
                    .title{
                        font-size:16px;
                        line-height: 16px;
                        color: '.$this->template['title-color'].';
                        font-family: '.$this->pdf->font_medium.';
                    }
                    .subtitle{
                        font-size:12px;
                        line-height: 25px;
                        font-family: '.$this->pdf->font_medium.';
                    }
                    .description{
                        font-size:11px;
                        font-family: '.$this->pdf->font_light.';
                    }
                    .break{
                        font-size:20px;
                        font-family: '.$this->pdf->font_light.';
                    }
                    </style>';

        $table .= '<table border="0" cellpadding="0" cellspacing="0">';

        foreach ($glossary as $line) {
            $table .= '<tr nobr="true"><td width="100%">';

            $img_file = PATH_IMAGES.'glossary/'.$line['img'].'.jpg';
            $img = '<img src="'.$img_file.'" width="32px" />';

            $table .= '<table border="0" cellpadding="0" cellspacing="0">';
            if ($this->pdf->template['show-icons']) {
                $table .= '<tr>
                            <td rowspan="2" width="10%">'.$img.'</td>
                            <td width="90%" class="title">'.$line['title'].'</td>
                        </tr>';
            } else {
                $table .= '<tr>
                            <td width="100%" class="title">'.$line['title'].'</td>
                        </tr>';
            }
            $table .= '<tr>
                            <td width="90%" class="subtitle">'.$line['subtitle'].'</td>
                        </tr>
                        <tr>
                            <td colspan="2" width="100%" class="description">'.$line['description'].'</td>
                        </tr>
                        <tr>
                            <td colspan="2" width="100%" class="break"></td>
                        </tr>';
            $table .= '</table>';

            $table .= '</td></tr>';
        }
        $table .= '</table>';

        $this->pdf->writeHTML($table, true, false, true, false, '');
    }
}
