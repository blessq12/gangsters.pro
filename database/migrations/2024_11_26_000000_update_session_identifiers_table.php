<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('session_identifiers', function (Blueprint $table) {
            $table->boolean('is_temporary')->default(true);
        });
    }

    public function down()
    {
        Schema::table('session_identifiers', function (Blueprint $table) {
            $table->dropColumn('is_temporary');
        });
    }
};
