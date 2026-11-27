<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function authorizationPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article protege',
        'slug' => 'article-protege',
        'body' => 'Contenu assez long pour passer la validation.',
        'is_published' => '1',
    ], $overrides);
}

it('redirects guests to the login page for protected post actions', function () {
    $post = Post::factory()->create();

    $this->get(route('posts.edit', $post))
        ->assertRedirect(route('login'));

    $this->get(route('posts.create'))
        ->assertRedirect(route('login'));
});

it('records the authenticated user as author and ignores a submitted owner id', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($author)
        ->post(route('posts.store'), authorizationPostData(['user_id' => $otherUser->id]))
        ->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'slug' => 'article-protege',
        'user_id' => $author->id,
    ]);
});

it('lets an author edit their own post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);

    $this->actingAs($author)
        ->get(route('posts.edit', $post))
        ->assertOk();
});

it('forbids an author from editing another users post and hides its controls', function () {
    $post = Post::factory()->published()->create([
        'user_id' => User::factory()->create()->id,
    ]);
    $author = User::factory()->create();

    $this->actingAs($author)
        ->get(route('posts.edit', $post))
        ->assertForbidden();

    $this->get(route('posts.show', $post))
        ->assertOk()
        ->assertDontSee('Modifier')
        ->assertDontSee('Confirmer la suppression');
});

it('forbids an author from updating another users post', function () {
    $post = Post::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('posts.update', $post), authorizationPostData())
        ->assertForbidden();

    $this->patch(route('posts.publication', $post))
        ->assertForbidden();

    $this->assertDatabaseMissing('posts', ['slug' => 'article-protege']);
    $this->assertDatabaseHas('posts', ['id' => $post->id, 'is_published' => false]);
});

it('forbids an author from deleting another users post', function () {
    $post = Post::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs(User::factory()->create())
        ->delete(route('posts.destroy', $post))
        ->assertForbidden();

    $this->assertDatabaseHas('posts', ['id' => $post->id]);
});

it('lets an admin update any post', function () {
    $post = Post::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->put(route('posts.update', $post), authorizationPostData())
        ->assertRedirect(route('posts.show', $post));

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'slug' => 'article-protege',
    ]);
});

it('restricts category management and its navigation link to admins', function () {
    $category = Category::factory()->create();
    $author = User::factory()->create();

    $this->actingAs($author)
        ->get(route('admin.categories.index'))
        ->assertForbidden();

    $this->get(route('home'))
        ->assertDontSee(route('admin.categories.index'));

    $this->post(route('admin.categories.store'), [
        'name' => 'Interdit',
        'slug' => 'interdit',
    ])->assertForbidden();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertSee($category->name);

    $this->get(route('home'))
        ->assertSee(route('admin.categories.index'));
});

it('lists only the authenticated users posts including drafts', function () {
    $author = User::factory()->create();
    $otherUser = User::factory()->create();
    Post::factory()->create([
        'title' => 'Mon brouillon',
        'user_id' => $author->id,
    ]);
    Post::factory()->published()->create([
        'title' => 'Article prive',
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($author)
        ->get(route('my-posts'))
        ->assertOk()
        ->assertSee('Mon brouillon')
        ->assertDontSee('Article prive');
});

it('redirects guests from their personal posts page to login', function () {
    $this->get(route('my-posts'))
        ->assertRedirect(route('login'));
});
