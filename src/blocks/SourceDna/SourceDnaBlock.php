<?php
/**
 * Created by PhpStorm.
 * User: alexunder
 * Date: 11/10/17
 * Time: 12:29 PM.
 */

namespace src\blocks\SourceDna;

use src\AtlasReport;

class SourceDnaBlock
{
    public $pdf;
    public $cover;

    //////////
    // Data //
    //////////
    public $source;

    public function __construct(AtlasReport $pdf)
    {
        $this->pdf = $pdf;

        if ($this->pdf->isSectionNeeded('dnaLegacySnps')) {
            $this->source = $this->getData();

            $this->build();
        }
        $this->pdf->addon->set('dnaLegacySnps');
    }

    public function build(): void
    {
        //block cover page
        $this->pdf->serviceLocator->getBookmarks()->addBookmark('dnaLegacySnps');

        $this->pdf->AddPage();
        $this->pdf->pageTitle->set('dnaLegacySnps', false);

        $source = $this->source;

        $headerList = [
            $this->pdf->getText('gen'),
            $this->pdf->getText('variant'),
            $this->pdf->getText('mutation'),
            $this->pdf->getText('genotypeYou'),
        ];
        $fieldsList = ['gene', 'snp', 'mutation', 'genotype'];
        $sizeList = ['39%', '20%', '17%', '25%'];
        $alignList = ['left', 'center', 'center', 'center'];
        $colorList = [null, null, null, null];

        $this->pdf->table->set($headerList, $source, $fieldsList, $sizeList, $colorList, $alignList);
    }

    //////////
    // DATA //
    //////////

    public function getData()
    {
        $source = $this->pdf->data['dnaLegacySnps'];
        foreach ($source as &$line) {
            $line['gene'] = \implode(', ', $line['genes']);

            $line['score'] = 1;
            if (null === $line['sample_genotype']) {
                $line['score'] = 0;
            }
            if (null !== $line['sample_genotype']) {
                foreach ($line['sample_genotype'] as &$letter) {
                    foreach ($line['alternative_alleles'] as $disease) {
                        if ($disease === $letter) {
                            $letter = '<span style="color: red;">'.$letter.'</span>';
                            $line['score'] = 2;
                        }
                    }
                }
            }

            $line['genotype'] = null === $line['sample_genotype'] ? $this->pdf->getText('noData') : \implode('/', $line['sample_genotype']);
            $line['mutation'] = null === $line['reference_allele'] ? '-' : $line['reference_allele'].' -> '.\implode('/', $line['alternative_alleles']);
        }

        \usort($source, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $source;
    }
}
