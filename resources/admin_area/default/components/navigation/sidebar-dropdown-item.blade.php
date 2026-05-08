{{-- resources/views/components/sidebar-dropdown-item.blade.php --}}

<li>
    <a wire:navigate href="{{ $href }}" class="dropdown-item {{ ($active ?? false) ? 'active' : '' }}">
        <span class="nav-link-icon d-md-none d-lg-inline-block">
            @if(!empty($icon))
                <x-admin::icon :icon="$icon" outline/>
            @else
                <x-admin::icon icon="chevron-right" outline/>
            @endif
        </span>
        <span class="nav-link-title">{{ $title }}</span>
    </a>
</li>
