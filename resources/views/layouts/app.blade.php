<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $siteSettings['branding.theme_color']??'#0d6efd' }}">
    <title>@yield('title', $siteSettings['seo.meta_title'] ?? 'DevNiox')</title>
    <meta name="description" content="@yield('description', $siteSettings['seo.meta_description'] ?? 'DevNiox digital solutions')">
    @if(filled($siteSettings['seo.meta_keywords'] ?? null))<meta name="keywords" content="{{ $siteSettings['seo.meta_keywords'] }}">@endif
    @php
        $pageSeo = (isset($product) ? $product->seo : null) ?? (isset($service) ? $service->seo : null) ?? (isset($project) ? $project->seo : null) ?? (isset($post) ? $post->seo : null);
        $pageIndexable = $cmsPage->is_indexable ?? $pageSeo?->is_indexable ?? true;
        $pageImagePath = $cmsPage->open_graph_image_path ?? $pageSeo?->open_graph_image_path ?? (isset($post) ? $post->social_image_path : null) ?? (isset($product) ? $product->banner_path : null) ?? (isset($service) ? $service->featured_image_path : null) ?? (isset($project) ? $project->cover_image_path : null) ?? ($siteSettings['branding.default_share_image'] ?? null);
        $pageImageUrl = filled($pageImagePath) ? url(Storage::url($pageImagePath)) : null;
        $canonicalUrl = $cmsPage->canonical_url ?? $pageSeo?->canonical_url ?? url()->current();
        $routeName = request()->route()?->getName();
        $currentLabel = isset($product) ? $product->name : (isset($service) ? $service->name : (isset($project) ? $project->name : (isset($post) ? $post->title : Str::headline((string) $routeName))));
        $breadcrumbSchema = [['@type'=>'ListItem','position'=>1,'name'=>'Home','item'=>route('home')]];
        if ($routeName && $routeName !== 'home') {
            $section = Str::before($routeName, '.');
            if (str_contains($routeName, '.') && Route::has($section)) $breadcrumbSchema[] = ['@type'=>'ListItem','position'=>2,'name'=>Str::headline($section),'item'=>route($section)];
            $breadcrumbSchema[] = ['@type'=>'ListItem','position'=>count($breadcrumbSchema)+1,'name'=>$currentLabel,'item'=>$canonicalUrl];
        }
    @endphp
    <meta name="robots" content="{{ $pageIndexable ? ($siteSettings['seo.robots_default'] ?? 'index,follow') : 'noindex,nofollow' }}">
    <link rel="canonical" href="@yield('canonical', $canonicalUrl)">
    @if(filled($siteSettings['branding.favicon'] ?? null))<link rel="icon" href="{{ Storage::url($siteSettings['branding.favicon']) }}">@endif
    @if(filled($siteSettings['branding.apple_touch_icon']??null))<link rel="apple-touch-icon" href="{{ Storage::url($siteSettings['branding.apple_touch_icon']) }}">@endif
    <meta property="og:title" content="@yield('title', $siteSettings['seo.og_title'] ?? 'DevNiox')">
    <meta property="og:description" content="@yield('description', $siteSettings['seo.og_description'] ?? 'Operational software and enterprise systems engineered for long-term ownership.')">
    <meta property="og:url" content="@yield('canonical', $canonicalUrl)">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteSettings['general.website_name'] ?? 'DevNiox' }}">
    @if($pageImageUrl)<meta property="og:image" content="{{ $pageImageUrl }}"><meta property="og:image:alt" content="@yield('title', $siteSettings['seo.og_title'] ?? 'DevNiox')"><meta name="twitter:image" content="{{ $pageImageUrl }}"><meta name="twitter:image:alt" content="@yield('title', $siteSettings['seo.og_title'] ?? 'DevNiox')">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $siteSettings['seo.og_title'] ?? 'DevNiox')">
    <meta name="twitter:description" content="@yield('description', $siteSettings['seo.og_description'] ?? 'Operational software and enterprise systems engineered for long-term ownership.')">
    <meta name="twitter:site" content="{{ $siteSettings['social.twitter_handle'] ?? '@devniox' }}">
    <script type="application/ld+json">{!! json_encode([chr(64).'context' => 'https://schema.org', chr(64).'type' => 'Organization', 'name' => $siteSettings['seo.organization_schema'] ?? ($siteSettings['general.website_name'] ?? 'DevNiox'), 'url' => route('home')], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode([chr(64).'context'=>'https://schema.org',chr(64).'type'=>'WebSite','name'=>$siteSettings['general.website_name']??'DevNiox','url'=>route('home'),'inLanguage'=>'en'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
    @if(count($breadcrumbSchema)>1)<script type="application/ld+json">{!! json_encode([chr(64).'context'=>'https://schema.org',chr(64).'type'=>'BreadcrumbList','itemListElement'=>$breadcrumbSchema], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    @if(filled($siteSettings['analytics.head_scripts']??null)){!! $siteSettings['analytics.head_scripts'] !!}@endif
</head>
<body class="public-site">
<a class="skip-link btn btn-primary" href="#main">Skip to content</a>
<nav class="navbar navbar-expand-xl public-navbar sticky-top" aria-label="Primary navigation" data-public-nav>
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            @if(filled($siteSettings['branding.logo'] ?? null))
                <img class="brand-logo brand-logo-light" src="{{ Storage::url($siteSettings['branding.logo']) }}" alt="{{ $siteSettings['company.name'] ?? 'DevNiox' }}">
                @if(filled($siteSettings['branding.dark_logo'] ?? null))<img class="brand-logo brand-logo-dark" src="{{ Storage::url($siteSettings['branding.dark_logo']) }}" alt="{{ $siteSettings['company.name'] ?? 'DevNiox' }}">@endif
            @else
                <span class="brand-mark" aria-hidden="true">D</span> DevNiox
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-nav" aria-controls="primary-nav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        @php
            $primaryNavigation = $headerNavigation;
            $fallbackNavigation = [
                'home' => 'Home',
                'products' => 'Products',
                'services' => 'Services',
                'portfolio' => 'Portfolio',
                'blog' => 'Blog',
                'about' => 'About',
                'contact' => 'Contact',
            ];
        @endphp
        <div class="collapse navbar-collapse" id="primary-nav"><ul class="navbar-nav ms-xl-auto align-items-xl-center gap-xl-1">
            @forelse($primaryNavigation as $item)
                @php $itemPath = '/'.ltrim(parse_url($item->url, PHP_URL_PATH) ?: '/', '/'); $isActive = $itemPath === '/' ? request()->routeIs('home') : request()->is(trim($itemPath, '/').'*'); @endphp
                <li class="nav-item"><a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $item->url }}" @if($isActive)aria-current="page"@endif @if($item->open_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a></li>
            @empty
                @foreach($fallbackNavigation as $routeName => $label)
                    @php $isActive = request()->routeIs($routeName, $routeName.'.*'); @endphp
                    <li class="nav-item"><a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ route($routeName) }}" @if($isActive)aria-current="page"@endif>{{ $label }}</a></li>
                @endforeach
            @endforelse
            <li class="nav-item ms-xl-2"><a class="btn btn-primary nav-cta" href="{{ route('demo-request') }}">Request demo <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></li>
            <li class="nav-item ms-xl-1"><button class="btn theme-toggle" type="button" data-theme-toggle aria-label="Toggle colour theme" aria-pressed="false"><i class="bi bi-sun" aria-hidden="true"></i></button></li>
        </ul></div>
    </div>
</nav>
<main id="main">@yield('content')@if(request()->routeIs('products.show') && isset($product))<x-lead-cta type="product" :item="$product->id" :name="$product->name"/>@elseif(request()->routeIs('services.show') && isset($service))<x-lead-cta type="service" :item="$service->id" :name="$service->name"/>@endif</main>
<footer class="public-footer"><div class="container"><div class="footer-grid"><div class="footer-brand"><a class="navbar-brand" href="{{ route('home') }}">@if(filled($siteSettings['branding.logo'] ?? null))<img class="brand-logo brand-logo-light" src="{{ Storage::url($siteSettings['branding.logo']) }}" alt="{{ $siteSettings['company.name'] ?? 'DevNiox' }}">@if(filled($siteSettings['branding.dark_logo'] ?? null))<img class="brand-logo brand-logo-dark" src="{{ Storage::url($siteSettings['branding.dark_logo']) }}" alt="{{ $siteSettings['company.name'] ?? 'DevNiox' }}">@endif @else<span class="brand-mark" aria-hidden="true">D</span> {{ $siteSettings['company.name']??'DevNiox' }}@endif</a><p>{{ $cmsFooter?->short_description }}</p><div class="footer-socials">@foreach($socialLinks as $link)<a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($link->platform) }} (opens in a new tab)"><i class="bi bi-{{ $link->platform === 'x' ? 'twitter-x' : $link->platform }}" aria-hidden="true"></i></a>@endforeach</div></div><nav class="footer-links" aria-label="Footer navigation"><div><h2>Explore</h2>@foreach($footerNavigation as $item)<a href="{{ $item->url }}" @if($item->open_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>@endforeach</div><div><h2>Company</h2><a href="{{ route('about') }}">About</a><a href="{{ route('contact', [], false) }}">Contact</a><a href="{{ route('demo-request') }}">Request demo</a><a href="{{ route('quote-request') }}">Request quote</a></div><div><h2>Resources</h2><a href="{{ route('blog') }}">Knowledge center</a><a href="{{ route('blog.rss') }}">RSS feed</a><a href="{{ route('sitemap') }}">Sitemap</a></div></nav><div class="footer-contact"><h2>Start a conversation</h2><p>Tell us where your business needs better software.</p>@if(filled($siteSettings['contact.email']??null))<a href="mailto:{{ $siteSettings['contact.email'] }}">{{ $siteSettings['contact.email'] }}</a>@endif @if(filled($siteSettings['contact.phone']??null))<a href="tel:{{ $siteSettings['contact.phone'] }}">{{ $siteSettings['contact.phone'] }}</a>@endif</div></div><div class="footer-bottom"><small>{{ $cmsFooter?->copyright }}</small><span>Software products · Enterprise systems · Business automation</span></div></div></footer>@if(filled($siteSettings['analytics.footer_scripts']??null)){!! $siteSettings['analytics.footer_scripts'] !!}@endif
</body>
</html>
