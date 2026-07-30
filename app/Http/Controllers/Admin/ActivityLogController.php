<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:180'],
            'user' => ['nullable', 'integer', 'exists:users,id'],
            'module' => ['nullable', 'string', 'max:50'],
            'action' => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $logs = ActivityLog::query()->with('user')
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $inner) => $inner->where('description', 'like', "%{$search}%")->orWhere('module', 'like', "%{$search}%")->orWhere('action', 'like', "%{$search}%")))
            ->when($filters['user'] ?? null, fn (Builder $query, int $user) => $query->where('user_id', $user))
            ->when($filters['module'] ?? null, fn (Builder $query, string $module) => $query->where('module', $module))
            ->when($filters['action'] ?? null, fn (Builder $query, string $action) => $query->where('action', $action))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')->latest('id')->paginate(30)->withQueryString();

        return view('admin.activity-logs.index', [
            'logs' => $logs,
            'users' => User::where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'modules' => ActivityLog::query()->distinct()->orderBy('module')->pluck('module'),
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
        ]);
    }

    public function show(ActivityLog $activityLog): View
    {
        $this->authorize('view', $activityLog);

        return view('admin.activity-logs.show', ['log' => $activityLog->load('user')]);
    }
}
