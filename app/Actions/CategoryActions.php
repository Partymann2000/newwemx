<?php

namespace App\Actions;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryActions extends Action
{
    /**
     * Create a new category
     *
     * @throws ValidationException
     */
    public static function createCategoryAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'icon' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug'],
            'status' => ['required', 'string', 'in:active,inactive,restricted,unlisted,disabled'],
        ])->validate();

        if (! isset($validatedData['icon'])) {
            $validatedData['icon'] = '/assets/common/img/category-placeholder.png';
        }

        return Category::create(self::omitNullValues($validatedData));
    }

    /**
     * Update a category
     *
     * @throws ValidationException
     */
    public static function updateCategoryAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'icon' => ['nullable', 'string', 'max:255'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($input['category_id'], 'id'),
            ],
            'status' => ['sometimes', 'required', 'string', 'in:active,inactive,restricted,unlisted,disabled'],
        ])->validate();

        $category = Category::find($input['category_id'], ['*']);

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'Category not found',
            ]);
        }

        unset($validatedData['category_id']);

        return $category->update(self::omitNullValues($validatedData));
    }

    /**
     * Delete a category as an admin.
     *
     * A category can only be deleted once all related packages
     * have already been deleted by an admin.
     *
     * @throws ValidationException
     */
    public static function deleteCategoryAsAdmin(array $input): bool
    {
        $validatedData = Validator::make($input, [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ])->validate();

        $category = Category::find($validatedData['category_id']);

        if (! $category) {
            throw ValidationException::withMessages([
                'category_id' => 'Category not found',
            ]);
        }

        return DB::transaction(function () use ($category): bool {
            $existingPackageIds = $category->packages()
                ->pluck('id');

            if ($existingPackageIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'category_id' => 'This category cannot be deleted until all related packages are deleted. Open packages: #'.$existingPackageIds->implode(', #'),
                ]);
            }

            return $category->delete();
        });
    }
}
