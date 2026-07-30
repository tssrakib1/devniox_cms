<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\CmsService;
use Illuminate\Http\Request;

class CmsFooterController extends Controller
{
    public function edit(CmsService $s)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.cms.footer', ['footer' => $s->footer()]);
    }

    public function update(Request $r, CmsService $s)
    {
        abort_unless($r->user()->isAdmin(), 403);

        $d = $r->validate(['copyright' => ['required', 'max:255'], 'short_description' => ['required', 'max:2000'], 'quick_links_heading' => ['required', 'max:100'], 'products_heading' => ['required', 'max:100'], 'services_heading' => ['required', 'max:100'], 'blog_heading' => ['required', 'max:100'], 'privacy_url' => ['nullable', 'url:http,https'], 'terms_url' => ['nullable', 'url:http,https'], 'cookies_url' => ['nullable', 'url:http,https']]);
        $s->updateFooter($d, $r->user()->id);
        ActivityLogService::log('cms', 'updated', 'Footer updated.');

        return back()->with('success', 'Footer updated.');
    }
}
