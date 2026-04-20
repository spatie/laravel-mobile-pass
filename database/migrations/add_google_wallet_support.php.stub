<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mobile_passes', 'expired_at')) {
            Schema::table('mobile_passes', function (Blueprint $table) {
                $table->timestamp('expired_at')->nullable()->after('download_name');
            });
        }

        Schema::create('mobile_pass_google_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('mobile_pass_id')
                ->constrained('mobile_passes')
                ->cascadeOnDelete();
            $table->string('event_type');
            $table->timestamp('received_at');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['mobile_pass_id', 'event_type']);
            $table->index('received_at');
        });
    }
};
