<?php

namespace App\Livewire\Admin\Groups;

use Livewire\Component;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Collection;

class AssignGroups extends Component
{
    public array $selectedUserIds = [];
    public array $selectedGroupIds = [];
    public string $userSearch = '';

    public Collection $availableUsers;
    public Collection $availableGroups;

    protected $rules = [
        'selectedUserIds' => 'required|array|min:1',
        'selectedUserIds.*' => 'integer|exists:users,id',
        'selectedGroupIds' => 'required|array|min:1',
        'selectedGroupIds.*' => 'integer|exists:groups,id',
    ];

    public function mount(): void
    {
        $this->availableUsers = collect();
        $this->availableGroups = Group::orderBy('name')->get();
    }

    public function updatedUserSearch(): void
    {
        $this->availableUsers = User::where('username', 'like', '%' . $this->userSearch . '%')
            ->orWhere('email', 'like', '%' . $this->userSearch . '%')
            ->orderBy('username')
            ->get();
    }

    public function render()
    {
        return view('admin::groups.livewire.assign-group', [
            'users' => $this->getUsers(),
            'groups' => $this->availableGroups,
        ]);
    }

    private function getUsers(): Collection
    {
        if ($this->userSearch) {
            return User::where('username', 'like', '%' . $this->userSearch . '%')
                ->orWhere('email', 'like', '%' . $this->userSearch . '%')
                ->orderBy('username')
                ->get();
        }

        return User::orderBy('username')->get();
    }

    public function save()
    {
        $this->validate();

        $users = User::whereIn('id', $this->selectedUserIds)->get();

        foreach ($users as $user) {
            $user->syncGroups($this->selectedGroupIds);
        }

        $this->dispatch('toast:success', __('messages.group_assign_success'));
        $this->reset(['selectedUserIds', 'selectedGroupIds']);
    }
}
