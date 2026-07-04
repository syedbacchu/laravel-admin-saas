<?php

namespace App\Http\Services\Pdf;

use PDF;

class SalarySheetPdfService
{
    public function generate($salarySheet, $companySettings)
    {
        $data = [
            'salarySheet' => $salarySheet,
            'tenant' => $companySettings,
            'generatedDate' => now()->format('d M Y, h:i A'),
        ];

        $pdf = PDF::loadView('pdfs.salary-sheet', $data);
        $pdf->setPaper('A4', 'landscape');

        // Configure DomPDF options for UTF-8 and Unicode support
        $options = $pdf->getOptions();
        $options->set('chroot', public_path());
        $options->set('isFontSubsettingEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);

        return $pdf;
    }
}
