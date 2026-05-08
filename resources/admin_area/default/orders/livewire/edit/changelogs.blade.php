<?php

use Livewire\Volt\Component;

new class extends Component
{
    public $order;

    public function mount($order)
    {
        $this->order = $order;
    }
}

?>

<div>
    <h4>Changelog Tab</h4>
</div>
