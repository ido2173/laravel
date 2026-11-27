<x-layout>
    <x-slot:title>Gestion des rôles - Mini-CMS</x-slot:title>

    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-gray-900">Gestion des rôles</h1>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-gray-200 text-gray-600">
                <tr>
                    <th scope="col" class="py-2 pr-4 font-medium">Nom</th>
                    <th scope="col" class="py-2 pr-4 font-medium">Adresse e-mail</th>
                    <th scope="col" class="py-2 pr-4 font-medium">Rôle</th>
                    <th scope="col" class="py-2 font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b border-gray-100 align-top">
                        <td class="py-3 pr-4 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="py-3 pr-4 text-gray-600">{{ $user->email }}</td>
                        <td class="py-3 pr-4 text-gray-600">{{ $user->role }}</td>
                        <td class="py-3">
                            @if ($user->is(auth()->user()))
                                <span class="text-gray-500">Votre rôle</span>
                            @else
                                <form method="POST" action="{{ route('admin.users.roles.update', $user) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')

                                    <label for="role-{{ $user->id }}" class="sr-only">Rôle de {{ $user->name }}</label>
                                    <select id="role-{{ $user->id }}" name="role" class="rounded-md border-gray-300 text-sm">
                                        <option value="author" @selected($user->role === 'author')>Auteur</option>
                                        <option value="admin" @selected($user->role === 'admin')>Administrateur</option>
                                    </select>
                                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">Enregistrer</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layout>
