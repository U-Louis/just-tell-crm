<?php

namespace App\Http\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

final class PdfResponse
{
    /**
     * @param  array<string, string>  $fields
     */
    public static function make(string $filename, string $title, array $fields): Response
    {
        $pdf = Pdf::loadView('crm.pdf', [
            'title' => $title,
            'fields' => $fields,
        ]);

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
