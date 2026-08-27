<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;

class UsersPanel extends Component
{
    use WithPagination;

    public User $user;

    public $roles;

    public bool $isEditing = false;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->ensureAdmin();
        $this->user = new User(['role_id' => 3, 'is_active' => true]);
        $this->roles = Role::orderBy('name')->get();
    }

    public function render()
    {
        $this->ensureAdmin();

        return view('livewire.lwUsers.users-panel', [
            'users' => User::with('role')->orderBy('name')->paginate(10, pageName: 'pageUser'),
        ]);
    }

    public function create(): void
    {
        $this->ensureAdmin();
        $this->resetValidation();
        $this->isEditing = false;
        $this->password = '';
        $this->password_confirmation = '';
        $this->user = new User(['role_id' => 3, 'is_active' => true]);
        $this->dispatch('open-modal', 'modal-form-user');
    }

    public function update(int $id): void
    {
        $this->ensureAdmin();
        $this->user = User::findOrFail($id);
        $this->isEditing = true;
        $this->password = '';
        $this->password_confirmation = '';
        $this->resetValidation();
        $this->dispatch('open-modal', 'modal-form-user');
    }

    public function save(): void
    {
        $this->ensureAdmin();
        $rules = [
            'user.name' => ['required', 'string', 'max:255'],
            'user.email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user->id)],
            'user.role_id' => ['required', 'exists:roles,id'],
            'user.is_active' => ['boolean'],
            'password' => [$this->user->exists ? 'nullable' : 'required', 'confirmed', Password::defaults()],
        ];
        $this->validate($rules);

        if ($this->user->exists && $this->user->is(Auth::user()) && ! $this->user->is_active) {
            $this->addError('user.is_active', 'No puede desactivar su propia cuenta.');

            return;
        }

        $old = $this->user->exists ? $this->user->getOriginal() : [];
        if ($this->password !== '') {
            $this->user->password = Hash::make($this->password);
        }
        $this->user->save();
        AuditService::record($this->isEditing ? 'user.updated' : 'user.created', $this->user, $old, $this->user->getAttributes());

        $this->dispatch('close-modal', 'modal-form-user');
        $this->user = new User(['role_id' => 3, 'is_active' => true]);
        $this->password = '';
        $this->password_confirmation = '';
        $this->isEditing = false;
    }

    public function destroy(int $id): void
    {
        $this->ensureAdmin();
        $user = User::with('role')->findOrFail($id);

        if ($user->is(Auth::user())) {
            abort(422, 'No puede desactivar su propia cuenta.');
        }
        if ($user->hasRole('Administrador') && User::where('role_id', $user->role_id)->where('is_active', true)->count() <= 1) {
            abort(422, 'Debe existir al menos un administrador activo.');
        }

        $old = ['is_active' => $user->is_active];
        $user->update(['is_active' => false]);
        AuditService::record('user.deactivated', $user, $old, ['is_active' => false]);
    }

    public function getUserRole(?int $id): string
    {
        return Role::find($id)?->name ?? 'Sin rol';
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasRole('Administrador'), 403);
    }
}
