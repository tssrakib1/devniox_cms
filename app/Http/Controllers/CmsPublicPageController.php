<?php

namespace App\Http\Controllers;

use App\Services\CmsService;

class CmsPublicPageController extends Controller
{
    public function about(CmsService $s)
    {
        return view('pages.about', ['cmsPage' => $s->page('about')]);
    }
}
