<?php

namespace App\Livewire\Administration;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class UsersPage extends Component
{
    use WithFileUploads, WithPagination;

    public User $user;

    public $roles;

    public bool $isEditing = false;

    public string $search = '';

    public $avatar;

    protected $rules = ['user.name' => 'required|string|max:255', 'user.email' => 'required|email|max:255', 'user.role_id' => 'required|exists:roles,id', 'avatar' => 'nullable|image|max:2048'];

    public function mount(): void
    {
        $this->user = new User;
        $this->roles = Role::orderBy('name')->get();
    }

    public function updatedSearch(): void
    {
        $this->resetPage('pageUser');
    }

    public function render()
    {
        $users = User::query()->with('role')->when($this->search, fn ($query) => $query->where(function ($query) {
            $query->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
        }))->orderBy('name')->paginate(10, pageName: 'pageUser');

        return view('livewire.administration.users', compact('users'));
    }

    public function update(int $id): void
    {
        $this->isEditing = true;
        $this->user = User::findOrFail($id);
        $this->reset('avatar');
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-user');
    }

    public function destroy(int $id): void
    {
        $user = User::findOrFail($id);
        if ($user->is(auth()->user())) {
            $this->dispatch('userDeleteFailed', message: 'No puede eliminar su propia cuenta.');

            return;
        }
        if ($user->sales()->exists()) {
            $this->dispatch('userDeleteFailed', message: 'El usuario posee ventas asociadas y debe conservarse para auditoría.');

            return;
        }
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        $user->delete();
        $this->dispatch('userDeleted');
    }

    public function save(): void
    {
        $this->validate();
        $this->user->save();
        if ($this->avatar) {
            if ($this->user->avatar_path) {
                Storage::disk('public')->delete($this->user->avatar_path);
            }
            $extension = strtolower($this->avatar->getClientOriginalExtension() ?: 'jpg');
            $this->user->avatar_path = $this->avatar->storeAs('avatars', "user_{$this->user->id}.{$extension}", 'public');
            $this->user->save();
        }
        $this->dispatch('close-modal', 'modal-form-user');
        $this->dispatch('userSaved');
        $this->user = new User;
        $this->reset('avatar');
        $this->isEditing = false;
    }
}
