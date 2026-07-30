<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Models\MediaUsage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class MediaLibraryService
{
    public function __construct(private readonly ManagedImageService $images) {}

    public function upload(UploadedFile $file, array $data, int $userId): MediaAsset
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $duplicate = MediaAsset::withTrashed()->where('sha256', $hash)->first();
        if ($duplicate) {
            if ($duplicate->trashed()) {
                $duplicate->restore();
            }
            ActivityLogService::log('media', 'duplicate_detected', "Existing media {$duplicate->name} reused instead of storing a duplicate.", $duplicate, null, ['sha256' => $hash], $userId);

            return $duplicate;
        }
        $kind = $this->kind((string) $file->getMimeType());
        $disk = $kind === 'image' ? 'public' : 'local';
        $path = null;
        try {
            $path = $kind === 'image' ? $this->images->store($file, 'media-library/images') : $file->storeAs('media-library/'.$kind, Str::uuid().'.'.strtolower($file->extension() ?: $file->getClientOriginalExtension()), 'local');
            if (! is_string($path)) {
                throw new \RuntimeException('The media file could not be stored.');
            }
            [$width,$height] = $this->dimensions($file, $kind);

            $asset = MediaAsset::create(['media_folder_id' => $data['media_folder_id'] ?? null, 'uploaded_by' => $userId, 'name' => ($data['name'] ?? null) ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'original_name' => $this->safeName($file), 'disk' => $disk, 'file_path' => $path, 'mime_type' => (string) $file->getMimeType(), 'extension' => strtolower(pathinfo($path, PATHINFO_EXTENSION)), 'kind' => $kind, 'file_size' => (int) (Storage::disk($disk)->size($path) ?: $file->getSize()), 'sha256' => $hash, 'width' => $width, 'height' => $height, 'alt_text' => $data['alt_text'] ?? null, 'description' => $data['description'] ?? null, 'is_optimized' => $kind === 'image' && str_ends_with($path, '.webp')]);
            ActivityLogService::log('media', 'uploaded', "Media {$asset->name} uploaded.", $asset, null, $asset->only(['name', 'kind', 'file_size']), $userId);

            return $asset;
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk($disk)->delete($path);
            } throw $e;
        }
    }

    public function replace(MediaAsset $asset, UploadedFile $file, int $userId): void
    {
        $hash = hash_file('sha256', $file->getRealPath());
        if (hash_equals((string) $asset->sha256, $hash)) {
            throw ValidationException::withMessages(['file' => 'The replacement is identical to the current file.']);
        }
        if (MediaAsset::where('sha256', $hash)->whereKeyNot($asset->id)->exists()) {
            throw ValidationException::withMessages(['file' => 'This file already exists in the Media Library. Reuse the existing asset instead.']);
        }
        $new = $this->upload($file, ['media_folder_id' => $asset->media_folder_id, 'name' => $asset->name, 'alt_text' => $asset->alt_text, 'description' => $asset->description], $userId);
        $oldDisk = $asset->disk;
        $oldPath = $asset->file_path;
        DB::transaction(function () use ($asset, $new) {
            $attributes = $new->only(['uploaded_by', 'original_name', 'disk', 'file_path', 'mime_type', 'extension', 'kind', 'file_size', 'sha256', 'width', 'height', 'is_optimized']);
            $new->forceDelete();
            $asset->update($attributes);
            foreach ($asset->usages()->with('usable')->get() as $usage) {
                if (! $usage->usable) {
                    continue;
                }
                if ($usage->field === 'attachment') {
                    $usage->usable->update(['file_path' => $asset->file_path, 'original_name' => $asset->original_name, 'mime_type' => $asset->mime_type, 'file_size' => $asset->file_size]);
                } elseif (array_key_exists($usage->field, $usage->usable->getAttributes())) {
                    $usage->usable->update([$usage->field => $asset->file_path]);
                }
            }
        });
        Storage::disk($oldDisk)->delete($oldPath);
        ActivityLogService::log('media', 'replaced', "Media {$asset->name} replaced.", $asset, null, ['file_size' => $asset->file_size], $userId);
    }

    public function update(MediaAsset $asset, array $data, int $userId): void
    {
        $old = $asset->only(['name', 'media_folder_id', 'alt_text']);
        $asset->update($data);
        ActivityLogService::log('media', 'updated', "Media {$asset->name} updated.", $asset, $old, $asset->only(array_keys($old)), $userId);
    }

    public function delete(MediaAsset $asset, int $userId): void
    {
        if ($asset->usages()->exists()) {
            throw ValidationException::withMessages(['media' => 'Used media cannot be deleted. Remove its usages first.']);
        }$asset->delete();
        ActivityLogService::log('media', 'deleted', "Media {$asset->name} archived.", $asset, null, ['deleted_at' => now()], $userId);
    }

    public function restore(MediaAsset $asset, int $userId): void
    {
        $asset->restore();
        ActivityLogService::log('media', 'restored', "Media {$asset->name} restored.", $asset, null, ['deleted_at' => null], $userId);
    }

    public function forceDelete(MediaAsset $asset, int $userId): void
    {
        if ($asset->usages()->exists()) {
            throw ValidationException::withMessages(['media' => 'Used media cannot be permanently deleted.']);
        }$disk = $asset->disk;
        $path = $asset->file_path;
        ActivityLogService::log('media', 'permanently_deleted', "Media {$asset->name} permanently deleted.", $asset, $asset->only(['id', 'name', 'file_path']), null, $userId);
        $asset->forceDelete();
        Storage::disk($disk)->delete($path);
    }

    public function track(MediaAsset $asset, Model $usable, string $field): void
    {
        $asset->usages()->updateOrCreate(['usable_type' => $usable->getMorphClass(), 'usable_id' => $usable->getKey(), 'field' => $field]);
    }

    public function release(Model $usable, string $field): void
    {
        MediaUsage::whereMorphedTo('usable', $usable)->where('field', $field)->delete();
    }

    public function createFolder(array $data, int $userId): MediaFolder
    {
        $folder = MediaFolder::create($data + ['slug' => $this->folderSlug($data['name'], $data['parent_id'] ?? null)]);
        ActivityLogService::log('media', 'folder_created', "Media folder {$folder->name} created.", $folder, null, $folder->only(['name', 'parent_id']), $userId);

        return $folder;
    }

    public function updateFolder(MediaFolder $folder, array $data, int $userId): void
    {
        $folder->update($data + ['slug' => $this->folderSlug($data['name'], $data['parent_id'] ?? null, $folder->id)]);
        ActivityLogService::log('media', 'folder_updated', "Media folder {$folder->name} updated.", $folder, null, $folder->only(['name', 'parent_id']), $userId);
    }

    public function deleteFolder(MediaFolder $folder, int $userId): void
    {
        if ($folder->assets()->exists() || $folder->children()->exists()) {
            throw ValidationException::withMessages(['folder' => 'Only empty folders can be deleted.']);
        }$folder->delete();
        ActivityLogService::log('media', 'folder_deleted', "Media folder {$folder->name} deleted.", $folder, null, null, $userId);
    }

    public function stats(): array
    {
        return Cache::remember('media.library.stats.v1', now()->addMinutes(5), function () {
            $q = MediaAsset::query();

            return ['files' => (clone $q)->count(), 'bytes' => (int) (clone $q)->sum('file_size'), 'images' => (clone $q)->where('kind', 'image')->count(), 'documents' => (clone $q)->where('kind', 'document')->count(), 'videos' => (clone $q)->where('kind', 'video')->count(), 'used' => MediaUsage::distinct('media_asset_id')->count('media_asset_id')];
        });
    }

    private function kind(string $mime): string
    {
        return str_starts_with($mime, 'image/') ? 'image' : (str_starts_with($mime, 'video/') ? 'video' : 'document');
    }

    private function dimensions(UploadedFile $file, string $kind): array
    {
        if ($kind !== 'image') {
            return [null, null];
        }$size = @getimagesize($file->getRealPath());

        return $size ? [(int) $size[0], (int) $size[1]] : [null, null];
    }

    private function safeName(UploadedFile $file): string
    {
        return mb_substr((string) preg_replace('/[\x00-\x1F\x7F]+/', '', basename($file->getClientOriginalName())), 0, 255);
    }

    private function folderSlug(string $name, ?int $parent, ?int $ignore = null): string
    {
        $base = Str::slug($name) ?: 'folder';
        $slug = $base;
        $i = 2;
        while (MediaFolder::withTrashed()->where('parent_id', $parent)->where('id', '!=', $ignore)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
