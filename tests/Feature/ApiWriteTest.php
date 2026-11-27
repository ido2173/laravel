<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

function apiPostData(array $overrides = []): array
{
    return array_merge([
        'title' => 'Article API',
        'slug' => 'article-api',
        'body' => 'Contenu assez long pour valider un article API.',
        'is_published' => false,
    ], $overrides);
}

it('returns 401 when a post write has no bearer token', function () {
    $this->postJson(route('api.posts.store'), apiPostData())
        ->assertUnauthorized();

    $this->assertDatabaseCount('posts', 0);
});

it('returns 422 for invalid login credentials with a French JSON error', function () {
    $user = User::factory()->create();

    $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'incorrect',
        'device_name' => 'test-client',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email'])
        ->assertJsonPath('errors.email.0', 'Ces identifiants ne correspondent à aucun compte.');

    $this->assertDatabaseCount('personal_access_tokens', 0);
});

it('issues a hashed bearer token that can create a post', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $login = $this->postJson(route('api.login'), [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'postman',
    ])
        ->assertOk()
        ->assertJsonPath('token_type', 'Bearer');

    $token = $login->json('token');
    $storedToken = DB::table('personal_access_tokens')->where('name', 'postman')->value('token');

    expect($storedToken)->not->toBe($token);

    $this->withToken($token)
        ->postJson(route('api.posts.store'), apiPostData([
            'user_id' => $otherUser->id,
            'is_published' => true,
        ]))
        ->assertCreated()
        ->assertJsonPath('data.title', 'Article API')
        ->assertJsonMissingPath('data.body')
        ->assertJsonMissingPath('data.tags');

    $this->assertDatabaseHas('posts', [
        'slug' => 'article-api',
        'user_id' => $user->id,
        'is_published' => true,
    ]);
});

it('lets an author update their own post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => $author->id]);
    $token = $author->createToken('test-client')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('api.posts.update', $post), apiPostData([
            'title' => 'Article modifié',
            'slug' => 'article-modifie',
        ]))
        ->assertOk()
        ->assertJsonPath('data.title', 'Article modifié');

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => 'Article modifié',
        'user_id' => $author->id,
    ]);
});

it('returns 403 when an author updates another authors post', function () {
    $author = User::factory()->create();
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);
    $token = $author->createToken('test-client')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('api.posts.update', $post), apiPostData(['title' => 'Interdit']))
        ->assertForbidden();

    $this->assertDatabaseHas('posts', [
        'id' => $post->id,
        'title' => $post->title,
    ]);
});

it('returns 422 for invalid post data with French JSON messages', function () {
    $author = User::factory()->create();
    $token = $author->createToken('test-client')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('api.posts.store'), ['title' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'slug', 'body'])
        ->assertJsonPath('errors.title.0', 'Le champ titre est obligatoire.');

    $this->assertDatabaseCount('posts', 0);
});

it('lets an admin delete another authors post', function () {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->create(['user_id' => User::factory()->create()->id]);
    $token = $admin->createToken('test-client')->plainTextToken;

    $this->withToken($token)
        ->deleteJson(route('api.posts.destroy', $post))
        ->assertNoContent();

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

it('revokes the current token on logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-client')->plainTextToken;
    $storedToken = $user->tokens()->firstOrFail();

    $this->withToken($token)
        ->postJson(route('api.logout'))
        ->assertOk()
        ->assertJsonPath('message', 'Déconnexion réussie.');

    $this->assertDatabaseMissing('personal_access_tokens', ['id' => $storedToken->id]);

    Auth::forgetGuards();

    $this->withToken($token)
        ->postJson(route('api.posts.store'), apiPostData())
        ->assertUnauthorized();
});
