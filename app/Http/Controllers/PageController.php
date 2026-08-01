<?php

namespace App\Http\Controllers;

use App\Services\CmsService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function privacy(CmsService $cms): View
    {
        return $this->legal('privacy-policy', $cms);
    }

    public function terms(CmsService $cms): View
    {
        return $this->legal('terms-conditions', $cms);
    }

    private function legal(string $key, CmsService $cms): View
    {
        return view('pages.legal', ['cmsPage' => $cms->page($key)]);
    }
}
