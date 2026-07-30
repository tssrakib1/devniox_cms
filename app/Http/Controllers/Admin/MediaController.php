<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaFolderRequest;
use App\Http\Requests\Admin\ReplaceMediaAssetRequest;
use App\Http\Requests\Admin\ReuseMediaAssetRequest;
use App\Http\Requests\Admin\StoreMediaAssetRequest;
use App\Http\Requests\Admin\UpdateMediaAssetRequest;
use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\FinanceTransaction;
use App\Models\Lead;
use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\Order;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\Service;
use App\Services\ActivityLogService;
use App\Services\MediaLibraryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function index(Request $request, MediaLibraryService $service): View
    {
        $this->authorize('viewAny', MediaAsset::class);
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'folder' => ['nullable', 'integer', 'exists:media_folders,id'], 'kind' => ['nullable', Rule::in(['image', 'document', 'video'])], 'usage' => ['nullable', Rule::in(['used', 'unused'])], 'trashed' => ['nullable', 'in:0,1']]);
        $assets = MediaAsset::query()->with(['folder', 'uploader'])->withCount('usages')->when(($filters['trashed'] ?? null) === '1', fn ($q) => $q->onlyTrashed())->when($filters['search'] ?? null, fn ($q, $v) => $q->search($v))->when($filters['folder'] ?? null, fn ($q, $v) => $q->where('media_folder_id', $v))->when($filters['kind'] ?? null, fn ($q, $v) => $q->where('kind', $v))->when(($filters['usage'] ?? null) === 'used', fn ($q) => $q->has('usages'))->when(($filters['usage'] ?? null) === 'unused', fn ($q) => $q->doesntHave('usages'))->latest()->paginate(24)->withQueryString();

        return view('admin.media.index', ['assets' => $assets, 'folders' => MediaFolder::with('parent')->withCount('assets')->orderBy('name')->get(), 'stats' => $service->stats()]);
    }

    public function store(StoreMediaAssetRequest $request, MediaLibraryService $service): RedirectResponse
    {
        foreach ($request->file('files') as $file) {
            $service->upload($file, $request->safe()->except('files'), $request->user()->id);
        }

        return back()->with('success', 'Media uploaded.');
    }

    public function show(MediaAsset $media): View
    {
        $this->authorize('view', $media);
        $media->load(['folder', 'uploader', 'usages.usable']);

        return view('admin.media.show', compact('media'));
    }

    public function update(UpdateMediaAssetRequest $request, MediaAsset $media, MediaLibraryService $service): RedirectResponse
    {
        $service->update($media, $request->validated(), $request->user()->id);

        return back()->with('success', 'Media updated.');
    }

    public function replace(ReplaceMediaAssetRequest $request, MediaAsset $media, MediaLibraryService $service): RedirectResponse
    {
        $service->replace($media, $request->file('file'), $request->user()->id);

        return back()->with('success', 'Media replaced.');
    }

    public function destroy(Request $request, MediaAsset $media, MediaLibraryService $service): RedirectResponse
    {
        $this->authorize('delete', $media);
        $service->delete($media, $request->user()->id);

        return redirect()->route('admin.media.index')->with('success', 'Media archived.');
    }

    public function restore(Request $request, int $media, MediaLibraryService $service): RedirectResponse
    {
        $asset = MediaAsset::onlyTrashed()->findOrFail($media);
        $this->authorize('restore', $asset);
        $service->restore($asset, $request->user()->id);

        return back()->with('success', 'Media restored.');
    }

    public function forceDelete(Request $request, int $media, MediaLibraryService $service): RedirectResponse
    {
        $asset = MediaAsset::onlyTrashed()->findOrFail($media);
        $this->authorize('forceDelete', $asset);
        $service->forceDelete($asset, $request->user()->id);

        return back()->with('success', 'Media permanently deleted.');
    }

    public function preview(MediaAsset $media): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('view', $media);
        abort_unless(Storage::disk($media->disk)->exists($media->file_path), 404);
        if ($media->isPublic()) {
            return redirect(Storage::disk('public')->url($media->file_path));
        }

        return response()->file(Storage::disk('local')->path($media->file_path), ['Content-Type' => $media->mime_type, 'Content-Disposition' => 'inline; filename="'.addslashes($media->original_name).'"', 'X-Content-Type-Options' => 'nosniff']);
    }

    public function download(MediaAsset $media): StreamedResponse
    {
        $this->authorize('view', $media);
        abort_unless(Storage::disk($media->disk)->exists($media->file_path), 404);

        return Storage::disk($media->disk)->download($media->file_path, $media->original_name);
    }

    public function reuse(ReuseMediaAssetRequest $request, MediaAsset $media, MediaLibraryService $service): RedirectResponse
    {
        [$module,$field] = explode('.', $request->validated('context'), 2);
        $id = (int) $request->validated('record_id');
        if (in_array($module, ['product', 'service', 'portfolio', 'blog', 'cms'], true)) {
            abort_unless($media->kind === 'image', 422);
            [$model,$column] = match ($module) {
                'product' => [Product::findOrFail($id), $field.'_path'],'service' => [Service::findOrFail($id), $field.'_path'],'portfolio' => [PortfolioProject::findOrFail($id), $field.'_path'],'blog' => [BlogPost::findOrFail($id), $field.'_path'],'cms' => [CmsPage::findOrFail($id), $field.'_path']
            };
            $this->authorize('update', $model);
            $model->update([$column => $media->file_path]);
            $service->release($model, $column);
            $service->track($media, $model, $column);
        } else {
            $target = match ($module) {
                'communication' => Lead::findOrFail($id),'finance' => FinanceTransaction::findOrFail($id),'order' => Order::findOrFail($id)
            };
            $ability = $module === 'communication' ? 'update' : 'manageAttachment';
            $this->authorize($ability, $target);
            $attributes = ['media_asset_id' => $media->id, 'uploaded_by' => $request->user()->id, 'label' => $request->validated('label'), 'file_path' => $media->file_path, 'original_name' => $media->original_name, 'mime_type' => $media->mime_type, 'file_size' => $media->file_size];
            $attachment = $target->attachments()->create($attributes);
            $service->track($media, $attachment, 'attachment');
            if ($module === 'finance') {
                $target->increment('attachment_count');
            }
        }
        ActivityLogService::log('media', 'reused', "Media {$media->name} reused in {$module} #{$id}.", $media, null, ['context' => $request->validated('context'), 'record_id' => $id], $request->user()->id);

        return back()->with('success', 'Media asset linked successfully.');
    }

    public function picker(Request $request): JsonResponse
    {
        $this->authorize('viewAny', MediaAsset::class);
        $f = $request->validate(['search' => ['nullable', 'string', 'max:180'], 'kind' => ['nullable', Rule::in(['image', 'document', 'video'])]]);
        $assets = MediaAsset::query()->when($f['search'] ?? null, fn ($q, $v) => $q->search($v))->when($f['kind'] ?? null, fn ($q, $v) => $q->where('kind', $v))->latest()->limit(50)->get()->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'kind' => $a->kind, 'mime_type' => $a->mime_type, 'size' => $a->file_size, 'preview_url' => route('admin.media.preview', $a)]);

        return response()->json(['data' => $assets]);
    }

    public function storeFolder(MediaFolderRequest $request, MediaLibraryService $service): RedirectResponse
    {
        $service->createFolder($request->validated(), $request->user()->id);

        return back()->with('success', 'Folder created.');
    }

    public function updateFolder(MediaFolderRequest $request, MediaFolder $folder, MediaLibraryService $service): RedirectResponse
    {
        $service->updateFolder($folder, $request->validated(), $request->user()->id);

        return back()->with('success', 'Folder updated.');
    }

    public function destroyFolder(Request $request, MediaFolder $folder, MediaLibraryService $service): RedirectResponse
    {
        $this->authorize('delete', $folder);
        $service->deleteFolder($folder, $request->user()->id);

        return back()->with('success', 'Folder deleted.');
    }
}
