<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $user;

    public $first_name;

    public $last_name;

    public $username;

    public $email;

    public $status;

    public $statusOptions = [
        'active' => 'Active (The user will be able to use their account)',
        'pending' => 'Pending (An admin needs to approve the user)',
        'suspended' => 'Suspended (The user will no longer be able to use their account)',
    ];

    public $password;

    public function mount($user)
    {
        $this->user = $user;

        $this->first_name = $user->first_name;

        $this->last_name = $user->last_name;

        $this->username = $user->username;

        $this->email = $user->email;

        $this->status = $user->status;
    }

    public function updateUser()
    {
        abort_if(!auth()->user()->hasPerm('admin.users.update'), 403);

        $this->resetErrorBag();

        User::actions()->updateUserAsAdmin([
            'user_id' => $this->user->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'status' => $this->status,
            'password' => $this->password,
        ]);

        $this->dispatch('user-updated');
    }
}

?>

<div>
    <div class="row mb-3">
        <div class="col-12 col-md-6 mb-3">
            <x-admin::form.label>
                {{ __('messages.first_name') }}

                @if($firstNameChangeCount = $user->getActivityLogCountForField('first_name'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'first_name']) }}">{{ $firstNameChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model.change="first_name" name="first_name" placeholder="{{ __('messages.first_name') }}" required />
            @error('first_name')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 col-md-6 mb-3">
            <x-admin::form.label>
                {{ __('messages.last_name') }}
                @if($lastNameChangeCount = $user->getActivityLogCountForField('last_name'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'last_name']) }}">{{ $lastNameChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="last_name" name="last_name" placeholder="{{ __('messages.last_name') }}" />
            @error('last_name')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.username') }}
                @if($usernameChangeCount = $user->getActivityLogCountForField('username'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'username']) }}">{{ $usernameChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="text" wire:model="username" name="username" placeholder="{{ __('messages.username') }}" required />
            @error('username')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.email') }}
                @if($emailChangeCount = $user->getActivityLogCountForField('email'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'email']) }}">{{ $emailChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="email" wire:model="email" name="email" placeholder="{{ __('messages.email') }}" required />
            @error('email')
                <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.status') }}
                @if($emailChangeCount = $user->getActivityLogCountForField('status'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'status']) }}">{{ $emailChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.select wire:model="status" searchable="1" value="{{ $this->status }}" :options="$statusOptions" name="status" id="status" required/>
            @error('status')
            <x-admin::form.error :message="$message" />
            @enderror
        </div>
        <div class="col-12 mb-3">
            <x-admin::form.label>
                {{ __('messages.password') }}
                @if($passwordChangeCount = $user->getActivityLogCountForField('password'))
                    <a class="form-label-description text-info" href="{{ route('admin.users.edit', ['user' => $user->id, 'userEditPage' => 'activity', 'filterByField' => 'password']) }}">{{ $passwordChangeCount }} changes</a>
                @endif
            </x-admin::form.label>
            <x-admin::form.input type="password" wire:model="password" name="password" placeholder="{{ __('messages.password') }}" />
            @error('password')
                <x-admin::form.error :message="$message" />
            @else
                <small class="form-hint">
                    Leave empty to keep the current password
                </small>
            @enderror
        </div>
    </div>
    <div class="text-end">
        <button type="button" wire:click="updateUser()" class="btn btn-primary">{{ __('messages.update') }}</button>
    </div>
</div>
