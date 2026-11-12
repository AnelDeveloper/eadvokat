<?php

use App\Models\User;

it('serves the public landing page to guests', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Landing')->where('auth.user', null));
});

it('serves the landing page to signed-in users too', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Landing')->where('auth.user.id', $user->id));
});

it('does not expose the live demo before Phase 8', function () {
    $this->get('/demo')->assertNotFound();
});

it('renders the page shell in Bosnian', function () {
    $this->get('/')->assertSee('<html lang="bs">', false);
});
