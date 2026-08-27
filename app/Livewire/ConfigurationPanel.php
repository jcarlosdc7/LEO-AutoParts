<?php

namespace App\Livewire;

use App\Models\CustomerType;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ConfigurationPanel extends Component
{
    public $users; // Lista de usuarios

    public $roles; // Lista de roles

    public $customerTypes; // Tipos de cliente

    public $selectedUserId; // Usuario seleccionado

    public $selectedRoleId; // Rol asignado

    public $defaultCustomerTypeId; // Tipo de cliente por defecto

    public $language; // Idioma actual

    public $selectedBackup;
    // VALIDACION ==============================================

    protected $rules = [
        'selectedUserId' => 'exists:users,id|required',
        'selectedRoleId' => 'exists:roles,id|required',
    ];

    protected $validationAttributes = [
        'selectedUserId' => 'Usuario',
        'selectedRoleId' => 'Rol',
    ];

    protected $messages = [
        'selectedUserId' => 'El usuario seleccionado no existe',
        'selectedRoleId' => 'El rol seleccionado no existe',
    ];

    // VALIDACION ==============================================

    public function mount()
    {
        $this->ensureAdmin();
        $this->users = User::with('role')->get();
        $this->roles = Role::all();
        $this->customerTypes = CustomerType::all();
        $this->language = App::getLocale();
        $this->defaultCustomerTypeId = config('app.default_customer_type', null);
    }

    public function render()
    {
        $this->ensureAdmin();
        // Accede a los archivos dentro de la carpeta 'laravel-backups' utilizando el disco 'backups'
        $backups = collect(Storage::disk('backups')->files('LEO AutoParts'))->map(function ($file) {
            return basename($file);
        });

        return view('livewire.lwConfiguration.configuration-panel', ['backups' => $backups]);
    }

    public function updateRole()
    {
        $this->ensureAdmin();
        $this->validate();

        $user = User::find($this->selectedUserId);

        if ($user) {
            if ($user->is(Auth::user()) && (int) $this->selectedRoleId !== (int) $user->role_id) {
                abort(422, 'No puede cambiar su propio rol.');
            }
            if ($user->hasRole('Administrador') && (int) $this->selectedRoleId !== (int) $user->role_id
                && User::where('role_id', $user->role_id)->where('is_active', true)->count() <= 1) {
                abort(422, 'Debe existir al menos un administrador activo.');
            }

            $oldRole = $user->role_id;
            $user->role_id = $this->selectedRoleId;
            $user->save();
            AuditService::record('user.role_updated', $user, ['role_id' => $oldRole], ['role_id' => $user->role_id]);
            $this->users = User::with('role')->get();
            $this->dispatch('roleUpdated');
        }
    }

    public function createBackup()
    {
        $this->ensureAdmin();
        try {
            $exitCode = Artisan::call('backup:run', ['--only-db' => true]);
            if ($exitCode !== 0) {
                throw new Exception('No se pudo crear el respaldo.');
            }

            $this->dispatch('backupSaveSuccess');
        } catch (Exception $e) {
            $this->dispatch('backupSaveFail');
        }
    }

    public function restoreBackup($backupFile)
    {
        abort_unless(auth()->user()?->hasRole('Administrador'), 403);

        $available = collect(Storage::disk('backups')->files('LEO AutoParts'))
            ->map(fn (string $file) => basename($file));

        if (! $available->contains((string) $backupFile)) {
            $this->dispatch('backupRestoreFail');

            return;
        }

        // La restauración es destructiva: permanece deshabilitada hasta contar
        // con confirmación reforzada, validación del ZIP y rollback probado.
        $this->dispatch('backupRestoreUnavailable');
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()?->hasRole('Administrador'), 403);
    }
}
