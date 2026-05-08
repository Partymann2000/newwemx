<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'status',
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
    ];

    public function icon()
    {
        // if icon is null, return default icon
        if (!$this->icon) {
            return '/assets/common/img/category-placeholder.png';
        }

        // if icon is a URL, return the URL
        if (filter_var($this->icon, FILTER_VALIDATE_URL)) {
            return $this->icon;
        }

        // if icon starts with 'categories/', return the icon with the full path
        if (strpos($this->icon, 'categories/') === 0) {
            return '/' . $this->icon;
        }

        // if the icon is a file path, return the file path
        return $this->icon;
    }

    public static function actions(): \App\Actions\CategoryActions
    {
        return new \App\Actions\CategoryActions();
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function scopeSearch($query, string $search): void
    {
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('slug', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        }
    }
}
