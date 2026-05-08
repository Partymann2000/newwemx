<?php

use App\Models\Session;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component
{
    public $user;

    #[\Livewire\Attributes\Computed]
    public function altAccounts()
    {
        $userIps = Session::query()
            ->where('user_id', $this->user->id)
            ->whereNotNull('ip_address')
            ->pluck('ip_address')
            ->filter()
            ->unique()
            ->values();

        if ($userIps->isEmpty()) {
            return collect();
        }

        $altSessions = Session::query()
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $this->user->id)
            ->whereIn('ip_address', $userIps)
            ->get(['user_id', 'ip_address', 'last_activity', 'user_agent']);

        if ($altSessions->isEmpty()) {
            return collect();
        }

        $altUsers = User::query()
            ->whereIn('id', $altSessions->pluck('user_id')->unique())
            ->get()
            ->keyBy('id');

        return $altSessions
            ->groupBy('user_id')
            ->map(function ($sessions, $userId) use ($altUsers) {
                $altUser = $altUsers->get($userId);
                if (!$altUser) {
                    return null;
                }

                return [
                    'user' => $altUser,
                    'shared_ips' => $sessions->pluck('ip_address')->filter()->unique()->values()->all(),
                    'shared_ip_count' => $sessions->pluck('ip_address')->filter()->unique()->count(),
                    'last_seen_at' => $sessions->max('last_activity'),
                    'recent_user_agent' => $sessions->sortByDesc('last_activity')->first()?->user_agent,
                ];
            })
            ->filter()
            ->sortByDesc('last_seen_at')
            ->values();
    }
}

?>

<div>
    <p class="text-secondary">
        Potential alt accounts are detected by finding other users who share session IP addresses with this user.
    </p>

    @if($this->altAccounts->isEmpty())
        <div class="alert alert-success" role="alert">
            No potential alt accounts found based on current session data.
        </div>
    @else
        <div class="alert alert-warning" role="alert">
            Found {{ $this->altAccounts->count() }} potential alt account{{ $this->altAccounts->count() === 1 ? '' : 's' }}.
        </div>

        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Shared IPs</th>
                    <th>Shared IP Count</th>
                    <th>Last Seen</th>
                    <th class="w-1"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($this->altAccounts as $alt)
                    <tr>
                        <td>
                            <div class="d-flex py-1 align-items-center">
                                <span class="avatar me-2" style="background-image: url({{ $alt['user']->getAvatarUrl() }})"></span>
                                <div class="flex-fill">
                                    <div class="font-weight-medium">{{ $alt['user']->username }}</div>
                                    <div class="text-secondary">{{ $alt['user']->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary">{{ implode(', ', $alt['shared_ips']) }}</td>
                        <td class="text-secondary">{{ $alt['shared_ip_count'] }}</td>
                        <td class="text-secondary">
                            @if($alt['last_seen_at'])
                                {{ \Illuminate\Support\Carbon::parse($alt['last_seen_at'])->diffForHumans() }}
                            @else
                                Never
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', ['user' => $alt['user']->id]) }}" wire:navigate>View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
