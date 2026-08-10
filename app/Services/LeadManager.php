<?php

namespace App\Services;

use App\Enums\LeadPriority;
use App\Enums\LeadStatus;
use App\Enums\LeadType;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LeadManager
{
    public function createContact(array $data, Request $request): Lead
    {
        return $this->create(LeadType::Contact, $data, $request, fn (Lead $lead) => $lead->contactMessage()->create(Arr::only($data, ['subject', 'message', 'website'])));
    }

    public function createDemo(array $data, Request $request): Lead
    {
        return $this->create(LeadType::Demo, $data, $request, fn (Lead $lead) => $lead->demoRequest()->create(Arr::only($data, ['item_type', 'product_id', 'service_id', 'preferred_date', 'preferred_time', 'meeting_type', 'message'])));
    }

    public function createQuote(array $data, Request $request): Lead
    {
        $path = null;
        try {
            return $this->create(LeadType::Quote, $data, $request, function (Lead $lead) use ($data, &$path) {
                $details = Arr::only($data, ['business_type', 'item_type', 'product_id', 'service_id', 'portfolio_project_id', 'budget', 'timeline', 'requirement_details']);
                if (($file = $data['attachment'] ?? null)instanceof UploadedFile) {
                    $path = $file->store('lead-attachments/'.$lead->id, 'local');
                    if (! is_string($path)) {
                        throw new \RuntimeException('The attachment could not be stored.');
                    }
                    $originalName = mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())), 0, 255);
                    $details += ['attachment_path' => $path, 'attachment_original_name' => $originalName, 'attachment_mime' => $file->getMimeType(), 'attachment_size' => $file->getSize()];
                }$lead->quoteRequest()->create($details);
            });
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }throw $e;
        }
    }

    private function create(LeadType $type, array $data, Request $request, callable $details): Lead
    {
        $lead = DB::transaction(function () use ($type, $data, $request, $details) {
            $lead = Lead::create(['type' => $type, 'status' => LeadStatus::New, 'priority' => LeadPriority::Medium, 'name' => $data['name'], 'company' => $data['company'] ?? null, 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'ip_address' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000), 'referrer' => mb_substr((string) $request->headers->get('referer'), 0, 2048) ?: null, 'landing_url' => mb_substr((string) ($data['landing_url'] ?? $request->fullUrl()), 0, 2048), 'submitted_at' => now()]);
            $details($lead);
            $lead->statusHistory()->create(['from_status' => null, 'to_status' => LeadStatus::New, 'changed_at' => now()]);
            $this->event($lead, 'created', ucfirst(str_replace('_', ' ', $type->value)).' enquiry received.');
            foreach (User::where('is_active', true)->get() as $user) {
                $user->notifications()->create(['lead_id' => $lead->id, 'type' => 'lead.created', 'title' => 'New '.ucfirst($type->value).' enquiry', 'message' => $lead->name.' submitted a new enquiry.', 'action_url' => route('admin.leads.show', $lead)]);
            }
            ActivityLogService::log('communication', 'created', ucfirst($type->value)." communication #{$lead->id} created.", $lead, null, ['type' => $type->value, 'status' => LeadStatus::New->value]);
            DB::afterCommit(fn () => Cache::forget('admin.dashboard.stats.v2'));

            return $lead->fresh();
        });
        $this->sendOptionalEmail($lead);

        return $lead;
    }

    public function markViewed(Lead $lead, int $user): void
    {
        if ($lead->status === LeadStatus::New) {
            $this->changeStatus($lead, LeadStatus::Viewed, $user);
        }
    }

    public function update(Lead $lead, LeadStatus $status, LeadPriority $priority, int $user): void
    {
        if ($lead->status !== $status) {
            $this->changeStatus($lead, $status, $user);
        }if ($lead->priority !== $priority) {
            $from = $lead->priority;
            $lead->update(['priority' => $priority]);
            $this->event($lead, 'priority_changed', 'Priority changed to '.str_replace('_', ' ', $priority->value).'.', $user);
            ActivityLogService::log('leads', 'priority_changed', "Lead {$lead->name} priority changed from {$from->value} to {$priority->value}.", $lead, ['priority' => $from->value], ['priority' => $priority->value], $user);
        }
    }

    public function changeStatus(Lead $lead, LeadStatus $status, int $user): void
    {
        if ($lead->status === $status) {
            return;
        }

        DB::transaction(function () use ($lead, $status, $user) {
            $from = $lead->status;
            $closed = in_array($status, [LeadStatus::Closed, LeadStatus::Completed, LeadStatus::Cancelled, LeadStatus::Rejected, LeadStatus::Converted], true);
            $lead->update(['status' => $status, 'closed_at' => $closed ? ($lead->closed_at ?? now()) : null]);
            $lead->statusHistory()->create(['changed_by' => $user, 'from_status' => $from, 'to_status' => $status, 'changed_at' => now()]);
            $this->event($lead, 'status_changed', 'Status changed from '.str_replace('_', ' ', $from->value).' to '.str_replace('_', ' ', $status->value).'.', $user);
            ActivityLogService::log('leads', 'status_changed', "Lead {$lead->name} status changed from {$from->value} to {$status->value}.", $lead, ['status' => $from->value], ['status' => $status->value], $user);
        });
    }

    public function addNote(Lead $lead, string $note, int $user): void
    {
        DB::transaction(function () use ($lead, $note, $user) {
            $lead->notes()->create(['author_id' => $user, 'note' => $note]);
            $this->event($lead, 'note_added', 'Private note added.', $user);
            ActivityLogService::log('leads', 'note_added', "Private note added to lead {$lead->name}.", $lead, null, ['note_length' => mb_strlen($note)], $user);
        });
    }

    public function archive(Lead $lead, int $user): void
    {
        DB::transaction(function () use ($lead, $user) {
            $this->event($lead, 'archived', 'Lead archived.', $user);
            $lead->delete();
            ActivityLogService::log('communication', 'deleted', "Communication #{$lead->id} archived.", $lead, null, ['deleted_at' => now()], $user);
        });
    }

    public function restore(Lead $lead, int $user): void
    {
        DB::transaction(function () use ($lead, $user) {
            $lead->restore();
            $this->event($lead, 'restored', 'Lead restored.', $user);
            ActivityLogService::log('communication', 'restored', "Communication #{$lead->id} restored.", $lead, ['deleted_at' => $lead->deleted_at], ['deleted_at' => null], $user);
        });
    }

    public function delete(Lead $lead, int $user): void
    {
        $attachment = $lead->quoteRequest?->attachment_path;
        $communicationAttachments = $lead->attachments()->pluck('file_path')->all();
        DB::transaction(function () use ($lead, $user) {
            $this->event($lead, 'deleted', 'Lead permanently deleted.', $user);
            ActivityLogService::log('communication', 'deleted', "Communication #{$lead->id} permanently deleted.", $lead, ['id' => $lead->id, 'type' => $lead->type->value], null, $user);
            $lead->forceDelete();
        });
        if ($attachment) {
            Storage::disk('local')->delete($attachment);
        }
        Storage::disk('local')->delete($communicationAttachments);
    }

    private function event(Lead $lead, string $type, string $description, ?int $actor = null): void
    {
        $lead->events()->create(['actor_id' => $actor, 'event_type' => $type, 'description' => $description, 'occurred_at' => now()]);
    }

    private function sendOptionalEmail(Lead $lead): void
    {
        $to = config('mail.lead_notifications_to');
        if (! $to) {
            return;
        }try {
            Mail::raw("New {$lead->type->value} enquiry from {$lead->name} ({$lead->email}).", fn ($message) => $message->to($to)->subject('New DevNiox lead #'.$lead->id));
        } catch (Throwable $e) {
            report($e);
        }
    }
}

