<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $defaultPages = [
        [
            'title' => 'About',
            'slug' => 'about',
            'status' => 'active',
            'content' => "Inform your customers about your company, services, and products. This page can be modified from the admin panel.",
        ],
        [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'status' => 'active',
            'content' => "Inform your customers about your privacy policy. This page can be modified from the admin panel.",
        ],
        [
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'status' => 'active',
            'content' => "Inform your customers about your terms of service. This page can be modified from the admin panel.",
        ],
    ];

    protected array $permissions = [
        'admin.pages.index' => 'View list of Custom Pages',
        'admin.pages.create' => 'Create Custom Pages',
        'admin.pages.view' => 'View Custom Page details',
        'admin.pages.update' => 'Update Custom Pages',
        'admin.pages.delete' => 'Delete Custom Pages',
    ];

    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->longText('content');
            $table->timestamps();
        });

        foreach ($this->permissions as $permission => $description) {
            DB::table('permissions')->updateOrInsert(
                ['permission' => $permission],
                ['description' => $description]
            );
        }

        foreach ($this->defaultPages as $page) {
            DB::table('pages')->updateOrInsert(
                ['slug' => $page['slug']],
                [
                    'title' => $page['title'],
                    'status' => $page['status'],
                    'content' => $page['content'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $pageId = DB::table('pages')->where('slug', $page['slug'])->value('id');

            if (! $pageId) {
                continue;
            }

            $extensionIdentifier = "core.custom-page.{$pageId}";
            $pageUrl = route('pages.view', ['page' => $page['slug']]);

            DB::table('extension_elements')
                ->where('extension_identifier', $extensionIdentifier)
                ->whereIn('element', ['navigation-item', 'client-dropdown-item'])
                ->delete();

            DB::table('extension_elements')->updateOrInsert(
                [
                    'extension_identifier' => $extensionIdentifier,
                    'element' => 'footer-item',
                ],
                [
                    'view' => null,
                    'permission' => null,
                    'attributes' => json_encode([
                        'name' => $page['title'],
                        'href' => $pageUrl,
                        'active' => 'page-'.$page['slug'],
                        'navigate' => true,
                    ], JSON_UNESCAPED_SLASHES),
                    'sort_order' => 1000,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $defaultPageIds = DB::table('pages')
            ->whereIn('slug', array_column($this->defaultPages, 'slug'))
            ->pluck('id');

        foreach ($defaultPageIds as $pageId) {
            DB::table('extension_elements')
                ->where('extension_identifier', "core.custom-page.{$pageId}")
                ->whereIn('element', ['navigation-item', 'client-dropdown-item', 'footer-item'])
                ->delete();
        }

        DB::table('permissions')
            ->whereIn('permission', array_keys($this->permissions))
            ->delete();

        Schema::dropIfExists('pages');
    }
};
