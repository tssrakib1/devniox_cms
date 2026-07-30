@extends('layouts.admin')
@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('content')
<div class="d-flex justify-content-end mb-3"><form method="post" action="{{ route('admin.notifications.read-all') }}">@csrf @method('PATCH')<button class="btn btn-outline-primary" type="submit">Mark all as read</button></form></div>
<x-card>
    @forelse($notifications as $note)
        <div class="d-flex flex-wrap align-items-start gap-3 border-bottom py-3 {{ $note->read_at ? 'opacity-75' : '' }}">
            <i class="bi bi-bell fs-4" aria-hidden="true"></i>
            <div class="flex-grow-1 notification-message"><strong>{{ $note->title }}</strong><p class="mb-1">{{ $note->message }}</p><time class="text-muted" datetime="{{ $note->created_at->toAtomString() }}">{{ $note->created_at->diffForHumans() }}</time></div>
            <div class="d-flex gap-2">
                @unless($note->read_at)<form method="post" action="{{ route('admin.notifications.read', $note) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-primary" type="submit">Read</button></form>@endunless
                <form method="post" action="{{ route('admin.notifications.destroy', $note) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" aria-label="Delete notification"><i class="bi bi-trash" aria-hidden="true"></i></button></form>
            </div>
        </div>
    @empty
        <x-empty-state>No notifications have been created.</x-empty-state>
    @endforelse
    <div class="mt-3">{{ $notifications->links() }}</div>
</x-card>
@endsection
