<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Salary Slip - {{ $employee->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .payslip-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            border: 1px solid #ccc;
            background: #fff;
        }
        .payslip-header {
            text-align: center;
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .payslip-header h2 {
            margin-bottom: 5px;
            font-weight: bold;
        }
        .payslip-header p {
            margin: 2px 0;
            font-size: 13px;
        }
        .payslip-title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .info-table, .earnings-deductions {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .info-table th, .info-table td,
        .earnings-deductions th, .earnings-deductions td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .info-table th {
            background: #f9f9f9;
            width: 25%;
        }
        .earnings-deductions th {
            background: #f1f1f1;
            text-align: center;
        }
        .earnings-deductions td {
            text-align: right;
        }
        .earnings-deductions td:first-child {
            text-align: left;
        }
        .total-row {
            font-weight: bold;
            background: #f9f9f9;
        }
        .net-salary {
            text-align: right;
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
        }
        .footer-note {
            font-size: 11px;
            color: #777;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 10px;
            margin-top: 30px;
        }
        .signature {
            margin-top: 60px;
            text-align: right;
        }
        .signature-line {
            border-top: 1px solid #333;
            display: inline-block;
            width: 200px;
            margin-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-danger {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="payslip-header">
            <h2>PayNinja Payment Technology Ltd.</h2>
            <p>Flat No. 1003, 10th Floor, Nirmal Tower 26, Barakhamba Road, New Delhi – 110001</p>
            <p>Phone: +91 9999092616 | +91 9654540842 | Email: info@payninjahr.com</p>
        </div>

        <div class="payslip-title">Salary Slip for {{ date('F Y', strtotime($monthYear)) }}</div>
        
        <table class="info-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $employee->name }}</td>
                <th>Employee ID</th>
                <td>{{ $employee->employee_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Pay Period</th>
                <td>{{ $payroll->pay_period_start->format('M d, Y') }} - {{ $payroll->pay_period_end->format('M d, Y') }}</td>
                <th>Payment Date</th>
                <td>{{ $payroll->payment_date ? $payroll->payment_date->format('M d, Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <th>Designation</th>
                <td>{{ $employee->designation->title ?? 'N/A' }}</td>
                <th>Department</th>
                <td>{{ $employee->department->name ?? 'N/A' }}</td>
            </tr>
        </table>

        <table class="earnings-deductions">
            <thead>
                <tr>
                    <th colspan="2">Earnings</th>
                    <th colspan="2">Deductions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Group items by type
                    $earnings = collect([
                        'Basic Salary' => $payroll->basic_salary ?? 0,
                        'HRA' => $payroll->hra ?? 0,
                        'DA' => $payroll->da ?? 0,
                        'Other Allowances' => $payroll->other_allowances ?? 0,
                        'Bonus' => $payroll->bonus ?? 0,
                        'Overtime' => $payroll->overtime_earnings ?? 0,
                    ])->filter(fn($amount) => $amount > 0);

                    $deductions = collect([
                        'PF' => $payroll->pf_deduction ?? 0,
                        'ESI' => $payroll->esi_deduction ?? 0,
                        'Professional Tax' => $payroll->professional_tax ?? 0,
                        'TDS' => $payroll->tds_deduction ?? 0,
                        'Loan' => $payroll->loan_deductions ?? 0,
                        'Other Deductions' => $payroll->other_deductions ?? 0,
                    ])->filter(fn($amount) => $amount > 0);

                    // Add dynamic items from payroll items
                    if ($payroll->items && $payroll->items->count() > 0) {
                        foreach ($payroll->items as $item) {
                            $amount = $item->amount ?? 0;
                            if ($amount <= 0) continue;

                            $name = $item->name ?? $item->description ?? 'Other';
                            
                            if (in_array($item->type, ['earning', 'allowance', 'bonus', 'overtime', 'reimbursement'])) {
                                $earnings[$name] = $amount;
                            } else {
                                $deductions[$name] = $amount;
                            }
                        }
                    }


                    $maxRows = max(count($earnings), count($deductions));
                    $maxRows = max(1, $maxRows);
                    $earnings = $earnings->toArray();
                    $deductions = $deductions->toArray();
                @endphp

                @for($i = 0; $i < $maxRows; $i++)
                    <tr>
                        <td>{{ $i < count($earnings) ? array_keys($earnings)[$i] : '' }}</td>
                        <td class="text-right">{{ $i < count($earnings) ? number_format(array_values($earnings)[$i], 2) : '' }}</td>
                        <td>{{ $i < count($deductions) ? array_keys($deductions)[$i] : '' }}</td>
                        <td class="text-right text-danger">
                            {{ $i < count($deductions) ? number_format(array_values($deductions)[$i], 2) : '' }}
                        </td>
                    </tr>
                @endfor
                
                @if(empty($earnings) && empty($deductions))
                    <tr>
                        <td>Basic Salary</td>
                        <td class="text-right">{{ number_format($payroll->basic_salary ?? 0, 2) }}</td>
                        <td>No deductions</td>
                        <td class="text-right">0.00</td>
                    </tr>
                @endif
                
                <tr class="total-row">
                    <td><strong>Total Earnings</strong></td>
                    <td class="text-right"><strong>{{ number_format($payroll->gross_salary ?? 0, 2) }}</strong></td>
                    <td><strong>Total Deductions</strong></td>
                    <td class="text-right text-danger"><strong>{{ number_format($payroll->total_deductions ?? 0, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="net-salary">
            Net Salary Payable: ₹{{ number_format($payroll->net_salary ?? 0, 2) }}
        </div>

        @if($payroll->notes)
            <div style="margin-top: 20px;">
                <strong>Notes:</strong>
                <p>{{ $payroll->notes }}</p>
            </div>
        @endif

        <div class="signature">
            <div class="signature-line"></div>
            <div>Authorized Signatory</div>
        </div>

        <div class="footer-note">
            This is a system-generated payslip. For discrepancies, contact HR within 7 days.
        </div>
    </div>
</body>
</html>
