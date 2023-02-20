<?php

declare(strict_types=1);

namespace src\blocks\SourceInfo;

use src\AtlasReport;

class SourceInfoBlock
{
    private array $sourceData;

    private AtlasReport $pdf;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('sourceInfo')) {
            $this->render();
            $this->pdf->addon->set('sourceInfo', false);
        }
    }

    private function render(): void
    {
        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('source', false);
        $this->pdf->ln(100);

        $tableRows = $this->getTableRows();
        $this->renderSourceTable($tableRows);
    }

    private function getTableRows(): array
    {
        $this->sourceData = $this->pdf->data['source'];

        $isBiome = AtlasReport::TYPE_BIOME === $this->pdf->type;
        $isShowEnglishDoubles = 'en' !== $this->pdf->locale && !$isBiome;

        $fullName = $this->renderFullName();
        $receptionDate = $this->renderReceptionDate();
        $generationDate = $this->renderGenerationDate();
        $methodString = $this->renderMethod();
        $biomaterialType = $isBiome ? 'feces' : $this->sourceData['biomaterial_type'];
        $barcode = $this->sourceData['barcode'];

        return [
            [
                'title' => $this->pdf->getText('user_name'),
                'value' => $fullName,
                'englishDoubledTitle' => null,
                'englishDoubledValue' => null,
            ],
            [
                'title' => $this->pdf->getText('bionumber'),
                'value' => $barcode,
                'englishDoubledTitle' => $isShowEnglishDoubles
                    ? $this->pdf->getText('bionumber_english_doubled', 'en')
                    : null,
                'englishDoubledValue' => null,
            ],
            [
                'title' => $this->pdf->getText('biosource'),
                'value' => $this->pdf->getText($biomaterialType),
                'englishDoubledTitle' => $isShowEnglishDoubles
                    ? $this->pdf->getText('biosource_english_doubled', 'en')
                    : null,
                'englishDoubledValue' => $isShowEnglishDoubles
                    ? $this->pdf->getText($biomaterialType, 'en')
                    : null,
            ],
            [
                'title' => $this->pdf->getText('dateReception'),
                'value' => $receptionDate,
                'englishDoubledTitle' => $isShowEnglishDoubles
                    ? $this->pdf->getText('dateReception', 'en')
                    : null,
                'englishDoubledValue' => null,
            ],
            [
                'title' => $this->pdf->getText('method'),
                'value' => $methodString,
                'englishDoubledTitle' => $isShowEnglishDoubles
                    ? $this->pdf->getText('method_english_doubled', 'en')
                    : null,
                'englishDoubledValue' => $isShowEnglishDoubles ? $this->renderMethodEn() : null,
            ],
            [
                'title' => $this->pdf->getText('dateReport'),
                'value' => $generationDate,
                'englishDoubledTitle' => $isShowEnglishDoubles
                    ? $this->pdf->getText('dateReport_english_doubled', 'en')
                    : null,
                'englishDoubledValue' => '',
            ],
        ];
    }

    private function renderFullName(): string
    {
        $profile = $this->pdf->data['profile'];
        $format = AtlasReport::OFFICE_REGION_JP === $this->pdf->officeRegion
            ? ['lastname', 'firstname']
            : ['firstname', 'middlename', 'lastname'];

        $parts = [];
        foreach ($format as $key) {
            if (isset($profile[$key]) && $profile[$key]) {
                $parts[] = $profile[$key];
            }
        }

        return \implode(' ', $parts);
    }

    private function renderReceptionDate(): string
    {
        return isset($this->sourceData['picked'])
            ? $this->pdf->renderLocalizedDate(new \DateTimeImmutable($this->sourceData['picked']))
            : '-';
    }

    private function renderGenerationDate(): string
    {
        return $this->pdf->renderLocalizedDate(new \DateTimeImmutable());
    }

    private function renderMethod(): string
    {
        switch ($this->pdf->type) {
            case AtlasReport::TYPE_BIOME:
                return $this->pdf->getText('16s');
            case AtlasReport::TYPE_DNA:
                return $this->pdf->getText('microchip');
            case AtlasReport::TYPE_DNA_WGS:
                return $this->pdf->getText('next_gen_wgs');
            default:
                throw new \Exception("Unknown report type {$this->pdf->type}");
        }
    }

    private function renderMethodEn(): ?string
    {
        switch ($this->pdf->type) {
            case AtlasReport::TYPE_BIOME:
                throw new \Exception('English doubling not supported for Biome type');
            case AtlasReport::TYPE_DNA:
                return $this->pdf->getText('microchip_english_doubled', 'en');
            case AtlasReport::TYPE_DNA_WGS:
                return $this->pdf->getText('next_gen_wgs_english_doubled', 'en');
            default:
                throw new \Exception("Unknown report type {$this->pdf->type}");
        }
    }

    private function renderSourceTable(array $rows): void
    {
        $html = '<style>
                    .primary {
                        font-size:16px;
                        line-height: 20px;
                        font-family: '.$this->pdf->font_regular.';
                    }
                    .secondary {
                        font-size:10px;
                        line-height: 1px;
                        font-family: '.$this->pdf->font_regular.';
                    }
                    .sep {
                        height: 0;
                        line-height: 0;
                        border-bottom: 1px solid '.$this->pdf->colors['gray'].';
                    }
                    </style>';

        $html .= '<table border="0" cellpadding="5" cellspacing="5">';

        foreach ($rows as $row) {
            $html .= '<tr>
                <td width="60%" class="primary">'.$row['title'].'</td>
                <td width="40%" class="primary">'.$row['value'].'</td>
            </tr>';
            if (null !== $row['englishDoubledTitle'] || null !== $row['englishDoubledValue']) {
                $html .= '<tr>
                <td width="60%" class="secondary">'.$row['englishDoubledTitle'].'</td>
                <td width="40%" class="secondary">'.$row['englishDoubledValue'].'</td>
            </tr>';
            }
            $html .= '<tr><td colspan="2" class="sep"></td></tr>';
        }
        $html .= '</table>';

        $this->pdf->writeHTML($html, true, false, true, false, '');
    }
}
