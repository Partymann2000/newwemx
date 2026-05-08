<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }

    public function resolveException($exceptionId)
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.update'), 403);

        $exception = $this->order->exceptions()->find($exceptionId);
        if ($exception) {
            $exception->resolve();
            $this->dispatch('order-updated');
        }
    }

    public function unresolveException($exceptionId)
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.update'), 403);

        $exception = $this->order->exceptions()->find($exceptionId);
        if ($exception) {
            $exception->unresolve();
            $this->dispatch('order-updated');
        }
    }

    public function tryAgain($exceptionId)
    {
        abort_if(!auth()->user()->hasPerm('admin.orders.perform_actions'), 403);

        $exception = $this->order->exceptions()->find($exceptionId);
        if (!$exception) {
            return;
        }

        if ($exception->action === 'create') {
            $exception->order->createServer(false);
        }

        if ($exception->action === 'suspend') {
            $exception->order->suspendServer(false);
        }

        if ($exception->action === 'unsuspend') {
            $exception->order->unsuspendServer(false);
        }

        if ($exception->action === 'terminate') {
            $exception->order->terminateServer(false);
        }

        // assume it was successfull, set all exceptions with that action to resolved
        $this->order->exceptions()->where('action', $exception->action)->whereNull('resolved_at')->get()->each(function ($ex) {
            $ex->resolve();
        });

        $this->redirect(route('admin.orders.edit', ['order' => $this->order->id, 'orderEditPage' => 'incident-logs']));
    }
}

?>

<div>
    @foreach($order->exceptions()->latest()->get() as $exception)
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Failed to perform action "{{ $exception->action }}" #{{ $exception->id }}</h4>
                <div>
                    @if($exception->isResolved())
                        <span class="badge bg-green-lt text-green-lt-fg">Resolved</span>
                    @else
                        <span class="badge bg-red-lt text-red-lt-fg">Unresolved</span>
                    @endif
                </div>
            </div>
            <h4>Message</h4>
            <div>
                <pre>{{ $exception->message }}</pre>
            </div>
            <h4>File</h4>
            <div>
                <p>{{ $exception->file }}, Line {{ $exception->line }}</p>
            </div>
            <h4>Occurred</h4>
            <div>
                <p>{{ $exception->created_at->diffForHumans() }} ({{ $exception->created_at->format('d M Y H:i:s') }})</p>
            </div>
            @if($exception->isResolved())
                <h4>Resolved</h4>
                <div>
                    <p>{{ $exception->resolved_at->diffForHumans() }} ({{ $exception->resolved_at->format('d M Y H:i:s') }})</p>
                </div>
            @endif
            <div class="text-end">
                @if(!$exception->isResolved())
                    @if(in_array($exception->action, ['create', 'suspend', 'unsuspend', 'terminate']))
                        <button type="button" class="btn btn-success" wire:confirm="" wire:click="tryAgain({{ $exception->id }})">Try "{{ $exception->action }}" Again</button>
                    @endif
                    <button type="button" class="btn btn-primary" wire:confirm="" wire:click="resolveException({{ $exception->id }})">Mark As Resolved</button>
                @else
                    <button type="button" class="btn btn-secondary" wire:confirm="" wire:click="unresolveException({{ $exception->id }})">Unresolve</button>
                @endif
            </div>
        </div>
    </div>
    @endforeach

</div>
