<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fee Voucher &amp; Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
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
            margin-bottom: 25px;
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
            background-color: #10b981;
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
            font-size: 24px;
            color: #0f172a;
            font-weight: 800;
            text-transform: uppercase;
        }
        .meta-container p {
            margin: 2px 0;
            color: #64748b;
        }
        .meta-container .invoice-number {
            font-size: 15px;
            color: #10b981;
            font-weight: bold;
        }
        .divider {
            height: 2px;
            background-color: #e2e8f0;
            margin-bottom: 20px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
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
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
        }
        .details-label {
            font-weight: bold;
            color: #334155;
            width: 22%;
        }
        .details-val {
            color: #475569;
            width: 28%;
        }
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .ledger-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            text-align: left;
        }
        .ledger-table th.right, .ledger-table td.right {
            text-align: right;
        }
        .ledger-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .ledger-table tr.total-row td {
            border-top: 2px solid #0f172a;
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-filer { background-color: #dcfce7; color: #15803d; }
        .badge-nonfiler { background-color: #fee2e2; color: #b91c1c; }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header Block -->
    <table class="header-table">
        <tr>
            <td class="logo-container">
                <?php if(!empty($logoBase64)): ?>
                    <img src="<?php echo e($logoBase64); ?>" alt="Institute Logo">
                <?php else: ?>
                    <div class="logo-placeholder">
                        <?php echo e($institute->name ?? 'Uplyft Institution'); ?>

                    </div>
                <?php endif; ?>
            </td>
            <td class="meta-container">
                <h1>Official Fee Voucher</h1>
                <p class="invoice-number">INV-<?php echo e(str_pad($invoice->id ?? $student->id, 6, '0', STR_PAD_LEFT)); ?></p>
                <p><strong>Fee Period / Month:</strong> <?php echo e($feeMonth ?? ($invoice->fee_month ?? now()->format('F Y'))); ?></p>
                <p>Issued Date: <?php echo e($issuedAt ?? now()->format('Y-m-d')); ?></p>
                <p>Due Date: <?php echo e(isset($invoice->due_date) ? $invoice->due_date->format('Y-m-d') : now()->addDays(7)->format('Y-m-d')); ?></p>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Student & Academic Details -->
    <table class="details-table">
        <thead>
            <tr>
                <th colspan="4">Student &amp; Academic Credentials</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="details-label">Student Name:</td>
                <td class="details-val"><strong><?php echo e($student->full_name); ?></strong></td>
                <td class="details-label">Roll Number:</td>
                <td class="details-val"><strong><?php echo e($student->roll_number); ?></strong></td>
            </tr>
            <tr>
                <td class="details-label">Class &amp; Section:</td>
                <td class="details-val">
                    <?php if($student->classSection && $student->classSection->instituteClass): ?>
                        <?php echo e($student->classSection->instituteClass->name); ?> — Sec <?php echo e($student->classSection->section_name ?? $student->classSection->name ?? ''); ?>

                    <?php else: ?>
                        <?php echo e($student->enrolled_program ?? 'General'); ?>

                    <?php endif; ?>
                </td>
                <td class="details-label">Father / Guardian:</td>
                <td class="details-val"><?php echo e($student->father_guardian_name ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <td class="details-label">Guardian Contact:</td>
                <td class="details-val"><?php echo e($student->guardian_phone ?? $student->phone ?? 'N/A'); ?></td>
                <td class="details-label">FBR Tax Status:</td>
                <td class="details-val">
                    <?php if($student->guardian_tax_status === 'filer'): ?>
                        <span class="badge badge-filer">Tax Filer (Active)</span>
                    <?php else: ?>
                        <span class="badge badge-nonfiler">Non-Filer</span>
                    <?php endif; ?>
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
                <th class="right">Scholarship / Concession</th>
                <th class="right">Tax Rate / Amount</th>
                <th class="right">Total (<?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?>)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>Monthly Base Tuition Fee</strong>
                    <?php if(!empty($student->scholarship_name) || !empty($feeBreakdown['scholarship_percentage'])): ?>
                        <br><span style="font-size:10px;color:#059669;font-weight:bold">
                            🎓 Policy: <?php echo e($student->scholarship_name ?? 'Scholarship'); ?> (<?php echo e(number_format($feeBreakdown['scholarship_percentage'], 0)); ?>% Off)
                        </span>
                    <?php endif; ?>
                </td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['base_fee'], 2)); ?></td>
                <td class="right" style="color:#059669">
                    <?php if(!empty($feeBreakdown['scholarship_amount']) && $feeBreakdown['scholarship_amount'] > 0): ?>
                        - <?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['scholarship_amount'], 2)); ?>

                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td class="right">—</td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['subtotal_after_scholarship'], 2)); ?></td>
            </tr>

            <?php if(!empty($feeBreakdown['admission_fee']) && $feeBreakdown['admission_fee'] > 0): ?>
            <tr>
                <td>
                    <strong>Admission Fee</strong>
                    <br><span style="font-size:9px;color:#dc2626;font-weight:bold">📌 Non-Refundable (One-Time Registration)</span>
                </td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['admission_fee'], 2)); ?></td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['admission_fee'], 2)); ?></td>
            </tr>
            <?php endif; ?>

            <?php if(!empty($feeBreakdown['security_fee']) && $feeBreakdown['security_fee'] > 0): ?>
            <tr>
                <td>
                    <strong>Security Deposit Fee</strong>
                    <br><span style="font-size:9px;color:#2563eb;font-weight:bold">🛡️ Refundable upon leaving / graduation</span>
                </td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['security_fee'], 2)); ?></td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['security_fee'], 2)); ?></td>
            </tr>
            <?php endif; ?>

            <tr>
                <td>
                    <strong>FBR Guardian Tax Amount</strong>
                    <br><span style="font-size:9px;color:#64748b">Applied rate: <?php echo e(number_format($feeBreakdown['tax_percentage'], 0)); ?>% (<?php echo e(strtolower($student->guardian_tax_status ?? 'filer') === 'filer' ? 'Filer' : 'Non-Filer'); ?>)</span>
                </td>
                <td class="right">—</td>
                <td class="right">—</td>
                <td class="right"><?php echo e(number_format($feeBreakdown['tax_percentage'], 0)); ?>%</td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['tax_amount'], 2)); ?></td>
            </tr>

            <tr class="total-row">
                <td colspan="3">
                    <span style="font-size:10px;color:#475569">
                        Official Fee Voucher Summary
                    </span>
                </td>
                <td class="right">Grand Total Payable:</td>
                <td class="right"><?php echo e($currencySymbol ?? $currency ?? 'PKR'); ?> <?php echo e(number_format($feeBreakdown['grand_total'], 2)); ?></td>
            </tr>
        </tbody>
    </table>

    <?php if((!empty($feeBreakdown['admission_fee']) && $feeBreakdown['admission_fee'] > 0) || (!empty($feeBreakdown['security_fee']) && $feeBreakdown['security_fee'] > 0)): ?>
    <!-- Legal Terms & Refund Policy Box (For Initial Admission Vouchers) -->
    <div style="margin-top: 18px; padding: 10px 14px; background-color: #fffbe0; border: 1px solid #fde047; border-left: 4px solid #eab308; border-radius: 6px;">
        <h4 style="margin: 0 0 4px 0; font-size: 11px; font-weight: bold; color: #854d0e; text-transform: uppercase; letter-spacing: 0.5px;">
            📌 Terms &amp; Conditions / Institutional Fee Policy:
        </h4>
        <ul style="margin: 0; padding-left: 16px; font-size: 10px; color: #713f12; line-height: 1.4;">
            <li><strong>Admission Fee Policy:</strong> The admission fee paid at the time of registration is strictly <u>Non-Refundable</u> under any circumstances.</li>
            <li><strong>Security Fee Policy:</strong> The security deposit fee is refundable and will be <u>given back to the student when they leave or graduate</u> from the institution.</li>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Page Footer -->
    <div class="footer">
        <p>This is an official system-generated electronic invoice issued by <?php echo e($institute->name ?? 'UPLYFT Platform'); ?>.</p>
        <p>Please clear your fee before the due date to avoid late payment surcharges.</p>
    </div>
</div>

</body>
</html>
<?php /**PATH D:\UPLYFT\uplifyt\resources\views\pdf\invoice.blade.php ENDPATH**/ ?>