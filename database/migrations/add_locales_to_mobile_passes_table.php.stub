<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_passes', function (Blueprint $table) {
            $table->json('locales')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_passes', function (Blueprint $table) {
            $table->dropColumn('locales');
        });
    }
};
