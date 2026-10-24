<div class="empty-state text-center">
    <svg viewBox="0 0 64 64" fill="none" aria-hidden="true">
        <path d="M11 21h42v32H11V21Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round"/>
        <path d="m11 21 21-10 21 10M32 32v21M20 31h6M38 31h6" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="48" cy="15" r="7" fill="#e0e7ff" stroke="currentColor" stroke-width="2"/>
        <path d="M48 12v6M45 15h6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
    </svg>
    <h3 class="opacity-50">{{ $title }}</h3>
    <p class="opacity-50">{{ $description }}</p>
    @if (! empty($actionUrl) && ! empty($actionLabel))
        @if (! empty($actionTarget))
            <button class="btn btn-primary rounded-3 px-3 py-2" type="button" data-bs-toggle="{{ $actionTargetType ?? 'modal' }}" data-bs-target="#{{ $actionTarget }}">{{ $actionLabel }}</button>
        @else
            <a class="btn btn-primary rounded-3 px-3 py-2" href="{{ $actionUrl }}">{{ $actionLabel }}</a>
        @endif
    @endif
</div>
