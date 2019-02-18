<?php

namespace App\Model\Pdf;

use Joseki\Application\Responses\PdfResponse;
use Nette\Application\UI\ITemplate;

final class PdfFactory
{

    const PAGE_FORMAT_A4_L = 'A4-L';


    /** @var string */
    private $tempDirPath;


    /**
     * @param string $tempDirPath
     */
    public function __construct($tempDirPath)
    {
        $this->tempDirPath = $tempDirPath;
    }


    /**
     * @param ITemplate $template
     * @param string|null $pageFormat
     * @return PdfResponse
     */
    public function create(ITemplate $template, $pageFormat = NULL)
    {
        $pdf = new PdfResponse($template);

        if ($pageFormat !== NULL) {
            $pdf->pageFormat = $pageFormat;
        }

        return $pdf;
    }


    /**
     * @param PdfResponse $pdf
     * @param string $name
     * @return string  - Path to saved file
     */
    public function savePdf(PdfResponse $pdf, $name)
    {
        $pdf->setSaveMode(PdfResponse::INLINE);

        return $pdf->save($this->tempDirPath, $name);
    }

}
