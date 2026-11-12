<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('case_number');
            $table->string('title');
            $table->string('subject'); // "Radi isplate duga"
            $table->string('court');
            $table->string('represented_side'); // "tužioca"
            $table->string('client_name');
            $table->string('jurisdiction', 16);
            $table->timestampsTz();

            $table->unique(['office_id', 'case_number']);
        });
        DB::statement("ALTER TABLE matters ADD CONSTRAINT matters_jurisdiction_check CHECK (jurisdiction IN ('fbih', 'rs', 'brcko'))");

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matter_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('kind', 32); // court_document, submission, proof, recording
            $table->string('name'); // "Rješenje · U-2026/041"
            $table->string('disk', 32)->nullable();
            $table->string('path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->date('document_date')->nullable();
            $table->text('extracted_text')->nullable();
            // Rendered paragraphs: [{text, align, bold, highlighted}]
            $table->jsonb('paragraphs')->nullable();
            // Raw output of DocumentExtractor / SpeechTranscriber.
            $table->jsonb('extraction')->nullable();
            $table->boolean('is_fictional')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();

            $table->index(['office_id', 'matter_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('matters');
    }
};
