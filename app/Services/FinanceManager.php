<?php

namespace App\Services;

use App\Enums\FinancePaymentMethod;
use App\Enums\FinanceTransactionSource;
use App\Enums\FinanceTransactionStatus;
use App\Enums\FinanceTransactionType;
use App\Enums\PaymentMethod as OrderPaymentMethod;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\TransactionAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class FinanceManager
{
    public function create(array $data, int $userId): FinanceTransaction
    {
        return DB::transaction(function () use ($data, $userId) {
            $transaction = FinanceTransaction::create($this->prepare($data) + ['created_by' => $userId, 'updated_by' => $userId]);
            $transaction->update(['transaction_number' => $this->number($transaction)]);
            ActivityLogService::log('finance', 'transaction_created', "Transaction {$transaction->transaction_number} created.", $transaction, null, $this->snapshot($transaction), $userId);
            DB::afterCommit(fn () => FinanceDashboardService::forget());

            return $transaction->fresh();
        });
    }

    public function update(FinanceTransaction $transaction, array $data, int $userId): FinanceTransaction
    {
        return DB::transaction(function () use ($transaction, $data, $userId) {
            $old = $this->snapshot($transaction);
            $transaction->update($this->prepare($data) + ['updated_by' => $userId]);
            $fresh = $transaction->fresh();
            ActivityLogService::log('finance', 'transaction_updated', "Transaction {$fresh->transaction_number} updated.", $fresh, $old, $this->snapshot($fresh), $userId);
            DB::afterCommit(fn () => FinanceDashboardService::forget());

            return $fresh;
        });
    }

    public function archive(FinanceTransaction $transaction, int $userId): void
    {
        $transaction->update(['archived_at' => now(), 'updated_by' => $userId]);
        ActivityLogService::log('finance', 'transaction_archived', "Transaction {$transaction->transaction_number} archived.", $transaction, null, ['archived_at' => $transaction->archived_at], $userId);
        FinanceDashboardService::forget();
    }

    public function delete(FinanceTransaction $transaction, int $userId): void
    {
        ActivityLogService::log('finance', 'transaction_deleted', "Transaction {$transaction->transaction_number} deleted.", $transaction, $this->snapshot($transaction), null, $userId);
        $transaction->delete();
        FinanceDashboardService::forget();
    }

    public function restore(FinanceTransaction $transaction, int $userId): void
    {
        $transaction->restore();
        ActivityLogService::log('finance', 'transaction_restored', "Transaction {$transaction->transaction_number} restored.", $transaction, null, $this->snapshot($transaction), $userId);
        FinanceDashboardService::forget();
    }

    public function syncOrderPayment(Order $order, ?int $incomeCategoryId, int $userId, bool $allowCreate): ?FinanceTransaction
    {
        $transaction = FinanceTransaction::withTrashed()->where('reference_type', 'order')->where('reference_id', $order->id)->first();
        if (! $transaction && ! $allowCreate) {
            return null;
        }
        if (! $transaction && ! $incomeCategoryId) {
            throw ValidationException::withMessages(['finance_income_category_id' => 'Select an income category to create the linked finance transaction.']);
        }

        return DB::transaction(function () use ($order, $incomeCategoryId, $userId, $transaction) {
            $amount = (float) $order->paid_amount;
            if (! $transaction) {
                $transaction = FinanceTransaction::create([
                    'type' => FinanceTransactionType::Income, 'source' => FinanceTransactionSource::Order,
                    'reference_type' => 'order', 'reference_id' => $order->id,
                    'reference' => $order->order_number.' — '.$order->customer_name,
                    'income_category_id' => $incomeCategoryId, 'title' => 'Payment for '.$order->order_number,
                    'description' => 'Order payment from '.$order->customer_name.'.', 'amount' => number_format($amount, 2, '.', ''),
                    'payment_method' => $this->mapOrderPaymentMethod($order->payment_method),
                    'transaction_date' => today(), 'status' => $amount > 0 ? FinanceTransactionStatus::Completed : FinanceTransactionStatus::Cancelled,
                    'archived_at' => $amount > 0 ? null : now(), 'created_by' => $userId, 'updated_by' => $userId,
                ]);
                $transaction->update(['transaction_number' => $this->number($transaction)]);
                ActivityLogService::log('finance', 'transaction_created', "Order payment transaction {$transaction->transaction_number} created.", $transaction, null, $this->snapshot($transaction), $userId);
            } else {
                if ($transaction->trashed()) {
                    $transaction->restore();
                }
                $old = $this->snapshot($transaction);
                $transaction->update(['income_category_id' => $incomeCategoryId ?: $transaction->income_category_id, 'reference' => $order->order_number.' — '.$order->customer_name, 'title' => 'Payment for '.$order->order_number, 'description' => 'Order payment from '.$order->customer_name.'.', 'amount' => number_format($amount, 2, '.', ''), 'payment_method' => $this->mapOrderPaymentMethod($order->payment_method), 'status' => $amount > 0 ? FinanceTransactionStatus::Completed : FinanceTransactionStatus::Cancelled, 'archived_at' => $amount > 0 ? null : now(), 'updated_by' => $userId]);
                ActivityLogService::log('finance', $amount > 0 ? 'transaction_updated' : 'transaction_archived', "Order payment transaction {$transaction->transaction_number} synchronized.", $transaction, $old, $this->snapshot($transaction), $userId);
            }
            DB::afterCommit(fn () => FinanceDashboardService::forget());

            return $transaction->fresh();
        });
    }

    public function addAttachments(FinanceTransaction $transaction, array $files, ?string $label, int $userId): void
    {
        $paths = [];
        try {
            DB::transaction(function () use ($transaction, $files, $label, $userId, &$paths) {
                foreach ($files as $file) {
                    $metadata = $this->storeFile($transaction, $file);
                    $paths[] = $metadata['file_path'];
                    $transaction->attachments()->create($metadata + ['uploaded_by' => $userId, 'label' => $label]);
                }
                $this->refreshAttachmentCount($transaction);
                ActivityLogService::log('finance', 'transaction_updated', "Attachments added to {$transaction->transaction_number}.", $transaction, null, ['attachment_count' => $transaction->attachment_count], $userId);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }
    }

    public function replaceAttachment(FinanceTransaction $transaction, TransactionAttachment $attachment, UploadedFile $file, ?string $label, int $userId): void
    {
        $new = $this->storeFile($transaction, $file);
        $oldPath = $attachment->media_asset_id ? null : $attachment->file_path;
        if ($attachment->media_asset_id) {
            app(MediaLibraryService::class)->release($attachment, 'attachment');
        }
        try {
            $attachment->update($new + ['media_asset_id' => null, 'label' => $label, 'uploaded_by' => $userId]);
            ActivityLogService::log('finance', 'transaction_updated', "Attachment replaced on {$transaction->transaction_number}.", $transaction, null, ['attachment_id' => $attachment->id], $userId);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($new['file_path']);
            throw $exception;
        }
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
    }

    public function removeAttachment(FinanceTransaction $transaction, TransactionAttachment $attachment, int $userId): void
    {
        $path = $attachment->media_asset_id ? null : $attachment->file_path;
        if ($attachment->media_asset_id) {
            app(MediaLibraryService::class)->release($attachment, 'attachment');
        }
        $attachment->delete();
        $this->refreshAttachmentCount($transaction);
        if ($path) {
            Storage::disk('local')->delete($path);
        }
        ActivityLogService::log('finance', 'transaction_updated', "Attachment removed from {$transaction->transaction_number}.", $transaction, null, ['attachment_count' => $transaction->attachment_count], $userId);
    }

    private function prepare(array $data): array
    {
        $attributes = Arr::only($data, ['type', 'source', 'income_category_id', 'expense_category_id', 'title', 'description', 'amount', 'payment_method', 'transaction_date', 'status', 'reference']);
        $attributes['amount'] = number_format(round((float) $data['amount'] * 100) / 100, 2, '.', '');
        if ($data['type'] === FinanceTransactionType::Income->value) {
            $attributes['expense_category_id'] = null;
        } else {
            $attributes['income_category_id'] = null;
        }

        return $attributes;
    }

    private function snapshot(FinanceTransaction $transaction): array
    {
        return ['type' => $transaction->type->value, 'source' => $transaction->source->value, 'category_id' => $transaction->income_category_id ?: $transaction->expense_category_id, 'title' => $transaction->title, 'amount' => $transaction->amount, 'payment_method' => $transaction->payment_method->value, 'transaction_date' => $transaction->transaction_date->toDateString(), 'status' => $transaction->status->value, 'reference' => $transaction->reference];
    }

    private function number(FinanceTransaction $transaction): string
    {
        return 'TXN-'.$transaction->transaction_date->format('Ym').'-'.str_pad((string) $transaction->id, 4, '0', STR_PAD_LEFT);
    }

    private function mapOrderPaymentMethod(?OrderPaymentMethod $method): FinancePaymentMethod
    {
        return match ($method) {
            OrderPaymentMethod::Cash => FinancePaymentMethod::Cash,
            OrderPaymentMethod::Bank => FinancePaymentMethod::Bank,
            OrderPaymentMethod::Bkash, OrderPaymentMethod::Nagad, OrderPaymentMethod::Rocket => FinancePaymentMethod::MobileBanking,
            OrderPaymentMethod::Card => FinancePaymentMethod::Card,
            default => FinancePaymentMethod::Other,
        };
    }

    private function storeFile(FinanceTransaction $transaction, UploadedFile $file): array
    {
        $extension = strtolower($file->extension() ?: $file->getClientOriginalExtension());
        $path = $file->storeAs('transaction-attachments/'.$transaction->id, Str::uuid().'.'.$extension, 'local');
        if (! is_string($path)) {
            throw new \RuntimeException('The attachment could not be stored.');
        }
        $originalName = mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())), 0, 255);

        return ['file_path' => $path, 'original_name' => $originalName, 'mime_type' => (string) $file->getMimeType(), 'file_size' => (int) $file->getSize()];
    }

    private function refreshAttachmentCount(FinanceTransaction $transaction): void
    {
        $transaction->update(['attachment_count' => $transaction->attachments()->count()]);
    }
}
