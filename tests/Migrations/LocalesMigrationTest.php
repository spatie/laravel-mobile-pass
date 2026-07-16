<?php

use Illuminate\Support\Facades\Schema;

it('fresh install: add_locales migration is a no-op when column already exists', function () {
    // TestCase already ran create_mobile_pass_tables which includes locales
    expect(Schema::hasColumn('mobile_passes', 'locales'))->toBeTrue();

    $migration = include __DIR__.'/../../database/migrations/add_locales_to_mobile_passes_table.php.stub';
    $migration->up();

    expect(Schema::hasColumn('mobile_passes', 'locales'))->toBeTrue();
});

it('existing install: add_locales migration adds the column when missing', function () {
    // Simulate an install that predates the locales column
    Schema::table('mobile_passes', function ($table) {
        $table->dropColumn('locales');
    });

    expect(Schema::hasColumn('mobile_passes', 'locales'))->toBeFalse();

    $migration = include __DIR__.'/../../database/migrations/add_locales_to_mobile_passes_table.php.stub';
    $migration->up();

    expect(Schema::hasColumn('mobile_passes', 'locales'))->toBeTrue();
});

it('add_locales migration down() is a no-op when column is absent', function () {
    Schema::table('mobile_passes', function ($table) {
        $table->dropColumn('locales');
    });

    $migration = include __DIR__.'/../../database/migrations/add_locales_to_mobile_passes_table.php.stub';
    $migration->down(); // Must not throw

    expect(Schema::hasColumn('mobile_passes', 'locales'))->toBeFalse();
});
