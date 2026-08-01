@extends('layouts.admin')
@section('title','Footer')
@section('heading','Footer Editor')
@section('content')
@php
    $textGroups = [
        'Brand and headings' => ['copyright','short_description','quick_links_heading','company_heading','resources_heading','products_heading','services_heading','ai_heading','blog_heading'],
        'Contact copy' => ['contact_heading','contact_text','address_label','email_label','phone_label','whatsapp_label','business_hours_text','support_hours_text'],
        'CTA' => ['cta_title','cta_description','cta_button_text','cta_button_url'],
        'Newsletter' => ['newsletter_heading','newsletter_description','newsletter_placeholder','newsletter_button_text'],
        'Bottom bar' => ['bottom_text','made_by_text','powered_by_text','version_text'],
    ];
    $linkFields = ['about','contact','demo','quote','blog','rss','sitemap'];
    $legalFields = ['privacy','terms','cookies'];
@endphp
<form method="post" action="{{ route('admin.cms.footer.update') }}">
    @csrf
    @method('PUT')
    @foreach($textGroups as $title => $fields)
        <x-card :title="$title" class="{{ $loop->first ? '' : 'mt-4' }}">
            <div class="row g-3">
                @foreach($fields as $field)
                    <div class="col-md-6">
                        <label class="form-label">{{ Str::headline($field) }}</label>
                        @if(Str::contains($field, ['description','text','hours']) || $field === 'short_description')
                            <textarea class="form-control" name="{{ $field }}" rows="3" @if(in_array($field,['copyright','short_description','quick_links_heading','blog_heading'])) required @endif>{{ old($field,$footer->{$field}) }}</textarea>
                        @else
                            <input class="form-control" name="{{ $field }}" value="{{ old($field,$footer->{$field}) }}" @if(in_array($field,['copyright','quick_links_heading','blog_heading'])) required @endif>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-card>
    @endforeach

    <x-card title="Footer links" class="mt-4">
        <div class="row g-3">
            @foreach($linkFields as $name)
                <div class="col-md-3"><label class="form-label">{{ Str::headline($name) }} label</label><input class="form-control" name="{{ $name }}_label" value="{{ old($name.'_label',$footer->{$name.'_label'}) }}"></div>
                <div class="col-md-3"><label class="form-label">{{ Str::headline($name) }} URL</label><input class="form-control" name="{{ $name }}_url" value="{{ old($name.'_url',$footer->{$name.'_url'}) }}"></div>
            @endforeach
            @foreach($legalFields as $name)
                <div class="col-md-3"><label class="form-label">{{ Str::headline($name) }} label</label><input class="form-control" name="{{ $name }}_label" value="{{ old($name.'_label',$footer->{$name.'_label'}) }}"></div>
                <div class="col-md-3"><label class="form-label">{{ Str::headline($name) }} URL</label><input class="form-control" name="{{ $name }}_url" value="{{ old($name.'_url',$footer->{$name.'_url'}) }}"></div>
            @endforeach
        </div>
    </x-card>
    <p class="text-body-secondary small mt-3">Logo, dark logo, company name, tagline, address, email, phone, WhatsApp, and social profiles remain managed by Website Settings/Social Links.</p>
    <button class="btn btn-primary mt-4">Save footer</button>
</form>
@endsection
