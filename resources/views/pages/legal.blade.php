@extends('layouts.app')
@section('title',$cmsPage->meta_title ?: $cmsPage->simpleContent->hero_heading)
@section('description',$cmsPage->meta_description ?: $cmsPage->simpleContent->hero_description)
@section('canonical',$cmsPage->canonical_url ?: url()->current())
@push('head')@unless($cmsPage->is_indexable)<meta name="robots" content="noindex,nofollow">@endunless @endpush
@section('content')
@php($c = $cmsPage->simpleContent)
<header class="page-hero" @if($c->hero_banner_path)style="--hero-image:url('{{ Storage::url($c->hero_banner_path) }}')"@endif><div class="container"><p class="eyebrow">{{ $c->hero_label ?: $c->hero_heading }}</p><h1>{{ $c->hero_heading }}</h1>@if($c->hero_description)<p>{{ $c->hero_description }}</p>@endif</div></header>
<section class="page-section"><div class="container"><div class="story-copy">{!! nl2br(e($c->body_content ?: 'Content will be published soon.')) !!}</div></div></section>
@endsection
