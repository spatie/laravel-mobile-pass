<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobile_passes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('pass_serial')->unique();

            $table->string('type');
            $table->string('platform');
            $table->string('builder_name');
            $table->json('content');
            $table->json('images');
            $table->string('download_name')->nullable();
            $table->nullableMorphs('model');

            $table->timestamp('expired_at')->nullable();

            $table->timestamps();
        });

        Schema::create('apple_mobile_pass_devices', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('push_token');
            $table->timestamps();
        });

        Schema::create('apple_mobile_pass_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('device_id');
            $table->foreign('device_id')->references('id')->on('apple_mobile_pass_devices');

            $table->string('pass_type_id');

            $table->foreignUuid('mobile_pass_id')
                ->constrained('mobile_passes', 'id');

            $table->timestamps();

            $table->index(['device_id', 'mobile_pass_id']);
            $table->index(['device_id', 'pass_type_id']);
        });

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
