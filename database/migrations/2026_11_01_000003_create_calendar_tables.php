<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained()->cascadeOnDelete();
            $table->string('type', 16);
            $table->string('title');
            $table->string('description')->nullable();
            $table->timestampTz('starts_at');
            $table->string('location')->nullable(); // "Sudnica 12"
            $table->string('venue')->nullable(); // "Općinski sud u Sarajevu"
            // Values the transcript could not settle, e.g. {"location": ["12", "21"]}.
            $table->jsonb('uncertain')->nullable();
            $table->string('outcome')->nullable(); // "Ročište odgođeno"
            $table->string('outcome_detail')->nullable();
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->text('source_quote')->nullable();
            $table->string('status', 16);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();

            $table->index(['office_id', 'starts_at']);
        });
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_type_check CHECK (type IN ('hearing', 'meeting', 'court_visit'))");
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_status_check CHECK (status IN ('proposed', 'confirmed'))");
        DB::statement("ALTER TABLE events ADD CONSTRAINT events_confirmed_check CHECK (status <> 'confirmed' OR (confirmed_by IS NOT NULL AND confirmed_at IS NOT NULL AND uncertain IS NULL))");

        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->text('transcript')->nullable();
            $table->string('source', 16);
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('status', 16);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();

            $table->index(['office_id', 'matter_id']);
        });
        DB::statement("ALTER TABLE notes ADD CONSTRAINT notes_source_check CHECK (source IN ('voice', 'manual'))");
        DB::statement("ALTER TABLE notes ADD CONSTRAINT notes_status_check CHECK (status IN ('proposed', 'confirmed'))");
        DB::statement("ALTER TABLE notes ADD CONSTRAINT notes_confirmed_check CHECK (status <> 'confirmed' OR (confirmed_by IS NOT NULL AND confirmed_at IS NOT NULL))");

        Schema::create('deadlines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('rule_text');
            $table->unsignedSmallInteger('days')->nullable();
            // "15 dana od prijema" — shown with the start date.
            $table->string('basis');
            $table->string('jurisdiction', 16);
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('source_mail_entry_id')->nullable()->constrained('mail_entries')->nullOnDelete();
            $table->foreignId('source_note_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->text('source_quote')->nullable();
            $table->string('source_highlight')->nullable();
            $table->date('start_date');
            $table->date('due_date');
            $table->boolean('due_date_manually_changed')->default(false);
            $table->boolean('remind_3_days')->default(true);
            $table->boolean('remind_1_day')->default(true);
            $table->string('status', 16);
            $table->boolean('rule_ack')->default(false);
            $table->boolean('start_ack')->default(false);
            $table->boolean('due_ack')->default(false);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('confirmed_at')->nullable();
            $table->foreignId('fulfilled_by_mail_entry_id')->nullable()->constrained('mail_entries')->nullOnDelete();
            $table->timestampTz('fulfilled_at')->nullable();
            $table->timestampsTz();

            $table->index(['office_id', 'due_date']);
        });
        DB::statement("ALTER TABLE deadlines ADD CONSTRAINT deadlines_status_check CHECK (status IN ('proposed', 'confirmed', 'fulfilled'))");
        DB::statement("ALTER TABLE deadlines ADD CONSTRAINT deadlines_jurisdiction_check CHECK (jurisdiction IN ('fbih', 'rs', 'brcko'))");
        // Rule 2: a deadline is active only after all three separate acknowledgements.
        DB::statement("ALTER TABLE deadlines ADD CONSTRAINT deadlines_acknowledged_check CHECK (status = 'proposed' OR (rule_ack AND start_ack AND due_ack AND confirmed_by IS NOT NULL AND confirmed_at IS NOT NULL))");
        DB::statement("ALTER TABLE deadlines ADD CONSTRAINT deadlines_fulfilled_check CHECK (status <> 'fulfilled' OR fulfilled_at IS NOT NULL)");
        DB::statement('ALTER TABLE deadlines ADD CONSTRAINT deadlines_dates_check CHECK (due_date >= start_date)');

        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->constrained()->cascadeOnDelete();
            $table->date('remind_on');
            $table->string('text');
            $table->string('detail')->nullable();
            // Kind of generated reminder ("deadline_3", "deadline_1", "follow_up", "manual").
            $table->string('kind', 16);
            $table->nullableMorphs('source');
            $table->timestampTz('checked_at')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->index(['office_id', 'remind_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('deadlines');
        Schema::dropIfExists('notes');
        Schema::dropIfExists('events');
    }
};
