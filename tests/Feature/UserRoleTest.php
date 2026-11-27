<?php

use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('lets an admin change another users role', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.roles.update', $author), ['role' => 'admin'])
        ->assertRedirect(route('admin.users.roles.index'))
        ->assertSessionHas('success', 'Rôle mis à jour.');

    expect($author->fresh()->role)->toBe('admin');
});

it('prevents an admin from changing their own role', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.roles.update', $admin), ['role' => 'author'])
        ->assertForbidden();

    expect($admin->fresh()->role)->toBe('admin');
});

it('forbids authors from viewing or changing user roles', function () {
    $author = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($author)
        ->get(route('admin.users.roles.index'))
        ->assertForbidden();

    $this->patch(route('admin.users.roles.update', $target), ['role' => 'admin'])
        ->assertForbidden();

    expect($target->fresh()->role)->toBe('author');
});

it('rejects unsupported roles without changing the user', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.roles.update', $author), ['role' => 'superuser'])
        ->assertSessionHasErrors(['role' => 'La valeur sélectionnée pour le champ rôle est invalide.']);

    expect($author->fresh()->role)->toBe('author');
});
