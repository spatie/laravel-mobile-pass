# Upgrading

## Apple passes get their own web service token

Every updatable Apple pass used to carry `mobile-pass.apple.webservice.secret` itself as its `authenticationToken`,
and the web service accepted that value for every pass. Anyone who unzipped one `.pkpass` could read the secret and then
fetch, register, unregister or personalize any other pass whose serial number they knew.

Passes generated from now on carry their own token, derived from the serial number, the secret and `APP_KEY`. The
web service accepts a token only for the pass it belongs to, and the secret doesn't open these passes. You don't
have to replace the secret, and there is no database migration. Your app needs an `APP_KEY`.

**Passes generated before this release keep the secret as their token.** Apple
[doesn't let an update change the token](https://developer.apple.com/documentation/walletpasses/adding-a-web-service-to-update-passes)
of a pass that is in a wallet, so these passes can't be moved to a token of their own. You choose what happens to
them:

- By default they keep receiving updates. The secret is accepted for these passes only, so anyone who has one of
  them can still access the others.
- Set `MOBILE_PASS_APPLE_WEBSERVICE_ACCEPT_SHARED_SECRET=false` to stop that. These passes stay in the wallet, but
  no longer receive updates. To make such a pass updatable again, hand out a new pass with a new serial number.

  If you published the config file, first add the key to the `apple.webservice` array:

  ```php
  'accept_shared_secret' => env('MOBILE_PASS_APPLE_WEBSERVICE_ACCEPT_SHARED_SECRET', true),
  ```

A pass counts as generated before this release when its stored `content` holds the secret as `authenticationToken`.
A pass that you saved while the web service was not configured has no token in its `content`. If you handed it out
after you configured the web service, it carries the secret too, but it is not recognized and stops updating. To
keep such a pass updating, store the secret as `authenticationToken` in its `content`.

Don't change the secret afterwards: every pass you handed out stops updating. Changing `APP_KEY` does the same for
the passes generated from this release on.

`VerifyApplePasskitRequest::expectedAuthorizationValue()` is removed, because there is no longer one value for all
passes.

---

## Apple seat semantic tags now use Apple's wire keys

`Seat` used to serialize its properties under bare keys (`number`, `row`, `section`, ...). Apple's
`SemanticTagType.Seat` dictionary expects `seat`-prefixed keys (`seatNumber`, `seatRow`, `seatSection`, ...),
so Wallet ignored the seat details this package wrote. They are now written under the keys Apple documents.

No code changes are required. The property names on `Seat` are unchanged, the three new properties
(`aisle`, `level` and `sectionColor`) were appended to the constructor so positional calls keep working,
and passes stored before this release still hydrate: `Seat::fromArray()` reads the old bare keys as a
fallback and rewrites them under the correct keys on the next save.

If you assert on the raw `pass.json` in your own tests, note that the keys inside `semantics.seats` changed.

---

## Apple Wallet Pass Personalization

This release adds Apple Wallet pass personalization support via a new `apple_mobile_pass_personalizations` table.

**New installs** — publish and run the package migrations normally.

**Existing installs** — publish and run the new migration:

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
php artisan migrate
```

---

## Google Wallet i18n — LocalizedString for Loyalty and Offer pass classes

`LoyaltyPassClass` and `OfferPassClass` now support per-locale translations.
The change is **additive**: legacy string fields continue to carry the default
value as a plain string, and the corresponding `localized*` fields carry the
full `LocalizedString` object.

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

**New installs** — publish and run the package migrations normally. The create-table
migration runs before the migration that adds the `locales` column.

**Existing installs** — publish and run the new migration:

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
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
