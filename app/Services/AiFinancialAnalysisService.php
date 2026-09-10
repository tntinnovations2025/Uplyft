<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiFinancialAnalysisService
{
    protected string $groqApiKey;
    protected string $groqModel;

    public function __construct()
    {
        $this->groqApiKey = (string) config('services.groq.api_key', '');
        $this->groqModel = (string) config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    /**
     * Generate financial summary and AI analysis report for specified date range.
     */
    public function generateReport(int $instituteId, string $period = 'monthly', ?string $customStart = null, ?string $customEnd = null): array
    {
        [$startDate, $endDate, $periodTitle] = $this->resolveDates($period, $customStart, $customEnd);

        // Fetch transactions within range
        $transactions = FinancialTransaction::where('institute_id', $instituteId)
            ->whereBetween('transaction_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->with('accountHead')
            ->orderBy('transaction_date', 'asc')
            ->get();

        // Also fetch paid student fee invoices as income if available
        $feeIncome = Invoice::where('institute_id', $instituteId)
            ->where('status', 'paid')
            ->whereBetween('updated_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->sum('amount_pkr');

        $directIncome = $transactions->where('type', 'income')->sum('amount');
        $totalIncome = $directIncome + $feeIncome;
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');

        $netProfitLoss = $totalIncome - $totalExpense;
        $profitMargin = $totalIncome > 0 ? round(($netProfitLoss / $totalIncome) * 100, 2) : 0;

        // Group income & expense by account heads
        $expenseByHead = [];
        $incomeByHead = [];

        if ($feeIncome > 0) {
            $incomeByHead['Student Tuition & Fee Vouchers'] = $feeIncome;
        }

        foreach ($transactions as $t) {
            $headName = $t->accountHead->name ?? 'General';
            if ($t->type === 'expense') {
                $expenseByHead[$headName] = ($expenseByHead[$headName] ?? 0) + $t->amount;
            } else {
                $incomeByHead[$headName] = ($incomeByHead[$headName] ?? 0) + $t->amount;
            }
        }

        arsort($expenseByHead);
        arsort($incomeByHead);

        $topExpenseHead = key($expenseByHead) ?? 'N/A';
        $topIncomeHead = key($incomeByHead) ?? 'N/A';

        // Calculate 6-month historical monthly trends for graphical plotting (Up/Down trajectory)
        $monthlyTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $mTx = FinancialTransaction::where('institute_id', $instituteId)
                ->whereBetween('transaction_date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->get();

            $mFeeIncome = Invoice::where('institute_id', $instituteId)
                ->where('status', 'paid')
                ->whereBetween('updated_at', [$mStart->copy()->startOfDay(), $mEnd->copy()->endOfDay()])
                ->sum('amount_pkr');

            $mInc = $mTx->where('type', 'income')->sum('amount') + $mFeeIncome;
            $mExp = $mTx->where('type', 'expense')->sum('amount');
            $mNet = $mInc - $mExp;

            $monthlyTrends[] = [
                'month' => $mStart->format('M Y'),
                'short_month' => $mStart->format('M'),
                'income' => $mInc,
                'expense' => $mExp,
                'net' => $mNet,
                'is_up' => $mNet >= 0,
            ];
        }

        // Prepare data payload for AI model
        $financialMetrics = [
            'period_title' => $periodTitle,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_income' => number_format($totalIncome, 2),
            'raw_total_income' => $totalIncome,
            'total_expense' => number_format($totalExpense, 2),
            'raw_total_expense' => $totalExpense,
            'net_profit_loss' => number_format($netProfitLoss, 2),
            'raw_net_profit_loss' => $netProfitLoss,
            'is_profit' => $netProfitLoss >= 0,
            'profit_margin_pct' => $profitMargin,
            'top_expense_head' => $topExpenseHead,
            'top_income_head' => $topIncomeHead,
            'income_breakdown' => $incomeByHead,
            'expense_breakdown' => $expenseByHead,
            'transaction_count' => $transactions->count(),
            'monthly_trends' => $monthlyTrends,
        ];

        // Call Groq API or Fallback AI Engine
        $aiAnalysis = $this->callGroqAiAnalysis($financialMetrics);

        return [
            'metrics' => $financialMetrics,
            'ai_analysis' => $aiAnalysis,
            'raw_transactions' => $transactions,
        ];
    }

    protected function resolveDates(string $period, ?string $customStart, ?string $customEnd): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'weekly':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                $title = "Weekly Report (" . $start->format('M d') . " - " . $end->format('M d, Y') . ")";
                break;

            case 'monthly':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $title = "Monthly Report (" . $start->format('F Y') . ")";
                break;

            case 'annual':
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $title = "Annual Financial Report (" . $start->format('Y') . ")";
                break;

            case 'jan_jun':
                $start = Carbon::create($now->year, 1, 1)->startOfDay();
                $end = Carbon::create($now->year, 6, 30)->endOfDay();
                $title = "Semi-Annual Report (Jan - Jun " . $now->year . ")";
                break;

            case 'jul_dec':
                $start = Carbon::create($now->year, 7, 1)->startOfDay();
                $end = Carbon::create($now->year, 12, 31)->endOfDay();
                $title = "Semi-Annual Report (Jul - Dec " . $now->year . ")";
                break;

            case 'custom':
                $start = $customStart ? Carbon::parse($customStart)->startOfDay() : $now->copy()->startOfMonth();
                $end = $customEnd ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfMonth();
                $title = "Custom Period (" . $start->format('M d, Y') . " - " . $end->format('M d, Y') . ")";
                break;

            default:
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $title = "Monthly Financial Audit";
                break;
        }

        return [$start, $end, $title];
    }

    protected function callGroqAiAnalysis(array $data): string
    {
        if (empty($this->groqApiKey)) {
            return $this->generateHeuristicAiReport($data);
        }

        $systemPrompt = "You are an expert Chief Financial Officer (CFO) and AI Financial Analyst for Educational Institutes. Your job is to analyze the institute's income, expenses, utility bills, maintenance costs, and profit/loss data, providing clear, executive, actionable strategic guidance for the Principal and Accountant.";

        $incomeJson = json_encode($data['income_breakdown']);
        $expenseJson = json_encode($data['expense_breakdown']);

        $userPrompt = <<<PROMPT
Please provide a comprehensive AI Financial Analysis Report based on the following audit data:

Period: {$data['period_title']}
Date Range: {$data['start_date']} to {$data['end_date']}
Total Income: Rs. {$data['total_income']}
Total Expenses: Rs. {$data['total_expense']}
Net Profit/Loss: Rs. {$data['net_profit_loss']} (Margin: {$data['profit_margin_pct']}%)
Top Income Stream: {$data['top_income_head']}
Top Expense Head: {$data['top_expense_head']}
Total Transactions Logged: {$data['transaction_count']}

Income Breakdown by Head:
{$incomeJson}

Expense Breakdown by Head:
{$expenseJson}

Structure your response cleanly using GitHub-style Markdown with clear headings:
1. Executive Financial Summary & Profitability Health Check
2. Key Expense Drivers & Bill Consumption Analysis (Utilities, Maintenance, Salaries)
3. Risk Assessment & Cash Flow Warnings
4. Strategic Action Plan & Cost Optimization Recommendations
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->groqApiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(25)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->groqModel,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt],
                ],
                'temperature' => 0.4,
                'max_tokens' => 1800,
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if (!empty($content)) {
                    return $content;
                }
            }
        } catch (\Exception $e) {
            Log::warning("AiFinancialAnalysisService Groq call failed: " . $e->getMessage());
        }

        return $this->generateHeuristicAiReport($data);
    }

    protected function generateHeuristicAiReport(array $data): string
    {
        $statusStr = $data['is_profit'] ? "PROFITABLE (Net Margin: {$data['profit_margin_pct']}%)" : "DEFICIT / LOSS AUDIT";
        $netVal = $data['net_profit_loss'];
        $topExp = $data['top_expense_head'];
        $topInc = $data['top_income_head'];
        $currency = "Rs.";

        $incomeItemsHtml = "";
        if (!empty($data['income_breakdown'])) {
            foreach ($data['income_breakdown'] as $head => $amt) {
                $pct = $data['raw_total_income'] > 0 ? round(($amt / $data['raw_total_income']) * 100, 1) : 0;
                $formatted = number_format($amt, 2);
                $incomeItemsHtml .= "
                <div style='display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,0.02)'>
                    <div style='display:flex;align-items:center;gap:10px'>
                        <span style='font-size:16px;color:#059669'>📈</span>
                        <span style='font-weight:700;font-size:13.5px;color:#0f172a'>{$head}</span>
                    </div>
                    <div style='text-align:right'>
                        <span style='font-weight:800;font-size:14px;color:#059669'>{$currency} {$formatted}</span>
                        <span style='font-size:11px;color:#64748b;margin-left:6px;font-weight:700;background:#f1f5f9;padding:2px 8px;border-radius:6px'>{$pct}%</span>
                    </div>
                </div>";
            }
        } else {
            $incomeItemsHtml = "<div style='font-size:13px;color:#94a3b8;font-style:italic;padding:14px;text-align:center;background:#ffffff;border:1px dashed #cbd5e1;border-radius:12px'>No income entries logged for this audit period.</div>";
        }

        $expenseItemsHtml = "";
        if (!empty($data['expense_breakdown'])) {
            foreach ($data['expense_breakdown'] as $head => $amt) {
                $pct = $data['raw_total_expense'] > 0 ? round(($amt / $data['raw_total_expense']) * 100, 1) : 0;
                $formatted = number_format($amt, 2);
                $expenseItemsHtml .= "
                <div style='display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:10px;box-shadow:0 1px 3px rgba(0,0,0,0.02)'>
                    <div style='display:flex;align-items:center;gap:10px'>
                        <span style='font-size:16px;color:#e11d48'>💸</span>
                        <span style='font-weight:700;font-size:13.5px;color:#0f172a'>{$head}</span>
                    </div>
                    <div style='text-align:right'>
                        <span style='font-weight:800;font-size:14px;color:#e11d48'>{$currency} {$formatted}</span>
                        <span style='font-size:11px;color:#64748b;margin-left:6px;font-weight:700;background:#f1f5f9;padding:2px 8px;border-radius:6px'>{$pct}%</span>
                    </div>
                </div>";
            }
        } else {
            $expenseItemsHtml = "<div style='font-size:13px;color:#94a3b8;font-style:italic;padding:14px;text-align:center;background:#ffffff;border:1px dashed #cbd5e1;border-radius:12px'>No expense entries logged for this audit period.</div>";
        }

        $recommendation = $data['is_profit']
            ? "Your institute is maintaining positive financial health. Reinvest surplus funds into staff development, lab equipment, or emergency reserves."
            : "Immediate budget restructuring is recommended. Audit high-consumption utility bills, streamline maintenance vendors, and follow up on outstanding student fee vouchers.";

        $profitBg = $data['is_profit'] ? '#064e3b' : '#4c0519';
        $profitColor = $data['is_profit'] ? '#34d399' : '#fb7185';
        $profitBorder = $data['is_profit'] ? '#10b981' : '#f43f5e';

        return <<<HTML
<div class="structured-ai-report" style="display:flex;flex-direction:column;gap:22px">

    {{-- Executive Summary Card --}}
    <div style="background:linear-gradient(135deg, #0f172a 0%, #1e293b 100%);border-radius:20px;padding:24px;color:#ffffff;box-shadow:0 12px 30px -5px rgba(15,23,42,0.3)">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:18px;border-bottom:1px solid rgba(255,255,255,0.12);padding-bottom:14px">
            <div>
                <h3 style="font-family:'Outfit',sans-serif;font-size:19px;font-weight:800;color:#f8fafc;margin:0">
                    📋 Executive Financial Audit &amp; Profitability Check
                </h3>
                <p style="font-size:12.5px;color:#94a3b8;margin-top:4px">
                    Scope: {$data['period_title']} ({$data['start_date']} to {$data['end_date']})
                </p>
            </div>
            <span style="font-size:12px;font-weight:800;padding:6px 16px;border-radius:9999px;background:{$profitBg};color:{$profitColor};border:1.5px solid {$profitBorder}">
                {$statusStr}
            </span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px">
            <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:16px">
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px">Net Financial Impact</div>
                <div style="font-size:20px;font-weight:800;color:{$profitColor};margin-top:4px">{$currency} {$netVal}</div>
            </div>
            <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:16px">
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px">Top Revenue Driver</div>
                <div style="font-size:15px;font-weight:800;color:#f8fafc;margin-top:4px">{$topInc}</div>
            </div>
            <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:16px">
                <div style="font-size:11px;font-weight:800;text-transform:uppercase;color:#94a3b8;letter-spacing:0.5px">Primary Expense Driver</div>
                <div style="font-size:15px;font-weight:800;color:#fb7185;margin-top:4px">{$topExp}</div>
            </div>
        </div>
    </div>

    {{-- Two Column Breakdown Grid --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        
        {{-- Revenue Stream Breakdown --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:20px;padding:22px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px">
                <span style="font-size:20px">📈</span>
                <h4 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">Revenue &amp; Income Stream Breakdown</h4>
            </div>
            {$incomeItemsHtml}
        </div>

        {{-- Expense Breakdown --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:20px;padding:22px">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;border-bottom:1px solid #e2e8f0;padding-bottom:12px">
                <span style="font-size:20px">📉</span>
                <h4 style="font-family:'Outfit',sans-serif;font-size:16px;font-weight:800;color:#0f172a;margin:0">Expense &amp; Cost Head Breakdown</h4>
            </div>
            {$expenseItemsHtml}
        </div>
    </div>

    {{-- Strategic AI Recommendations Box --}}
    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:20px;padding:24px;box-shadow:0 4px 20px -2px rgba(0,0,0,0.04)">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;border-bottom:1px solid #e2e8f0;padding-bottom:14px">
            <span style="font-size:22px">💡</span>
            <h4 style="font-family:'Outfit',sans-serif;font-size:17px;font-weight:800;color:#0f172a;margin:0">AI Strategic CFO Action Plan &amp; Cost Controls</h4>
        </div>

        <div style="display:flex;flex-direction:column;gap:14px">
            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:16px 20px">
                <div style="font-size:14px;font-weight:800;color:#1e40af;display:flex;align-items:center;gap:8px">
                    <span>⚡</span> 1. Utility &amp; Consumption Audit
                </div>
                <p style="font-size:13px;color:#334155;margin-top:6px;line-height:1.6;margin-bottom:0">
                    Ensure all physical utility bills (Electricity, Internet, Water) are verified against meter readings and uploaded receipts before payment approval.
                </p>
            </div>

            <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:14px;padding:16px 20px">
                <div style="font-size:14px;font-weight:800;color:#92400e;display:flex;align-items:center;gap:8px">
                    <span>🛡️</span> 2. Primary Cost Driver Mitigation
                </div>
                <p style="font-size:13px;color:#334155;margin-top:6px;line-height:1.6;margin-bottom:0">
                    High expense head identified: <strong style="color:#d97706">{$topExp}</strong>. Monitor weekly consumption patterns to reduce operational overhead.
                </p>
            </div>

            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:14px;padding:16px 20px">
                <div style="font-size:14px;font-weight:800;color:#065f46;display:flex;align-items:center;gap:8px">
                    <span>🎯</span> 3. Cash Flow Action Plan
                </div>
                <p style="font-size:13px;color:#334155;margin-top:6px;line-height:1.6;margin-bottom:0">
                    {$recommendation}
                </p>
            </div>
        </div>
    </div>

</div>
HTML;
    }
}
