<!doctype html>
<html lang="{{ $siteSettings['general.default_language'] ?? app()->getLocale() }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="{{ $siteSettings['branding.theme_color'] ?? '#0d6efd' }}">
    @php
        $siteName = $siteSettings['general.site_name'] ?? config('app.name');
        $companyName = $siteSettings['contact.company_name'] ?? $siteName;
        $pageSeo = (isset($product) ? $product->seo : null) ?? (isset($service) ? $service->seo : null) ?? (isset($project) ? $project->seo : null) ?? (isset($post) ? $post->seo : null);
        $pageIndexable = $cmsPage->is_indexable ?? $pageSeo?->is_indexable ?? true;
        $pageImagePath = $cmsPage->open_graph_image_path ?? $pageSeo?->open_graph_image_path ?? (isset($post) ? $post->social_image_path : null) ?? (isset($product) ? $product->banner_path : null) ?? (isset($service) ? $service->featured_image_path : null) ?? (isset($project) ? $project->cover_image_path : null) ?? ($siteSettings['seo.open_graph_image'] ?? null);
        $pageImageUrl = filled($pageImagePath) ? url(Storage::url($pageImagePath)) : null;
        $canonicalBase = filled($siteSettings['seo.canonical_base_url'] ?? null) ? rtrim($siteSettings['seo.canonical_base_url'], '/') : null;
        $canonicalUrl = $cmsPage->canonical_url ?? $pageSeo?->canonical_url ?? ($canonicalBase ? $canonicalBase.request()->getPathInfo() : url()->current());
        $routeName = request()->route()?->getName();
        $currentLabel = isset($product) ? $product->name : (isset($service) ? $service->name : (isset($project) ? $project->name : (isset($post) ? $post->title : Str::headline((string) $routeName))));
        $breadcrumbSchema = [[chr(64).'type'=>'ListItem','position'=>1,'name'=>'Home','item'=>route('home')]];
        if ($routeName && $routeName !== 'home') {
            $section = Str::before($routeName, '.');
            if (str_contains($routeName, '.') && Route::has($section)) $breadcrumbSchema[] = [chr(64).'type'=>'ListItem','position'=>2,'name'=>Str::headline($section),'item'=>route($section)];
            $breadcrumbSchema[] = [chr(64).'type'=>'ListItem','position'=>count($breadcrumbSchema)+1,'name'=>$currentLabel,'item'=>$canonicalUrl];
        }
    @endphp
    <title>@yield('title', $siteSettings['seo.meta_title'] ?? $siteName)</title>
    <meta name="description" content="@yield('description', $siteSettings['seo.meta_description'] ?? ($siteSettings['general.tagline'] ?? $siteName))">
    @if(filled($siteSettings['seo.meta_keywords'] ?? null))<meta name="keywords" content="{{ $siteSettings['seo.meta_keywords'] }}">@endif
    <meta name="robots" content="{{ $pageIndexable ? ($siteSettings['seo.robots_meta'] ?? 'index,follow') : 'noindex,nofollow' }}">
    <link rel="canonical" href="@yield('canonical', $canonicalUrl)">
    @if(filled($siteSettings['branding.favicon'] ?? null))<link rel="icon" href="{{ Storage::url($siteSettings['branding.favicon']) }}">@endif
    <meta property="og:title" content="@yield('title', $siteSettings['seo.meta_title'] ?? $siteName)">
    <meta property="og:description" content="@yield('description', $siteSettings['seo.meta_description'] ?? ($siteSettings['general.tagline'] ?? $siteName))">
    <meta property="og:url" content="@yield('canonical', $canonicalUrl)">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $siteName }}">
    @if($pageImageUrl)<meta property="og:image" content="{{ $pageImageUrl }}"><meta name="twitter:image" content="{{ $pageImageUrl }}">@endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $siteSettings['seo.meta_title'] ?? $siteName)">
    <meta name="twitter:description" content="@yield('description', $siteSettings['seo.meta_description'] ?? ($siteSettings['general.tagline'] ?? $siteName))">
    <script type="application/ld+json">{!! json_encode([chr(64).'context'=>'https://schema.org',chr(64).'type'=>'Organization','name'=>$siteSettings['seo.organization'] ?? $companyName,'url'=>route('home'),'email'=>$siteSettings['contact.email'] ?? null,'telephone'=>$siteSettings['contact.phone'] ?? null], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode([chr(64).'context'=>'https://schema.org',chr(64).'type'=>'WebSite','name'=>$siteName,'url'=>route('home'),'inLanguage'=>$siteSettings['general.default_language'] ?? app()->getLocale()], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>
    @if(count($breadcrumbSchema)>1)<script type="application/ld+json">{!! json_encode([chr(64).'context'=>'https://schema.org',chr(64).'type'=>'BreadcrumbList','itemListElement'=>$breadcrumbSchema], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) !!}</script>@endif
    @if(filled($siteSettings['analytics.ga4_measurement_id'] ?? null))<script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings['analytics.ga4_measurement_id'] }}"></script><script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $siteSettings['analytics.ga4_measurement_id'] }}');</script>@endif
    @if(filled($siteSettings['analytics.gtm_id'] ?? null))<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $siteSettings['analytics.gtm_id'] }}');</script>@endif
    @if(filled($siteSettings['analytics.clarity_id'] ?? null))<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src='https://www.clarity.ms/tag/'+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,'clarity','script','{{ $siteSettings['analytics.clarity_id'] }}');</script>@endif
    @if(filled($siteSettings['analytics.facebook_pixel_id'] ?? null))<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','{{ $siteSettings['analytics.facebook_pixel_id'] }}');fbq('track','PageView');</script>@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="public-site">
@if(filled($siteSettings['analytics.gtm_id'] ?? null))<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $siteSettings['analytics.gtm_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>@endif
<a class="skip-link btn btn-primary" href="#main">Skip to content</a>
<nav class="navbar navbar-expand-xl public-navbar sticky-top" aria-label="Primary navigation" data-public-nav>
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            @if(filled($siteSettings['branding.logo'] ?? null))
                <img class="brand-logo brand-logo-light" src="{{ Storage::url($siteSettings['branding.logo']) }}" alt="{{ $companyName }}">
                @if(filled($siteSettings['branding.dark_logo'] ?? null))<img class="brand-logo brand-logo-dark" src="{{ Storage::url($siteSettings['branding.dark_logo']) }}" alt="{{ $companyName }}">@endif
            @else
                <span class="brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($siteName,0,1)) }}</span> {{ $siteName }}
            @endif
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#primary-nav" aria-controls="primary-nav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        @php
            $primaryNavigation = $headerNavigation;
            $fallbackNavigation = ['home'=>'Home','products'=>'Products','services'=>'Services','portfolio'=>'Portfolio','blog'=>'Blog','about'=>'About','contact'=>'Contact'];
        @endphp
        <div class="collapse navbar-collapse" id="primary-nav"><ul class="navbar-nav ms-xl-auto align-items-xl-center gap-xl-1">
            @forelse($primaryNavigation as $item)
                @php $itemPath = '/'.ltrim(parse_url($item->url, PHP_URL_PATH) ?: '/', '/'); $isActive = $itemPath === '/' ? request()->routeIs('home') : request()->is(trim($itemPath, '/').'*'); @endphp
                <li class="nav-item"><a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $item->url }}" @if($isActive)aria-current="page"@endif @if($item->open_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a></li>
            @empty
                @foreach($fallbackNavigation as $routeName => $label)
                    @php($isActive = request()->routeIs($routeName, $routeName.'.*'))
                    <li class="nav-item"><a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ route($routeName) }}" @if($isActive)aria-current="page"@endif>{{ $label }}</a></li>
                @endforeach
            @endforelse
            <li class="nav-item ms-xl-2"><a class="btn btn-primary nav-cta" href="{{ route('demo-request') }}">Request demo <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></li>
            <li class="nav-item ms-xl-1"><button class="btn theme-toggle" type="button" data-theme-toggle aria-label="Toggle colour theme" aria-pressed="false"><i class="bi bi-sun" aria-hidden="true"></i></button></li>
        </ul></div>
    </div>
</nav>
<main id="main">@yield('content')@if(request()->routeIs('products.show') && isset($product))<x-lead-cta type="product" :item="$product->id" :name="$product->name"/>@elseif(request()->routeIs('services.show') && isset($service))<x-lead-cta type="service" :item="$service->id" :name="$service->name"/>@endif</main>
<footer class="public-footer"><div class="container"><div class="footer-grid"><div class="footer-brand"><a class="navbar-brand" href="{{ route('home') }}">@if(filled($siteSettings['branding.logo'] ?? null))<img class="brand-logo brand-logo-light" src="{{ Storage::url($siteSettings['branding.logo']) }}" alt="{{ $companyName }}">@if(filled($siteSettings['branding.dark_logo'] ?? null))<img class="brand-logo brand-logo-dark" src="{{ Storage::url($siteSettings['branding.dark_logo']) }}" alt="{{ $companyName }}">@endif @else<span class="brand-mark" aria-hidden="true">{{ Str::upper(Str::substr($siteName,0,1)) }}</span> {{ $siteName }}@endif</a><p>{{ $cmsFooter?->short_description ?: ($siteSettings['general.tagline'] ?? '') }}</p>@if($socialLinks->isNotEmpty())<div class="footer-socials">@foreach($socialLinks as $link)<a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ ucfirst($link->platform) }}"><i class="bi bi-{{ $link->platform === 'x' ? 'twitter-x' : $link->platform }}" aria-hidden="true"></i></a>@endforeach</div>@endif</div><nav class="footer-links" aria-label="Footer navigation"><div><h2>{{ $cmsFooter?->quick_links_heading ?: 'Explore' }}</h2>@foreach($footerNavigation as $item)<a href="{{ $item->url }}" @if($item->open_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>@endforeach</div><div><h2>{{ $cmsFooter?->company_heading ?: 'Company' }}</h2>@foreach([['label'=>$cmsFooter?->about_label ?: 'About','url'=>$cmsFooter?->about_url ?: route('about')],['label'=>$cmsFooter?->contact_label ?: 'Contact','url'=>$cmsFooter?->contact_url ?: route('contact', [], false)],['label'=>$cmsFooter?->demo_label ?: 'Request demo','url'=>$cmsFooter?->demo_url ?: route('demo-request')],['label'=>$cmsFooter?->quote_label ?: 'Request quote','url'=>$cmsFooter?->quote_url ?: route('quote-request')]] as $link)@if(filled($link['label']) && filled($link['url']))<a href="{{ $link['url'] }}">{{ $link['label'] }}</a>@endif @endforeach</div><div><h2>{{ $cmsFooter?->resources_heading ?: 'Resources' }}</h2>@foreach([['label'=>$cmsFooter?->blog_label ?: 'Knowledge center','url'=>$cmsFooter?->blog_url ?: route('blog')],['label'=>$cmsFooter?->rss_label ?: 'RSS feed','url'=>$cmsFooter?->rss_url ?: route('blog.rss')],['label'=>$cmsFooter?->sitemap_label ?: 'Sitemap','url'=>$cmsFooter?->sitemap_url ?: route('sitemap')],['label'=>$cmsFooter?->privacy_label ?: 'Privacy Policy','url'=>$cmsFooter?->privacy_url ?: route('privacy-policy')],['label'=>$cmsFooter?->terms_label ?: 'Terms & Conditions','url'=>$cmsFooter?->terms_url ?: route('terms-conditions')],['label'=>$cmsFooter?->cookies_label ?: 'Cookies','url'=>$cmsFooter?->cookies_url]] as $link)@if(filled($link['label']) && filled($link['url']))<a href="{{ $link['url'] }}">{{ $link['label'] }}</a>@endif @endforeach</div></nav><div class="footer-contact"><h2>{{ $cmsFooter?->contact_heading ?: 'Start a conversation' }}</h2><p>{{ $cmsFooter?->contact_text ?: ($siteSettings['general.tagline'] ?? 'Tell us where your business needs better software.') }}</p>@if(filled($siteSettings['contact.address'] ?? null))<p>{{ $siteSettings['contact.address'] }}</p>@endif @foreach(['contact.email'=>'mailto:', 'contact.support_email'=>'mailto:', 'contact.sales_email'=>'mailto:'] as $key=>$prefix)@if(filled($siteSettings[$key]??null))<a href="{{ $prefix.$siteSettings[$key] }}">{{ $siteSettings[$key] }}</a>@endif @endforeach @foreach(['contact.phone','contact.mobile'] as $key)@if(filled($siteSettings[$key]??null))<a href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSettings[$key]) }}">{{ $siteSettings[$key] }}</a>@endif @endforeach @if($whatsApp = $socialLinks->firstWhere('platform','whatsapp'))<a href="{{ $whatsApp->url }}" target="_blank" rel="noopener">{{ $cmsFooter?->whatsapp_label ?: 'WhatsApp' }}</a>@elseif(filled($siteSettings['contact.whatsapp'] ?? null))<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteSettings['contact.whatsapp']) }}" target="_blank" rel="noopener">{{ $siteSettings['contact.whatsapp'] }}</a>@endif @if(filled($cmsFooter?->business_hours_text))<p>{{ $cmsFooter->business_hours_text }}</p>@endif @if(filled($cmsFooter?->support_hours_text))<p>{{ $cmsFooter->support_hours_text }}</p>@endif @if(filled($cmsFooter?->cta_title) || filled($cmsFooter?->cta_description) || (filled($cmsFooter?->cta_button_text) && filled($cmsFooter?->cta_button_url)))<h2>{{ $cmsFooter?->cta_title }}</h2>@if(filled($cmsFooter?->cta_description))<p>{{ $cmsFooter->cta_description }}</p>@endif @if(filled($cmsFooter?->cta_button_text) && filled($cmsFooter?->cta_button_url))<a href="{{ $cmsFooter->cta_button_url }}">{{ $cmsFooter->cta_button_text }}</a>@endif @endif @if(filled($cmsFooter?->newsletter_heading) || filled($cmsFooter?->newsletter_description))<h2>{{ $cmsFooter?->newsletter_heading }}</h2>@if(filled($cmsFooter?->newsletter_description))<p>{{ $cmsFooter->newsletter_description }}</p>@endif @endif</div></div><div class="footer-bottom"><small>{{ $cmsFooter?->copyright ?: '© '.date('Y').' '.$siteName.'. All rights reserved.' }}</small><span>{{ $cmsFooter?->bottom_text ?: 'Software products - Enterprise systems - Business automation' }}@if(filled($cmsFooter?->made_by_text)) · {{ $cmsFooter->made_by_text }}@endif @if(filled($cmsFooter?->powered_by_text)) · {{ $cmsFooter->powered_by_text }}@endif @if(filled($cmsFooter?->version_text)) · {{ $cmsFooter->version_text }}@endif</span></div></div></footer>
</body>
</html>


