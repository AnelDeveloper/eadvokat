<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('register_counters', function (Blueprint $table) {
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 3);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);

            $table->primary(['office_id', 'direction', 'year']);
        });

        Schema::create('mail_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 3);
            $table->unsignedSmallInteger('register_year');
            $table->unsignedInteger('register_sequence');
            $table->string('register_number', 32); // U-2026/041
            $table->date('received_or_sent_on');
            $table->date('document_date')->nullable();
            $table->string('court');
            $table->string('case_number');
            $table->string('doc_type');
            // Timeline wording, e.g. "Zaprimljen podnesak tuženog".
            $table->string('summary')->nullable();
            $table->string('note')->nullable();
            $table->string('delivery_method', 32)->nullable();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->text('source_quote')->nullable();
            $table->foreignId('proof_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('proof_note')->nullable();
            $table->string('status', 16);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();

            $table->unique(['office_id', 'register_number']);
            $table->unique(['office_id', 'direction', 'register_year', 'register_sequence']);
            $table->index(['office_id', 'direction', 'received_or_sent_on']);
        });

        DB::statement("ALTER TABLE register_counters ADD CONSTRAINT register_counters_direction_check CHECK (direction IN ('in', 'out'))");
        DB::statement("ALTER TABLE mail_entries ADD CONSTRAINT mail_entries_direction_check CHECK (direction IN ('in', 'out'))");
        DB::statement("ALTER TABLE mail_entries ADD CONSTRAINT mail_entries_status_check CHECK (status IN ('proposed', 'confirmed'))");
        DB::statement("ALTER TABLE mail_entries ADD CONSTRAINT mail_entries_delivery_check CHECK (delivery_method IS NULL OR delivery_method IN ('in_person', 'registered_mail'))");
        DB::statement("ALTER TABLE mail_entries ADD CONSTRAINT mail_entries_confirmed_check CHECK (status <> 'confirmed' OR (confirmed_by IS NOT NULL AND confirmed_at IS NOT NULL))");
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_entries');
        Schema::dropIfExists('register_counters');
    }
};
