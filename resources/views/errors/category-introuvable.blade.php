<x-layout>
    <x-slot:title>Catégorie introuvable - Mini-CMS</x-slot:title>

    <h1 class="text-3xl font-bold text-gray-900">Catégorie introuvable</h1>
    <p class="mt-2 mb-8 text-gray-600">Cette catégorie n'existe pas ou a été déplacée.</p>

    <x-alert type="info" title="Erreur 404" class="mb-6 max-w-2xl">
        Choisissez une catégorie existante pour consulter ses articles.
    </x-alert>

    <ul class="space-y-2">
        @forelse ($categories as $category)
            <li>
                <a href="{{ route('posts.category', $category->slug) }}" class="font-medium text-indigo-600 hover:underline">{{ $category->name }}</a>
            </li>
        @empty
            <li class="text-gray-600">Aucune catégorie n'est disponible.</li>
        @endforelse
    </ul>

    <p class="mt-6">
        <a href="{{ route('home') }}" class="font-medium text-indigo-600 hover:underline">Retour à l'accueil</a>
    </p>
</x-layout>
