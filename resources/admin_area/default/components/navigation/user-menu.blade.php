<div class="nav-item dropdown">
    <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
        <span class="avatar avatar-sm" style="background-image: url('{{ $user->getAvatarUrl() }}')"></span>
        <div class="d-none d-xl-block ps-2">
            <div>{{ $user->getFullNameAttribute() }}</div>
            <div class="mt-1 small text-secondary">{{ $user->email }}</div>
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
        @foreach ($this->menuItems as $item)
            @if($item['divider'] ?? false)
                <div class="dropdown-divider"></div>
            @else
                @isset($item['callable'])
                    <a href="#" wire:click.prevent="{{ $item['callable'] }}" class="dropdown-item">
                        {{ $item['title'] }}
                    </a>
                @else
                    <a wire:navigate href="{{ $item['href'] }}" class="dropdown-item">
                        {{ $item['title'] }}
                    </a>
                @endisset
            @endif
        @endforeach
    </div>
</div>
