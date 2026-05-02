# Upgrading from v1 to v2

v2 makes the pass `serialNumber` the primary key of the `mobile_passes` table. In v1 the routes Apple's PassKit webservice calls (register-device, check-for-updates, unregister-device) looked up a pass by the model's auto-generated UUID `id`, which had no relation to the `serialNumber` baked into `pass.json`. That meant every webservice request returned 404, including for passes created via the documented `->setSerialNumber('...')` API.

In v2, the value passed to `->setSerialNumber('...')` becomes the model's `id`. When you do not call `setSerialNumber()`, the package generates a UUID and uses it as both the id and the serial. Either way, the value Apple sends back in `passSerial` always matches a row.

## Run the migration below

The columns `mobile_passes.id` and `apple_mobile_pass_registrations.pass_serial` change from `uuid` to `string`. Existing UUID values fit unchanged in the new string columns, so no data backfill is needed.

Create a new migration in your application and paste the following:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apple_mobile_pass_registrations', function (Blueprint $table) {
            $table->dropForeign(['pass_serial']);
        });

        Schema::table('mobile_passes', function (Blueprint $table) {
            $table->string('id')->change();
        });

        Schema::table('apple_mobile_pass_registrations', function (Blueprint $table) {
            $table->string('pass_serial')->change();
            $table->foreign('pass_serial')->references('id')->on('mobile_passes');
        });
    }
};
```

## Drop assumptions about UUID-typed ids

`mobile_passes.id` and `apple_mobile_pass_registrations.pass_serial` are now plain strings. If you had application code that type-asserted the id as a UUID (Postgres `uuid` casts, custom validation rules, type hints), allow arbitrary strings instead.

Custom queries that joined `mobile_passes.id` to `apple_mobile_pass_registrations.pass_serial` continue to work. The relationship between those two columns has not changed, only their column type.

## If you previously worked around the v1 bug

Some applications worked around the v1 bug by calling `->setSerialNumber('pending')`, then rewriting `$pass->content['serialNumber']` to `$pass->id` after `save()`. That workaround can be removed. Calling `->setSerialNumber($yourSerial)` once before `save()` now does the right thing on its own.
