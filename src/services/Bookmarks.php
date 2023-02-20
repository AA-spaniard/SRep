<?php

namespace src\services;

use src\AtlasReport;

class Bookmarks
{
    private array $bookmarksList = [];

    private int $currentBlock = 0;

    public function __construct(
        private AtlasReport $pdf
    ) {
    }

    public function addBookmark($sectionTitle, $sectionLevel = 0, $isCustom = false): void
    {
        if (0 === $sectionLevel) {
            ++$this->currentBlock;
        }

        $actualTitle = $isCustom ? $sectionTitle : $this->pdf->getText($sectionTitle);

        $this->bookmarksList[] = [
            'title' => $actualTitle,
            'page' => $this->pdf->PageNo(),
            'level' => $sectionLevel,
            'block' => $this->currentBlock,
        ];
    }

    public function getBookmarksList(): array
    {
        return $this->bookmarksList;
    }

    public function getLastBookmark(): ?array
    {
        $list = $this->bookmarksList;
        if (0 === \count($list)) {
            return null;
        }

        return $list[\count($list) - 1];
    }
}
