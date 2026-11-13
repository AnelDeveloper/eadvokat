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

it('renders the page shell in Bosnian', function () {
    $this->get('/')->assertSee('<html lang="bs">', false);
});

it('sends /cijene to the pricing section of the landing page', function () {
    $this->get('/cijene')->assertRedirect('/#cijene');
});
