{{-- resources/views/components/sidebar-item.blade.php --}}

<li class="nav-item dropdown {{ ($active ?? false) ? 'active' : '' }}">
    @if(isset($dropdown) && $dropdown)
        <a class="nav-link dropdown-toggle {{ ($active ?? false) ? '' : 'collapsed' }}" href="#" id="dropdown-{{ $id }}" role="button"
           data-bs-toggle="dropdown" aria-expanded="{{ ($active ?? false) ? 'true' : 'false' }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                {!! $icon ?? '' !!}
            </span>
            <span class="nav-link-title">{{ $title }}</span>
        </a>
        <ul class="dropdown-menu ps-1 {{ ($active ?? false) ? 'show' : '' }}" aria-labelledby="dropdown-{{ $id }}">
            {{ $slot }}
        </ul>
    @else
        <a wire:navigate class="nav-link {{ ($active ?? false) ? 'active' : '' }}" href="{{ $href }}">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
                {!! $icon ?? '' !!}
            </span>
            <span class="nav-link-title">{{ $title }}</span>
        </a>
    @endif
</li>
