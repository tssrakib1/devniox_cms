@props(['type' => 'info'])
<div {{ $attributes->class(['alert', 'alert-'.$type]) }} role="alert" aria-live="polite">{{ $slot }}</div>
