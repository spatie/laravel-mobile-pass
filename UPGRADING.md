# Upgrading

## Apple web service tokens are now unique per pass

Every pass used to carry `mobile-pass.apple.webservice.secret` itself as its `authenticationToken`.
Anyone who unzipped one pass could call the web service for any other pass. Each pass now carries
an HMAC of its serial number, keyed with that secret, and the web service only accepts the token
of the pass being requested.

Passes issued before this release still carry the raw secret, and their devices are rejected with
`401` until they get an updated pass. To migrate them:

1. Set `MOBILE_PASS_APPLE_WEBSERVICE_ACCEPT_SHARED_SECRET=true` (config key
   `mobile-pass.apple.webservice.accept_shared_secret`).
2. Push an update to existing Apple passes (for example by touching or updating them). Devices
   fetch the new `pass.json`, which carries the per-pass token.
3. Once devices have updated, remove the setting again. While it is enabled, the shared secret
   still grants access to every pass.

If you published the config file, add the new `accept_shared_secret` key to the `webservice` array.

## Apple personalization can only happen once per pass

`/personalize` signs the submitted token with your pass type certificate. It now refuses with
`409` when a pass that is already personalized receives a different token. A retry with the same
token still returns the signature, and `PassPersonalized` is not fired again.

## New migration for Apple registrations

`add_constraints_to_apple_mobile_pass_registrations_table` removes duplicate device registrations,
adds a unique `(device_id, mobile_pass_id)` index and makes registrations cascade when a pass or
device is deleted. Before this, deleting a pass that a device had registered failed on the foreign key.

```bash
php artisan vendor:publish --tag="mobile-pass-migrations"
php artisan migrate
```

## Apple device registration is validated

- `pushToken` must be a hex string of at most 200 characters, otherwise the request gets `422`.
- The pass type ID in the URL must match the pass's `passTypeIdentifier`, otherwise the request gets `404`.

## Apple pushes

- `PushPassUpdateJob` now runs after the surrounding database transaction commits, and not at all
  when it rolls back.
- When one device fails, the remaining devices are still notified. The first failure is thrown
  afterwards and the others are reported.
- Registrations that APNs rejects with `BadDeviceToken` or `DeviceTokenNotForTopic` are deleted,
  like `410` responses already were.

## Apple pass contents

- `Code39`, `Codabar` and `Ean13` barcodes throw `InvalidPass` on Apple passes. Apple Wallet only
  supports `Qr`, `Pdf417`, `Aztec` and `Code128`.
- Remote images are downloaded once per generated pass with a 10 second timeout. A failed download
  throws `ImageNotFound`, and URLs that aren't `http` or `https` are rejected.
- Stored `webServiceURL` and `authenticationToken` values are no longer reused; they always come
  from the current config.

## Google Wallet payloads

Several payloads contained fields the Google Wallet API doesn't accept, so those requests failed
with `400`. They now send what the API expects.

- **`GenericPassClass`** sends only its id and modules. `setIssuerName`, `setCardTitle`, `setHeader`,
  `setSubheader`, `setLogoUrl`, `setHeroImageUrl`, `setBackgroundColor` and `addLocation` are
  deprecated and no longer sent. Set these on `GenericPassBuilder` instead, which gains
  `setLogoUrl()`, `setHeroImageUrl()` and `setBackgroundColor()`.
- **`BoardingPassClass`** sends the carrier as `carrierIataCode` and the logo as the carrier's
  `airlineLogo`. `localScheduledDepartureDateTime` is sent without a UTC offset, so pass the local
  time at the departure airport.
- **`OfferPassBuilder::setTitle()`** is deprecated and sends nothing. Set the title on
  `OfferPassClass`. `setRedemptionCode()` becomes the barcode, or a text module when a barcode with
  a different value is set.
- **`LoyaltyPassBuilder`** sends exactly one balance type. Use `setBalanceString()`,
  `setBalanceInt()`, `setBalanceDouble()` or `setBalanceMoney()` (and the `setSecondaryBalance*`
  equivalents). `setBalanceMicros()` is deprecated. If you set several balance types before, only
  the last one is sent now.
- **`GooglePassClass::retire()`** is deprecated and throws `PlatformDoesntSupport`, because issuers
  can't set `reviewStatus` to `REJECTED`. Stop creating objects for the class and expire the
  existing passes instead.
- When a class or object already exists, it is replaced with `PUT` instead of `PATCH`, so fields you
  removed are cleared on Google. Fields set on Google outside this package are replaced as well.
- Class and object id suffixes must match `[A-Za-z0-9._-]+`, otherwise `InvalidPass` is thrown. An
  empty `mobile-pass.google.issuer_id` throws `InvalidConfig`.

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
