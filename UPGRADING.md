# Upgrading from v1 to v2

v2 splits a pass's primary key from its serial number. In v1 the routes Apple's PassKit webservice calls (register-device, check-for-updates, unregister-device) looked up a pass by `mobile_passes.id`, but Apple sends the pass's `serialNumber` (the value baked into `pass.json`) which had no relation to the model's auto-generated UUID. Every webservice request returned 404, including for passes created via the documented `->setSerialNumber('...')` API.

In v2 there is a new `mobile_passes.pass_serial` column. It holds the value passed to `->setSerialNumber('...')` (or a UUID when none is set), and that is what the webservice routes look up. `mobile_passes.id` keeps the same UUID it had in v1 and stays opaque, so it remains safe to expose via route model binding (e.g. when building a REST API on top of `MobilePass`).

## Run the migration below

Existing UUID values in `mobile_passes.id` can satisfy `pass_serial`, so the upgrade backfills the new column from the existing `id`.

Create a new migration in your application and paste the following:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apple_mobile_pass_registrations', function (Blueprint $table) {
            $table->dropForeign(['pass_serial']);
        });

        Schema::table('mobile_passes', function (Blueprint $table) {
            $table->string('pass_serial')->nullable()->after('id');
        });

        DB::table('mobile_passes')
            ->whereNull('pass_serial')
            ->update(['pass_serial' => DB::raw('id')]);

        Schema::table('mobile_passes', function (Blueprint $table) {
            $table->string('pass_serial')->nullable(false)->change();
            $table->unique('pass_serial');
        });

        Schema::table('apple_mobile_pass_registrations', function (Blueprint $table) {
            $table->string('pass_serial')->change();
            $table->foreign('pass_serial')->references('pass_serial')->on('mobile_passes');
        });
    }
};
```

## Update relations and queries that joined on `mobile_passes.id`

The `apple_mobile_pass_registrations.pass_serial` foreign key now references `mobile_passes.pass_serial` instead of `mobile_passes.id`. Custom queries that joined `mobile_passes.id` to `apple_mobile_pass_registrations.pass_serial` need to be updated to join on `pass_serial` on both sides.

Code using the package's `MobilePass::registrations()`, `MobilePass::devices()`, or `AppleMobilePassRegistration::pass()` relations does not need changes; the package wires the new keys internally.

## If you previously worked around the v1 bug

Some applications worked around the v1 bug by calling `->setSerialNumber('pending')`, then rewriting `$pass->content['serialNumber']` to `$pass->id` after `save()`. That workaround can be removed. Calling `->setSerialNumber($yourSerial)` once before `save()` now does the right thing on its own, and `$pass->pass_serial` is the value Apple will send back in webservice calls.
