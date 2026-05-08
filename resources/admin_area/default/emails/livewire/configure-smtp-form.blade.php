<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Mail;

new class extends Component
{
    public $host;

    public $port;

    public $encryption;

    public $mail_from_name;

    public $mail_from_address;

    public $username;

    public $password;

    public $connectionSuccessfull = false;

    public $connectionError = '';

    public function mount()
    {
        $this->host = config('mail.mailers.smtp.host');
        $this->port = config('mail.mailers.smtp.port');
        $this->encryption = config('mail.mailers.smtp.encryption');
        $this->mail_from_name = config('mail.from.name');
        $this->mail_from_address = config('mail.from.address');
        $this->username = config('mail.mailers.smtp.username');
        $this->password = config('mail.mailers.smtp.password');
    }

    public function testConnection()
    {
        // temporarily set the config values
        config([
            'mail.mailers.smtp.host' => $this->host,
            'mail.mailers.smtp.port' => $this->port,
            'mail.mailers.smtp.encryption' => $this->encryption,
            'mail.from.name' => $this->mail_from_name,
            'mail.from.address' => $this->mail_from_address,
            'mail.mailers.smtp.username' => $this->username,
            'mail.mailers.smtp.password' => $this->password,
        ]);

        try {
            Mail::raw('This is a test email', function ($message) {
                $message->to(auth()->user()->email)
                    ->subject('Testing SMTP');
            });
        } catch (\Exception $e) {
            $this->connectionError = $e->getMessage();
            $this->connectionSuccessfull = false;
            return;
        }

        $this->connectionSuccessfull = true;
        $this->connectionError = '';
    }

    public function updateSmtpConfig()
    {
        // before we update, make sure the connection is successfull
        if(!$this->connectionSuccessfull) {
            dd('Connection is not successfull');
            return;
        }

        // store values in the .env file
        (new \App\Helpers\EnvironmentWriter())->write([
            'MAIL_HOST' => $this->host,
            'MAIL_PORT' => $this->port,
            'MAIL_USERNAME' => $this->username,
            'MAIL_PASSWORD' => $this->password,
            'MAIL_ENCRYPTION' => $this->encryption,
            'MAIL_FROM_ADDRESS' => $this->mail_from_address,
            'MAIL_FROM_NAME' => $this->mail_from_name,
        ]);

        // reset the connection status
        $this->connectionSuccessfull = false;
        $this->connectionError = '';
    }
}

?>

<form class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('messages.configure_smtp') }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="host">Host</label>
            <div class="col">
                <input type="text" wire:model="host" class="form-control" aria-describedby="host" id="host" placeholder="Host" />
                @error('host')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The host of the SMTP server
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="port">Port</label>
            <div class="col">
                <input type="number" wire:model="port" class="form-control" aria-describedby="port" id="port" placeholder="Port" />
                @error('port')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The port of the SMTP server
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="encryption">Encryption</label>
            <div class="col">
                <select class="form-select @error('encryption') is-invalid @enderror" wire:model="encryption" id="encryption">
                    <option value="null">None</option>
                    <option value="tls">TLS</option>
                    <option value="ssl">SSL</option>
                </select>
                @error('encryption')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        Select the encryption type of the SMTP server
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="mail_from_name">Mail From Address</label>
            <div class="col">
                <input type="text" wire:model="mail_from_name" class="form-control" aria-describedby="mail_from_name" id="mail_from_name" placeholder="Mail From Name" />
                @error('mail_from_name')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The name that appears as the sender of the email
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="mail_from_address">Mail From Address</label>
            <div class="col">
                <input type="email" wire:model="mail_from_address" class="form-control" aria-describedby="mail_from_address" id="mail_from_address" placeholder="Mail From Address" />
                @error('mail_from_address')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The email address that appears as the sender of the email
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="username">Username</label>
            <div class="col">
                <input type="text" wire:model="username" class="form-control" aria-describedby="username" id="username" placeholder="Username" />
                @error('username')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The username of the SMTP server
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="password">Password</label>
            <div class="col">
                <input type="password" wire:model="password" class="form-control" aria-describedby="password" id="password" placeholder="Password" />
                @error('password')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">
                        The password of the SMTP server
                    </small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="test_connection">Test Connection</label>
            <div class="col">
                <button type="button" class="btn btn-primary mb-2" wire:click="testConnection()" onclick="isLoading(this)">
                    <i class="ti ti-plug-connected icon"></i>
                    Test Connection
                </button>
                @if($connectionError)
                    <div class="alert alert-important alert-danger alert-dismissible mt-2" wire:loading.remove>
                        {{ $connectionError }}
                    </div>
                @endif
                @if($connectionSuccessfull)
                    <div class="alert alert-important alert-success alert-dismissible mt-2" wire:loading.remove>
                        Successfully sent a test email to {{ auth()->user()->email }}, make sure to update the configuration
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button type="button" class="btn btn-primary" wire:click="updateSmtpConfig" @if(!$connectionSuccessfull) disabled @endif>{{ __('messages.update') }}</button>
    </div>
</form>
