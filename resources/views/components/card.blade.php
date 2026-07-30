@props(['title' => null])
<section {{ $attributes->class('card') }}>
    @if($title)<div class="card-header"><h2 class="card-title">{{ $title }}</h2></div>@endif
    <div class="card-body">{{ $slot }}</div>
</section>
