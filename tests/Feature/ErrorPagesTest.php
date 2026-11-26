<?php

use App\Models\Category;
use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

it('shows the styled 404 page for an unknown URL', function () {
    $this->get('/page-inexistante')
        ->assertNotFound()
        ->assertSee('Page introuvable')
        ->assertSee("Retour à l'accueil", false);
});

it('shows the tailored 404 page for an unknown post', function () {
    $this->get('/posts/999')
        ->assertNotFound()
        ->assertSee('Article introuvable')
        ->assertDontSee('Page introuvable');
});

it('shows a useful category 404 page with available categories', function () {
    $category = Category::factory()->create(['name' => 'Développement', 'slug' => 'developpement']);

    $this->get('/posts/categorie/inconnue')
        ->assertNotFound()
        ->assertSee('Catégorie introuvable')
        ->assertSee('Développement')
        ->assertSee(route('posts.category', $category->slug));
});

it('shows the styled 503 page when the site is read-only', function () {
    config(['site.read_only' => true]);

    $post = Post::factory()->create();

    $this->delete(route('posts.destroy', $post))
        ->assertStatus(503)
        ->assertSee('Service momentanément indisponible')
        ->assertSee('Le site est en lecture seule.');

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

it('keeps not found responses in JSON for JSON requests', function () {
    $this->get('/api/inconnue', ['Accept' => 'application/json'])
        ->assertNotFound()
        ->assertJsonStructure(['message']);

    $this->get('/posts/999', ['Accept' => 'application/json'])
        ->assertNotFound()
        ->assertJsonStructure(['message']);
});
