<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payroll Payslips</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .payslip-wrapper { page-break-after: always; padding: 25px; border: 1px solid #000; margin-bottom: 20px; }
        .payslip-wrapper:last-child { page-break-after: never; }

        /* Header Styling */
        .company-header { text-align: center; margin-bottom: 20px; line-height: 1.4; border-bottom: 1px double #000; padding-bottom: 10px; }
        .company-name { font-size: 16px; font-weight: bold; color: #1a365d; }
        .payroll-title { font-size: 14px; font-weight: bold; margin-top: 5px; text-transform: uppercase; }

        /* Info Grid */
        .info-section { width: 100%; margin-bottom: 15px; border: none; }
        .info-section td { border: none; padding: 2px 0; }

        /* Two-Column Layout Container */
        .details-container { width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 10px; }
        .details-column { width: 50%; vertical-align: top; border-right: 1px solid #000; }
        .details-column:last-child { border-right: none; }

        /* Table Styling inside columns */
        .item-table { width: 100%; border-collapse: collapse; }
        .item-table th { background: #f2f2f2; font-size: 9px; padding: 6px; border-bottom: 1px solid #000; text-transform: uppercase; text-align: center; }
        .item-table td { padding: 4px 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }

        /* Section Totals */
        .total-row { font-weight: bold; background: #fafafa; border-top: 1px solid #000; }

        /* Net Pay Box */
        .net-pay-row { font-weight: bold; background: #1a365d; color: #ffffff; border: 1px solid #000; }
        .net-pay-row td { padding: 10px; text-align: right; }

        /* Signatures */
        .signature-section { width: 100%; margin-top: 30px; border: none; }
        .signature-section td { border: none; vertical-align: top; }
        .sig-box { width: 33%; }
        .sig-line { border-top: 1px solid #000; margin-top: 35px; padding-top: 5px; font-weight: bold; text-transform: uppercase; font-size: 9px; width: 90%; }
        .role { font-size: 8px; color: #555; margin-top: 2px; }
    </style>
</head>
<body>

@php
    $earningFields = [
        'regular_pay' => 'Regular Pay',
        'absence_with_pay' => 'Absence w/ Pay',
        'regular_ot' => 'Regular OT',
        'rdot' => 'Rest Day OT',
        'regular_holiday_ot' => 'Reg. Holiday OT',
        'special_holiday_ot' => 'Spec. Holiday OT',
        'rd_regular_holiday_ot' => 'RD Reg. Holiday OT',
        'rd_special_holiday_ot' => 'RD Spec. Holiday OT',
        'night_differential' => 'Night Differential',
        'regular_holiday' => 'Regular Holiday',
        'special_holiday' => 'Special Holiday',
        'rd_regular_holiday' => 'RD Regular Holiday',
        'rd_special_holiday' => 'RD Special Holiday',
        'adjustment' => 'Adjustment',
        'allowance' => 'Allowance'
    ];

    $deductionFields = [
        'sss' => 'SSS',
        'pag_ibig' => 'Pag-IBIG',
        'philhealth' => 'PhilHealth',
        'tax' => 'Withholding Tax',
        'salary_loan' => 'Salary Loan',
        'cash_advance' => 'Cash Advance',
        'undertime' => 'Undertime',
        'absence_without_pay' => 'Absence w/o Pay',
        'flu_vaccine' => 'Flu Vaccine',
        'food' => 'Food'
    ];
@endphp

@foreach($payrolls as $p)
<div class="payslip-wrapper">
    <div class="company-header">
        <div class="company-name">HAPPIEST-NEST MARKETING CONSULTING SERVICES</div>
        <div>GT. Building, Gueco St. Lacson Corner Magalang Pampanga 2011</div>
        <div>happiestnestrealtydevelopment@gmail.com</div>
        <div class="payroll-title">PAYROLL PAYSLIP</div>
    </div>

    <table class="info-section">
        <tr>
            <td style="width: 50%;">
                <strong>Name:</strong> {{ $p->user->first_name }} {{ $p->user->last_name }}<br>
                <strong>Employee No:</strong> {{ $p->user->employee_id }}
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Date:</strong> {{ date('F d, Y') }}<br>
                <strong>Period:</strong> {{ \Carbon\Carbon::parse($cutoff->from_cutoff_date)->format('M d') }} - {{ \Carbon\Carbon::parse($cutoff->to_cutoff_date)->format('M d, Y') }}
            </td>
        </tr>
    </table>

    <table class="details-container">
        <tr>
            <td class="details-column">
                <table class="item-table">
                    <thead>
                        <tr><th colspan="2">EARNINGS</th></tr>
                    </thead>
                    <tbody>
                        @foreach($earningFields as $key => $label)
                            @if($key !== 'absence_with_pay' || (float)($p->$key) != 0)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td class="text-right">{{ number_format((float)($p->$key ?? 0), 2) }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td>TOTAL EARNINGS</td>
                            <td class="text-right">{{ number_format($p->total_earning, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </td>

            <td class="details-column">
                <table class="item-table">
                    <thead>
                        <tr><th colspan="2">DEDUCTIONS</th></tr>
                    </thead>
                    <tbody>
                        @foreach($deductionFields as $key => $label)
                            <tr>
                                <td>{{ $label }}</td>
                                <td class="text-right">({{ number_format((float)($p->$key ?? 0), 2) }})</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td>TOTAL DEDUCTIONS</td>
                            <td class="text-right">({{ number_format($p->total_deduction, 2) }})</td>
                        </tr>
                    </tfoot>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%;">
        <tr class="net-pay-row">
            <td style="width: 70%; font-size: 13px;">NET TAKE HOME PAY</td>
            <td class="text-right" style="width: 30%; font-size: 13px;">PHP {{ number_format($p->total_home_pay, 2) }}</td>
        </tr>
    </table>

    <table class="signature-section">
        <tr>
            <td class="sig-box">
                <p>Prepared By:</p>
                <div class="sig-line">ERICA MANLAPAZ</div>
                <div class="role">HCEWD Officer</div>
            </td>
            <td class="sig-box">
                <p>Noted By:</p>
                <div class="sig-line">Valerio, Dael</div>
                <div class="role">Marketing Officer for Commissions</div>
            </td>
            <td class="sig-box">
                <p>Received By:</p>
                <div class="sig-line">{{ $p->user->first_name }} {{ $p->user->last_name }}</div>
                <div class="role">Employee Signature</div>
            </td>
        </tr>
    </table>
</div>
@endforeach

</body>
</html>
