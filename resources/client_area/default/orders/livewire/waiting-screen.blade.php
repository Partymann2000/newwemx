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

    @if($order->status == 'pending')
    <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-xl" wire:poll>
        <div class="grid gap-8 items-center lg:gap-12 lg:grid-cols-12">
            <div class="col-span-6 text-center sm:mb-6 lg:text-left lg:mb-0">
                <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-3xl xl:text-4xl dark:text-white">
                    Your order is pending
                </h1>
                <p class="mx-auto max-w-xl font-light text-gray-500 lg:mx-0 xl:mb-8 md:text-lg xl:text-xl dark:text-gray-400">
                    Your order is currently in queue and will be processed shortly. Stay on this page to get the latest updates.
                </p>
            </div>
            <div class="hidden col-span-6 lg:flex">
                <img src="https://tabler.io/_next/image?url=%2Fillustrations%2Fdark%2Floading.png&w=800&q=75" alt="illustration">
            </div>
        </div>
    </div>
    @endif

    @if($order->status == 'processing')
        <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-xl" wire:poll>
            <div class="grid gap-8 items-center lg:gap-12 lg:grid-cols-12">
                <div class="col-span-6 text-center sm:mb-6 lg:text-left lg:mb-0">
                    <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-3xl xl:text-4xl dark:text-white">
                        Your order is processing
                    </h1>
                    <p class="mx-auto max-w-xl font-light text-gray-500 lg:mx-0 xl:mb-8 md:text-lg xl:text-xl dark:text-gray-400">
                        Your order is currently being processed. We are getting everything ready for you in the background. Stay on this page to get the latest updates.
                    </p>
                </div>
                <div class="hidden col-span-6 lg:flex">
                    <img src="https://tabler.io/_next/image?url=%2Fillustrations%2Fdark%2Fbuilding.png&w=800&q=75" alt="illustration">
                </div>
            </div>
        </div>
    @endif

    @if($order->status == 'failed')
        <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-xl">
            <div class="grid gap-8 items-center lg:gap-12 lg:grid-cols-12">
                <div class="col-span-6 text-center sm:mb-6 lg:text-left lg:mb-0">
                    <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-3xl xl:text-4xl dark:text-white">
                        Something went wrong
                    </h1>
                    <p class="mx-auto max-w-xl font-light text-gray-500 lg:mx-0 xl:mb-8 md:text-lg xl:text-xl dark:text-gray-400">
                        We were unable to process your order. Please contact support for further assistance. We apologize for the inconvenience.
                    </p>
                    <a href="" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                        Contact support
                    </a>
                </div>
                <div class="hidden col-span-6 lg:flex">
                    <img src="https://tabler.io/_next/image?url=%2Fillustrations%2Fdark%2Ferror.png&w=800&q=75" alt="illustration">
                </div>
            </div>
        </div>
    @endif

    @if($order->status == 'active')
        <div class="py-8 lg:py-16 px-4 mx-auto max-w-screen-xl">
            <div class="grid gap-8 items-center lg:gap-12 lg:grid-cols-12">
                <div class="col-span-6 text-center sm:mb-6 lg:text-left lg:mb-0">
                    <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-3xl xl:text-4xl dark:text-white">
                        Your order was successfully activated
                    </h1>
                    <p class="mx-auto max-w-xl font-light text-gray-500 lg:mx-0 xl:mb-8 md:text-lg xl:text-xl dark:text-gray-400">
                        Your order was successfully activated. Please press the button below to or refresh the page.
                    </p>
                    <a href="" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                        Refresh
                    </a>
                </div>
                <div class="hidden col-span-6 lg:flex">
                    <img src="https://tabler.io/_next/image?url=%2Fillustrations%2Fdark%2Felectric-scooter.png&w=800&q=75" alt="illustration">
                </div>
            </div>
        </div>
    @endif

</div>
