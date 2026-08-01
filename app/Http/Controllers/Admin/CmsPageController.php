<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCmsPageRequest;
use App\Models\CmsPage;
use App\Services\ActivityLogService;
use App\Services\CmsService;

class CmsPageController extends Controller
{
    public function edit(CmsPage $page, CmsService $cms)
    {
        $this->authorize('update', $page);
        $page->load($cms->relationsFor($page->key));
        $view = view()->exists("admin.cms.{$page->key}") ? "admin.cms.{$page->key}" : 'admin.cms.simple-page';

        return view($view, compact('page'));
    }

    public function update(UpdateCmsPageRequest $r, CmsPage $page, CmsService $s)
    {
        $s->updatePage($page, $r->validated(), $r->user()->id);
        ActivityLogService::log('cms', 'updated', ucfirst($page->key).' page updated.', $page, null, ['page' => $page->key]);

        return back()->with('success', ucfirst($page->key).' content updated.');
    }
}
