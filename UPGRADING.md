# Upgrading

## Google Wallet i18n — LocalizedString for Loyalty and Offer pass classes

`LoyaltyPassClass` and `OfferPassClass` now support per-locale translations.
The change is **additive**: legacy string fields continue to carry the default
value as a plain string, and the corresponding `localized*` fields carry the
full `LocalizedString` object when translations are present.

| Class | Legacy field (string) | Localized field (LocalizedString) |
|---|---|---|
| `LoyaltyPassClass` | `programName` | `localizedProgramName` |
| `LoyaltyPassClass` | `rewardsTier` | `localizedRewardsTier` |
| `LoyaltyPassClass` | `rewardsTierLabel` | `localizedRewardsTierLabel` |
| `LoyaltyPassClass` | `accountNameLabel` | `localizedAccountNameLabel` |
| `LoyaltyPassClass` | `accountIdLabel` | `localizedAccountIdLabel` |
| `OfferPassClass` | `title` | `localizedTitle` |
| `OfferPassClass` | `provider` | `localizedProvider` |
| `OfferPassClass` | `details` | `localizedDetails` |
| `OfferPassClass` | `finePrint` | `localizedFinePrint` |

Plain-string callers require no code changes:

```php
// still works — no change required
$class->setProgramName('Spatie Club');
```

To add translations, pass a `LocalizedString` directly:

```php
$class->setProgramName(
    LocalizedString::of('Spatie Club', 'en-US')
        ->addTranslation('it', 'Club Spatie')
);
```

If you assert on raw API payloads in your own tests, note that the `localized*`
fields now appear alongside the existing string fields.

---

## Apple Wallet Internationalization

This release adds Apple Wallet i18n support via a new `locales` nullable JSON column on the `mobile_passes` table.

**New installs** — the column is included in `create_mobile_pass_tables` automatically.

**Existing installs** — publish and run the new migration:

```bash
php artisan vendor:publish --tag="laravel-mobile-pass-migrations"
php artisan migrate
```

---

This release fixes the Apple PassKit webservice routes (#32). Previously, register-device, check-for-updates, and unregister-device looked up a `MobilePass` by its primary key, but Apple sends the value of `pass.json.serialNumber` as the `passSerial` route parameter. That value had no relation to the auto-generated UUID `id`, so every webservice request returned 404. This affected all installs, including those following the documented `->setSerialNumber('...')` API.

The fix introduces a dedicated `pass_serial` column on `mobile_passes` and renames `apple_mobile_pass_registrations.pass_serial` to `mobile_pass_id` (which always referenced the pass's id, not the serial). `mobile_passes.id` keeps the same UUID and stays opaque, so it remains safe to expose via route model binding.

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

## Update queries that joined on `pass_serial`

Custom queries that joined `mobile_passes.id` to `apple_mobile_pass_registrations.pass_serial` need to switch to `apple_mobile_pass_registrations.mobile_pass_id` (which still references `mobile_passes.id`).

Code that uses the package's `MobilePass::registrations()`, `MobilePass::devices()`, or `AppleMobilePassRegistration::pass()` relations does not need changes; the package wires the new keys internally.

## If you previously worked around the bug

Some applications worked around this bug by calling `->setSerialNumber('pending')` and then rewriting `$pass->content['serialNumber']` to `$pass->id` after `save()`. That workaround can be removed. Calling `->setSerialNumber($yourSerial)` once before `save()` now does the right thing on its own, and `$pass->pass_serial` is the value Apple will send back in webservice calls.
