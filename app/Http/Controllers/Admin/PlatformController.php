<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SettingGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformRequest;
use App\Models\Platform;
use App\Models\Setting;
use App\Services\ActivityLogService;
use App\Services\ManagedImageService;
use App\Services\SettingsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function index(Request $request, SettingsService $settings): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'in:active,inactive']]);
        $platforms = Platform::query()
            ->when($filters['search'] ?? null, fn (Builder $query, string $search) => $query->where(fn (Builder $inner) => $inner->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->orderBy('display_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $parent = collect($settings->all())->filter(fn ($value, $key) => str_starts_with($key, 'company.parent_'));

        return view('admin.platforms.index', compact('platforms', 'parent'));
    }

    public function updateParent(Request $request, SettingsService $settings, ManagedImageService $images): RedirectResponse
    {
        $data = $request->validate([
            'parent_company_name' => ['required', 'string', 'max:255'], 'parent_company_badge' => ['nullable', 'string', 'max:100'],
            'parent_company_description' => ['nullable', 'string'], 'parent_highlight_1_title' => ['nullable', 'string', 'max:255'],
            'parent_highlight_1_description' => ['nullable', 'string'], 'parent_highlight_2_title' => ['nullable', 'string', 'max:255'],
            'parent_highlight_2_description' => ['nullable', 'string'], 'parent_highlight_3_title' => ['nullable', 'string', 'max:255'],
            'parent_highlight_3_description' => ['nullable', 'string'], 'parent_company_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
        ]);
        $file = $request->file('parent_company_logo');
        unset($data['parent_company_logo']);
        if ($file) {
            $old = $settings->get('company.parent_company_logo');
            $data['parent_company_logo'] = $images->store($file, 'company', 1200, 1200);
        }
        foreach ($data as $key => $value) {
            Setting::where('group', SettingGroup::Company->value)->where('key', $key)->update(['value' => $value]);
        }
        $settings->forget();
        if ($file && filled($old ?? null)) {
            $images->delete($old);
        }
        ActivityLogService::log('settings', 'updated', 'Parent company settings updated.');

        return back()->with('success', 'Parent company settings saved.');
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
