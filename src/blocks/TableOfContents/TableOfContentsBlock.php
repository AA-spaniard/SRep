<?php

declare(strict_types=1);

namespace src\blocks\TableOfContents;

use src\AtlasReport;

class TableOfContentsBlock
{
    private AtlasReport $pdf;
    private array $template;

    private int $tocPagesLength = 0;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        $this->setTemplate();

        $this->pdf->TOCPageNumber = $this->pdf->PageNo() + 1;
    }

    public function build(): void
    {
        // This flag disables page header in TOC itself
        $this->pdf->TOCOnCurrentPage = false;

        $tocPageFrom = $this->pdf->PageNo() + 1;
        // Render without knowing correct TOC length in pages
        // This way we get inaccurate page links
        $this->render();
        $tocPageTo = $this->pdf->PageNo();
        // Now we know correct TOC length in pages
        $this->tocPagesLength = $tocPageTo - $tocPageFrom + 1;
        // Delete what was rendered to start again
        for ($index = 0; $index < $this->tocPagesLength; ++$index) {
            $this->pdf->deletePage($tocPageFrom);
        }
        // Now render with correct page links because we know correct TOC length in pages
        $this->render();

        // Move TOC section from end of the document to its correct position.
        for ($index = 0; $index < $this->tocPagesLength; ++$index) {
            $this->pdf->movePage($tocPageFrom + $index, $this->pdf->TOCPageNumber + $index);
        }

        $this->pdf->TOCOnCurrentPage = true;
    }

    private function render(): void
    {
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('content', false);
        $this->pdf->SetFont($this->pdf->font_light, '', 0);

        $bookmarksManager = $this->pdf->serviceLocator->getBookmarks();
        $bookmarksList = $bookmarksManager->getBookmarksList();
        foreach ($bookmarksList as $bookmark) {
            if (0 === $bookmark['level']) {
                $this->pdf->ln(10);
            }
            $this->pdf->writeHTML($this->getLineTemplate($bookmark), false, false, true, false, '');
        }

        if ($this->template['title-bg']) {// For EMC partner
            $this->renderTitleBg();
        }
    }

    private function renderTitleBg(): void
    {
        $this->pdf->SetFillColor(215, 215, 215);

        //left
        $cellWidth = ($this->pdf->getMargins()['left'] / 5) * 3;
        $cellPosX = 0;
        $cellPosY = $this->pdf->getMargins()['top'] + 5;
        $this->pdf->MultiCell(0.1, 17, '', 0, 'C', false, 0, $cellPosX, $cellPosY);
        $this->pdf->Cell($cellWidth, 15, '', 0, false, 'C', 1);

        //right
        $cellWidth = 300;
        $cellPosX = ($this->pdf->getPageWidth() / 100 * 62);
        $cellPosY = $this->pdf->getMargins()['top'] + 5;
        $this->pdf->MultiCell(0.1, 17, '', 0, 'C', false, 0, $cellPosX, $cellPosY);
        $this->pdf->Cell($cellWidth, 15, '', 0, false, 'C', 1);
    }

    private function setTemplate(): void
    {
        $template_name = $this->pdf->template_name;

        $this->template = [
            'title-bg' => false,
            'pattern' => true,
            'bullet-color' => '#cccccc',
            'line-color' => 'black',
        ];

        if ('emc' === $template_name) {
            $this->template = [
                'title-bg' => true,
                'pattern' => false,
                'bullet-color' => '#0A5594',
                'line-color' => '#0A5594',
            ];
        }
    }

    private function getLineTemplate(array $bookmark): string
    {
        $bookmarkTemplates = [];

        $sectionPage = $bookmark['page'] + $this->tocPagesLength + 1;
        $sectionUrl = '#'.($bookmark['page'] + 1);

        $sectionTitle = $bookmark['title'];

        $img_index = ($bookmark['block'] - 1) % 8 + 1;
        $img_file = PATH_IMAGES.'patterns/pattern_'.$img_index.'.png';
        $patternImg = '<img src="'.$img_file.'" width="10px" height="17px" />';

        $styles = '
        <style>
        .cell {
            font-family:'.$this->pdf->font_light.';
            font-size:13pt;
            color: '.$this->template['line-color'].';
            line-height:17px;
        }
        .cell-small {
            font-family:'.$this->pdf->font_light.';
            font-size:11pt;
            color: '.$this->template['line-color'].';
            line-height:17px;
        }
        .bullet {
            font-family: '.$this->pdf->font_secondary.';
            color: '.$this->template['bullet-color'].';
        }
        .cell-link {
            color: '.$this->template['line-color'].';
            text-decoration: none;
        }
        </style>
        ';

        $bookmarkTemplates[0] = $styles.'<table border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" class="cell"><a href="'.$sectionUrl.'" class="cell-link">'.$sectionTitle.'</a></td>
                <td width="15%" class="cell-small" align="right"><a href="'.$sectionUrl.'" class="cell-link">'.$sectionPage.'</a></td>';
        if ($this->template['pattern']) {
            $bookmarkTemplates[0] .= '<td width="22px" align="right">'.$patternImg.'</td>';
        }
        $bookmarkTemplates[0] .= '</tr></table>';

        $bookmarkTemplates[1] = $styles.'<table border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td width="50%" class="cell-small"><span class="bullet">&bull;</span>&nbsp;&nbsp;&nbsp;<a href="'.$sectionUrl.'" class="cell-link">'.$sectionTitle.'</a></td>
                <td width="15%" class="cell-small" align="right"><a href="'.$sectionUrl.'" class="cell-link">'.$sectionPage.'</a></td>';
        if ($this->template['pattern']) {
            $bookmarkTemplates[1] .= '<td width="22px" align="right">'.$patternImg.'</td>';
        }
        $bookmarkTemplates[1] .= '</tr></table>';

        return $bookmarkTemplates[$bookmark['level']];
    }
}
