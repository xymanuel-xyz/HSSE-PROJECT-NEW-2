<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Master Sumber Temuan
        if (!Schema::hasTable('finding_sources')) {
            Schema::create('finding_sources', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed sumber temuan awal sesuai spesifikasi revisi
            $initialSources = [
                'Audit GIMS',
                'Audit EBTKE',
                'Edibination',
                'Audit PMC',
                'GRM',
                'Hasil Observasi',
            ];

            $now = now();
            foreach ($initialSources as $src) {
                DB::table('finding_sources')->insertOrIgnore([
                    'name' => $src,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 2. Modifikasi kolom pada issue_follow_ups agar unit dan pic nullable
        if (Schema::hasTable('issue_follow_ups')) {
            // Gunakan ALTER TABLE statement langsung di MySQL agar aman dan tidak bergantung pada doctrine/dbal
            try {
                DB::statement("ALTER TABLE issue_follow_ups MODIFY COLUMN unit VARCHAR(50) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Abaikan jika sudah nullable
            }

            try {
                DB::statement("ALTER TABLE issue_follow_ups MODIFY COLUMN pic VARCHAR(255) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Abaikan jika sudah nullable
            }

            try {
                DB::statement("ALTER TABLE issue_follow_ups MODIFY COLUMN priority VARCHAR(50) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Abaikan jika sudah nullable
            }

            try {
                DB::statement("ALTER TABLE issue_follow_ups MODIFY COLUMN action_plan TEXT NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Abaikan jika sudah nullable
            }

            // Normalisasi status lama 'Closed' menjadi 'Close' jika ada
            try {
                DB::table('issue_follow_ups')->where('status', 'Closed')->update(['status' => 'Close']);
            } catch (\Throwable $e) {
                // Abaikan jika kolom status berbeda
            }
        }

        // 3. Tabel Dokumentasi Temuan (Add Documentation)
        if (!Schema::hasTable('issue_documentations')) {
            Schema::create('issue_documentations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issue_follow_up_id')
                    ->constrained('issue_follow_ups')
                    ->cascadeOnDelete();
                $table->string('file_name', 255);
                $table->string('original_name', 255);
                $table->string('file_path', 500);
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('mime_type', 100)->nullable();
                $table->string('uploaded_by', 150)->nullable();
                $table->string('caption', 255)->nullable();
                $table->timestamps();

                $table->index(['issue_follow_up_id', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_documentations');
        Schema::dropIfExists('finding_sources');
    }
};
