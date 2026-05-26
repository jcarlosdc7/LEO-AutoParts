<x-app-layout>
	<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2 h-[calc(100vh-64px)]"> <!-- CONTENEDOR MAESTRO -->
		<div class="grid grid-cols-2">
			<div class="flex p-6 justify-center">
				<div class="w-full">
					<livewire:profile.update-profile-information-form />
				</div>
			</div>

			<div class="flex p-6 justify-center">
				<div class="w-full">
					<livewire:profile.update-password-form />
				</div>
			</div>
	
		</div>
		
		{{-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
			<div class="max-w-xl">
				<livewire:profile.delete-user-form />
			</div>
		</div> --}}
	</div>
</x-app-layout>
