<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCmsPageRequest;
use App\Models\CmsPage;
use App\Services\ActivityLogService;
use App\Services\CmsService;

class CmsPageController extends Controller
{
    public function edit(CmsPage $page)
    {
        $this->authorize('update', $page);
        $page->load($page->key === 'home' ? ['home', 'whyItems', 'statistics'] : ($page->key === 'about' ? ['about', 'coreValues', 'workItems'] : ['contact', 'businessHours']));

        return view("admin.cms.{$page->key}", compact('page'));
    }

    public function update(UpdateCmsPageRequest $r, CmsPage $page, CmsService $s)
    {
        $s->updatePage($page, $r->validated(), $r->user()->id);
        ActivityLogService::log('cms', 'updated', ucfirst($page->key).' page updated.', $page, null, ['page' => $page->key]);

        return back()->with('success', ucfirst($page->key).' content updated.');
    }
}
