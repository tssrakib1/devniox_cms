<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index', ['notifications' => auth()->user()->notifications()->latest()->paginate(20)]);
    }

    public function read(int $notification): RedirectResponse
    {
        $notification = auth()->user()->notifications()->findOrFail($notification);
        $notification->update(['read_at' => now()]);
        ActivityLogService::log('notifications', 'read', 'Notification marked as read.', $notification);
        $target = $notification->action_url;
        $relative = is_string($target) && str_starts_with($target, '/') && ! str_starts_with($target, '//');
        $sameOrigin = is_string($target) && str_starts_with($target, url('/').'/');

        return redirect($relative || $sameOrigin ? $target : route('admin.notifications.index'));
    }

    public function readAll()
    {
        auth()->user()->notifications()->unread()->update(['read_at' => now()]);
        ActivityLogService::log('notifications', 'read_all', 'All notifications marked as read.');

        return back()->with('success', 'Notifications marked as read.');
    }

    public function destroy(int $notification)
    {
        $notification = auth()->user()->notifications()->findOrFail($notification);
        ActivityLogService::log('notifications', 'deleted', 'Notification deleted.', $notification, $notification->only(['type', 'title']));
        $notification->delete();

        return back()->with('success', 'Notification removed.');
    }
}
