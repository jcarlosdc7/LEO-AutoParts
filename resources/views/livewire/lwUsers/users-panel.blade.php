<div class="min-h-[calc(100vh-4rem)] bg-slate-50 p-6">
    <div class="mx-auto max-w-6xl space-y-6">
        <header class="flex items-end justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600">Administración</p><h1 class="text-3xl font-bold text-slate-900">Usuarios y roles</h1><p class="text-sm text-slate-500">Cuentas internas, estado y nivel de acceso.</p></div>
            <button wire:click="create" class="rounded-xl bg-blue-600 px-5 py-3 font-bold text-white">Nuevo usuario</button>
        </header>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Nombre</th><th class="px-5 py-3">Correo</th><th class="px-5 py-3">Rol</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Acciones</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $item)
                    <tr>
                        <td class="px-5 py-4 font-semibold">{{ $item->name }}</td><td class="px-5 py-4">{{ $item->email }}</td><td class="px-5 py-4">{{ $item->role?->name ?? 'Sin rol' }}</td>
                        <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $item->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">{{ $item->is_active ? 'Activo' : 'Inactivo' }}</span></td>
                        <td class="px-5 py-4 text-right"><button wire:click="update({{ $item->id }})" class="font-semibold text-blue-600">Editar</button>@if($item->is_active && auth()->id() !== $item->id)<button wire:click="destroy({{ $item->id }})" wire:confirm="¿Desactivar esta cuenta?" class="ml-4 font-semibold text-red-600">Desactivar</button>@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links('custom-tailwind') }}
    </div>

    <form wire:submit="save">
        <x-dialog-modal name="modal-form-user" maxWidth="md">
            <x-slot name="title"><h2 class="text-xl font-bold">{{ $isEditing ? 'Editar usuario' : 'Nuevo usuario' }}</h2></x-slot>
            <x-slot name="content">
                <div class="space-y-4">
                    <div><x-input-label>Nombre</x-input-label><x-text-input wire:model="user.name" class="mt-1 w-full"/>@error('user.name')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><x-input-label>Correo</x-input-label><x-text-input wire:model="user.email" type="email" class="mt-1 w-full"/>@error('user.email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><x-input-label>Rol</x-input-label><select wire:model="user.role_id" class="mt-1 w-full rounded-md border-slate-300">@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
                    <div><x-input-label>{{ $isEditing ? 'Nueva contraseña (opcional)' : 'Contraseña' }}</x-input-label><x-text-input wire:model="password" type="password" class="mt-1 w-full"/>@error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><x-input-label>Confirmar contraseña</x-input-label><x-text-input wire:model="password_confirmation" type="password" class="mt-1 w-full"/></div>
                    @if($isEditing)<label class="flex items-center gap-2"><input wire:model="user.is_active" type="checkbox" class="rounded border-slate-300"><span>Cuenta activa</span></label>@endif
                </div>
            </x-slot>
            <x-slot name="footer"><button type="button" wire:click="$dispatch('close-modal', 'modal-form-user')" class="rounded-xl px-4 py-2 font-semibold text-slate-600">Cancelar</button><button type="submit" class="ml-2 rounded-xl bg-blue-600 px-5 py-2 font-bold text-white">Guardar</button></x-slot>
        </x-dialog-modal>
    </form>
</div>