<x-layout>
    <x-slot:title>Article introuvable - Mini-CMS</x-slot:title>

    <h1 class="text-3xl font-bold text-gray-900">Article introuvable</h1>
    <p class="mt-2 mb-8 text-gray-600">Cet article n'existe pas ou a été déplacé.</p>

    <x-alert type="info" title="Erreur 404" class="max-w-2xl">
        Vérifiez l'adresse, ou consultez la liste des articles.
    </x-alert>

    <p class="mt-6">
        <a href="{{ route('posts.index') }}" class="font-medium text-indigo-600 hover:underline">Voir les articles</a>
    </p>
</x-layout>
