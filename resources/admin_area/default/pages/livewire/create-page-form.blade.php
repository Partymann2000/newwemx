<?php

use App\Models\CustomPage;
use Illuminate\Support\Str;
use Livewire\Volt\Component;

new class extends Component
{
    public string $title = '';

    public string $slug = '';

    public string $status = 'active';

    public string $content = '';

    public function createPage(): void
    {
        $page = CustomPage::actions()->createPageAsAdmin([
            'title' => $this->title,
            'slug' => $this->slug,
            'status' => $this->status,
            'content' => $this->content,
        ]);

        $this->redirect(route('admin.pages.view', ['page' => $page->id]), true);
    }

    public function updated(): void
    {
        if ($this->title && ! $this->slug) {
            $this->slug = Str::slug($this->title);
        }
    }
}
?>

<form class="card" wire:submit="createPage()">
    <div class="card-header">
        <h3 class="card-title">{{ __('messages.create_page') }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3 row">
            <label class="col-3 col-form-label required" for="title-input">{{ __('messages.title') }}</label>
            <div class="col">
                <input type="text" wire:model.change="title" class="form-control @error('title') is-invalid @enderror" id="title-input" placeholder="About us">
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
    </div>
    <div class="card-footer text-end">
        <button type="submit" class="btn btn-primary">{{ __('messages.create') }}</button>
    </div>
</form>
