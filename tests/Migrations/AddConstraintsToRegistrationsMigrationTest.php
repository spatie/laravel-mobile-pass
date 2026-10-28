<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Models\Apple\AppleMobilePassDevice;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('removes duplicate registrations before adding the unique index', function () {
    $migration = include __DIR__.'/../../database/migrations/add_constraints_to_apple_mobile_pass_registrations_table.php.stub';
    $migration->down();

    $pass = MobilePass::factory()->create();
    $device = AppleMobilePassDevice::factory()->create();

    foreach (range(1, 3) as $ignored) {
        DB::table('apple_mobile_pass_registrations')->insert([
            'id' => (string) Str::uuid(),
            'device_id' => $device->getKey(),
            'mobile_pass_id' => $pass->getKey(),
            'pass_type_id' => 'pass.com.example',
        ]);
    }

    $migration->up();

    expect(DB::table('apple_mobile_pass_registrations')->count())->toBe(1);
});
