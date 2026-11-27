<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

it('returns only published posts with their author and category', function () {
    $author = User::factory()->create(['name' => 'Aïcha Ben Salem']);
    $category = Category::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
    $published = Post::factory()->published()->create([
        'category_id' => $category->id,
        'user_id' => $author->id,
    ]);
    Post::factory()->create();

    $this->getJson(route('api.posts.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $published->id)
        ->assertJsonPath('data.0.author.name', 'Aïcha Ben Salem')
        ->assertJsonPath('data.0.category.slug', 'laravel')
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonMissingPath('data.0.body');
});

it('paginates posts and rejects an invalid page size with French errors', function () {
    Post::factory(11)->published()->create();

    $this->getJson(route('api.posts.index', ['per_page' => 5]))
        ->assertOk()
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.per_page', 5);

    $this->getJson(route('api.posts.index', ['per_page' => 0]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page' => 'Le champ articles par page doit être au moins égal à 1.']);
});

it('returns a published post and hides its body unless requested', function () {
    $post = Post::factory()->published()->create([
        'body' => 'Texte complet visible dans la réponse détaillée.',
    ]);

    $this->getJson(route('api.posts.show', $post))
        ->assertOk()
        ->assertJsonPath('data.id', $post->id)
        ->assertJsonMissingPath('data.body');

    $this->getJson(route('api.posts.show', [$post, 'include' => ['body']]))
        ->assertOk()
        ->assertJsonPath('data.body', 'Texte complet visible dans la réponse détaillée.');
});

it('returns 404 for drafts and unknown API posts', function () {
    $draft = Post::factory()->create();

    $this->getJson(route('api.posts.show', $draft))->assertNotFound();
    $this->getJson('/api/posts/999999')->assertNotFound();
});

it('includes tags only when requested', function () {
    $post = Post::factory()->published()->create();
    $tag = Tag::factory()->create(['name' => 'API', 'slug' => 'api']);
    $post->tags()->attach($tag);

    $this->getJson(route('api.posts.index'))
        ->assertJsonMissingPath('data.0.tags');

    $this->getJson(route('api.posts.index', ['include' => ['tags']]))
        ->assertOk()
        ->assertJsonPath('data.0.tags.0.slug', 'api');
});
