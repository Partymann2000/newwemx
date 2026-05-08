<?php

use App\Models\CustomPage;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component
{
    public CustomPage $page;

    public function mount(CustomPage $page): void
    {
        $this->page = $page;
    }

    public function deletePage(): void
    {
        abort_unless(auth()->user()?->hasPermission('admin.pages.delete'), 403);

        CustomPage::actions()->deletePageAsAdmin([
            'page_id' => $this->page->id,
        ]);

        $this->redirect(route('admin.pages.index'), true);
    }
}
?>

<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">{{ $page->title }}</h3>
            <div class="text-secondary mt-1">
                /pages/{{ $page->slug }}
                <span class="mx-1">-</span>
                @if($page->isActive())
                    <span class="badge bg-success-lt">{{ __('messages.active') }}</span>
                @else
                    <span class="badge bg-secondary-lt">{{ __('messages.inactive') }}</span>
                @endif
            </div>
        </div>
        <div class="card-actions">
            <a href="{{ route('pages.view', $page->slug) }}" target="_blank" class="btn btn-outline-primary">
                {{ __('messages.view') }}
            </a>
            @perm('admin.pages.delete')
            <button
                type="button"
                class="btn btn-outline-danger"
                x-on:click="if (confirm('Are you sure you want to delete this page?')) { $wire.deletePage() }"
            >
                {{ __('messages.delete') }}
            </button>
            @endperm
        </div>
    </div>
    <div class="card-body markdown">
        {!! Str::markdown($page->content) !!}
    </div>
</div>
