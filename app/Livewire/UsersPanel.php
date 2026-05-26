<?php

namespace App\Livewire;

use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;

class UsersPanel extends Component
{
	use WithPagination;

	public User $user;
	public Role $userRole;

	public $roles;

	public $modal = false;
	public $isEditing;

	protected $rules = [
		'user.name' => 'required',
		'user.email' => 'required',
		'user.role_id' => 'required',
	];

	public function mount() 
	{
		$this->user = new User();

		$this->roles = Role::all();
	}

	public function render()
	{
		$users = User::paginate(10, pageName: 'pageUser');

		// EN CASO DE QUE LA ULTIMA PAGINA SE QUEDE SIN REGISTROS PARA RENDERIZAR
		if($users->count() === 0) {
			$this->previousPage(pageName: 'pageUser');
			$users = User::paginate(10, pageName: 'pageUser');
		}

		return view('livewire.lwUsers.users-panel', compact('users'));
	}

	// ========================== CRUD ==========================
	public function create() 
	{
		$this->resetValidation();
		$this->dispatch('open-modal', 'modal-form-user');
	}

	public function update($id) 
	{
		$this->isEditing = true;
		$this->user = User::find($id);
		
		$this->resetValidation();
		$this->dispatch('open-modal', 'modal-form-user');
	}

	public function destroy($id) 
	{
		User::destroy($id);
		//$this->users = User::all();
	}

	public function save()
	{
		if($this->user->role_id == null){
			$this->user->role_id = 3;
		}
		
		//$this->validate();
		$this->user->update(['role_id', $this->user->role_id]);

		$this->dispatch('close-modal', 'modal-form-user');

		//$this->users = User::all();
		$this->user = new User();
		$this->isEditing = false;
	}

	// ========================== ADICIONALES ==========================
	public function getUserRole($id) 
	{
		$this->userRole = Role::find($id);
		return $this->userRole->name;
	}
}
