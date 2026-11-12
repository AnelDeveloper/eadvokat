<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Rule 10, second layer: even if a record slips past the office scope,
 * only users of the same office may see or change it.
 */
abstract class OfficeOwnedPolicy
{
    public function view(User $user, Model $model): bool
    {
        return $this->sameOffice($user, $model);
    }

    public function update(User $user, Model $model): bool
    {
        return $this->sameOffice($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $this->sameOffice($user, $model);
    }

    protected function sameOffice(User $user, Model $model): bool
    {
        return (int) $model->getAttribute('office_id') === $user->office_id;
    }
}
