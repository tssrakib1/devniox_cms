<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkServiceActionRequest;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\ActivityLogService;
use App\Services\ServiceManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Service::class);
        $f = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'in:draft,published,archived'], 'category' => ['nullable', 'integer', 'exists:service_categories,id'], 'featured' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:name,status,is_featured,updated_at'], 'direction' => ['nullable', 'in:asc,desc']]);
        $services = Service::with('category')->when($f['search'] ?? null, fn (Builder $q, string $s) => $q->where(fn (Builder $q) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")->orWhere('short_description', 'like', "%{$s}%")))->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($f['category'] ?? null, fn ($q, $v) => $q->where('service_category_id', $v))->when(array_key_exists('featured', $f), fn ($q) => $q->where('is_featured', (bool) $f['featured']))->orderBy($f['sort'] ?? 'updated_at', $f['direction'] ?? 'desc')->paginate(20)->withQueryString();

        return view('admin.services.index', ['services' => $services, 'categories' => ServiceCategory::orderBy('name')->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('admin.services.form', $this->formData(new Service));
    }

    public function store(StoreServiceRequest $request, ServiceManager $manager): RedirectResponse
    {
        $data = $request->validated();
        if (! $request->user()->isAdmin()) {
            $data['is_featured'] = false;
        }if (($data['status'] ?? null) === ServiceStatus::Archived->value && ! $request->user()->isAdmin()) {
            abort(403);
        }$service = $manager->create($data, $request->user()->id);
        ActivityLogService::log('services', 'created', "Service {$service->name} created.", $service, null, $service->only(['name', 'slug', 'status']));

        return redirect()->route('admin.services.edit', $service)->with('success', 'Service created.');
    }

    public function edit(Service $service): View
    {
        $this->authorize('update', $service);
        $service->load(['benefits', 'processSteps', 'features', 'technologies', 'deliverables', 'galleryImages', 'faqs', 'seo']);

        return view('admin.services.form', $this->formData($service));
    }

    public function update(UpdateServiceRequest $request, Service $service, ServiceManager $manager): RedirectResponse
    {
        $status = ServiceStatus::from($request->validated('status'));
        if ($status === ServiceStatus::Archived) {
            $this->authorize('archive', $service);
        } elseif ($status !== $service->status) {
            $this->authorize('publish', $service);
        }$data = $request->validated();
        if (! $request->user()->isAdmin()) {
            $data['is_featured'] = $service->is_featured;
        }$old = $service->only(['name', 'slug', 'is_featured']) + ['status' => $service->status->value];
        $manager->update($service, $data, $request->user()->id);
        $fresh = $service->fresh();
        ActivityLogService::log('services', 'updated', "Service {$fresh->name} updated.", $fresh, $old, $fresh->only(array_keys($old)));
        if ($old['status'] !== $fresh->status->value) {
            ActivityLogService::log('services', $fresh->status->value === 'published' ? 'published' : ($fresh->status->value === 'archived' ? 'archived' : 'drafted'), "Service {$fresh->name} status changed to {$fresh->status->value}.", $fresh, ['status' => $old['status']], ['status' => $fresh->status->value]);
        }

        return back()->with('success', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);
        ActivityLogService::log('services', 'deleted', "Service {$service->name} deleted.", $service, $service->only(['name', 'slug', 'status']));
        $service->delete();

        return redirect()->route('admin.services.index')->with('success', 'Service deleted.');
    }

    public function bulk(BulkServiceActionRequest $request): RedirectResponse
    {
        $services = Service::whereKey($request->validated('service_ids'))->get();
        $action = $request->validated('action');
        DB::transaction(function () use ($services, $action, $request) {
            foreach ($services as $s) {
                $before = $s->only(['status', 'is_featured']);
                if (in_array($action, ['publish', 'draft'], true)) {
                    $this->authorizeForUser($request->user(), 'publish', $s);
                    $status = $action === 'publish' ? ServiceStatus::Published : ServiceStatus::Draft;
                    $s->update(['status' => $status, 'published_at' => $status === ServiceStatus::Published ? ($s->published_at ?? now()) : null, 'updated_by' => $request->user()->id]);
                } elseif ($action === 'archive') {
                    $this->authorizeForUser($request->user(), 'archive', $s);
                    $s->update(['status' => ServiceStatus::Archived, 'published_at' => null]);
                } elseif ($action === 'delete') {
                    $this->authorizeForUser($request->user(), 'delete', $s);
                    $s->delete();
                } else {
                    $this->authorizeForUser($request->user(), 'feature', $s);
                    $s->update(['is_featured' => $action === 'feature']);
                }
                ActivityLogService::log('services', $action === 'publish' ? 'published' : ($action === 'archive' ? 'archived' : ($action === 'delete' ? 'deleted' : ($action === 'feature' ? 'featured' : ($action === 'unfeature' ? 'unfeatured' : 'drafted')))), "Bulk action {$action} applied to service {$s->name}.", $s, $before, $s->fresh()?->only(['status', 'is_featured']));
            }
        });

        return back()->with('success', 'Bulk action completed.');
    }

    private function formData(Service $service): array
    {
        return ['service' => $service, 'categories' => ServiceCategory::active()->orderBy('sort_order')->orderBy('name')->get(), 'statuses' => ServiceStatus::cases()];
    }
}
