<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Enums\OrderPriority;
use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Models\CommunicationAttachment;
use App\Models\CommunicationReply;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CommunicationManager
{
    public function __construct(private readonly OrderManager $orders) {}

    public function assign(Lead $lead, ?int $administratorId, int $actorId): void
    {
        if ($lead->assigned_to === $administratorId) {
            return;
        }
        $old = $lead->assigned_to;
        DB::transaction(function () use ($lead, $administratorId, $actorId, $old) {
            $lead->update(['assigned_to' => $administratorId]);
            $this->event($lead, 'assigned', $administratorId ? 'Communication assigned to an administrator.' : 'Communication unassigned.', $actorId);
            ActivityLogService::log('communication', 'assigned', "Communication #{$lead->id} assignment changed.", $lead, ['assigned_to' => $old], ['assigned_to' => $administratorId], $actorId);
        });
    }

    public function markRead(Lead $lead, int $actorId): void
    {
        if ($lead->read_at) {
            return;
        }
        $lead->update(['read_at' => now()]);
        $this->event($lead, 'read', 'Communication read.', $actorId);
        $this->flushDashboard();
    }

    public function reply(Lead $lead, array $data, int $actorId): CommunicationReply
    {
        $stored = [];
        try {
            return DB::transaction(function () use ($lead, $data, $actorId, &$stored) {
                $reply = $lead->replies()->create(['administrator_id' => $actorId, 'direction' => 'outgoing', 'subject' => $data['subject'], 'message' => $data['message'], 'replied_at' => now()]);
                foreach ($data['attachments'] ?? [] as $file) {
                    $metadata = $this->store($lead, $file);
                    $stored[] = $metadata['file_path'];
                    $reply->attachments()->create($metadata + ['lead_id' => $lead->id, 'uploaded_by' => $actorId]);
                }
                $before = $lead->status->value;
                $lead->update(['status' => LeadStatus::Replied, 'read_at' => $lead->read_at ?? now(), 'replied_at' => now()]);
                $this->event($lead, 'replied', 'Administrator reply recorded.', $actorId);
                ActivityLogService::log('communication', 'replied', "Reply recorded for communication #{$lead->id}.", $lead, ['status' => $before], ['status' => LeadStatus::Replied->value, 'reply_id' => $reply->id], $actorId);
                $this->flushDashboard();

                return $reply;
            });
        } catch (Throwable $e) {
            Storage::disk('local')->delete($stored);
            throw $e;
        }
    }

    public function recordIncomingReply(Lead $lead, string $subject, string $message): CommunicationReply
    {
        return DB::transaction(function () use ($lead, $subject, $message) {
            $reply = $lead->replies()->create(['administrator_id' => null, 'direction' => 'incoming', 'subject' => $subject, 'message' => $message, 'replied_at' => now()]);
            $this->event($lead, 'reply_received', 'A reply was received from the contact.', null);
            foreach (User::where('is_active', true)->get() as $user) {
                $user->notifications()->create(['lead_id' => $lead->id, 'type' => 'communication.reply_received', 'title' => 'Reply received', 'message' => "{$lead->name} replied to communication #{$lead->id}.", 'action_url' => route('admin.leads.show', $lead)]);
            }
            $this->flushDashboard();

            return $reply;
        });
    }

    public function addAttachment(Lead $lead, UploadedFile $file, ?string $label, int $actorId): CommunicationAttachment
    {
        $metadata = $this->store($lead, $file);
        try {
            $attachment = $lead->attachments()->create($metadata + ['uploaded_by' => $actorId, 'label' => $label]);
            $this->event($lead, 'attachment_added', 'Private attachment added.', $actorId);
            ActivityLogService::log('communication', 'attachment_added', "Attachment added to communication #{$lead->id}.", $lead, null, ['attachment_id' => $attachment->id], $actorId);

            return $attachment;
        } catch (Throwable $e) {
            Storage::disk('local')->delete($metadata['file_path']);
            throw $e;
        }
    }

    public function replaceAttachment(Lead $lead, CommunicationAttachment $attachment, UploadedFile $file, ?string $label, int $actorId): void
    {
        $new = $this->store($lead, $file);
        $oldPath = $attachment->media_asset_id ? null : $attachment->file_path;
        if ($attachment->media_asset_id) {
            app(MediaLibraryService::class)->release($attachment, 'attachment');
        }
        $old = $attachment->only(['id', 'original_name', 'mime_type', 'file_size']);
        try {
            $attachment->update($new + ['media_asset_id' => null, 'label' => $label, 'uploaded_by' => $actorId]);
        } catch (Throwable $e) {
            Storage::disk('local')->delete($new['file_path']);
            throw $e;
        }
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
        $this->event($lead, 'attachment_replaced', 'Private attachment replaced.', $actorId);
        ActivityLogService::log('communication', 'attachment_replaced', "Attachment replaced on communication #{$lead->id}.", $lead, $old, $attachment->only(array_keys($old)), $actorId);
    }

    public function removeAttachment(Lead $lead, CommunicationAttachment $attachment, int $actorId): void
    {
        $path = $attachment->media_asset_id ? null : $attachment->file_path;
        if ($attachment->media_asset_id) {
            app(MediaLibraryService::class)->release($attachment, 'attachment');
        }
        $old = $attachment->only(['id', 'original_name']);
        $attachment->delete();
        if ($path) {
            Storage::disk('local')->delete($path);
        }
        $this->event($lead, 'attachment_removed', 'Private attachment removed.', $actorId);
        ActivityLogService::log('communication', 'attachment_removed', "Attachment removed from communication #{$lead->id}.", $lead, $old, null, $actorId);
    }

    public function convertQuote(Lead $lead, array $data, int $actorId): Order
    {
        if ($lead->converted_order_id) {
            throw ValidationException::withMessages(['lead' => 'This quote has already been converted.']);
        }

        return DB::transaction(function () use ($lead, $data, $actorId) {
            $order = $this->orders->create(['customer_name' => $lead->name, 'company_name' => $lead->company, 'email' => $lead->email, 'phone' => $lead->phone, 'whatsapp' => null, 'address' => null, 'order_date' => today()->toDateString(), 'expected_delivery_date' => $data['expected_delivery_date'] ?? null, 'priority' => OrderPriority::Medium->value, 'status' => OrderStatus::Pending->value, 'source' => OrderSource::Lead->value, 'lead_id' => $lead->id, 'discount' => 0, 'paid_amount' => 0, 'payment_method' => null, 'items' => [['name' => $data['item_name'], 'type' => $data['item_type'], 'quantity' => 1, 'unit_price' => $data['amount'], 'discount' => 0]]], $actorId);
            $old = $lead->status->value;
            $lead->update(['status' => LeadStatus::Converted, 'converted_at' => now(), 'converted_order_id' => $order->id, 'closed_at' => now()]);
            $this->event($lead, 'converted', "Quote converted to order {$order->order_number}.", $actorId);
            ActivityLogService::log('communication', 'converted', "Quote #{$lead->id} converted to {$order->order_number}.", $lead, ['status' => $old], ['status' => 'converted', 'order_id' => $order->id], $actorId);
            $this->flushDashboard();

            return $order;
        });
    }

    private function store(Lead $lead, UploadedFile $file): array
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $path = $file->storeAs('communication-attachments/'.$lead->id, Str::uuid().'.'.$extension, 'local');
        if (! is_string($path)) {
            throw new \RuntimeException('The attachment could not be stored.');
        }

        return ['file_path' => $path, 'original_name' => mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())), 0, 255), 'mime_type' => (string) $file->getMimeType(), 'file_size' => (int) $file->getSize()];
    }

    private function event(Lead $lead, string $type, string $description, ?int $actorId): void
    {
        $lead->events()->create(['actor_id' => $actorId, 'event_type' => $type, 'description' => $description, 'occurred_at' => now()]);
    }

    private function flushDashboard(): void
    {
        DB::afterCommit(fn () => Cache::forget('admin.dashboard.stats.v2'));
    }
}
