<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderItemType;
use App\Enums\OrderPriority;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkOrderActionRequest;
use App\Http\Requests\Admin\ReplaceOrderAttachmentRequest;
use App\Http\Requests\Admin\StoreOrderAttachmentRequest;
use App\Http\Requests\Admin\StoreOrderNoteRequest;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\IncomeCategory;
use App\Models\Lead;
use App\Models\Order;
use App\Models\OrderAttachment;
use App\Services\OrderManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'string', 'max:40'],
            'priority' => ['nullable', 'string', 'max:20'], 'payment_status' => ['nullable', 'string', 'max:20'],
            'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'archived' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:order_number,customer_name,order_date,expected_delivery_date,priority,status,final_amount,paid_amount,due_amount,updated_at'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $orders = Order::query()->withCount('items')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->search($search))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $query, string $priority) => $query->where('priority', $priority))
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('order_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('order_date', '<=', $date))
            ->when(($filters['archived'] ?? null) === '1', fn (Builder $query) => $query->whereNotNull('archived_at'), fn (Builder $query) => $query->whereNull('archived_at'))
            ->orderBy($filters['sort'] ?? 'updated_at', $filters['direction'] ?? 'desc')->paginate(25)->withQueryString();

        return view('admin.orders.index', compact('orders') + $this->enumData());
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Order::class);
        $order = new Order(['order_date' => today(), 'priority' => OrderPriority::Medium, 'status' => OrderStatus::Pending, 'source' => OrderSource::Direct, 'discount' => 0, 'paid_amount' => 0]);
        if ($request->integer('lead')) {
            $lead = Lead::findOrFail($request->integer('lead'));
            $order->fill(['lead_id' => $lead->id, 'customer_name' => $lead->name, 'company_name' => $lead->company, 'email' => $lead->email, 'phone' => $lead->phone, 'source' => OrderSource::Lead]);
        }

        return view('admin.orders.form', $this->formData($order));
    }

    public function store(StoreOrderRequest $request, OrderManager $manager): RedirectResponse
    {
        $order = $manager->create($request->validated(), $request->user()->id);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order created.');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load(['lead', 'items', 'attachments.uploader', 'notes.author', 'events.actor', 'creator', 'updater']);

        return view('admin.orders.show', compact('order') + $this->enumData());
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);
        $order->load('items');

        return view('admin.orders.form', $this->formData($order));
    }

    public function update(UpdateOrderRequest $request, Order $order, OrderManager $manager): RedirectResponse
    {
        $manager->update($order, $request->validated(), $request->user()->id);

        return redirect()->route('admin.orders.show', $order)->with('success', 'Order updated.');
    }

    public function destroy(Order $order, OrderManager $manager): RedirectResponse
    {
        $this->authorize('delete', $order);
        $manager->delete($order, auth()->id());

        return redirect()->route('admin.orders.index')->with('success', 'Order deleted.');
    }

    public function bulk(BulkOrderActionRequest $request, OrderManager $manager): RedirectResponse
    {
        $orders = Order::whereKey($request->validated('order_ids'))->get();
        foreach ($orders as $order) {
            if ($request->validated('action') === 'status') {
                $this->authorizeForUser($request->user(), 'update', $order);
                $manager->changeStatus($order, OrderStatus::from($request->validated('status')), $request->user()->id);
            } elseif ($request->validated('action') === 'archive') {
                $this->authorizeForUser($request->user(), 'archive', $order);
                $manager->archive($order, $request->user()->id);
            } else {
                $this->authorizeForUser($request->user(), 'delete', $order);
                $manager->delete($order, $request->user()->id);
            }
        }

        return back()->with('success', 'Bulk action completed.');
    }

    public function note(StoreOrderNoteRequest $request, Order $order, OrderManager $manager): RedirectResponse
    {
        $manager->addNote($order, $request->validated('note'), $request->user()->id);

        return back()->with('success', 'Internal note added.');
    }

    public function attachments(StoreOrderAttachmentRequest $request, Order $order, OrderManager $manager): RedirectResponse
    {
        $manager->addAttachments($order, $request->file('files'), $request->validated('label'), $request->user()->id);

        return back()->with('success', 'Attachments uploaded.');
    }

    public function replaceAttachment(ReplaceOrderAttachmentRequest $request, Order $order, OrderAttachment $attachment, OrderManager $manager): RedirectResponse
    {
        abort_unless($attachment->order_id === $order->id, 404);
        $manager->replaceAttachment($order, $attachment, $request->file('file'), $request->validated('label'), $request->user()->id);

        return back()->with('success', 'Attachment replaced.');
    }

    public function deleteAttachment(Order $order, OrderAttachment $attachment, OrderManager $manager): RedirectResponse
    {
        abort_unless($attachment->order_id === $order->id, 404);
        $this->authorize('manageAttachment', $order);
        $manager->removeAttachment($order, $attachment, auth()->id());

        return back()->with('success', 'Attachment removed.');
    }

    public function downloadAttachment(Order $order, OrderAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->order_id === $order->id, 404);
        $this->authorize('view', $order);
        $disk = $attachment->mediaAsset?->disk ?? 'local';
        abort_unless(Storage::disk($disk)->exists($attachment->file_path), 404);

        return Storage::disk($disk)->download($attachment->file_path, $attachment->original_name, ['Content-Type' => $attachment->mime_type]);
    }

    private function formData(Order $order): array
    {
        return compact('order') + $this->enumData() + ['leads' => Lead::latest('submitted_at')->limit(500)->get(['id', 'name', 'company', 'email']), 'financeIncomeCategories' => IncomeCategory::active()->orderBy('name')->get()];
    }

    private function enumData(): array
    {
        return ['statuses' => OrderStatus::cases(), 'priorities' => OrderPriority::cases(), 'sources' => OrderSource::cases(), 'itemTypes' => OrderItemType::cases(), 'paymentStatuses' => PaymentStatus::cases(), 'paymentMethods' => PaymentMethod::cases()];
    }
}
