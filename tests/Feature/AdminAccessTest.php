<?php

use App\Models\Category;
use App\Models\Tag;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

it('redirects guests from the admin area to login', function () {
    $this->get(route('admin.categories.index'))
        ->assertRedirect(route('login'));
});

it('forbids authors from accessing category, tag, and role administration', function () {
    $author = User::factory()->create();

    $this->actingAs($author)
        ->get(route('admin.categories.index'))
        ->assertForbidden();

    $this->get(route('admin.tags.index'))->assertForbidden();
    $this->get(route('admin.users.roles.index'))->assertForbidden();

    $this->post(route('admin.tags.store'), ['name' => 'Interdit', 'slug' => 'interdit'])
        ->assertForbidden();
});

it('allows admins to access the admin area and see its navigation links', function () {
    Category::factory()->create(['name' => 'Programmation']);
    Tag::factory()->create(['name' => 'Laravel']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.categories.index'))
        ->assertOk();

    $this->get(route('admin.tags.index'))->assertOk();
    $this->get(route('admin.users.roles.index'))->assertOk();

    $this->get(route('home'))
        ->assertSee(route('admin.categories.index'))
        ->assertSee(route('admin.tags.index'))
        ->assertSee(route('admin.users.roles.index'));
});
