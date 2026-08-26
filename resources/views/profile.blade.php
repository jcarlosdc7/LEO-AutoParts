<x-app-layout>
    <div class="leo-page"><div class="leo-container">
        <header><h1 class="leo-title">Mi perfil</h1><p class="leo-subtitle">Administre sus datos personales y credenciales.</p></header>
		<div class="grid gap-5 xl:grid-cols-2">
			<section class="leo-card p-5 sm:p-7">
					<livewire:profile.update-profile-information-form />
			</section>
			<section class="leo-card p-5 sm:p-7">
					<livewire:profile.update-password-form />
			</section>
		</div>
		<section class="leo-card border-red-200 p-5 sm:p-7">
			<div class="max-w-xl">
				<livewire:profile.delete-user-form />
			</div>
		</section>
    </div></div>
</x-app-layout>
