<?php

namespace App\Actions;

use App\Models\CustomPage;
use App\Models\ExtensionElement;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomPageActions extends Action
{
    private const MANAGEABLE_ELEMENTS = [
        'navigation-item',
        'client-dropdown-item',
        'footer-item',
    ];

    /**
     * @throws ValidationException
     */
    public static function createPageAsAdmin(array $input): CustomPage
    {
        $validatedData = Validator::make($input, [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'content' => ['required', 'string'],
        ])->validate();

        return CustomPage::create(self::omitNullValues($validatedData));
    }

    /**
     * @throws ValidationException
     */
    public static function updatePageAsAdmin(array $input): bool
    {
        $validatedData = Validator::make($input, [
            'page_id' => ['required', 'integer', 'exists:pages,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('pages', 'slug')->ignore($input['page_id'], 'id'),
            ],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive'],
            'content' => ['sometimes', 'required', 'string'],
        ])->validate();

        $page = CustomPage::find($input['page_id'], ['*']);

        if (! $page) {
            throw ValidationException::withMessages([
                'page_id' => 'Page not found',
            ]);
        }

        unset($validatedData['page_id']);

        return $page->update(self::omitNullValues($validatedData));
    }

    /**
     * @throws ValidationException
     */
    public static function syncPageElementsAsAdmin(array $input): void
    {
        $validatedData = Validator::make($input, [
            'page_id' => ['required', 'integer', 'exists:pages,id'],
            'elements' => ['nullable', 'array'],
            'elements.*.type' => ['required', 'string', Rule::in(self::MANAGEABLE_ELEMENTS)],
            'elements.*.name' => ['nullable', 'string', 'max:255'],
            'elements.*.href' => ['required', 'string', 'max:255'],
            'elements.*.active' => ['nullable', 'string', 'max:255'],
            'elements.*.navigate' => ['sometimes', 'boolean'],
            'elements.*.target' => ['nullable', 'string', Rule::in(['_self', '_blank'])],
        ])->validate();

        $page = CustomPage::find($validatedData['page_id'], ['*']);

        if (! $page) {
            throw ValidationException::withMessages([
                'page_id' => 'Page not found',
            ]);
        }

        $selectedElements = array_values($validatedData['elements'] ?? []);
        $extensionIdentifier = self::pageExtensionIdentifier($page->id);

        ExtensionElement::query()
            ->where('extension_identifier', $extensionIdentifier)
            ->whereIn('element', self::MANAGEABLE_ELEMENTS)
            ->delete();

        foreach ($selectedElements as $index => $element) {
            ExtensionElement::query()->create([
                'extension_identifier' => $extensionIdentifier,
                'element' => $element['type'],
                'view' => null,
                'permission' => null,
                'sort_order' => 1000 + $index,
                'attributes' => [
                    'name' => $element['name'] ?: $page->title,
                    'href' => $element['href'],
                    'active' => $element['active'] ?: 'page-'.$page->slug,
                    'navigate' => (bool) ($element['navigate'] ?? true),
                    'target' => $element['target'] ?? null,
                ],
            ]);
        }
    }

    public static function pageExtensionIdentifier(int $pageId): string
    {
        return "core.custom-page.{$pageId}";
    }

    /**
     * @throws ValidationException
     */
    public static function deletePageAsAdmin(array $input): bool
    {
        $validatedData = Validator::make($input, [
            'page_id' => ['required', 'integer', 'exists:pages,id'],
        ])->validate();

        $page = CustomPage::find($validatedData['page_id'], ['*']);

        if (! $page) {
            throw ValidationException::withMessages([
                'page_id' => 'Page not found',
            ]);
        }

        ExtensionElement::query()
            ->where('extension_identifier', self::pageExtensionIdentifier($page->id))
            ->delete();

        return $page->delete();
    }
}
