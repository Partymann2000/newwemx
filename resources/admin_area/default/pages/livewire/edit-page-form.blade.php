<?php

use App\Models\CustomPage;
use App\Models\ExtensionElement;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component
{
    private const ELEMENT_TYPES = [
        'navigation-item',
        'client-dropdown-item',
        'footer-item',
    ];

    public int $pageId;

    public string $title = '';

    public string $slug = '';

    public string $status = 'active';

    public string $content = '';

    public array $elements = [];

    public function mount(CustomPage $page): void
    {
        $this->pageId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->status = $page->status;
        $this->content = $page->content;
        $this->elements = ExtensionElement::query()
            ->where('extension_identifier', \App\Actions\CustomPageActions::pageExtensionIdentifier($page->id))
            ->whereIn('element', self::ELEMENT_TYPES)
            ->orderBy('sort_order')
            ->get()
            ->map(function (ExtensionElement $element) use ($page): array {
                return [
                    'type' => $element->element,
                    'name' => $element->attributes['name'] ?? $page->title,
                    'href' => $element->attributes['href'] ?? route('pages.view', ['page' => $page->slug]),
                    'active' => $element->attributes['active'] ?? 'page-'.$page->slug,
                    'navigate' => (bool) ($element->attributes['navigate'] ?? true),
                    'target' => $element->attributes['target'] ?? null,
                ];
            })
            ->toArray();

        if (empty($this->elements)) {
            $this->elements[] = $this->defaultElementData();
        }
    }

    public function updatePage(): void
    {
        CustomPage::actions()->updatePageAsAdmin([
            'page_id' => $this->pageId,
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'content' => $this->content,
        ]);
        CustomPage::actions()->syncPageElementsAsAdmin([
            'page_id' => $this->pageId,
            'elements' => $this->elements,
        ]);

        $this->redirect(route('admin.pages.view', ['page' => $this->pageId]), true);
    }

    public function updated(): void
    {
        if ($this->title && ! $this->slug) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function addElement(): void
    {
        $this->elements[] = $this->defaultElementData();
    }

    public function removeElement(int $index): void
    {
        if (! isset($this->elements[$index])) {
            return;
        }

        unset($this->elements[$index]);
        $this->elements = array_values($this->elements);
    }

    private function defaultElementData(): array
    {
        return [
            'type' => 'footer-item',
            'name' => $this->title ?: 'New Page',
            'href' => route('pages.view', ['page' => $this->slug ?: 'new-page']),
            'active' => 'page-'.($this->slug ?: 'new-page'),
            'navigate' => true,
            'target' => null,
        ];
    }
}
?>

<form class="card" wire:submit="updatePage()">
    <div class="card-header">
        <h3 class="card-title">{{ __('messages.edit_page') }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label class="col-3 col-form-label required" for="title-input">{{ __('messages.title') }}</label>
            <div class="col">
                <input type="text" wire:model.change="title" class="form-control @error('title') is-invalid @enderror" id="title-input">
                @error('title')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">{{ __('messages.page_title_desc') }}</small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label required" for="slug-input">{{ __('messages.slug') }}</label>
            <div class="col">
                <div class="input-group input-group-flat">
                    <span class="input-group-text">
                        {{ rtrim(url('/'), '/') }}/pages/
                    </span>
                    <input type="text" wire:model="slug" class="form-control ps-0 @error('slug') is-invalid @enderror" id="slug-input" autocomplete="off">
                </div>
                @error('slug')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">{{ __('messages.page_slug_desc') }}</small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label" for="status-input">{{ __('messages.status') }}</label>
            <div class="col">
                <select class="form-select @error('status') is-invalid @enderror" wire:model="status" id="status-input">
                    <option value="active">{{ __('messages.active') }}</option>
                    <option value="inactive">{{ __('messages.inactive') }}</option>
                </select>
                @error('status')
                    <x-admin::form.error :message="$message" />
                @else
                    <small class="form-hint">{{ __('messages.page_status_desc') }}</small>
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label required" for="content-input">{{ __('messages.content') }}</label>
            <div class="col">
                <x-admin::form.markdown-editor id="content-input" wire:model="content" :rows="14" />
                @error('content')
                    <x-admin::form.error :message="$message" />
                @enderror
            </div>
        </div>
        <div class="mb-3 row">
            <label class="col-3 col-form-label">Elements</label>
            <div class="col">
                <div class="card card-body border mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title mb-1">Page Elements</h4>
                            <p class="text-secondary mb-0">Control where this page link appears in the client area.</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" wire:click="addElement">
                            Add Element
                        </button>
                    </div>
                </div>

                @foreach($elements as $index => $element)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Element #{{ $index + 1 }}</h5>
                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeElement({{ $index }})">
                                Remove
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Type</label>
                                    <select class="form-select @error('elements.'.$index.'.type') is-invalid @enderror" wire:model="elements.{{ $index }}.type">
                                        <option value="navigation-item">navigation-item</option>
                                        <option value="client-dropdown-item">client-dropdown-item</option>
                                        <option value="footer-item">footer-item</option>
                                    </select>
                                    @error('elements.'.$index.'.type')
                                        <x-admin::form.error :message="$message" />
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control @error('elements.'.$index.'.name') is-invalid @enderror" wire:model.change="elements.{{ $index }}.name" placeholder="Display name">
                                    @error('elements.'.$index.'.name')
                                        <x-admin::form.error :message="$message" />
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Active Key</label>
                                    <input type="text" class="form-control @error('elements.'.$index.'.active') is-invalid @enderror" wire:model.change="elements.{{ $index }}.active" placeholder="page-{{ $slug ?: 'slug' }}">
                                    @error('elements.'.$index.'.active')
                                        <x-admin::form.error :message="$message" />
                                    @enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Href</label>
                                    <input type="text" class="form-control @error('elements.'.$index.'.href') is-invalid @enderror" wire:model.change="elements.{{ $index }}.href" placeholder="{{ rtrim(url('/'), '/') }}/pages/{{ $slug ?: 'slug' }}">
                                    @error('elements.'.$index.'.href')
                                        <x-admin::form.error :message="$message" />
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Target</label>
                                    <select class="form-select @error('elements.'.$index.'.target') is-invalid @enderror" wire:model="elements.{{ $index }}.target">
                                        <option value="">Default</option>
                                        <option value="_self">_self</option>
                                        <option value="_blank">_blank</option>
                                    </select>
                                    @error('elements.'.$index.'.target')
                                        <x-admin::form.error :message="$message" />
                                    @enderror
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <label class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" wire:model="elements.{{ $index }}.navigate">
                                        <span class="form-check-label">wire:navigate</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.update') }}</button>
    </div>
</form>
