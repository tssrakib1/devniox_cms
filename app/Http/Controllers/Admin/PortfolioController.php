<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PortfolioStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkPortfolioActionRequest;
use App\Http\Requests\Admin\StorePortfolioProjectRequest;
use App\Http\Requests\Admin\UpdatePortfolioProjectRequest;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Services\ActivityLogService;
use App\Services\PortfolioManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $r): View
    {
        $this->authorize('viewAny', PortfolioProject::class);
        $f = $r->validate(['search' => ['nullable', 'string', 'max:180'], 'status' => ['nullable', 'in:draft,published,archived'], 'category' => ['nullable', 'integer', 'exists:portfolio_categories,id'], 'featured' => ['nullable', 'in:0,1'], 'trashed' => ['nullable', 'in:0,1'], 'sort' => ['nullable', 'in:name,status,is_featured,completion_date,updated_at'], 'direction' => ['nullable', 'in:asc,desc']]);
        $projects = PortfolioProject::query()->with('category')->when(($f['trashed'] ?? null) === '1', fn ($q) => $q->onlyTrashed())->when($f['search'] ?? null, fn (Builder $q, $s) => $q->where(fn ($q) => $q->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%")->orWhere('client_name', 'like', "%{$s}%")->orWhere('industry', 'like', "%{$s}%")))->when($f['status'] ?? null, fn ($q, $v) => $q->where('status', $v))->when($f['category'] ?? null, fn ($q, $v) => $q->where('portfolio_category_id', $v))->when(array_key_exists('featured', $f), fn ($q) => $q->where('is_featured', (bool) $f['featured']))->orderBy($f['sort'] ?? 'updated_at', $f['direction'] ?? 'desc')->paginate(20)->withQueryString();

        return view('admin.portfolio.index', ['projects' => $projects, 'categories' => PortfolioCategory::orderBy('name')->get()]);
    }

    public function create(): View
    {
        $this->authorize('create', PortfolioProject::class);

        return view('admin.portfolio.form', $this->formData(new PortfolioProject));
    }

    public function store(StorePortfolioProjectRequest $r, PortfolioManager $m): RedirectResponse
    {
        $d = $r->validated();
        if (! $r->user()->isAdmin()) {
            $d['is_featured'] = false;
        }if (($d['status'] ?? null) === 'archived' && ! $r->user()->isAdmin()) {
            abort(403);
        }$p = $m->create($d, $r->user()->id);
        ActivityLogService::log('portfolio', 'created', "Portfolio project {$p->name} created.", $p, null, $p->only(['name', 'slug', 'status']));

        return redirect()->route('admin.portfolio.edit', $p)->with('success', 'Portfolio project created.');
    }

    public function show(PortfolioProject $portfolio): View
    {
        $this->authorize('update', $portfolio);
        $portfolio->load($this->relations());

        return view('admin.portfolio.show', ['project' => $portfolio]);
    }

    public function edit(PortfolioProject $portfolio): View
    {
        $this->authorize('update', $portfolio);
        $portfolio->load($this->relations());

        return view('admin.portfolio.form', $this->formData($portfolio));
    }

    public function update(UpdatePortfolioProjectRequest $r, PortfolioProject $portfolio, PortfolioManager $m): RedirectResponse
    {
        $status = PortfolioStatus::from($r->validated('status'));
        if ($status === PortfolioStatus::Archived) {
            $this->authorize('archive', $portfolio);
        } elseif ($status !== $portfolio->status) {
            $this->authorize('publish', $portfolio);
        }$d = $r->validated();
        if (! $r->user()->isAdmin()) {
            $d['is_featured'] = $portfolio->is_featured;
        }$old = $portfolio->only(['name', 'slug', 'status', 'is_featured']);
        $m->update($portfolio, $d, $r->user()->id);
        $fresh = $portfolio->fresh();
        ActivityLogService::log('portfolio', 'updated', "Portfolio project {$fresh->name} updated.", $fresh, $old, $fresh->only(array_keys($old)));

        return back()->with('success', 'Portfolio project updated.');
    }

    public function destroy(PortfolioProject $portfolio, PortfolioManager $m): RedirectResponse
    {
        $this->authorize('delete', $portfolio);
        ActivityLogService::log('portfolio', 'deleted', "Portfolio project {$portfolio->name} deleted.", $portfolio, $portfolio->only(['name', 'slug', 'status']));
        $m->delete($portfolio);

        return redirect()->route('admin.portfolio.index')->with('success', 'Portfolio project deleted.');
    }

    public function restore(int $project, PortfolioManager $m): RedirectResponse
    {
        $p = PortfolioProject::onlyTrashed()->findOrFail($project);
        $this->authorize('restore', $p);
        $m->restore($p);
        ActivityLogService::log('portfolio', 'restored', "Portfolio project {$p->name} restored.", $p);

        return back()->with('success', 'Portfolio project restored.');
    }

    public function bulk(BulkPortfolioActionRequest $r, PortfolioManager $m): RedirectResponse
    {
        $action = $r->validated('action');
        $items = PortfolioProject::query()->when($action === 'restore', fn ($q) => $q->onlyTrashed())->whereKey($r->validated('project_ids'))->get();
        DB::transaction(function () use ($items, $action, $r, $m) {
            foreach ($items as $p) {
                $before = $p->only(['status', 'is_featured']);
                if (in_array($action, ['publish', 'draft', 'archive'], true)) {
                    $this->authorizeForUser($r->user(), $action === 'archive' ? 'archive' : 'publish', $p);
                    $status = match ($action) {
                        'publish' => PortfolioStatus::Published,'archive' => PortfolioStatus::Archived,default => PortfolioStatus::Draft
                    };
                    $m->setStatus($p, $status, $r->user()->id);
                } elseif (in_array($action, ['feature', 'unfeature'], true)) {
                    $this->authorizeForUser($r->user(), 'feature', $p);
                    $m->feature($p, $action === 'feature', $r->user()->id);
                } elseif ($action === 'restore') {
                    $this->authorizeForUser($r->user(), 'restore', $p);
                    $m->restore($p);
                } else {
                    $this->authorizeForUser($r->user(), 'delete', $p);
                    $m->delete($p);
                }
                $loggedAction = match ($action) {
                    'publish' => 'published', 'draft' => 'drafted', 'archive' => 'archived',
                    'feature' => 'featured', 'unfeature' => 'unfeatured', 'restore' => 'restored',
                    default => 'deleted',
                };
                ActivityLogService::log('portfolio', $loggedAction, "Bulk action {$action} applied to portfolio project {$p->name}.", $p, $before, $p->fresh()?->only(['status', 'is_featured']));
            }
        });

        return back()->with('success', 'Bulk action completed.');
    }

    private function formData(PortfolioProject $p): array
    {
        return ['project' => $p, 'categories' => PortfolioCategory::active()->orderBy('sort_order')->orderBy('name')->get(), 'statuses' => PortfolioStatus::cases()];
    }

    private function relations(): array
    {
        return ['objectives', 'solutions', 'features', 'galleryImages', 'technologies', 'links', 'results', 'faqs', 'seo'];
    }
}
