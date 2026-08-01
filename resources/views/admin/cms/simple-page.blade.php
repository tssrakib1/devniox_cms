@extends('layouts.admin')
@section('title', Str::headline($page->key).' Editor')
@section('heading', Str::headline($page->key).' Page')
@section('content')
@php
    $c = $page->simpleContent;
    $fields = ['hero_label','hero_heading','hero_description','intro_label','intro_title','intro_description','note_icon','note_text','chapter_one_label','chapter_two_label','chapter_three_label','name_label','company_label','email_label','phone_label','item_type_label','item_id_label','preferred_date_label','preferred_time_label','message_label','budget_label','budget_placeholder','timeline_label','timeline_placeholder','requirement_details_label','attachment_label','attachment_helper','optional_label','submit_button_text','success_message'];
    $isLegal = in_array($page->key, ['privacy-policy','terms-conditions'], true);
@endphp
<form method="post" enctype="multipart/form-data" action="{{ route('admin.cms.pages.update',$page) }}">
    @csrf
    @method('PUT')
    <x-card title="Page content">
        <div class="row g-3">
            @foreach($fields as $field)
                <div class="col-md-6">
                    <label class="form-label">{{ Str::headline($field) }}</label>
                    @if(Str::contains($field, ['description','note_text']))
                        <textarea class="form-control" name="{{ $field }}" rows="4">{{ old($field,$c->{$field}) }}</textarea>
                    @else
                        <input class="form-control" name="{{ $field }}" value="{{ old($field,$c->{$field}) }}" @if($field==='hero_heading') required @endif>
                    @endif
                </div>
            @endforeach
            <div class="col-md-6">
                <label class="form-label">Hero banner</label>
                @if($c->hero_banner_path)
                    <div class="mb-2"><img src="{{ Storage::url($c->hero_banner_path) }}" alt="" style="max-width:220px;height:auto;border-radius:8px"></div>
                    <label class="form-check mb-2"><input class="form-check-input" type="checkbox" name="remove_images[hero_banner]" value="1"> Remove current hero banner</label>
                @endif
                <input class="form-control" type="file" name="hero_banner" accept="image/png,image/jpeg,image/webp">
            </div>
            @if($isLegal)
                <div class="col-12"><label class="form-label">Body content</label><textarea class="form-control" name="body_content" rows="14">{{ old('body_content',$c->body_content) }}</textarea></div>
            @endif
        </div>
    </x-card>
    @if(! $isLegal)
        <x-card title="Steps and bullets" class="mt-4">
            <div class="row g-3">
                @foreach(collect(old('steps',$c->steps ?: ['']))->pad(4, '') as $index => $step)
                    <div class="col-md-6"><label class="form-label">Step {{ $index + 1 }}</label><input class="form-control" name="steps[]" value="{{ $step }}"></div>
                @endforeach
                @foreach(collect(old('bullets',$c->bullets ?: ['']))->pad(5, '') as $index => $bullet)
                    <div class="col-md-6"><label class="form-label">Bullet {{ $index + 1 }}</label><input class="form-control" name="bullets[]" value="{{ $bullet }}"></div>
                @endforeach
            </div>
        </x-card>
    @endif
    @include('admin.cms.seo')
    <button class="btn btn-primary my-4">Publish {{ Str::headline($page->key) }} content</button>
</form>
@endsection
