<?php

use Livewire\Volt\Component;
use Illuminate\View\View;
use Livewire\Attributes\On;

new class extends Component
{
    public $type = 'success';

    public $message = '';

    #[On('alert')]
    public function handleEvent($type, $message)
    {
        $this->type = $type;
        $this->message = $message;
    }
}

?>


<div>
    @if($message)
    <div class="alert alert-{{ $type }}" role="alert">
        <div>
            {{ $message }}
        </div>
    </div>
    @endif
</div>
