<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformRequest;
use App\Models\Platform;
use App\Services\ActivityLogService;
use App\Services\ManagedImageService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'in:active,inactive']]);
        $platforms = Platform::query()
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.platforms.index', compact('platforms'));
    }

    public function create(): View
    {
        return view('admin.platforms.form', ['platform' => new Platform(['status' => 'active', 'open_in_new_tab' => true])]);
    }

    public function store(PlatformRequest $request, ManagedImageService $images): RedirectResponse
    {
        $data = $request->validated();
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        if ($request->hasFile('logo')) {
            $data['logo'] = $images->store($request->file('logo'), 'platforms', 800, 800);
        }
        $platform = Platform::create($data);
        ActivityLogService::log('cms', 'created', "Platform {$platform->name} created.", $platform);

        return redirect()->route('admin.platforms.edit', $platform)->with('success', 'Platform created.');
    }

    public function edit(Platform $platform): View
    {
        return view('admin.platforms.form', compact('platform'));
    }

    public function update(PlatformRequest $request, Platform $platform, ManagedImageService $images): RedirectResponse
    {
        $data = $request->validated();
        $data['open_in_new_tab'] = $request->boolean('open_in_new_tab');
        $data['updated_by'] = $request->user()->id;
        if ($request->hasFile('logo')) {
            $images->delete($platform->logo);
            $data['logo'] = $images->store($request->file('logo'), 'platforms', 800, 800);
        }
        $old = $platform->only(['name', 'slug', 'status', 'display_order']);
        $platform->update($data);
        ActivityLogService::log('cms', 'updated', "Platform {$platform->name} updated.", $platform, $old, $platform->fresh()->only(array_keys($old)));

        return back()->with('success', 'Platform updated.');
    }

    public function destroy(Platform $platform, ManagedImageService $images): RedirectResponse
    {
        ActivityLogService::log('cms', 'deleted', "Platform {$platform->name} deleted.", $platform, $platform->only(['name', 'slug', 'status']));
        $platform->delete();

        return redirect()->route('admin.platforms.index')->with('success', 'Platform deleted.');
    }
}
