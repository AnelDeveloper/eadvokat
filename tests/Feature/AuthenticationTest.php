<?php

use App\Models\User;

it('shows the login page in Bosnian', function () {
    $this->get('/login')->assertOk()->assertInertia(fn ($page) => $page->component('auth/Login'));
});

it('logs a user in and sends them to Danas', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('today'));

    $this->assertAuthenticatedAs($user);
});

it('rejects a wrong password with a Bosnian message', function () {
    $user = User::factory()->create();

    $this->post('/login', ['email' => $user->email, 'password' => 'pogresno'])
        ->assertSessionHasErrors(['email' => 'Pogrešan e-mail ili lozinka.']);

    $this->assertGuest();
});

it('logs a user out', function () {
    $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});

it('has no public registration, password reset or e-mail verification', function (string $uri) {
    $this->get($uri)->assertNotFound();
})->with(['/register', '/forgot-password', '/email/verify']);

it('redirects guests away from the app', function () {
    $this->get('/danas')->assertRedirect('/login');
});
