<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component
{
    public $user;

    public $emailHasBeenResent = false;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    #[\Livewire\Attributes\On('user-updated')]
    public function userUpdated()
    {

    }

    public function resendVerificationEmail()
    {
        $this->user->emailVerificationToken();
        $this->emailHasBeenResent = true;
        $this->dispatch('alert', 'success', 'Verification email has been resent.');
    }

    public function verifyEmailManually()
    {
        $this->user->markEmailAsVerified();
        $this->dispatch('user-updated', $this->user);
    }

    public function disableTFA()
    {
        User::actions()->disableTwoFactorAuthAsAdmin([
            'user_id' => $this->user->id,
        ]);

        $this->dispatch('user-updated');
    }
}

?>

<div>
    @if($user->isStaff())
        <x-admin::alerts.warning title="Viewing Staff Member" message="You are viewing a staff user"/>
    @endif

    @if($user->status == 'pending')
        <x-admin::alerts.info title="Account Pending Approval" message="This user account is currently pending approval by an administrator."/>
    @elseif($user->status == 'suspended')
        <x-admin::alerts.danger title="Account Suspended" message="This user account has been suspended."/>
    @endif

    @if(!$user->hasVerifiedEmail())
    <div class="alert alert-warning alert-dismissible" role="alert">
        <h3 class="mb-1">Email not verified</h3>
        <p class="text-secondary">
            This user has not verified their email address.
        </p>
        <div class="btn-list">
            <button type="button" class="btn btn-success" wire:click="resendVerificationEmail()" @if($emailHasBeenResent) disabled @endif onclick="isLoading(this)">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mail-fast"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7h3" /><path d="M3 11h2" /><path d="M9.02 8.801l-.6 6a2 2 0 0 0 1.99 2.199h7.98a2 2 0 0 0 1.99 -1.801l.6 -6a2 2 0 0 0 -1.99 -2.199h-7.98a2 2 0 0 0 -1.99 1.801z" /><path d="M9.8 7.5l2.982 3.28a3 3 0 0 0 4.238 .202l3.28 -2.982" /></svg>
                @if($emailHasBeenResent)
                    Email Resent
                @else
                    Resend verification email
                @endif
            </button>
            <button type="button" class="btn btn-warning" wire:click="verifyEmailManually()" wire:confirm="Are you sure you want to manually verify this users email?">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-mail-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M11 19h-6a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v6" /><path d="M3 7l9 6l9 -6" /><path d="M15 19l2 2l4 -4" /></svg>
                Verify email manually
            </button>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
    </div>
    @endif

    @if($user->tfa_enabled)
        <div class="alert alert-success alert-dismissible" role="alert">
            <h3 class="mb-1">
                Two-Factor Authentication is enabled
            </h3>
            <p class="text-secondary">
                This user has two-factor authentication enabled.
            </p>
            <div class="btn-list">
                <button type="button" class="btn btn-danger" wire:click="disableTFA()" wire:confirm.prompt="Type 'confirm' to disable two factor authentication for this user.|confirm">
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-device-mobile-off"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7.159 3.185c.256 -.119 .54 -.185 .841 -.185h8a2 2 0 0 1 2 2v9m0 4v1a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2v-13" /><path d="M11 4h2" /><path d="M3 3l18 18" /><path d="M12 17v.01" /></svg>                    Disable 2FA
                </button>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif
</div>



