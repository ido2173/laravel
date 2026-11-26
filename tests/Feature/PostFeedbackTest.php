<?php

use App\Models\Post;

beforeEach(function () {
    $this->withoutVite();
});

function feedbackPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article avec confirmation',
        'slug' => 'article-avec-confirmation',
        'body' => 'Un contenu suffisamment long pour passer les règles de validation.',
    ], $overrides);
}

it('flashes a message after creating a post', function () {
    $this->post(route('posts.store'), feedbackPostData())
        ->assertRedirect()
        ->assertSessionHas('success', 'Article créé.');

    $this->assertDatabaseHas('posts', ['slug' => 'article-avec-confirmation']);
});

it('shows required-field validation messages in French', function () {
    $this->post(route('posts.store'), [])
        ->assertSessionHasErrors([
            'title' => 'Le champ titre est obligatoire.',
            'body' => 'Le champ contenu est obligatoire.',
        ]);

    $this->assertDatabaseCount('posts', 0);
});

it('flashes a message after updating a post', function () {
    $post = Post::factory()->create(['slug' => 'article-avec-confirmation']);

    $this->put(route('posts.update', $post), feedbackPostData(['title' => 'Article mis à jour']))
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article mis à jour.');

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Article mis à jour']);
});

it('flashes a message after deleting a post', function () {
    $post = Post::factory()->create();

    $this->delete(route('posts.destroy', $post))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Article supprimé.');

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('flashes a message after changing publication status', function () {
    $post = Post::factory()->create(['is_published' => false, 'published_at' => null]);

    $this->patch(route('posts.publication', $post))
        ->assertRedirect(route('posts.show', $post))
        ->assertSessionHas('success', 'Article publié.');

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => true]);
});
