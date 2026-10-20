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
        // 1. Buat tabel master instansi / perusahaan untuk event
        if (!Schema::hasTable('event_companies')) {
            Schema::create('event_companies', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150)->unique();
                $table->string('category', 100)->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Opsi default awal seperti EBTKE, dsb.
            $defaultCompanies = [
                'EBTKE',
                'PT Geo Dipa Energi (Persero)',
                'PLN',
                'Dinas Tenaga Kerja',
                'Kementerian ESDM',
                'BNPB / BPBD',
                'Mitra Kontraktor',
            ];

            $now = now();
            foreach ($defaultCompanies as $comp) {
                DB::table('event_companies')->insertOrIgnore([
                    'name' => $comp,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // 2. Tambah kolom company ke tabel events dan buat kolom lama nullable
        if (Schema::hasTable('events')) {
            if (!Schema::hasColumn('events', 'company')) {
                Schema::table('events', function (Blueprint $table) {
                    $table->string('company', 150)->nullable()->after('title');
                });
            }

            // Buat field yang dihapus dari form (category, unit, location, participants, start_time, end_time) menjadi nullable
            try {
                DB::statement("ALTER TABLE events MODIFY COLUMN category VARCHAR(100) NULL DEFAULT NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE events MODIFY COLUMN unit VARCHAR(50) NULL DEFAULT NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE events MODIFY COLUMN location VARCHAR(255) NULL DEFAULT NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE events MODIFY COLUMN participants INT NULL DEFAULT NULL");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE events MODIFY COLUMN pic VARCHAR(255) NULL DEFAULT NULL");
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('events') && Schema::hasColumn('events', 'company')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('company');
            });
        }

        Schema::dropIfExists('event_companies');
    }
};

