<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkLeadActionRequest;
use App\Http\Requests\Admin\ConvertQuoteToOrderRequest;
use App\Http\Requests\Admin\ReplaceCommunicationAttachmentRequest;
use App\Http\Requests\Admin\StoreCommunicationAttachmentRequest;
use App\Http\Requests\Admin\StoreCommunicationReplyRequest;
use App\Http\Requests\Admin\StoreLeadNoteRequest;
use App\Http\Requests\Admin\UpdateLeadRequest;
use App\Models\CommunicationAttachment;
use App\Models\Lead;
use App\Models\Product;
use App\Models\User;
use App\Services\CommunicationManager;
use App\Services\LeadManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Request $r, ?LeadType $fixedType = null): View
    {
        $this->authorize('viewAny', Lead::class);
        $f = $r->validate(['search' => ['nullable', 'string', 'max:180'], 'type' => ['nullable', 'in:contact,demo,quote'], 'status' => ['nullable', Rule::enum(LeadStatus::class)], 'priority' => ['nullable', 'in:low,medium,high,urgent'], 'assigned_to' => ['nullable', 'integer', 'exists:users,id'], 'product_id' => ['nullable', 'integer', 'exists:products,id'], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'trashed' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:id,name,type,status,priority,created_at'], 'direction' => ['nullable', 'in:asc,desc']]);
        $type = $fixedType?->value ?? ($f['type'] ?? null);
        $leads = Lead::query()->with(['assignee', 'contactMessage', 'demoRequest.product', 'demoRequest.service', 'quoteRequest.product', 'quoteRequest.service'])->when(($f['trashed'] ?? null) === '1', fn ($q) => $q->onlyTrashed())->when($f['search'] ?? null, fn ($q, $s) => $q->search($s))->when($type, fn ($q, $v) => $q->where('type', $v))->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($f['priority'] ?? null, fn ($q, $v) => $q->where('priority', $v))->when($f['assigned_to'] ?? null, fn ($q, $v) => $q->where('assigned_to', $v))->when($f['product_id'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q->whereHas('demoRequest', fn ($q) => $q->where('product_id', $v))->orWhereHas('quoteRequest', fn ($q) => $q->where('product_id', $v))))->when($f['date_from'] ?? null, fn ($q, $v) => $q->whereDate('submitted_at', '>=', $v))->when($f['date_to'] ?? null, fn ($q, $v) => $q->whereDate('submitted_at', '<=', $v))->orderBy($f['sort'] ?? 'created_at', $f['direction'] ?? 'desc')->paginate(25)->withQueryString();

        return view('admin.leads.index', ['leads' => $leads, 'fixedType' => $fixedType, 'statuses' => LeadStatus::cases(), 'priorities' => LeadPriority::cases(), 'administrators' => User::where('is_active', true)->orderBy('name')->get(['id', 'name']), 'products' => Product::orderBy('name')->get(['id', 'name'])]);
    }

    public function contacts(Request $r): View
    {
        return $this->index($r, LeadType::Contact);
    }

    public function demos(Request $r): View
    {
        return $this->index($r, LeadType::Demo);
    }

    public function quotes(Request $r): View
    {
        return $this->index($r, LeadType::Quote);
    }

    public function show(Lead $lead, LeadManager $m, CommunicationManager $communications): View
    {
        $this->authorize('view', $lead);
        $m->markViewed($lead, $this->userId());
        $communications->markRead($lead, $this->userId());
        $lead->load(['assignee', 'contactMessage', 'demoRequest.product', 'demoRequest.service', 'quoteRequest.product', 'quoteRequest.service', 'notes.author', 'statusHistory.author', 'events.actor', 'notifications', 'replies.administrator', 'replies.attachments', 'attachments.uploader', 'convertedOrder']);

        return view('admin.leads.show', ['lead' => $lead, 'statuses' => LeadStatus::cases(), 'priorities' => LeadPriority::cases(), 'administrators' => User::where('is_active', true)->orderBy('name')->get(['id', 'name'])]);
    }

    public function update(UpdateLeadRequest $r, Lead $lead, LeadManager $m, CommunicationManager $communications): RedirectResponse
    {
        $m->update($lead, LeadStatus::from($r->validated('status')), LeadPriority::from($r->validated('priority')), $this->userId());
        $communications->assign($lead, $r->validated('assigned_to'), $this->userId());

        return back()->with('success', 'Lead updated.');
    }

    public function note(StoreLeadNoteRequest $r, Lead $lead, LeadManager $m): RedirectResponse
    {
        $m->addNote($lead, $r->validated('note'), $this->userId());

        return back()->with('success', 'Private note added.');
    }

    public function reply(StoreCommunicationReplyRequest $r, Lead $lead, CommunicationManager $m): RedirectResponse
    {
        $m->reply($lead, $r->validated(), $this->userId());

        return back()->with('success', 'Reply recorded.');
    }

    public function storeAttachment(StoreCommunicationAttachmentRequest $r, Lead $lead, CommunicationManager $m): RedirectResponse
    {
        $m->addAttachment($lead, $r->file('file'), $r->validated('label'), $this->userId());

        return back()->with('success', 'Attachment uploaded.');
    }

    public function replaceAttachment(ReplaceCommunicationAttachmentRequest $r, Lead $lead, CommunicationAttachment $attachment, CommunicationManager $m): RedirectResponse
    {
        abort_unless($attachment->lead_id === $lead->id, 404);
        $m->replaceAttachment($lead, $attachment, $r->file('file'), $r->validated('label'), $this->userId());

        return back()->with('success', 'Attachment replaced.');
    }

    public function removeAttachment(Lead $lead, CommunicationAttachment $attachment, CommunicationManager $m): RedirectResponse
    {
        $this->authorize('update', $lead);
        abort_unless($attachment->lead_id === $lead->id, 404);
        $m->removeAttachment($lead, $attachment, $this->userId());

        return back()->with('success', 'Attachment removed.');
    }

    public function downloadAttachment(Lead $lead, CommunicationAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $lead);
        $disk = $attachment->mediaAsset?->disk ?? 'local';
        abort_unless($attachment->lead_id === $lead->id && Storage::disk($disk)->exists($attachment->file_path), 404);

        return Storage::disk($disk)->download($attachment->file_path, $attachment->original_name);
    }

    public function convert(ConvertQuoteToOrderRequest $r, Lead $lead, CommunicationManager $m): RedirectResponse
    {
        $order = $m->convertQuote($lead, $r->validated(), $this->userId());

        return redirect()->route('admin.orders.show', $order)->with('success', 'Quote converted to order.');
    }

    public function destroy(Lead $lead, LeadManager $m): RedirectResponse
    {
        $this->authorize('delete', $lead);
        $m->archive($lead, $this->userId());

        return redirect()->route('admin.leads.index')->with('success', 'Lead deleted.');
    }

    public function restore(int $lead, LeadManager $m): RedirectResponse
    {
        $item = Lead::onlyTrashed()->findOrFail($lead);
        $this->authorize('restore', $item);
        $m->restore($item, $this->userId());

        return back()->with('success', 'Lead restored.');
    }

    public function bulk(BulkLeadActionRequest $r, LeadManager $m): RedirectResponse
    {
        $action = $r->validated('action');
        $items = Lead::query()->when(in_array($action, ['restore', 'delete'], true), fn ($q) => $q->onlyTrashed())->whereKey($r->validated('lead_ids'))->get();
        DB::transaction(function () use ($items, $action, $r, $m) {
            foreach ($items as $lead) {
                if ($action === 'restore') {
                    $this->authorizeForUser($r->user(), 'restore', $lead);
                    $m->restore($lead, $this->userId());
                } elseif ($action === 'archive') {
                    $this->authorizeForUser($r->user(), 'delete', $lead);
                    $m->archive($lead, $this->userId());
                } elseif ($action === 'delete') {
                    $this->authorizeForUser($r->user(), 'forceDelete', $lead);
                    $m->delete($lead, $this->userId());
                } else {
                    $this->authorizeForUser($r->user(), 'update', $lead);
                    $status = match ($action) {
                        'viewed' => LeadStatus::Viewed,'contacted' => LeadStatus::Contacted,default => LeadStatus::from($r->validated('status'))
                    };
                    $m->changeStatus($lead, $status, $this->userId());
                }
            }
        });

        return back()->with('success', 'Bulk action completed.');
    }

    public function attachment(Lead $lead): StreamedResponse
    {
        $this->authorize('view', $lead);
        $quote = $lead->quoteRequest()->firstOrFail();
        abort_unless($quote->attachment_path && Storage::disk('local')->exists($quote->attachment_path), 404);

        return Storage::disk('local')->download($quote->attachment_path, $quote->attachment_original_name);
    }

    private function userId(): int
    {
        return (int) auth()->id();
    }
}
