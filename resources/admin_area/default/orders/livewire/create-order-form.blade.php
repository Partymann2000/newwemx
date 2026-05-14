<?php

use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackagePrice;
use Livewire\Attributes\Url;
use Illuminate\Validation\ValidationException;

new class extends Component {
    #[Url]
    public $user_id;

    #[Url]
    public $package_id;

    #[Url]
    public $package_price_id;

    public $due_date;

    public $create_server_instance = true;

    public $email_order_confirmation = true;

    public $config_options = [];

    public function createOrder()
    {
        $this->resetErrorBag();

        try {
            $order = Order::actions()->createOrderAsAdmin([
                'user_id' => $this->user_id,
                'package_price_id' => $this->package_price_id,
                'due_date' => filled($this->due_date) ? $this->due_date : null,
                'create_server_instance' => $this->create_server_instance,
                'email_order_confirmation' => $this->email_order_confirmation,
                'config_options' => $this->config_options,
            ]);

            $this->redirect(route('admin.orders.edit', ['order' => $order->id]), true);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ((array) $messages as $message) {
                    $this->addError($field, $message);
                }
            }
        } catch (\Throwable $e) {
            $this->addError('order', $e->getMessage() ?: 'Unable to create order.');
        }
    }

    public function updated()
    {
        if ($this->package_price_id) {
            $price = PackagePrice::find($this->package_price_id);

            if ($price && $price->isRecurring()) {
                $this->due_date = now()->addDays($price->period_in_days)->format('Y-m-d');
            } else {
                $this->due_date = null;
            }

            if($this->config_options == []) {
                // initialize config options with free values if available
                $this->config_options = $this->package->configOptions->mapWithKeys(function ($option) {
                    return [$option->key => $option->default_value ?? ''];
                })->toArray();
            }
        }
    }

    #[Computed]
    public function calculateConfigOptionCost()
    {
        try {
            return $this->package->configurableOptionCalculator($this->config_options, $this->packagePrice->period_in_days);
        } catch (ValidationException $e) {
            // If validation fails, return an empty array
            return [
                'total' => 0,
                'breakdown' => [],
            ];
        }
    }

    #[Computed]
    public function package()
    {
        return Package::find($this->package_id);
    }

    #[Computed]
    public function packagePrice()
    {
        return $this->package->prices()->find($this->package_price_id);
    }
}

?>

<form class="card" wire:submit="createOrder()">
    <div class="card-header">
        <h3 class="card-title">Create Order</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <x-admin::alerts.danger title="Unable to create order">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-admin::alerts.danger>
        @endif

        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="user-input">{{ __('messages.user') }}</label>
            <div class="col" wire:ignore>
                <select class="form-select @error('user_id') is-invalid @enderror mb-2" wire:model.change="user_id"
                        id="user-input">
                    <option value="">{{ __('messages.select') }}</option>
                    @foreach(User::all() as $user)
                        <option value="{{ $user->id }}"
                                data-custom-properties="<span class=&quot;avatar avatar-xs&quot; style=&quot;background-image: url({{ $user->getAvatarUrl() }})&quot;></span>">{{ $user->username }}
                            ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                <script>
                    // create tom select
                    document.addEventListener('livewire:navigated', function () {
                        new TomSelect("#user-input", {
                            copyClassesToDropdown: false,
                            dropdownParent: 'body',
                            controlInput: '<input>',
                            render: {
                                item: function (data, escape) {
                                    if (data.customProperties) {
                                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                                    }
                                    return '<div>' + escape(data.text) + '</div>';
                                },
                                option: function (data, escape) {
                                    if (data.customProperties) {
                                        return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                                    }
                                    return '<div>' + escape(data.text) + '</div>';
                                },
                            },
                        });
                    });
                </script>
                @error('user_id')
                    <x-admin::form.error :message="$message"/>
                @else
                    <small class="form-hint">
                        Select the user for which you want to create the order.
                    </small>
                    @enderror
            </div>
        </div>

        @if($user_id)
            <div class="mb-3 row">
                <label class="col-3 col-form-label" for="package-input">Package</label>
                <div class="col" wire:ignore>
                    <select class="form-select @error('package_id') is-invalid @enderror mb-2"
                            wire:model.change="package_id" id="package-input">
                        <option value="">{{ __('messages.select') }}</option>
                        @foreach(Package::all() as $package)
                            <option value="{{ $package->id }}"
                                    data-custom-properties="<span class=&quot;avatar avatar-xs&quot; style=&quot;background-size: contain; background-image: url({{ $package->icon() }})&quot;></span>">{{ $package->name }}
                                ({{ $package->category->name }})
                            </option>
                        @endforeach
                    </select>
                    <script>
                        // create tom select
                        document.addEventListener('livewire:navigated', function () {
                            const packageInput = new TomSelect("#package-input", {
                                copyClassesToDropdown: false,
                                dropdownParent: 'body',
                                controlInput: '<input>',
                                render: {
                                    item: function (data, escape) {
                                        if (data.customProperties) {
                                            return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                                        }
                                        return '<div>' + escape(data.text) + '</div>';
                                    },
                                    option: function (data, escape) {
                                        if (data.customProperties) {
                                            return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
                                        }
                                        return '<div>' + escape(data.text) + '</div>';
                                    },
                                },
                            });
                            packageInput.setValue(@json($package_id ? [$package_id] : []), true);
                        });
                    </script>
                    @error('package_id')
                    <x-admin::form.error :message="$message"/>
                    @else
                        <small class="form-hint">
                            Select the package for which you want to create the order.
                        </small>
                    @enderror
                </div>
            </div>
        @endif

        @if($package_id)
            <div class="mb-3 row">
                <label class="col-3 col-form-label" for="price-input">Price</label>
                <div class="col">
                    <select class="form-select @error('package_price_id') is-invalid @enderror mb-2"
                            wire:model.change="package_price_id" id="price-input">
                        <option value="">{{ __('messages.select') }}</option>
                        @foreach(PackagePrice::where('package_id', $package_id)->get() as $price)
                            <option value="{{ $price->id }}">{{ price($price->price) }} / {{ $price->cycle() }}</option>
                        @endforeach
                    </select>
                    @error('package_price_id')
                    <x-admin::form.error :message="$message"/>
                    @else
                        <small class="form-hint">
                            Select the price for the package. This will determine the amount to be charged for the
                            order.
                        </small>
                        @enderror
                </div>
            </div>
        @endif

        @if($package_price_id)
            @if($due_date)
                <div class="mb-3 row">
                    <label class="col-3 col-form-label" for="due_date-input">Due Date</label>
                    <div class="col">
                        <input type="date" wire:model="due_date"
                               class="form-control @error('due_date') is-invalid @enderror"
                               aria-describedby="due_date-input" id="due_date-input" placeholder="Due Date">
                        @error('due_date')
                        <x-admin::form.error :message="$message"/>
                        @else
                            <small class="form-hint">
                                The due date for the order. This is when the order will be considered overdue if not
                                paid.
                            </small>
                        @enderror
                    </div>
                </div>
            @endif
            <div class="mb-3 row">
                <label class="col-3 col-form-label" for="create_server_instance-input">Create Server Instance</label>
                <div class="col">
                    <label class="form-check">
                        <input type="checkbox" id="create_server_instance-input" class="form-check-input"
                               wire:model="create_server_instance">
                        <span class="form-check-label">When checked, an instance will be created of the package using an API</span>
                    </label>
                    @error('create_server_instance')
                    <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-3 col-form-label" for="email_order_confirmation-input">Email Order Details</label>
                <div class="col">
                    <label class="form-check">
                        <input type="checkbox" id="email_order_confirmation-input" class="form-check-input"
                               wire:model="email_order_confirmation">
                        <span class="form-check-label">When checked, the selected user will be emailed the details of this order.</span>
                    </label>
                    @error('email_order_confirmation')
                    <x-admin::form.error :message="$message"/>
                    @enderror
                </div>
            </div>

            @if($this->package->configOptions->isNotEmpty())
            <hr>
            <h4>Configurable Options</h4>
            @foreach($this->package->configOptions as $option)
                <div class="mb-3 row">
                    <label class="col-3 col-form-label" for="{{ $option->key }}-input">{{ $option->label }}</label>
                    <div class="col">
                        @if(in_array($option->type, ['text', 'email', 'password']))
                            <x-admin::form.input id="{{ $option->key }}-input"
                                               type="{{ $option->type }}"
                                             wire:model.change="config_options.{{ $option->key }}"
                                               placeholder="{{ $option->label }}"/>
                        @elseif($option->type === 'textarea')
                            <x-admin::form.textarea id="{{ $option->key }}-input"
                                                    wire:model.change="config_options.{{ $option->key }}"
                                                    placeholder="{{ $option->label }}"/>
                        @elseif($option->type === 'select')
                            <x-admin::form.select id="{{ $option->key }}-input"
                                                  wire:model.change="config_options.{{ $option->key }}"
                                                  :options="collect($option->data['options'] ?? [])->pluck('name', 'value')->toArray()"
                                                  placeholder="{{ $option->label }}"/>
                        @elseif($option->type === 'radio')
                            <div class="form-selectgroup-boxes row g-2">
                                @foreach(($option->data['options'] ?? []) as $index => $radioOption)
                                    @php
                                        $radioValue = data_get($radioOption, 'value', '');
                                        $radioTitle = data_get($radioOption, 'name', $radioValue);
                                        $radioDescription = data_get($radioOption, 'description');
                                        $radioId = $option->key.'-input-'.$index;
                                    @endphp
                                    <div class="col-md-4">
                                        <label class="form-selectgroup-item" for="{{ $radioId }}">
                                            <input
                                                type="radio"
                                                id="{{ $radioId }}"
                                                name="{{ $option->key }}-radio"
                                                class="form-selectgroup-input"
                                                value="{{ $radioValue }}"
                                                wire:model.change="config_options.{{ $option->key }}"
                                            />
                                            <span class="form-selectgroup-label d-flex align-items-start p-3">
                                                <span>
                                                    <span class="d-block fw-medium">{{ $radioTitle }}</span>
                                                    @if($radioDescription)
                                                        <span class="d-block text-secondary small mt-1">{{ $radioDescription }}</span>
                                                    @endif
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @elseif($option->type === 'range')
                            <div>
                                <input type="range" class="form-range mb-2" wire:model.change="config_options.{{ $option->key }}"
                                       id="{{ $option->key }}-input" min="{{ $option->data['min_value'] ?? 1 }}" max="{{ $option->data['max_value'] ?? 6 }}"
                                       step="{{ $option->data['step_value'] ?? 1 }}" oninput="document.getElementById('range-color').innerText = this.value"
                                />
                                <div class="form-range mb-2 text-green" id="range-color">{{ $config_options[$option->key] ?? $option->free_value ?? $option->data['min_value'] }}</div>
                            </div>
                        @elseif($option->type === 'number')
                            <x-admin::form.input id="{{ $option->key }}-input"
                                                 type="{{ $option->type }}"
                                                 wire:model.change="config_options.{{ $option->key }}"
                                                 placeholder="{{ $option->label }}"
                                                 min="{{ $option->data['min_value'] ?? '' }}"
                                                 max="{{ $option->data['max_value'] ?? '' }}"
                                                 step="{{ $option->data['step_value'] ?? 1 }}"
                            />
                        @endif
                        @error('config_options.'. $option->key)
                        <x-admin::form.error :message="$message"/>
                        @else
                            <small class="form-hint">{{ $option->description }}</small>
                        @enderror
                    </div>
                </div>
            @endforeach
            @endif

            <hr>

            <div class="mb-3 row">
                <label class="col-3 col-form-label" for="email_order_confirmation-input">Price Breakdown</label>
                <div class="col">
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <tbody>
                            <tr>
                                <td>Base Price</td>
                                <td class="text-secondary d-flex justify-content-end">
                                    {{ price($this->packagePrice->price) }}
                                </td>
                            </tr>
                            <tr>
                                <td>Setup Fee</td>
                                <td class="text-secondary d-flex justify-content-end">
                                    {{ price($this->packagePrice->setup_fee) }}
                                </td>
                            </tr>
                            @if($this->calculateConfigOptionCost()['total'] > 0)
                            <tr>
                                <td>Configurable Options</td>
                                @foreach($this->calculateConfigOptionCost()['breakdown'] as $option)
                                <td class="text-secondary d-flex justify-content-end">
                                    ({{ $option['label'] }}) {{ price($option['total']) }}
                                </td>
                                @endforeach
                            </tr>
                            @endif
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-secondary d-flex justify-content-end">
                                    {{ price($this->calculateConfigOptionCost()['total'] + $this->packagePrice->price) }} / {{ $this->packagePrice->cycle() }} @if($this->packagePrice->setup_fee > 0) (+ {{ price($this->packagePrice->setup_fee) }} Setup Fee) @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
    </div>
</form>
