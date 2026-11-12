<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('jurisdiction', 16);
            // Free text entered by the lawyer; the app bundles no tariff data.
            $table->text('tariff_source')->nullable();
            $table->string('status', 16);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampsTz();
        });
        DB::statement("ALTER TABLE cost_sheets ADD CONSTRAINT cost_sheets_status_check CHECK (status IN ('draft', 'approved'))");
        DB::statement("ALTER TABLE cost_sheets ADD CONSTRAINT cost_sheets_jurisdiction_check CHECK (jurisdiction IN ('fbih', 'rs', 'brcko'))");
        DB::statement("ALTER TABLE cost_sheets ADD CONSTRAINT cost_sheets_approved_check CHECK (status <> 'approved' OR (approved_by IS NOT NULL AND approved_at IS NOT NULL AND btrim(coalesce(tariff_source, '')) <> ''))");

        Schema::create('cost_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cost_sheet_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('action_date');
            $table->nullableMorphs('source');
            $table->string('source_label'); // "pošta I-2025/044", "kalendar"
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->text('source_quote')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('status', 16);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampsTz();

            $table->index(['cost_sheet_id', 'action_date']);
        });
        DB::statement("ALTER TABLE cost_items ADD CONSTRAINT cost_items_status_check CHECK (status IN ('proposed', 'confirmed'))");
        DB::statement("ALTER TABLE cost_items ADD CONSTRAINT cost_items_confirmed_check CHECK (status <> 'confirmed' OR (amount IS NOT NULL AND confirmed_by IS NOT NULL AND confirmed_at IS NOT NULL))");
        DB::statement('ALTER TABLE cost_items ADD CONSTRAINT cost_items_amount_check CHECK (amount IS NULL OR amount >= 0)');

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 64);
            $table->text('description');
            $table->nullableMorphs('subject');
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->timestampTz('created_at');

            $table->index(['matter_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('cost_items');
        Schema::dropIfExists('cost_sheets');
    }
};
