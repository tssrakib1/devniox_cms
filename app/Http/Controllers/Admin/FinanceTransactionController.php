<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinanceTransactionSource;
use App\Enums\FinanceTransactionStatus;
use App\Enums\FinanceTransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkFinanceTransactionRequest;
use App\Http\Requests\Admin\FinanceTransactionRequest;
use App\Http\Requests\Admin\ReplaceTransactionAttachmentRequest;
use App\Http\Requests\Admin\StoreTransactionAttachmentRequest;
use App\Models\ExpenseCategory;
use App\Models\FinanceTransaction;
use App\Models\IncomeCategory;
use App\Models\TransactionAttachment;
use App\Services\FinanceDashboardService;
use App\Services\FinanceManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceTransactionController extends Controller
{
    public function index(Request $request, FinanceDashboardService $dashboard): View
    {
        $this->authorize('viewAny', FinanceTransaction::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'type' => ['nullable', 'in:income,expense'], 'category' => ['nullable', 'regex:/^(income|expense):\d+$/'], 'status' => ['nullable', 'in:pending,completed,cancelled'], 'payment_method' => ['nullable', 'in:cash,bank,mobile_banking,card,cheque,other'], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'archived' => ['nullable', 'in:0,1'], 'trashed' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:transaction_number,title,type,amount,transaction_date,status,updated_at'], 'direction' => ['nullable', 'in:asc,desc']]);
        $category = isset($filters['category']) ? explode(':', $filters['category'], 2) : null;
        $transactions = FinanceTransaction::query()->with(['incomeCategory', 'expenseCategory'])->when(($filters['trashed'] ?? null) === '1', fn (Builder $query) => $query->onlyTrashed())
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->search($search))
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($category, fn (Builder $query) => $query->where($category[0].'_category_id', (int) $category[1]))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['payment_method'] ?? null, fn (Builder $query, string $method) => $query->where('payment_method', $method))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when(($filters['archived'] ?? null) === '1', fn (Builder $query) => $query->whereNotNull('archived_at'), fn (Builder $query) => $query->whereNull('archived_at'))
            ->orderBy($filters['sort'] ?? 'transaction_date', $filters['direction'] ?? 'desc')->orderByDesc('id')->paginate(30)->withQueryString();

        return view('admin.finance.transactions.index', ['transactions' => $transactions, 'stats' => $dashboard->stats()] + $this->formData());
    }

    public function create(): View
    {
        $this->authorize('create', FinanceTransaction::class);
        $transaction = new FinanceTransaction(['type' => FinanceTransactionType::Income, 'source' => FinanceTransactionSource::Manual, 'transaction_date' => today(), 'status' => FinanceTransactionStatus::Completed]);

        return view('admin.finance.transactions.form', compact('transaction') + $this->formData());
    }

    public function store(FinanceTransactionRequest $request, FinanceManager $manager): RedirectResponse
    {
        $transaction = $manager->create($request->validated(), $request->user()->id);

        return redirect()->route('admin.finance.transactions.show', $transaction)->with('success', 'Transaction created.');
    }

    public function show(FinanceTransaction $transaction): View
    {
        $this->authorize('view', $transaction);
        $transaction->load(['incomeCategory', 'expenseCategory', 'attachments.uploader', 'creator', 'updater']);

        return view('admin.finance.transactions.show', compact('transaction'));
    }

    public function edit(FinanceTransaction $transaction): View
    {
        $this->authorize('update', $transaction);
        abort_if($transaction->reference_type === 'order', 403, 'Order-linked transactions must be updated from the order.');

        return view('admin.finance.transactions.form', compact('transaction') + $this->formData());
    }

    public function update(FinanceTransactionRequest $request, FinanceTransaction $transaction, FinanceManager $manager): RedirectResponse
    {
        abort_if($transaction->reference_type === 'order', 403, 'Order-linked transactions must be updated from the order.');
        $manager->update($transaction, $request->validated(), $request->user()->id);

        return redirect()->route('admin.finance.transactions.show', $transaction)->with('success', 'Transaction updated.');
    }

    public function destroy(FinanceTransaction $transaction, FinanceManager $manager): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $manager->delete($transaction, auth()->id());

        return redirect()->route('admin.finance.transactions.index')->with('success', 'Transaction deleted.');
    }

    public function bulk(BulkFinanceTransactionRequest $request, FinanceManager $manager): RedirectResponse
    {
        $action = $request->validated('action');
        $transactions = FinanceTransaction::query()->when($action === 'restore', fn (Builder $query) => $query->onlyTrashed())->whereKey($request->validated('transaction_ids'))->get();
        foreach ($transactions as $transaction) {
            $this->authorizeForUser($request->user(), $action, $transaction);
            match ($action) {
                'archive' => $manager->archive($transaction, $request->user()->id), 'restore' => $manager->restore($transaction, $request->user()->id), default => $manager->delete($transaction, $request->user()->id)
            };
        }

        return back()->with('success', 'Bulk action completed.');
    }

    public function attachments(StoreTransactionAttachmentRequest $request, FinanceTransaction $transaction, FinanceManager $manager): RedirectResponse
    {
        $manager->addAttachments($transaction, $request->file('files'), $request->validated('label'), $request->user()->id);

        return back()->with('success', 'Attachments uploaded.');
    }

    public function replaceAttachment(ReplaceTransactionAttachmentRequest $request, FinanceTransaction $transaction, TransactionAttachment $attachment, FinanceManager $manager): RedirectResponse
    {
        abort_unless($attachment->finance_transaction_id === $transaction->id, 404);
        $manager->replaceAttachment($transaction, $attachment, $request->file('file'), $request->validated('label'), $request->user()->id);

        return back()->with('success', 'Attachment replaced.');
    }

    public function deleteAttachment(FinanceTransaction $transaction, TransactionAttachment $attachment, FinanceManager $manager): RedirectResponse
    {
        abort_unless($attachment->finance_transaction_id === $transaction->id, 404);
        $this->authorize('manageAttachment', $transaction);
        $manager->removeAttachment($transaction, $attachment, auth()->id());

        return back()->with('success', 'Attachment removed.');
    }

    public function downloadAttachment(FinanceTransaction $transaction, TransactionAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->finance_transaction_id === $transaction->id, 404);
        $this->authorize('view', $transaction);
        $disk = $attachment->mediaAsset?->disk ?? 'local';
        abort_unless(Storage::disk($disk)->exists($attachment->file_path), 404);

        return Storage::disk($disk)->download($attachment->file_path, $attachment->original_name, ['Content-Type' => $attachment->mime_type]);
    }

    private function formData(): array
    {
        return ['incomeCategories' => IncomeCategory::active()->orderBy('name')->get(), 'expenseCategories' => ExpenseCategory::active()->orderBy('name')->get(), 'types' => FinanceTransactionType::cases(), 'sources' => FinanceTransactionSource::cases(), 'statuses' => FinanceTransactionStatus::cases(), 'paymentMethods' => FinancePaymentMethod::cases()];
    }
}
