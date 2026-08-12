<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admission Fee Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333333;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-container {
            width: 50%;
        }
        .logo-container img {
            max-height: 70px;
            max-width: 200px;
        }
        .logo-placeholder {
            display: inline-block;
            padding: 10px 15px;
            background-color: #6366f1;
            color: #ffffff;
            font-weight: bold;
            font-size: 18px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta-container {
            width: 50%;
            text-align: right;
        }
        .meta-container h1 {
            margin: 0 0 5px 0;
            font-size: 26px;
            color: #1e1b4b;
            font-weight: 700;
            text-transform: uppercase;
        }
        .meta-container p {
            margin: 2px 0;
            color: #64748b;
        }
        .meta-container .invoice-number {
            font-size: 16px;
            color: #6366f1;
            font-weight: bold;
        }
        .divider {
            height: 2px;
            background-color: #e2e8f0;
            margin-bottom: 30px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .details-table th {
            text-align: left;
            padding: 8px 12px;
            background-color: #f8fafc;
            color: #475569;
            font-size: 11px;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }
        .details-table td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .details-label {
            font-weight: bold;
            color: #334155;
            width: 25%;
        }
        .details-val {
            color: #475569;
            width: 75%;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .ledger-table th {
            background-color: #1e1b4b;
            color: #ffffff;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 12px;
            text-align: left;
        }
        .ledger-table th.right, .ledger-table td.right {
            text-align: right;
        }
        .ledger-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .ledger-table tr.total-row td {
            border-top: 2px solid #1e1b4b;
            font-size: 16px;
            font-weight: bold;
            color: #1e1b4b;
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-filer {
            background-color: #dcfce7;
            color: #15803d;
        }
        .badge-nonfiler {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Institute Logo">
                @else
                    <div class="logo-placeholder">
                        {{ $institute->name ?? 'Uplyft Tenant' }}
                    </div>
                @endif
            </td>
            <td class="meta-container">
                <h1>Invoice</h1>
                <p class="invoice-number">REF-ADM-{{ str_pad($student->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p>Date Issued: {{ $issuedAt }}</p>
                <p>Due: On Presentation</p>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Student Details -->
    <table class="details-table">
        <thead>
            <tr>
                <th colspan="2">Student Details</th>
                <th colspan="2">Institute Information</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="details-label">Student Name:</td>
                <td class="details-val">{{ $student->full_name }}</td>
                <td class="details-label">Institute Name:</td>
                <td class="details-val">{{ $institute->name ?? 'Default Institute' }}</td>
            </tr>
            <tr>
                <td class="details-label">Email:</td>
                <td class="details-val">{{ $student->email }}</td>
                <td class="details-label">Phone:</td>
                <td class="details-val">{{ $student->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="details-label">Date of Birth:</td>
                <td class="details-val">{{ $student->date_of_birth->format('Y-m-d') }}</td>
                <td class="details-label">Guardian Status:</td>
                <td class="details-val">
                    @if($student->guardian_tax_status === 'filer')
                        <span class="badge badge-filer">Tax Filer (Active)</span>
                    @else
                        <span class="badge badge-nonfiler">Non-Filer</span>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Pricing Ledger -->
    <table class="ledger-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Base Amount</th>
                <th class="right">Tax Rate</th>
                <th class="right">Tax Amount</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Admission Registration & Processing Fee</td>
                <td class="right">${{ number_format($feeBreakdown['base_fee'], 2) }}</td>
                <td class="right">{{ $feeBreakdown['tax_rate'] * 100 }}%</td>
                <td class="right">${{ number_format($feeBreakdown['tax_amount'], 2) }}</td>
                <td class="right">${{ number_format($feeBreakdown['total_fee'], 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3"></td>
                <td class="right">Total Payable:</td>
                <td class="right">${{ number_format($feeBreakdown['total_fee'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Page Footer -->
    <div class="footer">
        <p>This is a system-generated electronic invoice. No signature is required.</p>
        <p>Thank you for choosing {{ $institute->name ?? 'Uplyft' }}. All payments are non-refundable.</p>
    </div>
</div>

</body>
</html>
