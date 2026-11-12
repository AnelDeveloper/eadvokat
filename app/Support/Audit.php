<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Matter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * The single place where audit entries are written (rule 5). Domain actions
 * call this for every significant change, with old and new values.
 */
final class Audit
{
    /**
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $new
     */
    public static function record(
        Matter $matter,
        string $action,
        string $description,
        ?Model $subject = null,
        array $old = [],
        array $new = [],
    ): AuditLog {
        return AuditLog::query()->create([
            'office_id' => $matter->office_id,
            'matter_id' => $matter->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'old_values' => $old === [] ? null : $old,
            'new_values' => $new === [] ? null : $new,
            'created_at' => Clock::now(),
        ]);
    }
}
