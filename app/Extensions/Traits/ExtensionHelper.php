<?php

namespace App\Extensions\Traits;

use App\Models\Extension;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use ReflectionClass;

trait ExtensionHelper
{
    /**
     * Cached ReflectionClass instance.
     *
     * @var ReflectionClass|null
     */
    protected ?ReflectionClass $reflection = null;

    /**
     * Instance of the extension model.
     *
     * @var Extension|null
     */
    protected ?Extension $extensionModel = null;

    /**
     * Get the ReflectionClass instance for the class using this trait.
     *
     * @return ReflectionClass
     */
    public function getReflection(): ReflectionClass
    {
        if ($this->reflection === null) {
            $this->reflection = new ReflectionClass($this);
        }
        return $this->reflection;
    }

    /**
     * Get the directory where the class using this trait is located.
     *
     * @return string
     */
    public function getExtensionDirectory(): string
    {
        return dirname($this->getReflection()->getFileName());
    }

    /**
     * Get the directory where the class using this trait is located.
     *
     * @return string
     */
    public function getPath($path = null): string
    {
        $path = ltrim($path, DIRECTORY_SEPARATOR);
        return $this->getExtensionDirectory() . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }

    /**
     * Get the folder name where the class using this trait is located.
     *
     * @return string
     */
    public function getExtensionFolderName(): string
    {
        return basename($this->getExtensionDirectory());
    }

    /**
     * Get the name of the parent folder where the class folder is located.
     *
     * @return string
     */
    public function getExtensionTypeFromLocation(): string
    {
        return basename(dirname($this->getExtensionDirectory()));
    }

    /**
     * Get the name of the class where this trait is being used.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->getReflection()->getName(); // Returns the fully qualified class name
    }

    /**
     * Get the extension model.
     *
     * @return Extension
     * @throws \Exception
     */
    public function model(): Extension
    {
        if ($this->extensionModel === null) {
            $extension = Extension::where('namespace', $this->getClassName())->orWhere('identifier', $this->getId())->first();

            if (!$extension) {
                throw new \Exception("Failed to locate extension from namespace {$this->getClassName()} or identifier {$this->getId()}");
            }

            $this->extensionModel = $extension;
        }

        return $this->extensionModel;
    }

    /**
     * Get identifier of the extension.
     *
     * @return string
     */
    public function getId(): string
    {
        if (!isset($this->id)) {
            $this->id = $this->getName();
        }

        return Str::slug($this->id);
    }

    /**
     * Get Name of the extension.
     *
     * @return string
     */
    public function getName(): string
    {
        // To see the directory of the class that uses this trait
        if (!isset($this->name)) {
            $this->name = $this->getExtensionFolderName();
        }

        return $this->name;
    }

    /**
     * Get Name of the extension.
     *
     * @return string
     */
    public function getLowerName(): string
    {
        $name = $this->getName();
        return Str::lower($name);
    }

    /**
     * Get Description of the extension.
     */
    public function getDescription()
    {
        return $this->description ?? null;
    }

    /**
     * Get the extension icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    public function getMarketplaceId(): ?string
    {
        return $this->marketplace_id ?? null;
    }

    /**
     * Get Version of the extension.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the WemX Version of the extension.
     */
    public function getWemXVersion(): array
    {
        return $this->wemxVersions ?? [];
    }

    /**
     * Get Authors of the extension.
     */
    public function getAuthors(): array
    {
        return $this->authors ?? [];
    }

    /**
     * Get the extension type.
     */
    public function getExtensionType()
    {
        if (!isset($this->type)) {
            $extensionType = Str::singluar($this->getExtensionTypeFromLocation());
            $this->type = $extensionType;
        }

        return $this->type;
    }

    /**
     * Check if the extension is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->model()->isEnabled();
    }

    /**
     * Check if the extension is disabled.
     */
    public function isDisabled(): bool
    {
        return $this->model()->isDisabled();
    }

    /**
     * Get the path to the extension's views directory.
     */
    public function getViewsPath(bool $relative = false): string
    {
        $views = $this->views ?? 'Views';

        if ($relative) {
            return $views;
        }

        return $this->getPath($views);
    }

    /**
     * Get the path to the extension's language directory.
     */
    public function getTranslationsPath(bool $relative = false): string
    {
        $translations = $this->translations ?? 'Lang';

        if ($relative) {
            return $translations;
        }

        return $this->getPath($translations);
    }

    /**
     * Get the path to the extension's migration directory.
     */
    public function getMigrationsPath(bool $relative = false): string
    {
        $migrations = $this->migrations ?? 'Migrations';

        if ($relative) {
            return $migrations;
        }

        return $this->getPath($migrations);
    }

    /**
     * Get the path to the extension's routes directory.
     */
    public function getRoutesPath(bool $relative = false): string
    {
        $routes = $this->routes ?? 'routes.php';

        if ($relative) {
            return $routes;
        }

        return $this->getPath($routes);
    }

    /**
     * Get the path to the extension's config directory.
     */
    public function getConfigPath(bool $relative = false): string
    {
        $config = $this->config ?? 'Config/config.php';

        if (!is_string($config)) {
            throw new \Exception("The config value must be an string in the extension class {$this->getClassName()}");
        }

        if ($relative) {
            return $config;
        }

        return $this->getPath($config);
    }

    public function hasViews(): bool
    {
        return is_dir($this->getViewsPath());
    }

    public function hasTranslations(): bool
    {
        return is_dir($this->getTranslationsPath());
    }

    public function hasMigrations(): bool
    {
        return is_dir($this->getMigrationsPath());
    }

    public function hasRoutes(): bool
    {
        return file_exists($this->getRoutesPath());
    }

    public function hasConfig(): bool
    {
        return file_exists($this->getConfigPath());
    }

    /**
     * Make views for the extension.
     */
    public function makeViews(): void
    {
        if($this->hasViews()) {
            throw new \Exception("Views already exist for '{$this->getName()}'.");
        }

        // create a mew directory for the views
        mkdir($this->getViewsPath(), 0755, true);
    }

    /**
     * Make translations for the extension.
     */
    public function makeTranslations(): void
    {
        if($this->hasTranslations()) {
            throw new \Exception("Translations already exist for '{$this->getName()}'.");
        }

        // create a mew directory for the translations
        mkdir($this->getTranslationsPath(), 0755, true);
    }

    /**
     * Make migrations for the extension.
     */
    public function makeMigrations(): void
    {
        if($this->hasMigrations()) {
            throw new \Exception("Migrations already exist for '{$this->getName()}'.");
        }

        // create a mew directory for the migrations
        mkdir($this->getMigrationsPath(), 0755, true);
    }

    /**
     * Make routes for the extension.
     */
    public function makeRoutes(): void
    {
        if($this->hasRoutes()) {
            throw new \Exception("Routes already exist for '{$this->getName()}'.");
        }

        // Define the path for the Module.php file
        $routeFile = $this->getRoutesPath();

        // Create the routes directory if it does not exist
        if (!is_dir(dirname($routeFile))) {
            mkdir(dirname($routeFile), 0755, true);
        }

        // Stub content
        $stub = file_get_contents(base_path('app/Extensions/Commands/stubs/routes.stub'));

        // Replace placeholders in the stub
        $content = str_replace(
            ['{{extension_name}}', '{{extension_lower_name}}'],
            [$this->getName(), $this->getId()],
            $stub
        );

        // Create the Module.php file
        file_put_contents($routeFile, $content);
    }

    /**
     * Make config for the extension.
     */
    /**
     * Make routes for the extension.
     */
    public function makeConfig(): void
    {
        if($this->hasConfig()) {
            throw new \Exception("Config already exist for '{$this->getName()}'.");
        }

        // Define the path for the Module.php file
        $configFile = $this->getConfigPath();

        // Create the routes directory if it does not exist
        if (!is_dir(dirname($configFile))) {
            mkdir(dirname($configFile), 0755, true);
        }

        file_put_contents($configFile, '<?php return [];');
    }

    /**
     * Run the migrations for the extension.
     *
     * @param string $path The path to the migration files. For example 'extensions/Modules/Marketplace/Migrations'
     */
    public function migrate($path): void
    {
        Artisan::call('migrate', [
            '--path' => $path,
            '--force' => true,
        ]);
    }

    /**
     * Get settings of the extension.
     */
    public function getFieldsForSettingsPage(): array
    {
        // check if the extension has function called setSettings
        if (!method_exists($this, 'setSettingsFields')) {
            return [];
        }

        return $this->setSettingsFields();
    }

    public function hasSettingsPage(): bool
    {
        return !empty($this->getFieldsForSettingsPage());
    }

    public function getSettingsTitle(): string
    {
        return $this->getFieldsForSettingsPage()['title'] ?? $this->getName();
    }
}
