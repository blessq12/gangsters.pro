<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('name');
            $table->string('tagline')->nullable()->after('description');
            $table->string('public_email')->nullable()->after('email_address');
            $table->string('support_phone')->nullable()->after('phone_additional');
            $table->string('whatsapp_phone')->nullable()->after('support_phone');
            $table->string('telegram')->nullable()->after('whatsapp_phone');
            $table->string('site_url')->nullable()->after('telegram');
            $table->string('work_hours')->nullable()->after('site_url');
            $table->string('delivery_hours')->nullable()->after('work_hours');
            $table->unsignedInteger('min_order_amount_kopecks')->nullable()->after('delivery_hours');
            $table->unsignedInteger('delivery_fee_kopecks')->nullable()->after('min_order_amount_kopecks');
            $table->unsignedSmallInteger('average_delivery_time_minutes')->nullable()->after('delivery_fee_kopecks');
            $table->text('city_coverage')->nullable()->after('average_delivery_time_minutes');
            $table->text('address_comment')->nullable()->after('city_coverage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'brand_name',
                'tagline',
                'public_email',
                'support_phone',
                'whatsapp_phone',
                'telegram',
                'site_url',
                'work_hours',
                'delivery_hours',
                'min_order_amount_kopecks',
                'delivery_fee_kopecks',
                'average_delivery_time_minutes',
                'city_coverage',
                'address_comment',
            ]);
        });
    }
};
