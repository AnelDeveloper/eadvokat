<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trial access for newly registered offices. Null means no limit
 * (existing offices, paying offices). Data is never deleted when it ends.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->date('trial_ends_on')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('trial_ends_on');
        });
    }
};
