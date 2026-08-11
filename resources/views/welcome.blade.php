<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uplyft School Management - Student Admission Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --bg-gradient: radial-gradient(circle at 50% 50%, #1e1b4b 0%, #090514 100%);
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        /* Animated background decorative elements */
        .decor-blob {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(150px);
            z-index: -1;
            opacity: 0.35;
            animation: pulse-blob 8s infinite alternate;
        }
        .blob-1 {
            background-color: var(--primary);
            top: -100px;
            left: -100px;
        }
        .blob-2 {
            background-color: #ec4899;
            bottom: -150px;
            right: -100px;
            animation-delay: 2s;
        }

        @keyframes pulse-blob {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.2) translate(50px, 50px); }
        }

        .container {
            width: 100%;
            max-width: 1100px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            backdrop-filter: blur(20px);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
        }

        @media (max-width: 900px) {
            .container {
                grid-template-columns: 1fr;
            }
        }

        /* Form section styling */
        .form-section {
            padding: 45px;
            border-right: 1px solid var(--card-border);
        }

        @media (max-width: 900px) {
            .form-section {
                border-right: none;
                border-bottom: 1px solid var(--card-border);
            }
        }

        .logo-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 30px;
        }
        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), #ec4899);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
            color: #ffffff;
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.4);
        }
        .logo-title h2 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .logo-title span {
            background: linear-gradient(to right, #a5b4fc, #f472b6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .section-header {
            margin-bottom: 30px;
        }
        .section-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .section-header p {
            color: var(--text-muted);
            font-size: 15px;
        }

        /* Input Grid layout */
        .grid-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 600px) {
            .grid-inputs {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 13px;
            font-weight: 500;
            color: #cbd5e1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input, select {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text-main);
            font-size: 15px;
            transition: all 0.3s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 10px rgba(99, 102, 241, 0.2);
        }

        select option {
            background-color: #1e1b4b;
            color: var(--text-main);
        }

        .radio-card-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .radio-card {
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .radio-card:hover {
            background: rgba(255, 255, 255, 0.05);
        }
        .radio-card.active {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.08);
        }
        .radio-card input {
            width: auto;
            cursor: pointer;
        }
        .radio-card-text {
            display: flex;
            flex-direction: column;
        }
        .radio-title {
            font-weight: 600;
            font-size: 14px;
        }
        .radio-desc {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Error Text container */
        .error-message {
            color: var(--danger);
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        /* Live Preview Ledger section */
        .preview-section {
            padding: 45px;
            background: rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .preview-header {
            margin-bottom: 30px;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
            padding-bottom: 20px;
        }
        .preview-header h3 {
            font-size: 18px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #e2e8f0;
            text-transform: uppercase;
        }

        .ledger-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .ledger-label {
            color: var(--text-muted);
        }
        .ledger-value {
            font-weight: 500;
        }
        .ledger-value.free {
            color: var(--success);
        }

        .ledger-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 20px 0;
        }

        .ledger-row.total {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 0;
            color: #ffffff;
        }
        .ledger-row.total .ledger-value {
            color: var(--primary);
            text-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary), #4f46e5);
            color: #ffffff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            margin-top: 30px;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }
        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:disabled {
            background: var(--text-muted);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* Success screen card styling */
        .success-card {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 40px;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            color: var(--success);
            font-size: 40px;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .success-card h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #ffffff;
        }
        .success-card p {
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 30px;
            max-width: 500px;
        }

        .invoice-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            width: 100%;
            max-width: 500px;
            margin-bottom: 30px;
            text-align: left;
        }
        .invoice-card h4 {
            margin-bottom: 15px;
            font-size: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 10px;
            color: #e2e8f0;
        }

        .download-btn {
            background: linear-gradient(135deg, var(--success), #059669);
            color: #ffffff;
            border: none;
            padding: 16px 30px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .download-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .reset-btn {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--text-muted);
            padding: 10px 20px;
            margin-top: 15px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .reset-btn:hover {
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.03);
        }

        /* Form Submitting overlay */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #ffffff;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger);
            color: #fca5a5;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- Background decorative blobs -->
    <div class="decor-blob blob-1"></div>
    <div class="decor-blob blob-2"></div>

    <div class="container" id="portalContainer">
        <!-- FORM INTERFACE -->
        <div class="form-section" id="formWrapper">
            <div class="logo-title">
                <div class="logo-icon">U</div>
                <h2>UPLYFT<span>.portal</span></h2>
            </div>

            <div class="section-header">
                <h1>Student Admission Registration</h1>
                <p>Submit details to register a new student and generate their tax-adjusted invoice.</p>
            </div>

            <div class="alert-error" id="formErrorAlert"></div>

            <form id="admissionForm">
                <div class="grid-inputs">
                    <!-- Institute Scoping -->
                    <div class="form-group full-width">
                        <label for="institute_id">Target Institute</label>
                        <select name="institute_id" id="institute_id" required>
                            @php
                                try {
                                    $institutes = \App\Models\Institute::all();
                                } catch (\Throwable $e) {
                                    $institutes = collect();
                                }
                            @endphp
                            @forelse($institutes as $inst)
                                <option value="{{ $inst->id }}" 
                                        data-base-fee="{{ $inst->settings['base_admission_fee'] ?? 10000.00 }}"
                                        data-filer-rate="{{ $inst->settings['filer_tax_rate'] ?? 0.05 }}"
                                        data-non-filer-rate="{{ $inst->settings['non_filer_tax_rate'] ?? 0.15 }}">
                                    {{ $inst->name }}
                                </option>
                            @empty
                                <option value="" disabled selected>No institutes found. Please run seeders!</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Personal Details -->
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" name="first_name" id="first_name" placeholder="John" required>
                        <div class="error-message" id="error-first_name"></div>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" name="last_name" id="last_name" placeholder="Doe" required>
                        <div class="error-message" id="error-last_name"></div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" name="email" id="email" placeholder="john.doe@example.com" required>
                        <div class="error-message" id="error-email"></div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number (Optional)</label>
                        <input type="text" name="phone" id="phone" placeholder="+923001234567">
                        <div class="error-message" id="error-phone"></div>
                    </div>

                    <div class="form-group">
                        <label for="date_of_birth">Date of Birth</label>
                        <input type="date" name="date_of_birth" id="date_of_birth" required>
                        <div class="error-message" id="error-date_of_birth"></div>
                    </div>

                    <div class="form-group">
                        <label for="previous_marks">Previous Marks (%)</label>
                        <input type="number" step="0.01" name="previous_marks" id="previous_marks" min="0" max="100" placeholder="85.5" required>
                        <div class="error-message" id="error-previous_marks"></div>
                    </div>

                    <div class="form-group">
                        <label for="blood_group">Blood Group (Optional)</label>
                        <select name="blood_group" id="blood_group">
                            <option value="" selected>Select Blood Group</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                        <div class="error-message" id="error-blood_group"></div>
                    </div>
                    <div></div> <!-- Spacer for grid alignment -->

                    <!-- Guardian Tax Status -->
                    <div class="form-group full-width">
                        <label>Guardian Tax Filing Profile (For Tax Calculations)</label>
                        <div class="radio-card-group">
                            <div class="radio-card active" id="card-filer" onclick="setTaxStatus('filer')">
                                <input type="radio" name="guardian_tax_status" id="tax_status_filer" value="filer" checked>
                                <div class="radio-card-text">
                                    <span class="radio-title">Active Filer</span>
                                    <span class="radio-desc">Reduced tax rate applied (e.g. 4-5%)</span>
                                </div>
                            </div>
                            <div class="radio-card" id="card-nonfiler" onclick="setTaxStatus('non-filer')">
                                <input type="radio" name="guardian_tax_status" id="tax_status_nonfiler" value="non-filer">
                                <div class="radio-card-text">
                                    <span class="radio-title">Non-Filer</span>
                                    <span class="radio-desc">Standard higher tax rate (e.g. 12-15%)</span>
                                </div>
                            </div>
                        </div>
                        <div class="error-message" id="error-guardian_tax_status"></div>
                    </div>
                </div>
            </form>
        </div>

        <!-- REAL-TIME PREVIEW LEDGER -->
        <div class="preview-section" id="previewWrapper">
            <div>
                <div class="preview-header">
                    <h3>Admission Fee Ledger</h3>
                </div>

                <div class="ledger-row">
                    <span class="ledger-label">Target Institute</span>
                    <span class="ledger-value" id="val-inst-name">--</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Base Processing Fee</span>
                    <span class="ledger-value" id="val-base-fee">$0.00</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Filing Adjustment</span>
                    <span class="ledger-value" id="val-tax-status">Tax Filer</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Applicable Tax Rate</span>
                    <span class="ledger-value" id="val-tax-rate">0%</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Tax Surcharge</span>
                    <span class="ledger-value" id="val-tax-amount">$0.00</span>
                </div>

                <div class="ledger-divider"></div>

                <div class="ledger-row total">
                    <span class="ledger-label">Grand Total</span>
                    <span class="ledger-value" id="val-total-fee">$0.00</span>
                </div>
            </div>

            <button type="button" class="submit-btn" id="submitFormBtn" onclick="submitAdmission()">
                <span class="spinner" id="btnSpinner"></span>
                <span id="btnText">Confirm Admission & Generate Invoice</span>
            </button>
        </div>
    </div>

    <!-- SUCCESS SCREEN -->
    <div class="container" id="successContainer" style="display: none; grid-template-columns: 1fr;">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1>Admission Confirmed!</h1>
            <p>The student record has been successfully registered in the multi-institute database with scope isolation applied.</p>

            <div class="invoice-card">
                <h4>Calculation Breakdown Summary</h4>
                <div class="ledger-row">
                    <span class="ledger-label">Student Name:</span>
                    <span class="ledger-value" id="rec-student-name">--</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Assigned ID / Ref:</span>
                    <span class="ledger-value" id="rec-ref">--</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Base Fee:</span>
                    <span class="ledger-value" id="rec-base-fee">--</span>
                </div>
                <div class="ledger-row">
                    <span class="ledger-label">Tax Paid:</span>
                    <span class="ledger-value" id="rec-tax-fee">--</span>
                </div>
                <div class="ledger-row total" style="font-size: 16px; margin-top: 10px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                    <span class="ledger-label">Total Fee Paid:</span>
                    <span class="ledger-value" id="rec-total-fee">--</span>
                </div>
            </div>

            <a href="#" class="download-btn" id="invoiceDownloadLink" target="_blank">
                Download PDF Invoice
            </a>

            <div>
                <button type="button" class="reset-btn" onclick="resetPortal()">Register Another Student</button>
            </div>
        </div>
    </div>

    <!-- Scripting for Realtime Ledger & AJAX -->
    <script>
        const instSelect = document.getElementById('institute_id');
        const filerCard = document.getElementById('card-filer');
        const nonFilerCard = document.getElementById('card-nonfiler');

        // Initial setup on window load
        window.addEventListener('DOMContentLoaded', () => {
            updateRealtimeLedger();
            instSelect.addEventListener('change', updateRealtimeLedger);
        });

        // Set the active radio card dynamically and calculate
        function setTaxStatus(status) {
            if (status === 'filer') {
                document.getElementById('tax_status_filer').checked = true;
                filerCard.classList.add('active');
                nonFilerCard.classList.remove('active');
            } else {
                document.getElementById('tax_status_nonfiler').checked = true;
                nonFilerCard.classList.add('active');
                filerCard.classList.remove('active');
            }
            updateRealtimeLedger();
        }

        // Live calculation logic in frontend mirroring backend
        function updateRealtimeLedger() {
            const selectedOption = instSelect.options[instSelect.selectedIndex];
            if (!selectedOption) return;

            const instName = selectedOption.text;
            const baseFee = parseFloat(selectedOption.getAttribute('data-base-fee'));
            const filerRate = parseFloat(selectedOption.getAttribute('data-filer-rate'));
            const nonFilerRate = parseFloat(selectedOption.getAttribute('data-non-filer-rate'));

            const isFiler = document.getElementById('tax_status_filer').checked;
            const activeRate = isFiler ? filerRate : nonFilerRate;

            const taxAmount = baseFee * activeRate;
            const totalFee = baseFee + taxAmount;

            // Update UI elements
            document.getElementById('val-inst-name').textContent = instName;
            document.getElementById('val-base-fee').textContent = '$' + baseFee.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('val-tax-status').textContent = isFiler ? 'Tax Filer (Reduced)' : 'Non-Filer (Standard)';
            document.getElementById('val-tax-rate').textContent = (activeRate * 100).toFixed(0) + '%';
            document.getElementById('val-tax-amount').textContent = '$' + taxAmount.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('val-total-fee').textContent = '$' + totalFee.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // Handle AJAX form submission
        function submitAdmission() {
            // Reset errors
            document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');
            document.getElementById('formErrorAlert').style.display = 'none';

            // Check form validity
            const form = document.getElementById('admissionForm');
            if (!form.reportValidity()) {
                return;
            }

            // Show loading spinner
            const submitBtn = document.getElementById('submitFormBtn');
            const spinner = document.getElementById('btnSpinner');
            const btnText = document.getElementById('btnText');

            submitBtn.disabled = true;
            spinner.style.display = 'inline-block';
            btnText.textContent = 'Processing Admission...';

            // Gather parameters
            const formData = new FormData(form);
            const dataObj = {};
            formData.forEach((val, key) => dataObj[key] = val);

            // API POST Request
            fetch('/api/admissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(dataObj)
            })
            .then(async response => {
                const resData = await response.json();
                if (!response.ok) {
                    throw { status: response.status, data: resData };
                }
                return resData;
            })
            .then(result => {
                // Success path
                if (result.success) {
                    const student = result.data.student;
                    const breakdown = result.data.fee_breakdown;

                    // Populate success card
                    document.getElementById('rec-student-name').textContent = student.first_name + ' ' + student.last_name;
                    document.getElementById('rec-ref').textContent = 'REF-ADM-' + String(student.id).padStart(6, '0');
                    document.getElementById('rec-base-fee').textContent = '$' + parseFloat(breakdown.base_fee).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    document.getElementById('rec-tax-fee').textContent = '$' + parseFloat(breakdown.tax_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' (' + (breakdown.tax_rate * 100) + '%)';
                    document.getElementById('rec-total-fee').textContent = '$' + parseFloat(breakdown.total_fee).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    // Download URL
                    document.getElementById('invoiceDownloadLink').href = result.data.invoice_download_url;

                    // Transition UI panels
                    document.getElementById('portalContainer').style.display = 'none';
                    document.getElementById('successContainer').style.display = 'grid';
                }
            })
            .catch(error => {
                // Error path
                console.error('Submission Error:', error);
                
                if (error.data && error.data.errors) {
                    const validationErrors = error.data.errors;
                    // Field errors
                    for (const field in validationErrors) {
                        const errorEl = document.getElementById('error-' + field);
                        if (errorEl) {
                            errorEl.textContent = validationErrors[field][0];
                            errorEl.style.display = 'block';
                        }
                    }
                    document.getElementById('formErrorAlert').textContent = 'Validation error occurred. Please correct the fields marked in red.';
                } else if (error.data && error.data.message) {
                    document.getElementById('formErrorAlert').textContent = error.data.message;
                } else {
                    document.getElementById('formErrorAlert').textContent = 'A connection error occurred. Make sure your local server is running.';
                }
                
                document.getElementById('formErrorAlert').style.display = 'block';
            })
            .finally(() => {
                // Restore submit button state
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                btnText.textContent = 'Confirm Admission & Generate Invoice';
            });
        }

        // Reset Portal for another admission
        function resetPortal() {
            document.getElementById('admissionForm').reset();
            setTaxStatus('filer');
            updateRealtimeLedger();

            // Transition UI panels
            document.getElementById('successContainer').style.display = 'none';
            document.getElementById('portalContainer').style.display = 'grid';
        }
    </script>
</body>
</html>
