@extends('layouts.app')
@section('title', $post->seo?->meta_title ?: $post->title)
@section('description', $post->seo?->meta_description ?: $post->summary)
@section('canonical', $post->seo?->canonical_url ?: route('blog.show', $post->slug))
@push('head')
@if($post->seo && ! $post->seo->is_indexable)<meta name="robots" content="noindex,nofollow">@endif
<meta property="og:type" content="article">
<meta property="og:title" content="{{ $post->seo?->meta_title ?: $post->title }}">
<meta property="og:description" content="{{ $post->seo?->meta_description ?: $post->summary }}">
@if($post->seo?->open_graph_image_path || $post->social_image_path || $post->featured_image_path)<meta property="og:image" content="{{ url(Storage::url($post->seo?->open_graph_image_path ?: ($post->social_image_path ?: $post->featured_image_path))) }}">@endif
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'Article','headline'=>$post->title,'description'=>$post->summary,'datePublished'=>$post->published_at->toAtomString(),'author'=>['@type'=>'Person','name'=>$post->author->name],'publisher'=>['@type'=>'Organization','name'=>'DevNiox']], JSON_UNESCAPED_SLASHES) !!}</script>
@endpush
@section('content')
<div class="container py-4"><nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item"><a href="{{ route('blog') }}">Blog</a></li><li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li></ol></nav></div>
<article class="container pb-5">
    <header class="mx-auto" style="max-width:900px">
        <p class="text-primary fw-semibold">{{ $post->category->name }}</p>
        <h1 class="display-4 fw-bold">{{ $post->title }}</h1>
        <p class="lead">{{ $post->summary }}</p>
        <p class="text-muted">By {{ $post->author->name }} · {{ $post->published_at->format('F j, Y') }} · {{ $post->reading_time }} min read</p>
        @if($post->featured_image_path)<img class="img-fluid rounded-4 w-100 my-4" src="{{ Storage::url($post->featured_image_path) }}" alt="{{ $post->title }}">@endif
    </header>
    <nav class="mx-auto article-share" style="max-width:800px" aria-label="Share this article">
        <span>Share this article</span>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('blog.show', $post->slug)) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn"><i class="bi bi-linkedin"></i></a>
        <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('blog.show', $post->slug)) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener noreferrer" aria-label="Share on X"><i class="bi bi-twitter-x"></i></a>
        <a href="mailto:?subject={{ rawurlencode($post->title) }}&body={{ rawurlencode(route('blog.show', $post->slug)) }}" aria-label="Share by email"><i class="bi bi-envelope"></i></a>
    </nav>
    <div class="mx-auto rich-content" style="max-width:800px">{!! nl2br(e($post->body)) !!}</div>
    @if($post->tags->isNotEmpty())<div class="mx-auto mt-4" style="max-width:800px">@foreach($post->tags as $tag)<a class="badge text-bg-light text-decoration-none" href="{{ route('blog', ['tag'=>$tag->slug]) }}">{{ $tag->name }}</a>@endforeach</div>@endif
    @if($post->faqs->isNotEmpty())<section class="mx-auto mt-5" style="max-width:800px"><h2>Frequently asked questions</h2>@foreach($post->faqs as $faq)<details class="card p-3 mb-2"><summary>{{ $faq->question }}</summary><p class="mt-3">{{ $faq->answer }}</p></details>@endforeach</section>@endif
    @if($related->isNotEmpty())<section class="mt-5"><h2>Related articles</h2><div class="row g-4">@foreach($related as $item)<div class="col-md-4"><x-blog-card :post="$item" /></div>@endforeach</div></section>@endif
</article>
@endsection
