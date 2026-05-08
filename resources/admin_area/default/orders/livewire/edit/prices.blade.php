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
    @livewire(admin_view_path('livewire.table'), [
        'title' => 'Order Prices',
        'class' => '',
        'columns' => [
            'ID',
            'Description',
            'Price',
            'Upgrade Fee',
            'status',
            ''
        ],
        'sortableColumns' => [],
        'rows' => $order->prices->map(function($price) use ($order) {
            return [
                'id' => $price->id,
                'description' => $price->value ? $price->description . ' (' . $price->value . ')' : $price->description,
                'price' => price($price->price) . ' / '. $order->cycle(),
                'upgrade_fee' => price($price->upgrade_fee),
                'status' => $price->is_active ? 'Active' : 'Inactive',
                '' => '',
            ];
        })->toArray(),
    ])
</div>
