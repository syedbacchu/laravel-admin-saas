<?php

namespace App\Exports;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalarySheetExport implements FromView, WithTitle, WithColumnFormatting
{
    protected $data;
    protected $companySettings;

    public function __construct($data, $companySettings)
    {
        $this->data = $data;
        $this->companySettings = $companySettings;
    }

    public function view(): View
    {
        return view('exports.salary-sheet', [
            'data' => $this->data,
            'tenant' => $this->companySettings,
        ]);
    }

    public function title(): string
    {
        return 'Salary Sheet - ' . $this->data->month;
    }

    public function columnFormats(): array
    {
        return [
            // Format columns to have 2 decimal places
            'C' => NumberFormat::FORMAT_NUMBER_00,
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
