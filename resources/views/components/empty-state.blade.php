@props(['title' => 'Nothing here yet', 'icon' => 'inbox'])
<div class="empty-state" role="status"><i class="bi bi-{{ $icon }} fs-1" aria-hidden="true"></i><h3 class="h5 mt-3">{{ $title }}</h3>@if(trim((string) $slot) !== '')<div class="text-muted">{{ $slot }}</div>@endif</div>
