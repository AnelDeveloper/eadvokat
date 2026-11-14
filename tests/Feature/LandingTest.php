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

it('hides the dictation shortcut for offices without a speech service', function () {
    $user = User::factory()->create();
    expect($user->office->is_demo)->toBeFalsy();

    $this->actingAs($user)->get('/danas')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Today')->where('canDictate', false));
});

it('shows a Bosnian page for unknown addresses', function () {
    $this->get('/ova-stranica-ne-postoji')
        ->assertNotFound()
        ->assertSee('Stranica nije pronađena')
        ->assertSee('<html lang="bs">', false);
});
