<?php

namespace App\Livewire\Admin\Groups;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Group;
use App\Models\GroupPermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GroupForm extends Component
{
    use WithFileUploads;

    public ?int $groupId = null;

    public string $name = '';
    public ?string $description = null;
    public ?string $color = null;
    public $image = null;
    public ?string $currentImage = null;
    public bool $is_admin = false;
    public bool $is_default = false;
    public int $priority = 0;
    public ?int $parent_id = null;
    public array $possibleParents = [];
    public Collection $allPermissions;

    public array $selectedPermissions = [];

    protected $rules = [
        'name'                => 'required|string|max:255',
        'description'         => 'nullable|string|max:1000',
        'color'               => 'nullable|string|max:20',
        'image'               => 'nullable|image|max:1024',
        'is_admin'            => 'boolean',
        'is_default'          => 'boolean',
        'priority'            => 'integer',
        'parent_id'           => 'nullable|integer|exists:groups,id',
        'selectedPermissions' => 'array',
    ];

    public function mount(?int $groupId = null): void
    {
        $this->groupId = $groupId;
        $this->allPermissions = collect(GroupPermission::allPermissions())->map(fn($perm) => [
            'value' => $perm,
            'label' => Str::title(str_replace(['.', '_'], ' ', $perm)),
        ]);

        $groups = Group::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $this->possibleParents = $this->buildGroupHierarchy($groups);

        if ($this->groupId) {
            $group = Group::with('permissions')->findOrFail($this->groupId);
            $this->name                = $group->name;
            $this->description         = $group->description;
            $this->color               = $group->color;
            $this->currentImage        = $group->image;
            $this->is_admin            = (bool)$group->is_admin;
            $this->is_default          = (bool)$group->is_default;
            $this->priority            = $group->priority;
            $this->parent_id           = $group->parent_id;
            $this->selectedPermissions = $group->permissions->pluck('permission')->toArray();
        }
    }

    private function buildGroupHierarchy($groups, $prefix = ''): array
    {
        $result = [];

        foreach ($groups as $group) {
            if ($this->groupId && $group->id === $this->groupId) {
                continue;
            }

            $result[] = [
                'id'   => $group->id,
                'name' => $prefix . $group->name,
            ];

            if ($group->children && $group->children->count()) {
                $result = array_merge($result, $this->buildGroupHierarchy($group->children, $prefix . '-- '));
            }
        }

        return $result;
    }

    public function save()
    {
        $this->validate();

        if ($this->groupId) {
            $group = Group::findOrFail($this->groupId);
        } else {
            $group = new Group();
        }

        $group->name       = $this->name;
        $group->description = $this->description;
        $group->color      = $this->color;

        if ($this->image) {
            $group->image = $this->image->store('group_images', 'public');
        } elseif (!$this->image && $this->currentImage) {
            $group->image = $this->currentImage;
        }

        $group->is_admin   = $this->is_admin;
        $group->is_default = $this->is_default;
        $group->priority   = $this->priority;
        $group->parent_id  = $this->parent_id;
        $group->save();

        $currentPermissions = $group->permissions()->pluck('permission')->toArray();
        $selectedPermissions = array_unique($this->selectedPermissions);
        $permissionsToDelete = array_diff($currentPermissions, $selectedPermissions);
        $permissionsToAdd = array_diff($selectedPermissions, $currentPermissions);

        if (!empty($permissionsToDelete)) {
            $group->permissions()->whereIn('permission', $permissionsToDelete)->delete();
        }

        foreach ($permissionsToAdd as $permName) {
            $group->permissions()->create(['permission' => $permName]);
        }

        $this->dispatch('toast:success', __('messages.group_save_success'));
        $this->redirectRoute('admin.groups.index', navigate: true);
    }

    public function getGroupedPermissionsProperty(): Collection
    {
        return $this->allPermissions
            ->groupBy(function ($perm) {
                $parts = explode('.', $perm['value']);
                return ucfirst($parts[0] ?? 'Misc');
            })
            ->sortKeys();
    }

    public function render()
    {
        return view('admin::groups.livewire.group-form');
    }
}
