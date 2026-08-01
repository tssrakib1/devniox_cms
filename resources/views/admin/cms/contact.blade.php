@extends('layouts.admin')
@section('title','Contact Editor')
@section('heading','Contact Page')
@section('content')
@php
    $c = $page->contact;
    $fallbackHours = collect(range(0, 6))->map(fn ($day) => (object) ['day_of_week' => $day, 'is_closed' => in_array($day, [0, 6]), 'opens_at' => '09:00', 'closes_at' => '18:00', 'holiday_text' => null]);
    $hoursRows = $page->businessHours->isNotEmpty() ? $page->businessHours->sortBy('day_of_week')->values() : $fallbackHours;
    $textFields = ['hero_label','hero_heading','hero_description','company_name','address','email','phone','whatsapp','map_embed_url','success_message','auto_reply_subject','auto_reply_message'];
    $labelFields = ['response_primary_cta_text','response_secondary_cta_text','email_card_label','phone_card_label','whatsapp_card_label','inquiry_card_label','guidance_label','guidance_title','guidance_description','form_label','form_description','form_name_label','form_company_label','form_email_label','form_phone_label','optional_label','website_label','subject_label','message_label','submit_button_text','office_label','business_hours_heading','closed_label','map_link_text'];
    $guidanceGroups = [
        'one' => 'Business inquiry',
        'two' => 'Product demo',
        'three' => 'Quote request',
    ];
@endphp
<form method="post" enctype="multipart/form-data" action="{{ route('admin.cms.pages.update',$page) }}">
    @csrf
    @method('PUT')
    <x-card title="Contact content">
        <div class="row g-3">
            @foreach($textFields as $field)
                <div class="col-md-6">
                    <label class="form-label">{{ Str::headline($field) }}</label>
                    @if(Str::contains($field, ['description','message','address']))
                        <textarea class="form-control" name="{{ $field }}" rows="4" @if(in_array($field,['hero_description'])) required @endif>{{ old($field,$c->{$field}) }}</textarea>
                    @else
                        <input class="form-control" name="{{ $field }}" value="{{ old($field,$c->{$field}) }}" @if(in_array($field,['hero_heading','company_name','email','success_message'])) required @endif>
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
            <input type="hidden" name="auto_reply_enabled" value="0">
            <div class="col-md-6 d-flex align-items-end">
                <label class="form-check"><input class="form-check-input" type="checkbox" name="auto_reply_enabled" value="1" @checked(old('auto_reply_enabled',$c->auto_reply_enabled))> Enable auto reply</label>
            </div>
        </div>
    </x-card>

    <x-card title="Labels and CTA text" class="mt-4">
        <div class="row g-3">
            @foreach($labelFields as $field)
                <div class="col-md-6">
                    <label class="form-label">{{ Str::headline($field) }}</label>
                    @if(Str::contains($field, ['description']))
                        <textarea class="form-control" name="{{ $field }}" rows="3">{{ old($field,$c->{$field}) }}</textarea>
                    @else
                        <input class="form-control" name="{{ $field }}" value="{{ old($field,$c->{$field}) }}">
                    @endif
                </div>
            @endforeach
        </div>
    </x-card>

    <x-card title="Guidance cards" class="mt-4">
        <div class="row g-3">
            @foreach($guidanceGroups as $key => $label)
                <div class="col-12"><h3 class="h6 mb-0">{{ $label }}</h3></div>
                @foreach(['title','description','helper_title','helper_items'] as $suffix)
                    @php $field = 'guidance_'.$key.'_'.$suffix; @endphp
                    <div class="col-md-6">
                        <label class="form-label">{{ Str::headline($field) }}</label>
                        @if(Str::contains($suffix, ['description','items']))
                            <textarea class="form-control" name="{{ $field }}" rows="4">{{ old($field,$c->{$field}) }}</textarea>
                        @else
                            <input class="form-control" name="{{ $field }}" value="{{ old($field,$c->{$field}) }}">
                        @endif
                    </div>
                @endforeach
                @if($key !== 'one')
                    @php $field = 'guidance_'.$key.'_link_text'; @endphp
                    <div class="col-md-6">
                        <label class="form-label">{{ Str::headline($field) }}</label>
                        <input class="form-control" name="{{ $field }}" value="{{ old($field,$c->{$field}) }}">
                    </div>
                @endif
            @endforeach
        </div>
    </x-card>

    <x-card title="Business hours" class="mt-4">
        <div class="row g-3">
            @foreach($hoursRows as $index => $hours)
                <div class="col-12">
                    <div class="row g-2 align-items-end">
                        <input type="hidden" name="business_hours[{{ $index }}][day_of_week]" value="{{ $hours->day_of_week }}">
                        <input type="hidden" name="business_hours[{{ $index }}][is_closed]" value="0">
                        <div class="col-md-2"><label class="form-label">Day</label><input class="form-control" value="{{ Carbon\Carbon::create()->startOfWeek()->addDays(($hours->day_of_week+6)%7)->format('l') }}" disabled></div>
                        <div class="col-md-2"><label class="form-check"><input class="form-check-input" type="checkbox" name="business_hours[{{ $index }}][is_closed]" value="1" @checked(old('business_hours.'.$index.'.is_closed',$hours->is_closed))> Closed</label></div>
                        <div class="col-md-2"><label class="form-label">Opens at</label><input class="form-control" type="time" name="business_hours[{{ $index }}][opens_at]" value="{{ old('business_hours.'.$index.'.opens_at',substr((string) $hours->opens_at,0,5)) }}"></div>
                        <div class="col-md-2"><label class="form-label">Closes at</label><input class="form-control" type="time" name="business_hours[{{ $index }}][closes_at]" value="{{ old('business_hours.'.$index.'.closes_at',substr((string) $hours->closes_at,0,5)) }}"></div>
                        <div class="col-md-4"><label class="form-label">Holiday text</label><input class="form-control" name="business_hours[{{ $index }}][holiday_text]" value="{{ old('business_hours.'.$index.'.holiday_text',$hours->holiday_text) }}"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>

    @include('admin.cms.seo')
    <button class="btn btn-primary my-4">Publish contact content</button>
</form>
@endsection

