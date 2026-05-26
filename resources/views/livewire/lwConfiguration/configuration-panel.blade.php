<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->
	<!-- Vista Superior -->
	<div class="flex items-center w-full my-3">
		<h2 class="text-xl font-semibold my-2 ml-6 text-gray-900 ms- w-full">Panel de control: Configuración</h2>
	</div>

	<div class="border-t border-gray-300"></div> <!-- Separador -->

	<div class="p-6 m-2 border border-gray-300 bg-gray-100 h-[calc(100%-5.3rem)]">
		
		<!-- Gestión de roles -->
		<div class="mb-6">
			<h3 class="text-lg font-semibold mb-3">Gestionar Roles de Usuarios</h3>
			<div class="flex items-center space-x-4">

				<span class="text-base">Usuario</span>
				<x-select wire:model="selectedUserId" class="p-2 border rounded" style="width: 24rem">
					<option value="0">--Seleccione un usuario--</option>
					@foreach ($users as $user)
						<option value="{{ $user->id }}">{{ $user->name }} (Rol: {{ $user->role->name }})</option>
					@endforeach
				</x-select>
				@error('selectedUserId') <span class="text-red-600"> {{ $message }} </span> @enderror
	
				<span>Rol</span>
				<x-select wire:model="selectedRoleId" class="p-2 border rounded" style="width: 15rem">
					<option value="0">--Seleccione un rol--</option>
					@foreach ($roles as $role)
						<option value="{{ $role->id }}">{{ $role->name }}</option>
					@endforeach
				</x-select>
				@error('selectedRoleId') <span class="text-red-600"> {{ $message }} </span> @enderror
	
				<x-primary-button wire:click="updateRole" class="px-4 py-2 text-white rounded">
					Actualizar Rol
				</x-primary-button>
			</div>
		</div>

		<div class="border-t border-gray-300 mb-6"></div> <!-- Separador -->
		
		<!-- Cambio de idioma -->
		<div class="mb-6">
			<h3 class="text-lg font-semibold mb-3">Cambio de Idioma</h3>
			<x-primary-button wire:click="updateLanguage('es')" class="px-4 py-2 bg-green-500 text-white rounded">
				Español
			</x-primary-button>
			<x-primary-button wire:click="updateLanguage('en')" class="px-4 py-2 bg-green-500 text-white rounded">
				Inglés
			</x-primary-button>
		</div>
	
		<div class="border-t border-gray-300 mb-6"></div> <!-- Separador -->

		<div class="mb-6">
			<h3 class="text-lg font-semibold mb-3">Gestionar de Respaldos</h3>
			<x-primary-button wire:click="createBackup" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
				Generar Respaldo
			</x-primary-button>

			<!-- Lista de respaldos disponibles -->
			<div class="mt-6">
				<h3 class="text-lg font-semibold">Restaurar Respaldo</h3>
		
				@if ($backups->isEmpty())
					<p class="text-gray-600">No hay respaldos disponibles.</p>
				@else
					<x-select	wire:model.live="selectedBackup" class="border-gray-300 rounded mt-2">
						<option value="">-- Seleccione un respaldo --</option>
						@foreach ($backups as $backup)
							<option value="{{ $backup }}">{{ $backup }}</option>
						@endforeach
					</x-select>
		
					<x-primary-button wire:click="restoreBackup('{{ $selectedBackup }}')" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 mt-2">
						Restaurar Respaldo
					</x-primary-button>
				@endif
			</div>
		</div>
		<!-- Botón para generar respaldo -->
		
		{{-- <!-- Configuración de tipo de cliente por defecto -->
		<div class="mb-6">
			<h3 class="text-lg font-semibold mb-3">Tipo de Cliente por Defecto</h3>
			<x-select wire:model="defaultCustomerTypeId" class="p-2 border rounded">
				<option value="">Selecciona un tipo de cliente</option>
				@foreach ($customerTypes as $type)
					<option value="{{ $type->id }}">{{ $type->name }}</option>
				@endforeach
			</x-select>
			<x-primary-button wire:click="updateDefaultCustomerType" class="px-4 py-2 text-white rounded">
				Guardar
			</x-primary-button>
		</div> --}}
	</div>
	
	@script
	<script>
		$wire.on('roleUpdated', () => {
			Swal.fire({
				title: "Rol actualizado correctamente",
				icon: "success",
				timer: 1500,
				showConfirmButton: false
			});

		});

		$wire.on('backupSaveSuccess', () => {
			Swal.fire({
				title: "Backup generado correctamente",
				icon: "success",
				timer: 1500,
				showConfirmButton: false
			});

		});

		$wire.on('backupSaveFail', () => {
			Swal.fire({
				title: "El Backup no se pudo generar",
				icon: "error",
				timer: 1500,
				showConfirmButton: false
			});

		});

		$wire.on('backupRestoreSuccess', () => {
			Swal.fire({
				title: "Backup restaurado correctamente",
				icon: "success",
				timer: 1500,
				showConfirmButton: false
			});

		});

		$wire.on('backupRestoreFail', () => {
			Swal.fire({
				title: "El Backup no se pudo restaurar",
				icon: "error",
				timer: 1500,
				showConfirmButton: false
			});

		});
	</script>
	@endscript
</div>
