@props(['name', 'label', 'type' => 'text'])
@php($errorId = $name.'-error')
<div class="mb-3">
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($name, $attributes->get('value')) }}" @error($name) aria-describedby="{{ $errorId }}" aria-invalid="true" @enderror {{ $attributes->except('value')->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)<div class="invalid-feedback" id="{{ $errorId }}">{{ $message }}</div>@enderror
</div>
