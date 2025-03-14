<?php

declare(strict_types=1);

namespace App\Service;

use Qipsius\TCPDFBundle\Controller\TCPDFController;

class PdfFactuurService
{
    public const FONT = 'helvetica';

    public const FONT_BOLD = 'helveticab';

    /**
     * @var \TCPDF
     */
    protected $pdf;

    protected $fontname;

    protected $fontnameBold;

    /**
     * @var FactuurService
     */
    protected $factuurService;

    /**
     * @var TCPDFController
     */
    protected $tcpdfController;

    protected $projectDir;

    public function __construct(FactuurService $factuurService, TCPDFController $tcpdfController, string $projectDir)
    {
        $this->factuurService = $factuurService;
        $this->tcpdfController = $tcpdfController;
        $this->projectDir = $projectDir;

        $this->fontname = self::FONT;
        $this->fontnameBold = self::FONT_BOLD;
    }

    public function generate($koopman, $dagvergunningen)
    {
        $this->pdf = $this->tcpdfController->create();

        // set document information
        $this->pdf->SetCreator('Gemeente Amsterdam');
        $this->pdf->SetAuthor('Gemeente Amsterdam');
        $this->pdf->SetTitle('Factuur');
        $this->pdf->SetSubject('Factuur');
        $this->pdf->SetKeywords('Factuur');

        $this->pdf->SetPrintHeader(false);
        $this->pdf->SetPrintFooter(false);
        $this->pdf->SetAutoPageBreak(false, 0);

        foreach ($dagvergunningen as $vergunning) {
            if (null !== $vergunning->getFactuur()) {
                $this->addVergunning($koopman, $vergunning);
            }
        }

        return $this->pdf;
    }

    protected function addVergunning($koopman, $vergunning)
    {
        $this->pdf->AddPage();
        $this->pdf->Image(
            $this->projectDir.'/public/images/GASD_1.png',
            10,
            10,
            50
        );

        $this->pdf->Ln(40);

        $this->pdf->SetFont($this->fontname, 'b', 8);
        $this->pdf->Cell(16, 6, '', 0, 0);
        $this->pdf->Cell(164, 6, '', 0, 0);

        $this->pdf->Ln(8);

        $this->pdf->SetFont($this->fontnameBold, 'b', 11);
        $this->pdf->Cell(16, 6, '', 0, 0);
        $this->pdf->Cell(180, 6, 'BTW-overzicht', 0, 0);

        $this->pdf->Ln(10);

        $this->pdf->SetFont($this->fontname, 'b', 11);
        $this->pdf->Cell(16, 6, '', 0, 0);
        $this->pdf->Cell(164, 6, $koopman->getVoorletters().' '.$koopman->getTussenvoegsels().' '.$koopman->getAchternaam(), 0, 1);

        $this->pdf->SetY(10);

        $this->pdf->SetFont($this->fontname, 'b', 10);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'Bezoekadres', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'Amstel 1', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, '1011 PN Amsterdam', 0, 0);

        $this->pdf->Ln(10);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'Postbus 202', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, '1000 AE Amsterdam', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'Telefoon 14 020', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'Bereikbaar van 8.00-18.00', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'Email', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'marktbureau@amsterdam.nl', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'https://amsterdam.nl/markt', 0, 0);

        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'BTW nr NL002564440B01', 0, 0);
        $this->pdf->Ln(5);
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(50, 6, 'KvK nr 34366966 0000', 0, 0);

        $this->pdf->Ln(10);

        $this->pdf->Cell(16, 6, '', 0, 0);
        $this->pdf->SetFont($this->fontnameBold, 'b', 9);
        $this->pdf->Cell(26, 6, 'Factuurnummer', 0, 0);
        $this->pdf->SetFont($this->fontname, 'b', 9);
        $this->pdf->Cell(26, 6, 'mm'.$vergunning->getFactuur()->getId(), 0, 0);
        $this->pdf->SetFont($this->fontnameBold, 'b', 9);
        $this->pdf->Cell(26, 6, 'Factuurdatum', 0, 0);
        $this->pdf->SetFont($this->fontname, 'b', 9);
        $dag = implode('-', array_reverse(explode('-', $vergunning->getDag()->format('d-m-Y'))));
        $this->pdf->Cell(26, 6, $dag, 0, 1);

        $this->pdf->Cell(16, 6, '', 0, 0);
        $this->pdf->SetFont($this->fontnameBold, 'b', 9);
        $this->pdf->Cell(144, 6, 'Omschrijving', 'B', 0);
        $this->pdf->Cell(20, 6, 'Bedrag €', 'B', 1, 'R');

        $this->pdf->SetFont($this->fontname, 'b', 9);

        $this->pdf->Cell(16, 6, '', 0, 0);
        $this->pdf->Cell(164, 6, 'Markt: '.$vergunning->getMarkt()->getNaam(), '', 1);

        $btwTotaal = [];
        $btwOver = [];

        foreach ($vergunning->getFactuur()->getProducten() as $product) {
            $this->pdf->Cell(16, 6, '', 0, 0);
            $btwText = $product->getBtwHoog() > 0 ? '. excl. '.$product->getBtwHoog().'% BTW' : '';
            $this->pdf->Cell(144, 6, $product->getAantal().' maal '.$product->getNaam().$btwText, '', 0);
            $this->pdf->Cell(20, 6, number_format($product->getAantal() * $product->getBedrag(), 2), 0, 0, 'R');
            if (!isset($btwTotaal[$product->getBtwHoog()])) {
                $btwTotaal[$product->getBtwHoog()] = 0;
                $btwOver[$product->getBtwHoog()] = 0;
            }

            $btwTotaal[$product->getBtwHoog()] += number_format($product->getAantal() * $product->getBedrag() * ($product->getBtwHoog() / 100), 2);
            $btwOver[$product->getBtwHoog()] += number_format($product->getAantal() * $product->getBedrag(), 2);

            $this->pdf->Ln(5);
        }

        $this->pdf->Ln(5);

        $this->pdf->Cell(98, 6, '', 0, 0);
        $this->pdf->Cell(41, 6, 'Subtotaal', 'T', 0);
        $this->pdf->Cell(41, 6, $this->factuurService->getTotaal($vergunning->getFactuur(), false), 'T', 0, 'R');
        $this->pdf->Ln(5);
        foreach ($btwTotaal as $key => $value) {
            $this->pdf->Cell(98, 6, '', 0, 0);
            $this->pdf->Cell(41, 6, 'BTW '.$key.'% over '.number_format(floatval($btwOver[$key]), 2), 0, 0);
            $this->pdf->Cell(41, 6, number_format(floatval($value), 2), 0, 0, 'R');
            $this->pdf->Ln(5);
        }

        $this->pdf->SetFont($this->fontnameBold, 'b', 9);
        $this->pdf->Cell(98, 6, '', 0, 0);
        $this->pdf->Cell(41, 6, 'Totaal', 'T', 0);
        $this->pdf->Cell(41, 6, $this->factuurService->getTotaal($vergunning->getFactuur()), 'T', 0, 'R');
    }
}
