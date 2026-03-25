<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'image_mobile')) {
                $table->string('image_mobile')->nullable()->after('image');
            }
            if (!Schema::hasColumn('banners', 'image_desktop')) {
                $table->string('image_desktop')->nullable()->after('image_mobile');
            }
        });

        // Backfill: если старое image есть, считаем его дефолтом для обоих.
        if (Schema::hasColumn('banners', 'image')) {
            if (Schema::hasColumn('banners', 'image_mobile')) {
                DB::table('banners')
                    ->whereNull('image_mobile')
                    ->update(['image_mobile' => DB::raw('image')]);
            }
            if (Schema::hasColumn('banners', 'image_desktop')) {
                DB::table('banners')
                    ->whereNull('image_desktop')
                    ->update(['image_desktop' => DB::raw('image')]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            if (Schema::hasColumn('banners', 'image_desktop')) {
                $table->dropColumn('image_desktop');
            }
            if (Schema::hasColumn('banners', 'image_mobile')) {
                $table->dropColumn('image_mobile');
            }
        });
    }
};

