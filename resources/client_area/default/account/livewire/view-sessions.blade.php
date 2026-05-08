<?php

use Livewire\Volt\Component;
use App\Models\Session;
use App\Models\User;

new class extends Component
{
    #[\Livewire\Attributes\Computed]
    public function sessions()
    {
        return Session::where('user_id', auth()->id())->latest('last_activity')->paginate(6);
    }

    public function logSessionOut($sessionId)
    {
        $status = User::actions()->logoutSessionAsClient([
            'user_id' => auth()->id(),
            'session_id' => $sessionId,
        ]);

        if ($status) {
            $this->dispatch('toast', type: 'success', message: 'Session logged out successfully!', title: 'Success');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Session not found!', title: 'Error');
        }
    }
}
?>


<div class="mb-4 rounded-lg bg-white p-4 shadow dark:bg-gray-800 sm:p-6 xl:p-8">
    <h3 class="text-xl font-bold dark:text-white">Sessions</h3>
    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
        @foreach($this->sessions() as $session)
        <li class="py-4">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    @if($session->isDesktopDevice())
                    <svg class="h-6 w-6 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                        </path>
                    </svg>
                    @else
                    <svg class="h-6 w-6 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                        </path>
                    </svg>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-base font-semibold text-gray-900 dark:text-white">
                        {{ $session->operatingSystem() }} ({{ $session->browser() }})
                    </p>
                    <p class="truncate text-sm font-normal text-gray-500 dark:text-gray-400">
                        {{ $session->ip_address }} <br>
                        Last seen: {{ $session->last_activity->diffForHumans() }}
                    </p>
                </div>
                <div class="inline-flex items-center">
                    <button wire:click="logSessionOut('{{ $session->id }}')" wire:confirm="" class="focus:ring-primary-300 mb-3 mr-3 rounded-lg border border-gray-300 bg-white px-3 py-2 text-center text-sm font-medium text-gray-900 hover:bg-gray-100 focus:ring-4 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                        Log Out
                    </button>
                </div>
            </div>
        </li>
        @endforeach
    </ul>
    <div class="mt-4">
        {{ $this->sessions()->links() }}
    </div>
</div>
