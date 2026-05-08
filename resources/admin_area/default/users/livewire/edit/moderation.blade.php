<?php

use App\Models\Session;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public $user;
    public $reason = '';
    public $expires_at = null;
    public $ip_ban = false;

    #[\Livewire\Attributes\Computed]
    public function bans()
    {
        return $this->user->bans()->latest()->get();
    }

    #[\Livewire\Attributes\Computed]
    public function latestSessionIp()
    {
        return Session::query()
            ->where('user_id', $this->user->id)
            ->whereNotNull('ip_address')
            ->latest('last_activity')
            ->value('ip_address');
    }

    public function createBan()
    {
        abort_if(!auth()->user()->hasPerm('admin.users.update'), 403);
        $this->resetErrorBag();

        try {
            User::actions()->banUserAsAdmin([
                'user_id' => $this->user->id,
                'admin_id' => auth()->id(),
                'reason' => $this->reason ?: null,
                'expires_at' => $this->expires_at ?: null,
                'ip_ban' => (bool) $this->ip_ban,
            ]);
        } catch (ValidationException $e) {
            $reasonError = $e->errors()['reason'][0]
                ?? $e->errors()['ip_ban'][0]
                ?? $e->errors()['user_id'][0]
                ?? 'Unable to create ban.';
            $this->addError('reason', $reasonError);
            return;
        } catch (\Throwable $e) {
            $this->addError('reason', $e->getMessage() ?: 'Unable to create ban.');
            return;
        }

        $this->reset(['reason', 'expires_at', 'ip_ban']);
    }

    public function liftBan($banId)
    {
        abort_if(!auth()->user()->hasPerm('admin.users.update'), 403);

        User::actions()->liftBanAsAdmin([
            'ban_id' => $banId,
            'admin_id' => auth()->id(),
        ]);
    }
}

?>

<div>
    <div class="alert alert-info mb-3" role="alert">
        <div>Latest session IP: <code>{{ $this->latestSessionIp ?: 'No session IP found' }}</code></div>
        <div class="text-secondary">IP bans are blocked for local/private/reserved addresses.</div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Create Ban</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <x-admin::form.label for="ban_reason" label="Reason (optional)" />
                    <x-admin::form.textarea id="ban_reason" wire:model="reason" rows="3" placeholder="Optional reason for moderation action." />
                @error('reason')
                    <x-admin::form.error :message="$message" />
                @enderror
                </div>
                <div class="col-12 col-md-6">
                    <x-admin::form.label for="ban_expires_at" label="Ban Expiry Date (optional)" />
                    <x-admin::form.input id="ban_expires_at" type="datetime-local" wire:model="expires_at" />
                    @error('expires_at')
                    <x-admin::form.error :message="$message" />
                    @enderror
                </div>
                <div class="col-12 col-md-6 d-flex align-items-end">
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" wire:model="ip_ban">
                        <span class="form-check-label">Also apply IP ban using latest session IP</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="button" class="btn btn-danger" wire:click="createBan" wire:confirm="Are you sure you want to apply this ban?">Create Ban</button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ban History</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter">
                <thead>
                <tr>
                    <th>Status</th>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Expires</th>
                    <th>Created</th>
                    <th>Lifted</th>
                    <th>Lifted By</th>
                    <th class="w-1"></th>
                </tr>
                </thead>
                <tbody>
                @forelse($this->bans as $ban)
                    @php($isActive = $ban->isActive())
                    <tr>
                        <td>
                            @if($isActive)
                                <span class="badge bg-red-lt">Active</span>
                            @else
                                <span class="badge bg-green-lt">Inactive</span>
                            @endif
                        </td>
                        <td class="text-secondary">
                            {{ $ban->is_ip_ban ? 'Account + IP' : 'Account' }}
                            @if($ban->is_ip_ban && $ban->ip_address)
                                <div><code>{{ $ban->ip_address }}</code></div>
                            @endif
                        </td>
                        <td class="text-secondary">{{ $ban->reason ?: 'No reason provided.' }}</td>
                        <td class="text-secondary">{{ $ban->expires_at ? $ban->expires_at->format(settings('date_format', 'd M Y H:i')) : 'No expiry' }}</td>
                        <td class="text-secondary">{{ $ban->created_at->format(settings('date_format', 'd M Y H:i')) }}</td>
                        <td class="text-secondary">{{ $ban->lifted_at ? $ban->lifted_at->format(settings('date_format', 'd M Y H:i')) : '-' }}</td>
                        <td class="text-secondary">
                            @if($ban->liftedBy)
                                <a href="{{ route('admin.users.edit', ['user' => $ban->liftedBy->id]) }}" wire:navigate>
                                    {{ $ban->liftedBy->username }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($isActive)
                                <a href="#" class="text-danger" wire:click.prevent="liftBan({{ $ban->id }})" wire:confirm="Lift this ban?">Lift</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-secondary">No bans found for this user.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
