<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\CmsService;
use Illuminate\Http\Request;

class CmsNavigationController extends Controller
{
    public function edit(CmsService $s)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.cms.navigation', ['header' => $s->navigation('header'), 'footer' => $s->navigation('footer')]);
    }

    public function update(Request $r, CmsService $s)
    {
        abort_unless($r->user()->isAdmin(), 403);
        $safeUrl = ['required', 'string', 'max:2048', 'regex:/^(?:\/(?!\/)|https?:\/\/)/i'];
        $d = $r->validate([
            'items' => ['required', 'array', 'max:100'],
            'items.*.location' => ['required', 'in:header,footer'],
            'items.*.label' => ['required', 'string', 'max:100'],
            'items.*.url' => $safeUrl,
            'items.*.open_new_tab' => ['required', 'boolean'],
            'items.*.is_visible' => ['required', 'boolean'],
            'items.*.display_order' => ['required', 'integer', 'min:0', 'max:1000000'],
            'items.*.children' => ['nullable', 'array', 'max:100'],
            'items.*.children.*.location' => ['required', 'in:header,footer'],
            'items.*.children.*.label' => ['required', 'string', 'max:100'],
            'items.*.children.*.url' => $safeUrl,
            'items.*.children.*.open_new_tab' => ['required', 'boolean'],
            'items.*.children.*.is_visible' => ['required', 'boolean'],
            'items.*.children.*.display_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ]);
        $s->updateNavigation($d['items']);
        ActivityLogService::log('cms', 'updated', 'Navigation updated.', null, null, ['item_count' => count($d['items'])]);

        return back()->with('success', 'Navigation updated.');
    }
}
