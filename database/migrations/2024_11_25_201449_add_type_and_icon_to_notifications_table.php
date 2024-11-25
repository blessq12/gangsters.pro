<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('type', ['info', 'promotion', 'warning', 'error'])->default('info');
            $table->string('icon')->nullable();
        });
    }

    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['type', 'icon']);
        });
    }
};
