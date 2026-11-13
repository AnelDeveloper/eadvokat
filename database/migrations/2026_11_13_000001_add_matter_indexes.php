<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Postgres does not index foreign keys on its own. A matter's timeline and
 * its documents are read by matter_id, so these lookups get an index each.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $tables = ['events', 'deadlines', 'reminders', 'mail_entries'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->index('matter_id'));
        }

        Schema::table('users', fn (Blueprint $t) => $t->index('office_id'));
        Schema::table('cost_items', fn (Blueprint $t) => $t->index('office_id'));
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropIndex(['matter_id']));
        }

        Schema::table('users', fn (Blueprint $t) => $t->dropIndex(['office_id']));
        Schema::table('cost_items', fn (Blueprint $t) => $t->dropIndex(['office_id']));
    }
};
