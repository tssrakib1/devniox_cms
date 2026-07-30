<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinanceTransactionStatus;
use App\Enums\FinanceTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinanceReportRequest;
use App\Models\ExpenseCategory;
use App\Models\FinanceTransaction;
use App\Models\IncomeCategory;
use App\Services\FinanceReportService;
use App\Services\SimplePdfService;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    public function index(FinanceReportRequest $request, FinanceReportService $reports): View
    {
        $this->authorize('viewAny', FinanceTransaction::class);
        $filters = $request->validated();
        $query = $reports->query($filters);

        return view('admin.finance.reports.index', ['transactions' => (clone $query)->paginate(50)->withQueryString(), 'summary' => $reports->summary($query), 'dates' => $reports->dates($filters), 'incomeCategories' => IncomeCategory::orderBy('name')->get(), 'expenseCategories' => ExpenseCategory::orderBy('name')->get(), 'types' => FinanceTransactionType::cases(), 'statuses' => FinanceTransactionStatus::cases()]);
    }

    public function csv(FinanceReportRequest $request, FinanceReportService $reports): StreamedResponse
    {
        $this->authorize('viewAny', FinanceTransaction::class);
        $transactions = $reports->query($request->validated())->cursor();

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Transaction Number', 'Date', 'Type', 'Category', 'Title', 'Reference', 'Amount', 'Payment Method', 'Status']);
            foreach ($transactions as $transaction) {
                fputcsv($handle, [$transaction->transaction_number, $transaction->transaction_date->toDateString(), $transaction->type->value, $transaction->category?->name, $transaction->title, $transaction->reference, $transaction->amount, $transaction->payment_method->value, $transaction->status->value]);
            }
            fclose($handle);
        }, 'devniox-finance-report-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(FinanceReportRequest $request, FinanceReportService $reports, SimplePdfService $pdf): Response
    {
        $this->authorize('viewAny', FinanceTransaction::class);
        $query = $reports->query($request->validated());
        $summary = $reports->summary($query);
        $lines = ['DevNiox Finance Report', 'Generated: '.now()->format('M j, Y g:i A'), str_repeat('-', 92), 'Completed income: '.number_format($summary['income'], 2).' | Completed expense: '.number_format($summary['expense'], 2).' | Net: '.number_format($summary['net'], 2), str_repeat('-', 92)];
        foreach ($query->cursor() as $transaction) {
            $lines[] = implode(' | ', [$transaction->transaction_date->format('Y-m-d'), $transaction->transaction_number, strtoupper($transaction->type->value), str($transaction->title)->limit(34), number_format((float) $transaction->amount, 2), strtoupper($transaction->status->value)]);
        }

        return response($pdf->make($lines), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="devniox-finance-report-'.now()->format('Ymd-His').'.pdf"']);
    }
}
