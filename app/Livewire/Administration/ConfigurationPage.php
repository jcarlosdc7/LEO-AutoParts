<?php

namespace App\Livewire\Administration;

use App\Models\CustomerType;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class ConfigurationPage extends Component
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
        $this->users = User::with('role')->get();
        $this->roles = Role::all();
        $this->customerTypes = CustomerType::all();
        $this->language = App::getLocale();
        $this->defaultCustomerTypeId = config('app.default_customer_type', null);
    }

    public function render()
    {
        // Accede a los archivos dentro de la carpeta 'laravel-backups' utilizando el disco 'backups'
        $backups = collect(Storage::disk('backups')->files('LEO AutoParts'))->map(function ($file) {
            return basename($file);
        });

        return view('livewire.administration.configuration', ['backups' => $backups]);
    }

    public function updateRole()
    {
        $this->validate();

        $user = User::find($this->selectedUserId);

        if ($user) {
            $user->role_id = $this->selectedRoleId;
            $user->save();
            $this->users = User::with('role')->get(); // AQUI REFRESCO LISTA
            $this->dispatch('roleUpdated');
        }
    }

    public function createBackup()
    {
        try {
            $pathToArtisan = base_path('artisan');
            $command = 'php '.$pathToArtisan.' backup:run --only-db';
            exec($command);

            $this->dispatch('backupSaveSuccess');
        } catch (Exception $e) {
            $this->dispatch('backupSaveFail');
        }
    }

    public function restoreBackup($backupFile)
    {
        try {
            $zipFilePath = Storage::disk('backups')->path("LEO AutoParts/{$backupFile}");
            $extractPath = storage_path('app/laravel-backups/LEO AutoParts/extracted');

            if (! file_exists($zipFilePath)) {
                throw new \Exception('El archivo de respaldo no existe.');
            }

            if (! file_exists($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $zip = new \ZipArchive;

            if ($zip->open($zipFilePath) === true) {
                $zip->extractTo($extractPath);
                $zip->close();
            } else {
                throw new \Exception('No se pudo descomprimir el archivo.');
            }

            // Llamar a la restauración de la base de datos
            // $this->restoreDatabase($extractPath);

            $this->dispatch('backupRestoreSuccess');
        } catch (\Exception $e) {
            $this->dispatch('backupRestoreFail');
        }
    }

    // public function restoreDatabase($extractPath)
    // {
    // 	//try {
    // 		$sqlFile = $extractPath . '/db-dumps/mysql-leo_autoparts.sql';

    // 		if (!file_exists($sqlFile)) {
    // 			throw new \Exception('El archivo SQL no existe dentro del respaldo.');
    // 		}

    // 		// Convertir las barras para compatibilidad con Windows
    // 		$sqlFile = str_replace('/', '\\', $sqlFile);

    // 		$databaseName = env('DB_DATABASE');
    // 		$databaseUsername = env('DB_USERNAME');
    // 		$databasePassword = env('DB_PASSWORD');

    // 		$command = "mysql -u {$databaseUsername} -p{$databasePassword} {$databaseName} < \"{$sqlFile}\"";
    // 		dd($command);
    // 		exec($command);

    // 		//$this->deleteExtractedFiles($extractPath);

    // 	// } catch (\Exception $e) {
    // 	// 	throw new \Exception('Error al restaurar la base de datos: ' . $e->getMessage());
    // 	// }
    // }
}
