<?php

namespace App\Actions;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Self-service sign-up: a new, empty office with its first user (the owner).
 * Every query afterwards is scoped to this office (rule 10).
 */
final class RegisterOffice
{
    /**
     * @param  array{office_name: string, title: string|null, name: string, email: string, password: string}  $input
     */
    public function handle(array $input): User
    {
        return DB::transaction(function () use ($input) {
            $office = Office::query()->create([
                'name' => $input['office_name'],
                'is_demo' => false,
            ]);

            return User::query()->create([
                'office_id' => $office->id,
                'title' => $input['title'] !== null && trim($input['title']) !== '' ? trim($input['title']) : null,
                'name' => $input['name'],
                'email' => mb_strtolower($input['email']),
                'password' => $input['password'],
            ]);
        });
    }
}
