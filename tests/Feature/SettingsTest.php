<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates the profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch('/postavke/profil', ['name' => 'Lejla Karić', 'email' => 'lejla@example.test'])
        ->assertRedirect('/postavke');

    expect($user->fresh())->name->toBe('Lejla Karić')->email->toBe('lejla@example.test');
});

it('changes the password only with the current password', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/postavke/lozinka', ['current_password' => 'pogresno', 'password' => 'nova-lozinka', 'password_confirmation' => 'nova-lozinka'])
        ->assertSessionHasErrors(['current_password' => 'Lozinka nije tačna.']);

    $this->actingAs($user)
        ->put('/postavke/lozinka', ['current_password' => 'password', 'password' => 'nova-lozinka', 'password_confirmation' => 'nova-lozinka'])
        ->assertRedirect('/postavke');

    expect(Hash::check('nova-lozinka', $user->fresh()->password))->toBeTrue();
});
