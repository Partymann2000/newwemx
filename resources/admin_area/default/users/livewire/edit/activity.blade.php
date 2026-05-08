<?php

use Livewire\Volt\Component;
use App\Models\ActivityLog;

new class extends Component
{
    public $user;

    public $filterBy = 'all';

    #[\Livewire\Attributes\Url]
    public $filterByField = '';

    public function filter($filter)
    {
        // ensure filter is all, user, or admin
        if (!in_array($filter, ['all', 'user', 'admin'])) {
            $filter = 'all';
        }

        $this->filterBy = $filter;
    }
}

?>

@php
    $address = $user->address;

    $activities = ActivityLog::where(function ($query) use ($user, $address) {
        $query->where(function ($q) use ($user) {
            $q->where('model_type', \App\Models\User::class)
              ->where('model_id', $user->id);
        });

        if ($address) {
            $query->orWhere(function ($q) use ($address) {
                $q->where('model_type', \App\Models\Address::class)
                  ->where('model_id', $address->id);
            });
        }
    })
    ->oldest()
    ->get();

    if ($filterBy == 'user') {
        $activities = $activities->where('user_id', $user->id);
    } elseif ($filterBy == 'admin') {
        $activities = $activities->where('user_id', '!=', $user->id);
    }

    if ($filterByField) {
        $activities = $activities->where('field', $filterByField);
    }
@endphp


<div>
    <div class="d-flex align-items-center mb-3">
        <div class="dropdown me-2">
            <a href="#" class="btn dropdown-toggle" data-bs-toggle="dropdown">Sort Causer</a>
            <div class="dropdown-menu">
                <a class="dropdown-item" wire:click="filter('all')" href="#">All Activities</a>
                <a class="dropdown-item" wire:click="filter('user')" href="#">By User</a>
                <a class="dropdown-item" wire:click="filter('admin')" href="#">By Admin</a>
            </div>
        </div>
    </div>
    <div>
        <ul class="timeline timeline-simple">
            @foreach($activities as $activity)
            <li class="timeline-event">
                <div class="card timeline-event-card">
                    <div class="card-body">
                        <div class="text-secondary float-end">
                            {{ $activity->created_at->diffForHumans() }} ({{ $activity->created_at->format('Y-m-d H:i') }})
                        </div>
                        <h4>
                            {{ $activity->description }}
                        </h4>
                        <p class="text-secondary">
                            @if($activity->old_value)
                                From <code>{{ $activity->old_value }}</code>
                            @else
                                From no previous value
                            @endif
                            @if($activity->new_value)
                                To <code>{{ $activity->new_value }}</code>
                            @else
                                To no new value
                            @endif
                        </p>
                        <div class="d-flex align-items-center mb-3">
                            @if($activity->user)
                            <span class="avatar avatar-xs me-2 rounded" style="background-image: url({{ $activity->user->getAvatarUrl() }})"></span>
                            @endif
                            <span class="text-secondary">
                                @if($activity->user)
                                    <a href="{{ route('admin.users.edit', $activity->user_id) }}" target="_blank">{{ $activity->user->username }} ({{ $activity->user->email }})</a>
                                @else
                                    Deleted User
                                @endif
                            </span>
                        </div>
                        @if($activity->request)
                        <div>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $activity->id }}-tabs" aria-expanded="false">
                                        <span class="accordion-button-text mb-2">Request Data</span>
                                        <div class="accordion-button-toggle">
                                            <!-- Download SVG icon from http://tabler.io/icons/icon/chevron-down -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-1">
                                                <path d="M6 9l6 6l6 -6"></path>
                                            </svg>
                                        </div>
                                    </button>
                                </div>
                                <div id="collapse-{{ $activity->id }}-tabs" class="accordion-collapse collapse" data-bs-parent="#accordion-tabs" style="">
                                    <div class="accordion-body">
                                        <pre>IP Address: {{ $activity->ip_address }}</pre>
                                        <pre>{{ $activity->request ? json_encode($activity->request, JSON_PRETTY_PRINT) : 'No request data' }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach

        </ul>

    </div>
</div>
