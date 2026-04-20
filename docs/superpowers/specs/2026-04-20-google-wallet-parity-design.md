# Google Wallet feature parity for `spatie/laravel-mobile-pass`

Status: draft, pending review.
Author: Freek Van der Herten + Claude.
Date: 2026-04-20.

## 1. Context

The package currently supports Apple Wallet (PKPass) only, despite its composer description and README advertising "iOS and Android". The `Builders/Google/GooglePassBuilder.php` class exists as a three-line empty abstract, `Platform::Google` is defined but not used, and there are no Google routes, models, migrations, or config keys. This spec describes a complete Google Wallet implementation that brings feature parity as close as the platform allows, and also lands several Apple-side improvements that become natural byproducts of the work.

## 2. Decisions summary

The design was reached through a structured Q&A. Each row lists the option selected and why.

| Question | Decision | Rationale |
| --- | --- | --- |
| 1. Parity level | Full, including device events Google actually exposes | Maximum useful coverage. Google has less than Apple here, so "full" is bounded by the platform. |
| 2. API shape | Parallel Apple/Google namespaces (option A) | Matches the directory and enum scaffolding already in place. Honors real platform differences. |
| 3. Class/Object strategy | Pre-declared classes, no local cache (option B) | Aligns with Google's own docs and samples. Avoids hidden machinery and opaque debugging. |
| 4. Credentials | Mirror Apple pattern (base64 vs contents vs path, checked in priority order) | Consistency for users, already-proven deployment ergonomics. |
| 5. Pass delivery | Universal `$pass->addToWalletUrl(): string` (option A) | URLs compose with emails, buttons, QR codes. A signed download route on the Apple side is a small addition, useful on its own. |
| 6a. Update push wiring | Separate action class registered in config (option B) | Matches existing "every action is swappable via config" pattern. |
| 6b. Queueing | Opt-in via `config('mobile-pass.queue.connection')`; sync by default | Backward compatible; users who care about request latency flip one env var. |
| 7. Images | Hybrid: accept URL directly, or package-hosted via existing `images` JSON column (option C) | URL-in for prod, package-hosted for dev/staging and Apple symmetry. |
| 8. Device events | Save/remove callbacks (option B); Smart Tap out of scope for v1 | Small, obviously useful, maps to the existing "fire Laravel events" pattern. Smart Tap is niche and can be added later. |
| 9. Event schema | Persist every callback to a table AND fire Laravel events (option C) | Covers both "I want the history" and "I want to react immediately" use cases. |

## 3. Architecture

### 3.1. Namespace layout

The Google tree mirrors the existing Apple tree structurally so users who know one side can navigate the other without surprises.

```
src/
├── Builders/
│   ├── Apple/                                    (existing, unchanged)
│   │   ├── ApplePassBuilder.php
│   │   ├── AirlinePassBuilder.php
│   │   ├── BoardingPassBuilder.php
│   │   ├── CouponPassBuilder.php
│   │   ├── GenericPassBuilder.php
│   │   ├── StoreCardPassBuilder.php
│   │   ├── EventTicketPassBuilder.php            (NEW, added for parity)
│   │   ├── Entities/
│   │   └── Validators/
│   └── Google/                                   (NEW)
│       ├── GooglePassBuilder.php                 (abstract, replaces today's stub)
│       ├── GooglePassClass.php                   (abstract base for class builders)
│       ├── BoardingPassBuilder.php
│       ├── OfferPassBuilder.php
│       ├── EventTicketPassBuilder.php
│       ├── LoyaltyPassBuilder.php
│       ├── GenericPassBuilder.php
│       ├── BoardingPassClass.php
│       ├── OfferPassClass.php
│       ├── EventTicketPassClass.php
│       ├── LoyaltyPassClass.php
│       ├── GenericPassClass.php
│       ├── Entities/
│       └── Validators/
├── Actions/
│   ├── Apple/                                    (existing)
│   └── Google/                                   (NEW)
│       ├── CreateGoogleObjectAction.php
│       ├── NotifyGoogleOfPassUpdateAction.php
│       └── HandleGoogleCallbackAction.php
├── Http/
│   ├── Controllers/
│   │   ├── Apple/                                (existing)
│   │   │   └── DownloadApplePassController.php   (NEW, serves signed .pkpass URL)
│   │   └── Google/                               (NEW)
│   │       └── HandleCallbackController.php
│   └── Middleware/
│       └── VerifyGoogleCallbackRequest.php       (NEW; validates Google's signed JWT)
├── Jobs/                                          (NEW directory)
│   └── PushPassUpdateJob.php                     (queues both Apple and Google update pushes)
├── Models/
│   ├── MobilePass.php                             (extended: platform-aware)
│   ├── Apple/                                    (existing)
│   └── Google/                                   (NEW)
│       └── GoogleMobilePassEvent.php
├── Events/                                        (NEW directory)
│   ├── GoogleMobilePassSaved.php
│   └── GoogleMobilePassRemoved.php
└── Support/
    ├── Apple/                                    (existing)
    └── Google/                                   (NEW)
        ├── GoogleCredentials.php
        ├── GoogleWalletClient.php
        └── GoogleJwtSigner.php
```

### 3.2. Pass type mapping

| Google type      | Apple equivalent                               | Typical use case         |
| ---------------- | ---------------------------------------------- | ------------------------ |
| `BoardingPass`   | `BoardingPass` (`AirlinePassBuilder`)          | Flights, trains          |
| `Offer`          | `CouponPassBuilder`                            | Discount codes           |
| `EventTicket`    | `EventTicketPassBuilder` (NEW, added for parity) | Concerts, stadiums     |
| `Loyalty`        | `StoreCardPassBuilder`                         | Rewards cards            |
| `Generic`        | `GenericPassBuilder`                           | Anything else            |

## 4. User-facing API

### 4.1. Declaring a class

Classes live on Google's servers. The builder is a thin client that POSTs or PATCHes a class record to Google's REST API. There is no local cache; re-running `save()` with the same id is idempotent.

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;

EventTicketPassClass::make('taylor-swift-brussels-2026-05-12')
    ->setEventName('The Eras Tour')
    ->setVenueName('King Baudouin Stadium')
    ->setVenueAddress('Avenue du Marathon 135, 1020 Brussels')
    ->setStartDate(Carbon::parse('2026-05-12 20:00'))
    ->setLogoUrl('https://cdn.example.com/ts-logo.png')
    ->setHeroImageUrl('https://cdn.example.com/ts-hero.png')
    ->setBackgroundColor('#000000')
    ->save();
```

### 4.2. Querying, updating, retiring classes

```php
// list all event ticket classes on the issuer
$all = EventTicketPassClass::all();             // Collection<EventTicketPassClass>

// read one. Returns a hydrated class builder instance populated
// from Google's GET response, or null when not found.
$class = EventTicketPassClass::find('taylor-swift-brussels-2026-05-12');

// update
$class->setVenueName('Stade Roi Baudouin')->save();

// retire (Google has no hard delete for classes; this flips reviewStatus
// and any other flags that stop the class from accepting new objects)
$class->retire();
```

### 4.3. Creating a pass (object) against a class

```php
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Enums\BarcodeType;

$pass = EventTicketPassBuilder::make()
    ->setClass('taylor-swift-brussels-2026-05-12')
    ->setAttendeeName('John Smith')
    ->setSection('B12')
    ->setSeat('Row 8, Seat 22')
    ->setBarcode(Barcode::make(BarcodeType::QR, 'TS-BRU-JS-b12-r8s22'))
    ->save();
```

`save()` creates a `MobilePass` row (platform = `google`) and inserts the object on Google via REST.

### 4.4. Handing the pass to the user

Works on both platforms.

```php
return redirect($pass->addToWalletUrl());
```

* Apple: a signed route on the package (`/mobile-pass/apple/{uuid}/download?signature=...`) that emits the `.pkpass` file.
* Google: `https://pay.google.com/gp/v/save/{jwt}` with the object reference embedded.

Because it returns a string, it composes with emails, buttons, QR codes, deep links.

### 4.5. Expiring a pass

```php
$pass->expire();
```

* Apple: sets `voided=true` and `expirationDate=now()` on the pass JSON, fires the existing APNs update.
* Google: PATCHes the object with `state=EXPIRED`; Google pushes the update to the device.

On both platforms the pass greys out in the user's wallet but cannot be forcibly removed from the device (a platform limitation, not a package one).

### 4.6. Save/remove callbacks

Two consumption styles, and users pick based on their needs.

```php
use Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassRemoved;

Event::listen(GoogleMobilePassSaved::class, function (GoogleMobilePassSaved $event) {
    $event->mobilePass;
    $event->receivedAt;
});
```

Or query the stored history:

```php
$pass->googleEvents;
$pass->googleEvents()->saves()->count();
$pass->isCurrentlySavedToGoogleWallet();  // helper based on most recent event
```

## 5. Configuration

### 5.1. `config/mobile-pass.php` additions

```php
return [
    // existing keys preserved...
    'apple' => [ /* unchanged */ ],

    'google' => [
        'issuer_id' => env('MOBILE_PASS_GOOGLE_ISSUER_ID'),

        // Provide ONE of these. Resolution order: base64, contents, path.
        'service_account_key_base64'   => env('MOBILE_PASS_GOOGLE_KEY_BASE64'),
        'service_account_key_contents' => env('MOBILE_PASS_GOOGLE_KEY_CONTENTS'),
        'service_account_key_path'     => env('MOBILE_PASS_GOOGLE_KEY_PATH'),

        'origins' => [env('APP_URL')],

        'api_base_url' => env(
            'MOBILE_PASS_GOOGLE_API_BASE_URL',
            'https://walletobjects.googleapis.com/walletobjects/v1'
        ),
    ],

    'actions' => [
        'notify_apple_of_pass_update'  => NotifyAppleOfPassUpdateAction::class,
        'notify_google_of_pass_update' => NotifyGoogleOfPassUpdateAction::class,
        'register_device'              => RegisterDeviceAction::class,
        'unregister_device'            => UnregisterDeviceAction::class,
        'handle_google_callback'       => HandleGoogleCallbackAction::class,
    ],

    'models' => [
        'mobile_pass'                    => MobilePass::class,
        'apple_mobile_pass_registration' => AppleMobilePassRegistration::class,
        'apple_mobile_pass_device'       => AppleMobilePassDevice::class,
        'google_mobile_pass_event'       => GoogleMobilePassEvent::class,
    ],

    'builders' => [
        'apple' => [ /* existing keys plus: */
            'event_ticket' => EventTicketPassBuilder::class,
        ],
        'google' => [
            'boarding'     => Google\BoardingPassBuilder::class,
            'event_ticket' => Google\EventTicketPassBuilder::class,
            'generic'      => Google\GenericPassBuilder::class,
            'loyalty'      => Google\LoyaltyPassBuilder::class,
            'offer'        => Google\OfferPassBuilder::class,
        ],
    ],

    'queue' => [
        'connection' => env('MOBILE_PASS_QUEUE_CONNECTION'),
        'name'       => env('MOBILE_PASS_QUEUE_NAME', 'default'),
    ],
];
```

### 5.2. `.env.example` additions

```dotenv
# Google
MOBILE_PASS_GOOGLE_ISSUER_ID=3388000000012345678
MOBILE_PASS_GOOGLE_KEY_BASE64=
MOBILE_PASS_GOOGLE_KEY_CONTENTS=
MOBILE_PASS_GOOGLE_KEY_PATH=

# Queue (both Apple APNs pushes and Google REST updates)
MOBILE_PASS_QUEUE_CONNECTION=
MOBILE_PASS_QUEUE_NAME=default
```

### 5.3. Routes

The existing `Route::mobilePass($prefix)` macro is extended to also mount:

* `POST {prefix}/google/callbacks` (wrapped in `VerifyGoogleCallbackRequest` middleware).
* `GET {prefix}/apple/{uuid}/download` (signed URL, served by `DownloadApplePassController`).

Users keep calling the same macro; they do not need to know which routes belong to which platform.

### 5.4. `GoogleCredentials` helper

```php
GoogleCredentials::key();          // decoded array
GoogleCredentials::privateKey();   // PEM string for JWT signing
GoogleCredentials::clientEmail();  // service account email (`iss` in JWTs)
GoogleCredentials::issuerId();     // config('mobile-pass.google.issuer_id')
```

Throws `InvalidConfig` when none of the three key-providing env vars are set, with a clear message pointing at the docs page for obtaining credentials from Google.

## 6. Internal mechanics

### 6.1. `GoogleWalletClient` (REST wrapper)

A single service class wrapping every REST call. Uses Laravel's `Http` facade so tests can `Http::fake()` it exactly like the existing Apple push action.

```php
$client->insertClass('eventTicketClass', $id, $payload);
$client->patchClass('eventTicketClass', $id, $partial);
$client->getClass('eventTicketClass', $id);
$client->listClasses('eventTicketClass');

$client->insertObject('eventTicketObject', $id, $payload);
$client->patchObject('eventTicketObject', $id, $partial);
$client->getObject('eventTicketObject', $id);
```

Each call acquires an access token via `GoogleJwtSigner::accessToken()` (cached for its TTL in the Laravel cache), attaches `Authorization: Bearer ...`, and delegates to `Http::`. Retries with exponential backoff on 5xx. Fails hard on 4xx other than 409 (idempotent upgrade: create becomes patch).

### 6.2. `GoogleJwtSigner` (RS256 signing)

Used in two places.

1. OAuth2 access token for REST calls: signs a JWT assertion per Google's service-account flow, exchanges for a bearer token, caches the bearer for `expires_in`.
2. Save URL JWT for `addToWalletUrl()`: signs a compact JWT with payload such as `{iss, aud: "google", typ: "savetowallet", iat, payload: {eventTicketObjects: [{id: ...}]}}`. Origin restrictions come from `config('mobile-pass.google.origins')`.

Uses `firebase/php-jwt` for readability. If we want to avoid the dependency we can hand-roll with `openssl_sign`; the interface is identical either way.

### 6.3. Update push: `NotifyGoogleOfPassUpdateAction`

Mirrors `NotifyAppleOfPassUpdateAction` structurally. The existing `MobilePass::boot()` listener becomes platform-aware and dispatches a `PushPassUpdateJob`, which runs sync when `config('mobile-pass.queue.connection')` is null and queued otherwise.

```php
static::updated(function (MobilePass $mobilePass) {
    $configKey = $mobilePass->platform === Platform::Apple
        ? 'notify_apple_of_pass_update'
        : 'notify_google_of_pass_update';

    $action = Config::getActionClass(
        $configKey,
        match ($mobilePass->platform) {
            Platform::Apple  => NotifyAppleOfPassUpdateAction::class,
            Platform::Google => NotifyGoogleOfPassUpdateAction::class,
        }
    );

    PushPassUpdateJob::dispatch($mobilePass, $action);
});
```

Both Apple and Google updates route through the same job, so queuing Apple's APNs push is a free byproduct. This also closes out the tech debt flagged in the pre-work review.

### 6.4. Images (hybrid)

Google's `Image` entity exposes two constructors.

```php
Image::fromUrl('https://cdn.example.com/logo.png');      // path A: URL used verbatim
Image::fromLocalPath('/var/app/images/logo.png');        // path B: package hosts and serves
```

`$image->publicUrl()` returns the correct URL for each path. Path B writes the binary into the `images` JSON column on the `MobilePass` row and returns a signed URL to a new route (`GET /mobile-pass/google-image/{uuid}/{key}?signature=...`) that streams the bytes to Google on demand. No new column is needed; the existing `images` JSON column accommodates both modes.

Limitation: path B (package-hosted) is supported only for **object-level** images (for example a barcode overlay rendered per user). **Class-level** images (logo, hero, background) must use path A (URL), because classes are not persisted locally in the chosen design (option B in Section 2) and therefore have no row to store binary against. This constraint is documented in the image handling doc page. Users who want to avoid per-user image generation entirely can keep using path A everywhere.

### 6.5. Save/remove callbacks

One controller, wrapped in `VerifyGoogleCallbackRequest`. On a valid request the action:

1. Decodes the Google-signed JWT payload to extract `classId`, `objectId`, `eventType` (`save` or `del`).
2. Resolves the `MobilePass` row from the `objectId`.
3. Writes a `GoogleMobilePassEvent` row.
4. Fires `GoogleMobilePassSaved` or `GoogleMobilePassRemoved`.
5. Returns 200.

The action class is swappable via `config('mobile-pass.actions.handle_google_callback')`.

## 7. Database schema

Two migration operations, published via `--tag="mobile-pass-migrations"`.

```php
// Add expired_at to existing table.
Schema::table('mobile_passes', function (Blueprint $table) {
    $table->timestamp('expired_at')->nullable()->after('download_name');
});

// New table for Google save/remove callback history.
Schema::create('mobile_pass_google_events', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('mobile_pass_id')
        ->constrained('mobile_passes')
        ->cascadeOnDelete();
    $table->string('event_type');              // 'save' | 'remove'
    $table->timestamp('received_at');
    $table->json('raw_payload')->nullable();   // decoded callback JWT, for debugging
    $table->timestamps();

    $table->index(['mobile_pass_id', 'event_type']);
    $table->index('received_at');
});
```

Google class data lives on Google's servers, so no local `mobile_pass_google_classes` table is needed. Google object metadata (class id reference, state) lives inside the existing `content` JSON column on `mobile_passes`, mirroring how Apple stores `passTypeIdentifier` and similar fields.

## 8. Apple-side improvements bundled in

Four items that land naturally as part of this cross-platform work.

1. `EventTicketPassBuilder` for Apple (new). Missing today; added for parity with Google's event ticket builder.
2. Signed `.pkpass` download route served by `DownloadApplePassController`. Required for Apple's `addToWalletUrl()`, useful on its own for email and QR code flows.
3. Queued APNs push via `PushPassUpdateJob`. Closes out the tech debt flagged during the pre-work review.
4. `$pass->expire()` on Apple. Sets `voided=true` plus `expirationDate=now()` and triggers the APNs push. Was missing.

## 9. Testing strategy

Tests are integral to every milestone, following the patterns already in place for the Apple side.

* **Builders.** One test file per class and object builder. Snapshot tests for the JSON payloads, same mechanism as the existing `toMatchMobilePassSnapshot()` pattern in `tests/Pest.php`.
* **Actions.** `NotifyGoogleOfPassUpdateAction` tested with `Http::fake()` asserting the outgoing PATCH, plus error-response tests (404, 410-equivalent) that verify model state.
* **Controllers.** Callback controller tested like the existing PassKit controllers: verify middleware auth, verify the event fires, verify the DB row is written, return 200.
* **JWT signing.** Unit tests decode the JWT and assert both payload and signature against the fixture public key.
* **`addToWalletUrl()`.** Google path: decode the JWT, assert its contents. Apple path: hit the signed download route and assert the `.pkpass` contents parse correctly.
* **Fixtures.** `tests/TestSupport/google-service-account.json` with a throwaway RSA keypair. Mirrors how the Apple side has a test certificate.

## 10. Documentation deliverable

Tone matches freek.dev: conversational, first-person plural ("we"), examples-first, tight one-paragraph intros. No em-dashes in prose (Spatie convention). Each page follows the skeleton of the current `docs/basic-usage/generating-your-first-pass.md`: one-paragraph intro (what, why), code block, prose around code explaining anything non-obvious, one or two practical gotchas called out inline.

New pages:

```
docs/
├── basic-usage/
│   ├── choosing-between-apple-and-google.md
│   ├── getting-credentials-from-google.md
│   ├── declaring-google-pass-classes.md
│   ├── generating-your-first-google-pass.md
│   ├── updating-google-passes.md
│   ├── handing-out-passes.md                (unified addToWalletUrl walk-through)
│   └── expiring-passes.md                   (unified expire() walk-through)
└── advanced-usage/
    ├── listening-to-google-save-remove-events.md
    ├── queueing-update-pushes.md
    └── hosting-your-own-google-images.md
```

Existing pages get updated:

* `docs/introduction.md` and `docs/_index.md`: mention Google support, update the hero pitch.
* `docs/installation-setup.md`: add a Google credentials subsection, reference the new `getting-credentials-from-google.md` page.
* `README.md`: switch "iOS and Android" claim from aspirational to accurate, link both platform walk-throughs.

## 11. Implementation milestones and quality gates

After every milestone that writes code, a quality gate runs. The final sweep runs across the entire cumulative diff, catching cross-file smells that are invisible within a single milestone.

Each gate runs:

1. `laravel-simplifier` agent on the milestone's changed files. Reviews for reuse, dead code, over-abstraction, misplaced responsibilities. Any fix it proposes, we apply, then re-run `pest` and `phpstan` to catch regressions.
2. `php-guidelines-from-spatie` skill applied as a review pass on the same files. Targets happy-path-last ordering, no-else, short nullable syntax, single-line docblocks where appropriate, no inline fully-qualified namespaces, no single-letter vars, no unnecessary comments.
3. Baseline: `./vendor/bin/pest`, `./vendor/bin/phpstan analyse --memory-limit=1G`, `./vendor/bin/pint --test` all green.

### Milestone list

1. **Shared infrastructure.** `GoogleCredentials`, `GoogleJwtSigner`, `GoogleWalletClient`, queue config, `PushPassUpdateJob` (wrapping both Apple and Google push actions). **Gate.**
2. **Google class builders.** `GooglePassClass` abstract plus five concrete types plus validators plus tests. **Gate.**
3. **Google pass builders.** `GooglePassBuilder` abstract plus five concrete types plus validators plus tests. **Gate.**
4. **Cross-platform unification on `MobilePass`.** `expire()`, `addToWalletUrl()`, `DownloadApplePassController`, signed route wiring. **Gate.**
5. **Save/remove callbacks.** `VerifyGoogleCallbackRequest`, `HandleCallbackController`, `HandleGoogleCallbackAction`, `GoogleMobilePassEvent` model, migration, Laravel events. **Gate.**
6. **Apple `EventTicketPassBuilder`** for parity with Google's event ticket builder. **Gate.**
7. **Docs.** New pages under `docs/basic-usage/` and `docs/advanced-usage/`, updates to `introduction.md`, `installation-setup.md`, `README.md`. **Gate (prose review).**
8. **Final sweep.** `laravel-simplifier` and `php-guidelines-from-spatie` run across the whole cumulative diff. Targets cross-file issues. Full pest plus phpstan plus pint baseline. PR-ready.

If the simplifier and the guidelines ever recommend conflicting changes on the same code, we surface the conflict rather than pick silently.

## 12. Migration path for existing users

The package is pre-1.0, but worth spelling out so users are not surprised when they pull the change.

1. `vendor:publish --tag="mobile-pass-migrations" --force` to pull new migrations. `php artisan migrate` adds `expired_at` and the `mobile_pass_google_events` table.
2. `vendor:publish --tag="mobile-pass-config" --force` to pull the new config. Users merge their existing overrides manually.
3. Google env vars are opt-in. Apps that do not set `MOBILE_PASS_GOOGLE_ISSUER_ID` keep working identically.
4. `MobilePass::boot()` becomes platform-aware but preserves today's Apple behavior by default. No user change is required for Apple-only apps.
5. APNs push moves into `PushPassUpdateJob`. With `MOBILE_PASS_QUEUE_CONNECTION` unset, the job runs sync and the observable behavior matches today's.

## 13. Out of scope for v1

Called out explicitly so expectations match.

* **Smart Tap.** Google's NFC integration for loyalty/transit POS terminals. Niche, specialized, can be added later via a dedicated opt-in layer. The `Entities/` tree will be shaped so adding Smart Tap fields later does not require restructuring.
* **Co-branded passes (issuer co-branding).** Low demand, high platform complexity.
* **Google Pay API for in-app purchases.** Different API entirely, not Wallet.
* **Offline-first flows.** Out of scope for both platforms.

## 14. Open questions

None at this time. Any that surface during implementation go in the PR description and are resolved before merge, not after.
