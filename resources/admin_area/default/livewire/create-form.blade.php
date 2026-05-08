<?php

use Livewire\Volt\Component;
use Illuminate\View\View;

new class extends Component
{
    public array $fields;

    public function mount($fields)
    {
        $this->fields = $fields;
    }
}

?>



