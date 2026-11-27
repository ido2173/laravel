<?php

use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('shows only published posts to guests on the public index', function () {
    Post::factory()->published()->create(['title' => 'Article public']);
    Post::factory()->create(['title' => 'Brouillon privé']);

    $this->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Article public')
        ->assertDontSee('Brouillon privé');
});

it('shows an author published posts and own drafts but hides other drafts', function () {
    $author = User::factory()->create();
    $otherAuthor = User::factory()->create();
    Post::factory()->published()->create(['title' => 'Article publié']);
    Post::factory()->create(['title' => 'Mon brouillon', 'user_id' => $author->id]);
    Post::factory()->create(['title' => 'Brouillon privé', 'user_id' => $otherAuthor->id]);

    $this->actingAs($author)
        ->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Article publié')
        ->assertSee('Mon brouillon')
        ->assertDontSee('Brouillon privé');
});

it('shows published posts and every draft to admins on the public index', function () {
    Post::factory()->published()->create(['title' => 'Article publié']);
    Post::factory()->create(['title' => 'Brouillon auteur']);
    Post::factory()->create(['title' => 'Brouillon sans auteur']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('posts.index'))
        ->assertOk()
        ->assertSee('Article publié')
        ->assertSee('Brouillon auteur')
        ->assertSee('Brouillon sans auteur');
});

it('allows guests to view published posts but forbids draft access', function () {
    $publishedPost = Post::factory()->published()->create();
    $draft = Post::factory()->create();

    $this->get(route('posts.show', $publishedPost))->assertOk();
    $this->get(route('posts.show', $draft))->assertForbidden();
});

it('allows authors to view their drafts and forbids another authors drafts', function () {
    $author = User::factory()->create();
    $ownedDraft = Post::factory()->create(['user_id' => $author->id]);
    $otherDraft = Post::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($author)
        ->get(route('posts.show', $ownedDraft))
        ->assertOk();

    $this->get(route('posts.show', $otherDraft))->assertForbidden();
});
