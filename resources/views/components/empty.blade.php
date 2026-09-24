@props(['title', 'action' => null, 'href' => null])
<div class="empty">
    <strong>{{ $title }}</strong>
    <div>{{ $slot }}</div>
    @if ($action && $href)
        <a href="{{ $href }}" class="btn btn-primary btn-sm mt-3">{{ $action }}</a>
    @endif
</div>
