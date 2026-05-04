# Upgrading from v1 to v2

v2 splits a pass's primary key from its serial number. In v1 the routes Apple's PassKit webservice calls (register-device, check-for-updates, unregister-device) looked up a pass by `mobile_passes.id`, but Apple sends the pass's `serialNumber` (the value baked into `pass.json`) which had no relation to the model's auto-generated UUID. Every webservice request returned 404, including for passes created via the documented `->setSerialNumber('...')` API.

In v2 there is a new `mobile_passes.pass_serial` column. It holds the value passed to `->setSerialNumber('...')` (or a UUID when none is set), and that is what the webservice routes look up. `mobile_passes.id` keeps the same UUID it had in v1 and stays opaque, so it remains safe to expose via route model binding (e.g. when building a REST API on top of `MobilePass`).

The `apple_mobile_pass_registrations.pass_serial` column has also been renamed to `mobile_pass_id` to reflect what it actually stores: a foreign key to `mobile_passes.id`.

## Run the migration below

The existing UUID values in `mobile_passes.id` populate the new `pass_serial` column, and `apple_mobile_pass_registrations.pass_serial` is renamed to `mobile_pass_id` (its values are already mobile pass ids).

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
            $table->renameColumn('pass_serial', 'mobile_pass_id');
        });

        Schema::table('apple_mobile_pass_registrations', function (Blueprint $table) {
            $table->foreign('mobile_pass_id')->references('id')->on('mobile_passes');
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
    }
};
```

## Update relations and queries that joined on `pass_serial`

The `apple_mobile_pass_registrations.pass_serial` column is now `mobile_pass_id` and references `mobile_passes.id` directly. Custom queries that joined on `pass_serial` need to be updated to use `mobile_pass_id`.

Code using the package's `MobilePass::registrations()`, `MobilePass::devices()`, or `AppleMobilePassRegistration::pass()` relations does not need changes; the package wires the new keys internally.

## If you previously worked around the v1 bug

Some applications worked around the v1 bug by calling `->setSerialNumber('pending')`, then rewriting `$pass->content['serialNumber']` to `$pass->id` after `save()`. That workaround can be removed. Calling `->setSerialNumber($yourSerial)` once before `save()` now does the right thing on its own, and `$pass->pass_serial` is the value Apple will send back in webservice calls.
