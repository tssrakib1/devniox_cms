<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderManager
{
    public function __construct(private readonly FinanceManager $finance) {}

    public function create(array $data, int $userId): Order
    {
        return DB::transaction(function () use ($data, $userId) {
            [$attributes, $items] = $this->prepare($data);
            $order = Order::create($attributes + ['created_by' => $userId, 'updated_by' => $userId]);
            $order->update(['order_number' => 'ORD-'.$order->order_date->format('Ym').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT)]);
            $order->items()->createMany($items);
            $this->event($order, 'created', 'Order created.', $userId, null, ['status' => $order->status->value, 'final_amount' => $order->final_amount]);
            ActivityLogService::log('orders', 'created', "Order {$order->order_number} created.", $order, null, $this->snapshot($order), $userId);
            if (! empty($data['sync_finance']) && (float) $order->paid_amount > 0) {
                $this->finance->syncOrderPayment($order, isset($data['finance_income_category_id']) ? (int) $data['finance_income_category_id'] : null, $userId, true);
            }

            return $order->fresh(['items']);
        });
    }

    public function update(Order $order, array $data, int $userId): Order
    {
        return DB::transaction(function () use ($order, $data, $userId) {
            $before = $this->snapshot($order);
            [$attributes, $items] = $this->prepare($data);
            $order->update($attributes + ['updated_by' => $userId]);
            $order->items()->delete();
            $order->items()->createMany($items);
            $fresh = $order->fresh(['items']);
            $after = $this->snapshot($fresh);

            $this->event($fresh, 'updated', 'Order information updated.', $userId, $before, $after);
            ActivityLogService::log('orders', 'updated', "Order {$fresh->order_number} updated.", $fresh, $before, $after, $userId);

            if ($before['status'] !== $after['status']) {
                $this->recordStatusChange($fresh, $before['status'], $after['status'], $userId);
            }
            $paymentKeys = ['total_amount', 'discount', 'final_amount', 'paid_amount', 'due_amount', 'payment_status', 'payment_method'];
            if (Arr::only($before, $paymentKeys) !== Arr::only($after, $paymentKeys)) {
                $oldPayment = Arr::only($before, $paymentKeys);
                $newPayment = Arr::only($after, $paymentKeys);
                $this->event($fresh, 'payment_updated', 'Order payment tracking updated.', $userId, $oldPayment, $newPayment);
                ActivityLogService::log('orders', 'payment_updated', "Payment tracking updated for {$fresh->order_number}.", $fresh, $oldPayment, $newPayment, $userId);
            }
            $this->finance->syncOrderPayment($fresh, isset($data['finance_income_category_id']) ? (int) $data['finance_income_category_id'] : null, $userId, ! empty($data['sync_finance']) && (float) $fresh->paid_amount > 0);

            return $fresh;
        });
    }

    public function changeStatus(Order $order, OrderStatus $status, int $userId): void
    {
        if ($order->status === $status) {
            return;
        }

        DB::transaction(function () use ($order, $status, $userId) {
            $old = $order->status->value;
            $order->update(['status' => $status, 'updated_by' => $userId]);
            $this->recordStatusChange($order, $old, $status->value, $userId);
        });
    }

    public function addNote(Order $order, string $note, int $userId): void
    {
        DB::transaction(function () use ($order, $note, $userId) {
            $order->notes()->create(['author_id' => $userId, 'note' => $note]);
            $this->event($order, 'note_added', 'Internal note added.', $userId);
            ActivityLogService::log('orders', 'note_added', "Internal note added to {$order->order_number}.", $order, null, ['note_length' => mb_strlen($note)], $userId);
        });
    }

    /** @return list<OrderAttachment> */
    public function addAttachments(Order $order, array $files, ?string $label, int $userId): array
    {
        $paths = [];
        try {
            return DB::transaction(function () use ($order, $files, $label, $userId, &$paths) {
                $attachments = [];
                foreach ($files as $file) {
                    $metadata = $this->storeFile($order, $file);
                    $paths[] = $metadata['file_path'];
                    $attachment = $order->attachments()->create($metadata + ['uploaded_by' => $userId, 'label' => $label]);
                    $attachments[] = $attachment;
                    $this->event($order, 'attachment_added', "Attachment {$attachment->original_name} added.", $userId, null, ['attachment_id' => $attachment->id]);
                    ActivityLogService::log('orders', 'attachment_added', "Attachment added to {$order->order_number}.", $order, null, ['attachment_id' => $attachment->id, 'name' => $attachment->original_name], $userId);
                }

                return $attachments;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }
    }

    public function replaceAttachment(Order $order, OrderAttachment $attachment, UploadedFile $file, ?string $label, int $userId): void
    {
        $new = $this->storeFile($order, $file);
        $oldPath = $attachment->media_asset_id ? null : $attachment->file_path;
        if ($attachment->media_asset_id) {
            app(MediaLibraryService::class)->release($attachment, 'attachment');
        }
        try {
            DB::transaction(function () use ($order, $attachment, $new, $label, $userId) {
                $old = $attachment->only(['id', 'original_name', 'mime_type', 'file_size']);
                $attachment->update($new + ['media_asset_id' => null, 'label' => $label, 'uploaded_by' => $userId]);
                $this->event($order, 'attachment_replaced', 'Attachment replaced.', $userId, $old, $attachment->only(array_keys($old)));
                ActivityLogService::log('orders', 'attachment_added', "Attachment replaced on {$order->order_number}.", $order, $old, $attachment->only(array_keys($old)), $userId);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($new['file_path']);
            throw $exception;
        }
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }

    public function removeAttachment(Order $order, OrderAttachment $attachment, int $userId): void
    {
        $path = $attachment->media_asset_id ? null : $attachment->file_path;
        if ($attachment->media_asset_id) {
            app(MediaLibraryService::class)->release($attachment, 'attachment');
        }
        DB::transaction(function () use ($order, $attachment, $userId) {
            $old = $attachment->only(['id', 'original_name', 'mime_type', 'file_size']);
            $attachment->delete();
            $this->event($order, 'attachment_removed', "Attachment {$old['original_name']} removed.", $userId, $old);
            ActivityLogService::log('orders', 'attachment_removed', "Attachment removed from {$order->order_number}.", $order, $old, null, $userId);
        });
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }

    public function archive(Order $order, int $userId): void
    {
        DB::transaction(function () use ($order, $userId) {
            $order->update(['archived_at' => now(), 'updated_by' => $userId]);
            $this->event($order, 'archived', 'Order archived.', $userId);
            ActivityLogService::log('orders', 'archived', "Order {$order->order_number} archived.", $order, null, ['archived_at' => $order->archived_at], $userId);
        });
    }

    public function delete(Order $order, int $userId): void
    {
        $this->event($order, 'deleted', 'Order deleted.', $userId);
        ActivityLogService::log('orders', 'deleted', "Order {$order->order_number} deleted.", $order, $this->snapshot($order), null, $userId);
        $order->delete();
    }

    private function prepare(array $data): array
    {
        $items = [];
        $totalCents = 0;
        foreach (array_values($data['items']) as $index => $item) {
            $unitCents = $this->toCents($item['unit_price']);
            $discountCents = $this->toCents($item['discount']);
            $grossCents = $unitCents * (int) $item['quantity'];
            if ($discountCents > $grossCents) {
                throw ValidationException::withMessages(["items.{$index}.discount" => 'The item discount cannot exceed its gross amount.']);
            }
            $lineCents = $grossCents - $discountCents;
            $totalCents += $lineCents;
            $items[] = Arr::only($item, ['name', 'type', 'quantity']) + ['unit_price' => $this->money($unitCents), 'discount' => $this->money($discountCents), 'total' => $this->money($lineCents), 'sort_order' => $index];
        }

        $discountCents = $this->toCents($data['discount']);
        if ($discountCents > $totalCents) {
            throw ValidationException::withMessages(['discount' => 'The order discount cannot exceed the item total.']);
        }
        $finalCents = $totalCents - $discountCents;
        $paidCents = $this->toCents($data['paid_amount']);
        if ($paidCents > $finalCents) {
            throw ValidationException::withMessages(['paid_amount' => 'The paid amount cannot exceed the final amount.']);
        }
        if ($paidCents > 0 && empty($data['payment_method'])) {
            throw ValidationException::withMessages(['payment_method' => 'Select a payment method when recording a payment.']);
        }
        $dueCents = $finalCents - $paidCents;
        $paymentStatus = $paidCents === 0 ? PaymentStatus::Unpaid : ($dueCents === 0 ? PaymentStatus::Paid : PaymentStatus::Partial);

        $attributes = Arr::only($data, ['lead_id', 'customer_name', 'company_name', 'email', 'phone', 'whatsapp', 'address', 'order_date', 'expected_delivery_date', 'priority', 'status', 'source', 'payment_method']);
        if ($data['source'] === OrderSource::Direct->value) {
            $attributes['lead_id'] = null;
        }
        if ($paidCents === 0) {
            $attributes['payment_method'] = null;
        }
        $attributes += ['total_amount' => $this->money($totalCents), 'discount' => $this->money($discountCents), 'final_amount' => $this->money($finalCents), 'paid_amount' => $this->money($paidCents), 'due_amount' => $this->money($dueCents), 'payment_status' => $paymentStatus];

        return [$attributes, $items];
    }

    private function recordStatusChange(Order $order, string $old, string $new, int $userId): void
    {
        $description = 'Status changed from '.str($old)->headline().' to '.str($new)->headline().'.';
        $this->event($order, 'status_changed', $description, $userId, ['status' => $old], ['status' => $new]);
        ActivityLogService::log('orders', 'status_changed', "{$order->order_number}: {$description}", $order, ['status' => $old], ['status' => $new], $userId);
        if ($new === OrderStatus::Completed->value) {
            ActivityLogService::log('orders', 'completed', "Order {$order->order_number} completed.", $order, ['status' => $old], ['status' => $new], $userId);
        } elseif ($new === OrderStatus::Cancelled->value) {
            ActivityLogService::log('orders', 'cancelled', "Order {$order->order_number} cancelled.", $order, ['status' => $old], ['status' => $new], $userId);
        }
    }

    private function event(Order $order, string $type, string $description, int $userId, ?array $old = null, ?array $new = null): void
    {
        $order->events()->create(['actor_id' => $userId, 'event_type' => $type, 'description' => $description, 'old_values' => $old, 'new_values' => $new, 'occurred_at' => now()]);
        DB::afterCommit(fn () => Cache::forget('admin.dashboard.stats.v2'));
    }

    private function snapshot(Order $order): array
    {
        return ['customer_name' => $order->customer_name, 'priority' => $order->priority->value, 'status' => $order->status->value, 'total_amount' => $order->total_amount, 'discount' => $order->discount, 'final_amount' => $order->final_amount, 'paid_amount' => $order->paid_amount, 'due_amount' => $order->due_amount, 'payment_status' => $order->payment_status->value, 'payment_method' => $order->payment_method?->value];
    }

    private function storeFile(Order $order, UploadedFile $file): array
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $path = $file->storeAs('order-attachments/'.$order->id, Str::uuid().'.'.$extension, 'local');
        if (! is_string($path)) {
            throw new \RuntimeException('The attachment could not be stored.');
        }
        $originalName = mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())), 0, 255);

        return ['file_path' => $path, 'original_name' => $originalName, 'mime_type' => (string) $file->getMimeType(), 'file_size' => (int) $file->getSize()];
    }

    private function toCents(mixed $amount): int
    {
        return (int) round((float) $amount * 100);
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}
