<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Salary Sheet - {{ $salarySheet->month }}</title>
</head>
<body>
    <!-- Header Section -->
    <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        @if(isset($tenant['logo']) && !empty($tenant['logo']))
        <div style="text-align: center; margin-bottom: 10px;">
            <img src="{{ $tenant['logo'] }}" alt="{{ $tenant['site_title'] ?? 'Company' }}" style="max-height: 60px; max-width: 200px;">
        </div>
        @endif
        <div style="font-size: 24px; font-weight: bold; color: #1e40af;">{{ $tenant['site_title'] ?? 'Transport Company' }}</div>
        @if($tenant['tag_title'] ?? false)
        <div style="font-size: 12px; color: #6b7280;">{{ $tenant['tag_title'] ?? '' }}</div>
        @endif
        @if($tenant['address'] ?? false)
        <div style="font-size: 11px; color: #6b7280;">{{ $tenant['address'] ?? '' }}, Mob: {{ $tenant['contact_phone'] ?? '' }}</div>
        @endif
        <div style="font-size: 18px; font-weight: bold; margin-top: 10px; color: #4b5563;">Salary Sheet</div>
        <div style="font-size: 14px; color: #6b7280; margin-top: 5px;">Period: {{ $salarySheet->month }}</div>
        <div style="font-size: 14px; color: #6b7280;">Generated: {{ now()->format('d M Y, H:i') }}</div>
    </div>

    @if($salarySheet->summary)
    <!-- Summary Section -->
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="border: 1px solid #ddd; padding: 10px; width: 25%; text-align: center; background-color: #f9fafb;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 5px;">TOTAL EMPLOYEES</div>
                <div style="font-size: 16px; font-weight: bold; color: #1f2937;">{{ $salarySheet->summary['total_employee'] ?? count($salarySheet->salary_sheet) }}</div>
            </td>
            <td style="border: 1px solid #ddd; padding: 10px; width: 25%; text-align: center; background-color: #f9fafb;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 5px;">TOTAL EARNINGS</div>
                <div style="font-size: 16px; font-weight: bold; color: #16a34a;">BDT {{ number_format($salarySheet->summary['grand_total_earnings'] ?? 0, 2) }}</div>
            </td>
            <td style="border: 1px solid #ddd; padding: 10px; width: 25%; text-align: center; background-color: #f9fafb;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 5px;">TOTAL DEDUCTIONS</div>
                <div style="font-size: 16px; font-weight: bold; color: #dc2626;">BDT {{ number_format($salarySheet->summary['grand_total_deduction'] ?? 0, 2) }}</div>
            </td>
            <td style="border: 1px solid #ddd; padding: 10px; width: 25%; text-align: center; background-color: #f9fafb;">
                <div style="font-size: 11px; color: #6b7280; margin-bottom: 5px;">NET PAYABLE</div>
                <div style="font-size: 16px; font-weight: bold; color: #1e40af;">BDT {{ number_format($salarySheet->summary['grand_total_net_payable'] ?? 0, 2) }}</div>
            </td>
        </tr>
    </table>
    @endif

    @if($salarySheet->salary_sheet && count($salarySheet->salary_sheet) > 0)
    <!-- Employee Table -->
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background-color: #f3f4f6; border-bottom: 2px solid #374151;">
                <th style="padding: 8px; text-align: left; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 15%;">Employee</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 10%;">Gross Salary</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 8%;">Bonus</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 10%;">Total Earnings</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 8%;">Advance Deduction</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 8%;">Loan Deduction</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 10%;">Net Payable</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 8%;">Paid</th>
                <th style="padding: 8px; text-align: right; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 8%;">Due</th>
                <th style="padding: 8px; text-align: center; font-weight: bold; color: #1f2937; border: 1px solid #d1d5db; font-size: 11px; width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salarySheet->salary_sheet as $index => $sheet)
            <tr style="{{ $index % 2 === 0 ? 'background-color: #ffffff;' : 'background-color: #f9fafb;' }}">
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px;">
                    <div style="font-weight: bold; color: #1f2937;">{{ $sheet->employee['name'] ?? 'Unknown' }}</div>
                    <div style="color: #6b7280; font-size: 10px;">{{ $sheet->employee['designation'] ?? '-' }}</div>
                    <div style="font-size: 9px; color: #4b5563;">
                        Basic: BDT{{ number_format($sheet->basic_salary, 2) }} |
                        H.Rent: BDT{{ number_format($sheet->house_rent, 2) }} |
                        Med: BDT{{ number_format($sheet->medical, 2) }}
                    </div>
                </td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right;">BDT {{ number_format($sheet->gross_salary, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; color: #16a34a;">+BDT {{ number_format($sheet->bonus, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; font-weight: bold;">BDT {{ number_format($sheet->total_earnings, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; color: #dc2626;">-BDT {{ number_format($sheet->advance_deduction, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; color: #dc2626;">-BDT {{ number_format($sheet->loan_deduction, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; font-weight: bold;">BDT {{ number_format($sheet->net_payable, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; color: #16a34a;">BDT {{ number_format($sheet->paid_amount, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: right; color: #dc2626;">BDT {{ number_format($sheet->due_amount, 2) }}</td>
                <td style="padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 11px; text-align: center;">
                    @if($sheet->payment_status === 'paid')
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; display: inline-block; background-color: #dcfce7; color: #16a34a;">Paid</span>
                    @elseif($sheet->payment_status === 'partial')
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; display: inline-block; background-color: #fef9c3; color: #ca8a04;">Partial</span>
                    @else
                        <span style="padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold; display: inline-block; background-color: #fee2e2; color: #dc2626;">Unpaid</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align: center; padding: 40px; color: #9ca3af;">
        <p>No salary sheet data found</p>
    </div>
    @endif

    <!-- Footer Section -->
    <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af;">
        <p>Generated by Transport SaaS System | {{ now()->format('d M Y, H:i') }}</p>
        @if(isset($tenant['site_title']))
        <p>{{ $tenant['site_title'] }}</p>
        @endif
        @if(isset($tenant['address']))
        <p>{{ $tenant['address'] }}</p>
        @endif
        @if(isset($tenant['contact_phone']) || isset($tenant['contact_email']))
        <p>
            @if(isset($tenant['contact_phone'])) Phone: {{ $tenant['contact_phone'] }} @endif
            @if(isset($tenant['contact_phone']) && isset($tenant['contact_email'])) | @endif
            @if(isset($tenant['contact_email'])) Email: {{ $tenant['contact_email'] }} @endif
        </p>
        @endif
    </div>
</body>
</html>
