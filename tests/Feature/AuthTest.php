<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
});

function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Auteur de test',
        'email' => 'author@example.test',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ], $overrides);
}

it('registers an author without granting submitted administrator privileges', function () {
    $this->post(route('register.store'), registrationData(['role' => 'admin']))
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Compte créé : bienvenue, Auteur de test.');

    $user = User::where('email', 'author@example.test')->firstOrFail();

    expect($user->role)->toBe('author')
        ->and(Hash::check('password123', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('validates registration fields and password confirmation in French', function () {
    $this->get(route('register'))->assertOk()->assertSee('Créer un compte');
    $this->get(route('login'))->assertOk()->assertSee('Connexion');

    $this->post(route('register.store'), [])
        ->assertSessionHasErrors([
            'name' => 'Le champ nom est obligatoire.',
            'email' => 'Le champ adresse e-mail est obligatoire.',
            'password' => 'Le champ mot de passe est obligatoire.',
        ]);

    $this->post(route('register.store'), registrationData([
        'password_confirmation' => 'different-password',
    ]))->assertSessionHasErrors([
        'password' => 'La confirmation du champ mot de passe ne correspond pas.',
    ]);
});

it('rejects invalid login credentials without authenticating the visitor', function () {
    User::factory()->create(['email' => 'author@example.test']);

    $this->post(route('login.store'), [
        'email' => 'author@example.test',
        'password' => 'incorrect',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'email' => 'Ces identifiants ne correspondent à aucun compte.',
        ]);

    $this->assertGuest();
});

it('logs in the user and remembers them when requested', function () {
    $user = User::factory()->create(['email' => 'author@example.test']);

    $this->post(route('login.store'), [
        'email' => 'author@example.test',
        'password' => 'password',
        'remember' => '1',
    ])
        ->assertRedirect(route('posts.index'))
        ->assertSessionHas('success', 'Vous êtes connecté.');

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'remember_token' => $user->fresh()->remember_token,
    ]);
    expect($user->fresh()->remember_token)->not->toBeNull();
});

it('returns users to the page that required login', function () {
    $user = User::factory()->create(['email' => 'author@example.test']);

    $this->get(route('posts.create'))
        ->assertRedirect(route('login'));

    $this->post(route('login.store'), [
        'email' => 'author@example.test',
        'password' => 'password',
    ])->assertRedirect(route('posts.create'));

    $this->assertAuthenticatedAs($user);
});

it('logs out and invalidates the authenticated session', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('home'))
        ->assertSessionHas('success', 'Vous êtes déconnecté.');

    $this->assertGuest();
});
