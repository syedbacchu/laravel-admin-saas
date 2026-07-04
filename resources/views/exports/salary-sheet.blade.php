<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <!-- Header Section -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="font-size: 24px; font-weight: bold; color: #1e40af; text-align: center;">{{ $tenant['site_title'] ?? 'Transport Company' }}</td>
        </tr>
        <tr>
            <td style="font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 10px;">Salary Sheet - {{ $data->month }}</td>
        </tr>
        @if($tenant['tag_title'] ?? false)
        <tr>
            <td style="font-size: 12px; text-align: center;">{{ $tenant['tag_title'] ?? '' }}</td>
        </tr>
        @endif
        @if($tenant['address'] ?? false)
        <tr>
            <td style="font-size: 11px; text-align: center;">{{ $tenant['address'] ?? '' }}, Mob: {{ $tenant['contact_phone'] ?? '' }}</td>
        </tr>
        @endif
        <tr>
            <td style="text-align: center; margin-bottom: 20px;">Generated: {{ now()->format('d M Y, H:i') }}</td>
        </tr>
    </table>

    @if($data->summary)
    <!-- Summary Section -->
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="border: 1px solid #374151; padding: 10px; width: 25%; background-color: #f9fafb;"><strong>Total Employees:</strong> {{ $data->summary['total_employee'] ?? count($data->salary_sheet) }}</td>
            <td style="border: 1px solid #374151; padding: 10px; width: 25%; background-color: #f9fafb;"><strong>Total Earnings:</strong> BDT {{ number_format($data->summary['grand_total_earnings'] ?? 0, 2) }}</td>
            <td style="border: 1px solid #374151; padding: 10px; width: 25%; background-color: #f9fafb;"><strong>Total Deductions:</strong> BDT {{ number_format($data->summary['grand_total_deduction'] ?? 0, 2) }}</td>
            <td style="border: 1px solid #374151; padding: 10px; width: 25%; background-color: #f9fafb;"><strong>Net Payable:</strong> BDT {{ number_format($data->summary['grand_total_net_payable'] ?? 0, 2) }}</td>
        </tr>
    </table>
    @endif

    @if($data->salary_sheet && count($data->salary_sheet) > 0)
    <!-- Employee Data Table -->
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background-color: #4b5563; color: white;">
                <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #374151;">Employee</th>
                <th style="padding: 10px; text-align: left; font-weight: bold; border: 1px solid #374151;">Designation</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Gross Salary</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Bonus</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Total Earnings</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Advance Deduction</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Loan Deduction</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Total Deductions</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Net Payable</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Paid Amount</th>
                <th style="padding: 10px; text-align: right; font-weight: bold; border: 1px solid #374151;">Due Amount</th>
                <th style="padding: 10px; text-align: center; font-weight: bold; border: 1px solid #374151;">Payment Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data->salary_sheet as $sheet)
            <tr>
                <td style="padding: 8px; border: 1px solid #d1d5db;">{{ $sheet->employee['name'] ?? 'Unknown' }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db;">{{ $sheet->employee['designation'] ?? '-' }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->gross_salary, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->bonus, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right; font-weight: bold;">{{ number_format($sheet->total_earnings, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->advance_deduction, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->loan_deduction, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->total_deduction, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right; font-weight: bold;">{{ number_format($sheet->net_payable, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->paid_amount, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: right;">{{ number_format($sheet->due_amount, 2) }}</td>
                <td style="padding: 8px; border: 1px solid #d1d5db; text-align: center;">{{ ucfirst($sheet->payment_status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Row -->
    <table style="margin-top: 20px;">
        <tr>
            <td style="font-weight: bold;">Summary</td>
            <td></td>
            <td></td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($data->summary['grand_total_earnings'] ?? 0, 2) }}</td>
            <td></td>
            <td></td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($data->summary['grand_total_deduction'] ?? 0, 2) }}</td>
            <td></td>
            <td style="text-align: right; font-weight: bold;">{{ number_format($data->summary['grand_total_net_payable'] ?? 0, 2) }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    @endif

    <!-- Footer Section -->
    <table style="width: 100%; margin-top: 30px;">
        <tr>
            <td style="text-align: center; font-size: 11px; color: #9ca3af;">
                Generated by Transport SaaS System | {{ now()->format('d M Y, H:i') }}
            </td>
        </tr>
        @if(isset($tenant['site_title']))
        <tr>
            <td style="text-align: center; font-size: 11px; color: #9ca3af;">
                {{ $tenant['site_title'] }}
            </td>
        </tr>
        @endif
        @if(isset($tenant['address']))
        <tr>
            <td style="text-align: center; font-size: 11px; color: #9ca3af;">
                {{ $tenant['address'] }}
            </td>
        </tr>
        @endif
        @if(isset($tenant['contact_phone']) || isset($tenant['contact_email']))
        <tr>
            <td style="text-align: center; font-size: 11px; color: #9ca3af;">
                @if(isset($tenant['contact_phone'])) Phone: {{ $tenant['contact_phone'] }} @endif
                @if(isset($tenant['contact_phone']) && isset($tenant['contact_email'])) | @endif
                @if(isset($tenant['contact_email'])) Email: {{ $tenant['contact_email'] }} @endif
            </td>
        </tr>
        @endif
    </table>
</body>
</html>
