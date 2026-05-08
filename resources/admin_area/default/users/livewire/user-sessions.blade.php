<?php

use Livewire\Volt\Component;
use App\Models\Session;

new class extends Component
{
    #[\Livewire\Attributes\Locked]
    public $user_id;

    #[\Livewire\Attributes\Computed]
    public function sessions()
    {
        return Session::where('user_id', $this->user_id)->latest('last_activity')->paginate(6);
    }

    public function logoutSession($sessionId)
    {
        abort_if(!auth()->user()->hasPerm('admin.users.update'), 403);

        $session = Session::where('id', $sessionId)->where('user_id', $this->user_id)->first();

        if ($session) {
            $session->delete();
        }
    }
}

?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">Sessions</h3>
    </div>
    <div class="list-group list-group-flush list-group-hoverable">
        @foreach($this->sessions as $session)
            <div class="list-group-item">
                <div class="row align-items-center">
                    <div class="col-auto">
                        @if($session->isDesktopDevice())
                            <x-admin::icon icon="device-imac" class="icon"/>
                        @else
                            <x-admin::icon icon="device-mobile" class="icon"/>
                        @endif
                    </div>
                    <div class="col text-truncate">
                        <a class="text-reset d-block">{{ $session->operatingSystem() }} ({{ $session->browser() }})</a>
                        <div class="d-block text-secondary text-truncate mt-n1">
                            <span>{{ $session->ip_address }}</span>
                            <span class="mx-0">·</span>
                            <span>Last seen: {{ $session->last_activity->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a class="list-group-item-actions" wire:confirm="" wire:click="logoutSession('{{ $session->id }}')">
                            <x-admin::icon icon="trash" class="icon"/>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card-footer">
        {{ $this->sessions->links('pagination::bootstrap-5') }}
    </div>
</div>



