@extends('admin::layouts.wrapper', [
    'activePage' => 'taxes'
])

@section('title', 'Taxes')

@section('actions')
    <div class="col-auto ms-auto d-print-none">
        <div class="btn-list">
            <x-admin::button href="{{ route('admin.taxes.create') }}" wire:navigate>Add New</x-admin::button>
        </div>
    </div>
@endsection

@section('content')
    @livewire(admin_view_path('livewire.table'), [
        'title' => 'Tax Countries',
        'entries' => 15,
        'columns' => [
            'ID',
            'Country Code',
            'Tax Name',
            'Tax Rate',
            'Status',
            ''
        ],
       'sortableColumns' => [
            'ID',
            'State Code',
            'Tax Name',
            'Tax Rate',
            'Status'
        ],
       'rows' => \App\Models\SalesTaxCountry::query()
            ->select(['id', 'country_code', 'sales_tax_name', 'sales_tax_rate', 'is_active'])
            ->get()
            ->map(function ($model) {
                return [
                    $model->id,
                    $model->country_code,
                    $model->sales_tax_name,
                    $model->sales_tax_rate . '%',
                    $model->is_active ? 'Active' : 'Inactive',
                    '<a href="'.route('admin.taxes.edit', ['country_code' => $model->country_code]).'" wire:navigate>Edit</a>'
                ];
            })->toArray(),

    ])
@endsection
