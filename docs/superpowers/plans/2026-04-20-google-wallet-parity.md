# Google Wallet feature parity implementation plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Google Wallet generation, updates, and save/remove callbacks to `spatie/laravel-mobile-pass` so it honors its "iOS and Android" promise, and land the Apple-side improvements that naturally fall out of the cross-platform work.

**Architecture:** Parallel `Builders/Google/` namespace alongside the existing `Builders/Apple/`. Pre-declared classes (no local cache). Platform-aware `MobilePass` with unified `expire()` and `addToWalletUrl()`. Queued update push via `PushPassUpdateJob` wrapping both platforms. Save/remove callback endpoint with persisted events and Laravel event fan-out.

**Tech Stack:** PHP 8.3+, Laravel 12/13, Pest 4, PHPStan level 5 via Larastan, Testbench, `firebase/php-jwt` for RS256 signing, Laravel `Http` client for Google REST calls.

**Reference spec:** `docs/superpowers/specs/2026-04-20-google-wallet-parity-design.md`. All design decisions live there; this plan only turns them into code.

---

## File structure

### New files

```
src/
├── Builders/
│   ├── Apple/
│   │   ├── EventTicketPassBuilder.php                      (M6)
│   │   └── Validators/EventTicketApplePassValidator.php    (M6)
│   └── Google/
│       ├── GooglePassBuilder.php                            (M3) – abstract, replaces stub
│       ├── GooglePassClass.php                              (M2) – abstract
│       ├── BoardingPassClass.php                            (M2)
│       ├── OfferPassClass.php                               (M2)
│       ├── EventTicketPassClass.php                         (M2)
│       ├── LoyaltyPassClass.php                             (M2)
│       ├── GenericPassClass.php                             (M2)
│       ├── BoardingPassBuilder.php                          (M3)
│       ├── OfferPassBuilder.php                             (M3)
│       ├── EventTicketPassBuilder.php                       (M3)
│       ├── LoyaltyPassBuilder.php                           (M3)
│       ├── GenericPassBuilder.php                           (M3)
│       ├── Entities/
│       │   ├── LocalizedString.php                          (M2)
│       │   ├── Image.php                                    (M2)
│       │   └── Barcode.php                                  (M2) – Google barcode shape
│       └── Validators/
│           ├── GooglePassClassValidator.php                 (M2)
│           ├── BoardingClassValidator.php                   (M2)
│           ├── OfferClassValidator.php                      (M2)
│           ├── EventTicketClassValidator.php                (M2)
│           ├── LoyaltyClassValidator.php                    (M2)
│           ├── GenericClassValidator.php                    (M2)
│           ├── GooglePassObjectValidator.php                (M3)
│           └── <one per pass type>                          (M3)
├── Actions/
│   ├── Apple/
│   │   └── DownloadApplePassAction.php                      (M4) – serves signed .pkpass
│   └── Google/
│       ├── CreateGoogleObjectAction.php                     (M3)
│       ├── NotifyGoogleOfPassUpdateAction.php               (M1)
│       └── HandleGoogleCallbackAction.php                   (M5)
├── Http/
│   ├── Controllers/
│   │   ├── Apple/
│   │   │   └── DownloadApplePassController.php              (M4)
│   │   └── Google/
│   │       └── HandleCallbackController.php                 (M5)
│   └── Middleware/
│       └── VerifyGoogleCallbackRequest.php                  (M5)
├── Jobs/
│   └── PushPassUpdateJob.php                                (M1)
├── Models/
│   └── Google/
│       └── GoogleMobilePassEvent.php                        (M5)
├── Events/
│   ├── GoogleMobilePassSaved.php                            (M5)
│   └── GoogleMobilePassRemoved.php                          (M5)
├── Exceptions/
│   └── GoogleWalletApiError.php                             (M1)
└── Support/
    └── Google/
        ├── GoogleCredentials.php                            (M1)
        ├── GoogleWalletClient.php                           (M1)
        └── GoogleJwtSigner.php                              (M1)

database/
├── factories/
│   └── GoogleMobilePassEventFactory.php                     (M5)
└── migrations/
    └── add_google_wallet_support.php.stub                   (M5) – adds expired_at + mobile_pass_google_events

docs/
├── basic-usage/
│   ├── choosing-between-apple-and-google.md                 (M7)
│   ├── getting-credentials-from-google.md                   (M7)
│   ├── declaring-google-pass-classes.md                     (M7)
│   ├── generating-your-first-google-pass.md                 (M7)
│   ├── updating-google-passes.md                            (M7)
│   ├── handing-out-passes.md                                (M7)
│   └── expiring-passes.md                                   (M7)
└── advanced-usage/
    ├── listening-to-google-save-remove-events.md            (M7)
    ├── queueing-update-pushes.md                            (M7)
    └── hosting-your-own-google-images.md                    (M7)

tests/
├── Builders/Google/                                         (M2+M3)
├── Actions/Google/                                          (M1, M3, M5)
├── Http/Controllers/Google/                                 (M5)
├── Http/Controllers/Apple/DownloadApplePassControllerTest.php (M4)
├── Jobs/PushPassUpdateJobTest.php                           (M1)
├── Models/Concerns/HasMobilePassesGoogleTest.php            (M4)
├── Models/MobilePassPlatformTest.php                        (M4)
├── Support/Google/                                          (M1)
└── TestSupport/
    ├── google-service-account.json                          (M1 – throwaway keypair)
    └── Google/FakeGoogleWalletClient.php                    (M1 – optional helper)
```

### Modified files

```
src/MobilePassServiceProvider.php           (M1, M5 – bind GoogleWalletClient, register events)
src/Models/MobilePass.php                   (M4 – platform-aware expire, addToWalletUrl)
src/Models/Concerns/HasMobilePasses.php     (M5 – googleEvents relation helper)
src/Exceptions/InvalidConfig.php            (M1 – Google credentials missing)
src/Exceptions/CannotDownload.php           (M4 – adjust message after addToWalletUrl arrives)
src/Enums/Platform.php                      (unchanged — already has Google case)
src/Support/Config.php                      (M5 – googleMobilePassEventModel resolver)
config/mobile-pass.php                      (M1 – google block, queue block, M5 – handle_google_callback, new model keys, M3 – builders.google, M6 – builders.apple.event_ticket)
routes/mobile-pass.php                      (M4 – apple download route, M5 – google callback route)
database/migrations/create_mobile_pass_tables.php.stub  (M4 – add expired_at — or use separate migration; see M5 task 5.1)
composer.json                               (M1 – add firebase/php-jwt)
.env.example                                (M1 – google keys, queue keys)
README.md                                   (M7 – accurate platform claim)
docs/introduction.md                        (M7 – mention Google)
docs/installation-setup.md                  (M7 – Google credentials section)
```

---

## Conventions used throughout the plan

* **TDD:** every task writes the test first, verifies it fails, writes minimal implementation, verifies it passes, then commits.
* **Test runner:** `./vendor/bin/pest <path> --filter="<name>"` for single-test runs, `./vendor/bin/pest` for the full suite.
* **Commit style:** match the existing repo (`feat:`, `fix:`, `chore:`, `refactor:`, `test:`, `docs:`). Co-authored trailer as used in prior commits:

```
Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
```

* **Quality gate** after every milestone:
  1. Dispatch `laravel-simplifier` agent on the milestone's changed files. Apply its fixes, re-run pest and phpstan.
  2. Apply `php-guidelines-from-spatie` skill as a review pass on the same files. Apply fixes.
  3. Run: `./vendor/bin/pest`, `./vendor/bin/phpstan analyse --memory-limit=1G`, `./vendor/bin/pint --test`. All must be green.
  4. Commit any cleanup from the gate under `chore: simplify <milestone>`.

* **Small grouped tasks:** repetitive builder setters (one per field) are grouped into a single TDD task that asserts the JSON payload shape, not one task per setter.

---

## Milestone 1 — Shared Google infrastructure

### Task 1.1: Add `firebase/php-jwt` dependency

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Add the package**

```bash
composer require firebase/php-jwt:^6.10
```

- [ ] **Step 2: Verify install**

Run: `composer show firebase/php-jwt`
Expected: version 6.10.x or later listed.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: add firebase/php-jwt for RS256 signing"
```

### Task 1.2: Google test fixture keypair

**Files:**
- Create: `tests/TestSupport/google-service-account.json`
- Create: `tests/TestSupport/Google/GoogleFixtures.php`

- [ ] **Step 1: Generate a throwaway RSA keypair**

Run in a scratch dir:
```bash
openssl genrsa -out /tmp/mobilepass-test-priv.pem 2048
openssl rsa -in /tmp/mobilepass-test-priv.pem -pubout -out /tmp/mobilepass-test-pub.pem
```

Capture both files' contents.

- [ ] **Step 2: Compose the service-account JSON**

Create `tests/TestSupport/google-service-account.json`:

```json
{
  "type": "service_account",
  "project_id": "mobile-pass-test",
  "private_key_id": "test-key-id",
  "private_key": "<PASTE /tmp/mobilepass-test-priv.pem>",
  "client_email": "mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com",
  "client_id": "100000000000000000000",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/mobile-pass-test%40mobile-pass-test.iam.gserviceaccount.com"
}
```

Keep `\n` escapes in the PEM string. Verify the JSON is valid:

```bash
php -r 'json_decode(file_get_contents("tests/TestSupport/google-service-account.json"), flags: JSON_THROW_ON_ERROR); echo "ok\n";'
```

- [ ] **Step 3: Create a fixtures helper**

Create `tests/TestSupport/Google/GoogleFixtures.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Tests\TestSupport\Google;

class GoogleFixtures
{
    public static function serviceAccountPath(): string
    {
        return __DIR__.'/../google-service-account.json';
    }

    public static function serviceAccountContents(): string
    {
        return (string) file_get_contents(self::serviceAccountPath());
    }

    public static function privateKey(): string
    {
        $decoded = json_decode(self::serviceAccountContents(), true, flags: JSON_THROW_ON_ERROR);

        return $decoded['private_key'];
    }

    public static function publicKey(): string
    {
        return file_get_contents(__DIR__.'/../google-public-key.pem');
    }
}
```

Save the public key PEM to `tests/TestSupport/google-public-key.pem`.

- [ ] **Step 4: Commit**

```bash
git add tests/TestSupport/google-service-account.json tests/TestSupport/google-public-key.pem tests/TestSupport/Google/GoogleFixtures.php
git commit -m "test: add Google service account fixture"
```

### Task 1.3: `GoogleCredentials` support class

**Files:**
- Create: `src/Support/Google/GoogleCredentials.php`
- Modify: `src/Exceptions/InvalidConfig.php`
- Create: `tests/Support/Google/GoogleCredentialsTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Support/Google/GoogleCredentialsTest.php`:

```php
<?php

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google', [
        'issuer_id' => null,
        'service_account_key_base64'   => null,
        'service_account_key_contents' => null,
        'service_account_key_path'     => null,
    ]);
});

it('throws InvalidConfig when no key is configured', function () {
    GoogleCredentials::key();
})->throws(InvalidConfig::class);

it('loads credentials from a file path', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('loads credentials from raw contents', function () {
    config()->set('mobile-pass.google.service_account_key_contents', GoogleFixtures::serviceAccountContents());

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('loads credentials from base64 contents', function () {
    config()->set(
        'mobile-pass.google.service_account_key_base64',
        base64_encode(GoogleFixtures::serviceAccountContents())
    );

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('prefers base64 over contents and path when multiple are set', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.service_account_key_contents', '{"client_email":"wrong@example.com"}');
    config()->set(
        'mobile-pass.google.service_account_key_base64',
        base64_encode(GoogleFixtures::serviceAccountContents())
    );

    expect(GoogleCredentials::clientEmail())
        ->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
});

it('exposes the private key as PEM', function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());

    expect(GoogleCredentials::privateKey())->toContain('-----BEGIN PRIVATE KEY-----');
});

it('returns the configured issuer id', function () {
    config()->set('mobile-pass.google.issuer_id', '3388000000012345678');

    expect(GoogleCredentials::issuerId())->toBe('3388000000012345678');
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Support/Google/GoogleCredentialsTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Add exception helper**

Open `src/Exceptions/InvalidConfig.php` and append:

```php
public static function missingGoogleCredentials(): self
{
    return new self(
        'No Google service account key is configured. Set one of '
        .'MOBILE_PASS_GOOGLE_KEY_BASE64, MOBILE_PASS_GOOGLE_KEY_CONTENTS, '
        .'or MOBILE_PASS_GOOGLE_KEY_PATH. See the docs on getting credentials from Google.'
    );
}
```

- [ ] **Step 4: Implement `GoogleCredentials`**

Create `src/Support/Google/GoogleCredentials.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Spatie\LaravelMobilePass\Exceptions\InvalidConfig;

class GoogleCredentials
{
    /** @return array<string, mixed> */
    public static function key(): array
    {
        $raw = static::rawKeyContents();

        return json_decode($raw, true, flags: JSON_THROW_ON_ERROR);
    }

    public static function privateKey(): string
    {
        return static::key()['private_key'];
    }

    public static function clientEmail(): string
    {
        return static::key()['client_email'];
    }

    public static function issuerId(): string
    {
        return (string) config('mobile-pass.google.issuer_id');
    }

    protected static function rawKeyContents(): string
    {
        $base64 = config('mobile-pass.google.service_account_key_base64');
        if (! empty($base64)) {
            return base64_decode($base64);
        }

        $contents = config('mobile-pass.google.service_account_key_contents');
        if (! empty($contents)) {
            return $contents;
        }

        $path = config('mobile-pass.google.service_account_key_path');
        if (! empty($path) && is_file($path)) {
            return (string) file_get_contents($path);
        }

        throw InvalidConfig::missingGoogleCredentials();
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Support/Google/GoogleCredentialsTest.php`
Expected: 7 passed.

- [ ] **Step 6: Commit**

```bash
git add src/Support/Google/GoogleCredentials.php src/Exceptions/InvalidConfig.php tests/Support/Google/GoogleCredentialsTest.php
git commit -m "feat: add GoogleCredentials resolver"
```

### Task 1.4: Google config block

**Files:**
- Modify: `config/mobile-pass.php`
- Modify: `.env.example`

- [ ] **Step 1: Add google block to config**

Insert into `config/mobile-pass.php`, below the `apple` key:

```php
'google' => [
    'issuer_id' => env('MOBILE_PASS_GOOGLE_ISSUER_ID'),

    'service_account_key_base64'   => env('MOBILE_PASS_GOOGLE_KEY_BASE64'),
    'service_account_key_contents' => env('MOBILE_PASS_GOOGLE_KEY_CONTENTS'),
    'service_account_key_path'     => env('MOBILE_PASS_GOOGLE_KEY_PATH'),

    'origins' => [env('APP_URL')],

    'api_base_url' => env(
        'MOBILE_PASS_GOOGLE_API_BASE_URL',
        'https://walletobjects.googleapis.com/walletobjects/v1'
    ),
],
```

- [ ] **Step 2: Add queue block to config**

Below the `builders` key, add:

```php
'queue' => [
    'connection' => env('MOBILE_PASS_QUEUE_CONNECTION'),
    'name'       => env('MOBILE_PASS_QUEUE_NAME', 'default'),
],
```

- [ ] **Step 3: Add env examples**

Append to `.env.example`:

```dotenv
MOBILE_PASS_GOOGLE_ISSUER_ID=
MOBILE_PASS_GOOGLE_KEY_BASE64=
MOBILE_PASS_GOOGLE_KEY_CONTENTS=
MOBILE_PASS_GOOGLE_KEY_PATH=
MOBILE_PASS_GOOGLE_API_BASE_URL=

MOBILE_PASS_QUEUE_CONNECTION=
MOBILE_PASS_QUEUE_NAME=default
```

- [ ] **Step 4: Commit**

```bash
git add config/mobile-pass.php .env.example
git commit -m "feat: add google and queue config blocks"
```

### Task 1.5: `GoogleJwtSigner`

**Files:**
- Create: `src/Support/Google/GoogleJwtSigner.php`
- Create: `tests/Support/Google/GoogleJwtSignerTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Support/Google/GoogleJwtSignerTest.php`:

```php
<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388000000012345678');
    cache()->clear();
});

it('signs a Save-to-Wallet JWT with the required claims', function () {
    $jwt = app(GoogleJwtSigner::class)->signSaveUrlJwt([
        'eventTicketObjects' => [['id' => '3388000000012345678.abc']],
    ]);

    $decoded = JWT::decode($jwt, new Key(GoogleFixtures::publicKey(), 'RS256'));

    expect($decoded->iss)->toBe('mobile-pass-test@mobile-pass-test.iam.gserviceaccount.com');
    expect($decoded->aud)->toBe('google');
    expect($decoded->typ)->toBe('savetowallet');
    expect($decoded->payload->eventTicketObjects[0]->id)->toBe('3388000000012345678.abc');
});

it('exchanges an assertion JWT for an access token and caches it', function () {
    Http::fake([
        'oauth2.googleapis.com/token' => Http::response([
            'access_token' => 'ya29.fake-token',
            'token_type'   => 'Bearer',
            'expires_in'   => 3600,
        ], 200),
    ]);

    $token = app(GoogleJwtSigner::class)->accessToken();

    expect($token)->toBe('ya29.fake-token');

    // second call does NOT hit the network
    app(GoogleJwtSigner::class)->accessToken();
    Http::assertSentCount(1);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Support/Google/GoogleJwtSignerTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement the signer**

Create `src/Support/Google/GoogleJwtSigner.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GoogleJwtSigner
{
    protected const TOKEN_CACHE_KEY = 'mobile-pass.google.access-token';

    protected const SCOPE = 'https://www.googleapis.com/auth/wallet_object.issuer';

    /** @param array<string, mixed> $payload */
    public function signSaveUrlJwt(array $payload): string
    {
        $now = time();

        $claims = [
            'iss'     => GoogleCredentials::clientEmail(),
            'aud'     => 'google',
            'typ'     => 'savetowallet',
            'iat'     => $now,
            'origins' => config('mobile-pass.google.origins', []),
            'payload' => $payload,
        ];

        return JWT::encode($claims, GoogleCredentials::privateKey(), 'RS256');
    }

    public function accessToken(): string
    {
        $cached = Cache::get(self::TOKEN_CACHE_KEY);

        if ($cached) {
            return $cached;
        }

        $assertion = $this->signAssertionJwt();

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $assertion,
        ])->throw();

        $token = $response->json('access_token');
        $ttl   = max(60, ((int) $response->json('expires_in', 3600)) - 30);

        Cache::put(self::TOKEN_CACHE_KEY, $token, $ttl);

        return $token;
    }

    protected function signAssertionJwt(): string
    {
        $now = time();

        $claims = [
            'iss'   => GoogleCredentials::clientEmail(),
            'scope' => self::SCOPE,
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ];

        return JWT::encode($claims, GoogleCredentials::privateKey(), 'RS256');
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Support/Google/GoogleJwtSignerTest.php`
Expected: 2 passed.

- [ ] **Step 5: Commit**

```bash
git add src/Support/Google/GoogleJwtSigner.php tests/Support/Google/GoogleJwtSignerTest.php
git commit -m "feat: add GoogleJwtSigner"
```

### Task 1.6: `GoogleWalletApiError` exception

**Files:**
- Create: `src/Exceptions/GoogleWalletApiError.php`

- [ ] **Step 1: Write the exception**

```php
<?php

namespace Spatie\LaravelMobilePass\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;

class GoogleWalletApiError extends Exception
{
    public function __construct(
        public readonly int $status,
        public readonly string $body,
        public readonly string $endpoint,
    ) {
        parent::__construct("Google Wallet API returned {$status} for {$endpoint}: {$body}");
    }

    public static function fromResponse(Response $response, string $endpoint): self
    {
        return new self($response->status(), (string) $response->body(), $endpoint);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/Exceptions/GoogleWalletApiError.php
git commit -m "feat: add GoogleWalletApiError exception"
```

### Task 1.7: `GoogleWalletClient`

**Files:**
- Create: `src/Support/Google/GoogleWalletClient.php`
- Create: `tests/Support/Google/GoogleWalletClientTest.php`
- Modify: `src/MobilePassServiceProvider.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Support/Google/GoogleWalletClientTest.php`:

```php
<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('inserts a class with the bearer token', function () {
    Http::fake([
        'example.com/walletobjects/v1/eventTicketClass' => Http::response(['id' => '3388.abc'], 200),
    ]);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', ['foo' => 'bar']);

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token')
        && $request->method() === 'POST'
        && $request->url() === 'https://example.com/walletobjects/v1/eventTicketClass'
        && $request['foo'] === 'bar'
    );
});

it('upgrades a 409 on insert to a patch', function () {
    Http::fakeSequence()
        ->push(['error' => ['code' => 409]], 409)
        ->push(['id' => '3388.abc'], 200);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', ['foo' => 'baz']);

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/eventTicketClass/3388.abc')
        && $request->method() === 'PATCH'
    );
});

it('throws GoogleWalletApiError on unexpected 4xx', function () {
    Http::fake([
        '*/eventTicketClass' => Http::response(['error' => 'nope'], 403),
    ]);

    app(GoogleWalletClient::class)->insertClass('eventTicketClass', '3388.abc', []);
})->throws(GoogleWalletApiError::class);

it('patches an object', function () {
    Http::fake([
        '*/eventTicketObject/3388.abc' => Http::response(['id' => '3388.abc'], 200),
    ]);

    app(GoogleWalletClient::class)->patchObject('eventTicketObject', '3388.abc', ['state' => 'EXPIRED']);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH' && $request['state'] === 'EXPIRED');
});

it('lists classes and returns the resources array', function () {
    Http::fake([
        '*/eventTicketClass?*' => Http::response([
            'resources' => [['id' => '3388.a'], ['id' => '3388.b']],
        ], 200),
    ]);

    $classes = app(GoogleWalletClient::class)->listClasses('eventTicketClass');

    expect($classes)->toHaveCount(2);
    expect($classes[0]['id'])->toBe('3388.a');
});

it('retries on 5xx with backoff', function () {
    Http::fakeSequence()
        ->push('oops', 503)
        ->push('oops', 503)
        ->push(['id' => 'ok'], 200);

    app(GoogleWalletClient::class)->getClass('eventTicketClass', '3388.abc');

    Http::assertSentCount(3);
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `./vendor/bin/pest tests/Support/Google/GoogleWalletClientTest.php`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement the client**

Create `src/Support/Google/GoogleWalletClient.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Support\Google;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError;

class GoogleWalletClient
{
    public function __construct(protected GoogleJwtSigner $signer) {}

    /** @param array<string, mixed> $payload */
    public function insertClass(string $resource, string $id, array $payload): array
    {
        return $this->insertOrPatch($resource, $id, $payload);
    }

    /** @param array<string, mixed> $payload */
    public function insertObject(string $resource, string $id, array $payload): array
    {
        return $this->insertOrPatch($resource, $id, $payload);
    }

    /** @param array<string, mixed> $payload */
    public function patchClass(string $resource, string $id, array $payload): array
    {
        return $this->patch($resource, $id, $payload);
    }

    /** @param array<string, mixed> $payload */
    public function patchObject(string $resource, string $id, array $payload): array
    {
        return $this->patch($resource, $id, $payload);
    }

    public function getClass(string $resource, string $id): array
    {
        return $this->get("/{$resource}/{$id}");
    }

    public function getObject(string $resource, string $id): array
    {
        return $this->get("/{$resource}/{$id}");
    }

    /** @return array<int, array<string, mixed>> */
    public function listClasses(string $resource): array
    {
        $issuerId = GoogleCredentials::issuerId();

        $response = $this->get("/{$resource}?issuerId={$issuerId}");

        return $response['resources'] ?? [];
    }

    protected function insertOrPatch(string $resource, string $id, array $payload): array
    {
        $endpoint = "/{$resource}";
        $response = $this->request()->post($this->url($endpoint), $payload + ['id' => $id]);

        if ($response->status() === 409) {
            return $this->patch($resource, $id, $payload);
        }

        return $this->parse($response, $endpoint);
    }

    protected function patch(string $resource, string $id, array $payload): array
    {
        $endpoint = "/{$resource}/{$id}";

        return $this->parse(
            $this->request()->patch($this->url($endpoint), $payload),
            $endpoint
        );
    }

    protected function get(string $endpoint): array
    {
        return $this->parse($this->request()->get($this->url($endpoint)), $endpoint);
    }

    protected function request(): PendingRequest
    {
        return Http::withToken($this->signer->accessToken())
            ->acceptJson()
            ->retry(3, 200, fn ($exception, $request) => true, throw: false);
    }

    protected function url(string $endpoint): string
    {
        return rtrim((string) config('mobile-pass.google.api_base_url'), '/').$endpoint;
    }

    protected function parse(Response $response, string $endpoint): array
    {
        if ($response->failed()) {
            throw GoogleWalletApiError::fromResponse($response, $endpoint);
        }

        return $response->json() ?? [];
    }
}
```

- [ ] **Step 4: Bind in the service provider**

Modify `src/MobilePassServiceProvider.php`:

```php
use Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;
// ...
public function registeringPackage(): void
{
    $this->app->singleton(GoogleJwtSigner::class);
    $this->app->singleton(GoogleWalletClient::class);
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `./vendor/bin/pest tests/Support/Google/GoogleWalletClientTest.php`
Expected: 6 passed.

- [ ] **Step 6: Commit**

```bash
git add src/Support/Google/GoogleWalletClient.php src/MobilePassServiceProvider.php tests/Support/Google/GoogleWalletClientTest.php
git commit -m "feat: add GoogleWalletClient"
```

### Task 1.8: `PushPassUpdateJob`

**Files:**
- Create: `src/Jobs/PushPassUpdateJob.php`
- Create: `tests/Jobs/PushPassUpdateJobTest.php`
- Create: `src/Actions/Google/NotifyGoogleOfPassUpdateAction.php`

- [ ] **Step 1: Scaffold the Google notify action**

Create `src/Actions/Google/NotifyGoogleOfPassUpdateAction.php` with a stub that will be fully implemented in Milestone 3:

```php
<?php

namespace Spatie\LaravelMobilePass\Actions\Google;

use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;

class NotifyGoogleOfPassUpdateAction
{
    public function __construct(protected GoogleWalletClient $client) {}

    public function execute(MobilePass $mobilePass): void
    {
        $googleClassType = $mobilePass->content['googleClassType'] ?? null;
        $objectId        = $mobilePass->content['googleObjectId'] ?? null;

        if (! $googleClassType || ! $objectId) {
            return;
        }

        $resource = str_replace('Class', 'Object', $googleClassType);

        $this->client->patchObject($resource, $objectId, $mobilePass->content['googleObjectPayload'] ?? []);
    }
}
```

- [ ] **Step 2: Write failing test**

Create `tests/Jobs/PushPassUpdateJobTest.php`:

```php
<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('runs sync when no queue connection is configured', function () {
    config()->set('mobile-pass.queue.connection', null);
    Bus::fake();

    $pass = MobilePass::factory()->create(['platform' => Platform::Apple]);

    PushPassUpdateJob::dispatch($pass, NotifyAppleOfPassUpdateAction::class);

    Bus::assertDispatchedSync(PushPassUpdateJob::class);
});

it('queues when a queue connection is configured', function () {
    config()->set('mobile-pass.queue.connection', 'redis');
    config()->set('mobile-pass.queue.name', 'mobile-pass');
    Queue::fake();

    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);

    PushPassUpdateJob::dispatch($pass, NotifyGoogleOfPassUpdateAction::class);

    Queue::assertPushedOn('mobile-pass', PushPassUpdateJob::class);
});
```

- [ ] **Step 3: Run test to verify it fails**

Run: `./vendor/bin/pest tests/Jobs/PushPassUpdateJobTest.php`
Expected: FAIL.

- [ ] **Step 4: Implement the job**

Create `src/Jobs/PushPassUpdateJob.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\LaravelMobilePass\Models\MobilePass;

class PushPassUpdateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @param class-string $actionClass */
    public function __construct(
        public MobilePass $mobilePass,
        public string $actionClass,
    ) {
        $this->onConnection(config('mobile-pass.queue.connection'))
            ->onQueue(config('mobile-pass.queue.name', 'default'));
    }

    public function handle(): void
    {
        app($this->actionClass)->execute($this->mobilePass);
    }
}
```

Laravel runs `ShouldQueue` jobs sync when the connection is `null` via `sync` driver default. To keep behavior explicit, update `MobilePass::boot()` in Milestone 4 to call `dispatchSync()` when the connection is not set. For this task we rely on Laravel's own behavior: `onConnection(null)` falls back to the default connection. Adjust the test accordingly, OR explicitly branch in the job's constructor:

Replace the constructor:

```php
public function __construct(
    public MobilePass $mobilePass,
    public string $actionClass,
) {
    $connection = config('mobile-pass.queue.connection');

    if ($connection === null) {
        $this->onConnection('sync');
    } else {
        $this->onConnection($connection)->onQueue(config('mobile-pass.queue.name', 'default'));
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `./vendor/bin/pest tests/Jobs/PushPassUpdateJobTest.php`
Expected: 2 passed.

- [ ] **Step 6: Commit**

```bash
git add src/Jobs/PushPassUpdateJob.php src/Actions/Google/NotifyGoogleOfPassUpdateAction.php tests/Jobs/PushPassUpdateJobTest.php
git commit -m "feat: add PushPassUpdateJob wrapping both platforms"
```

### Task 1.9: Milestone 1 quality gate

- [ ] **Step 1: Run simplifier on M1 files**

Dispatch `laravel-simplifier` agent with:

```
Review these files for reuse, dead code, over-abstraction, misplaced responsibilities:
- src/Support/Google/GoogleCredentials.php
- src/Support/Google/GoogleJwtSigner.php
- src/Support/Google/GoogleWalletClient.php
- src/Actions/Google/NotifyGoogleOfPassUpdateAction.php
- src/Jobs/PushPassUpdateJob.php
- src/Exceptions/GoogleWalletApiError.php
- src/Exceptions/InvalidConfig.php (diff only)
- src/MobilePassServiceProvider.php (diff only)

Apply your fixes directly. Do not change behavior. Do not rename public methods.
```

- [ ] **Step 2: Apply Spatie guidelines pass**

Re-invoke `php-guidelines-from-spatie` skill mentally, applying checklist to M1 files: happy-path-last ordering, no-else, short nullable, single-line docblocks, no FQ namespaces inline, no single-letter vars.

- [ ] **Step 3: Baseline**

Run: `./vendor/bin/pest && ./vendor/bin/phpstan analyse --memory-limit=1G && ./vendor/bin/pint --test`
Expected: all green.

- [ ] **Step 4: Commit any cleanup**

```bash
git add -A
git commit -m "chore: simplify M1 shared Google infrastructure" || echo "nothing to commit"
```

---

## Milestone 2 — Google class builders

### Task 2.1: `LocalizedString` entity

**Files:**
- Create: `src/Builders/Google/Entities/LocalizedString.php`
- Create: `tests/Builders/Google/Entities/LocalizedStringTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;

it('builds a default-value localized string', function () {
    $ls = LocalizedString::of('The Eras Tour');

    expect($ls->toArray())->toBe([
        'defaultValue' => ['language' => 'en-US', 'value' => 'The Eras Tour'],
    ]);
});

it('adds translated values', function () {
    $ls = LocalizedString::of('Hello')
        ->addTranslation('nl-BE', 'Hallo')
        ->addTranslation('fr-FR', 'Bonjour');

    expect($ls->toArray()['translatedValues'])->toHaveCount(2);
});

it('can use a custom default language', function () {
    $ls = LocalizedString::of('Bonjour', 'fr-FR');

    expect($ls->toArray()['defaultValue']['language'])->toBe('fr-FR');
});
```

- [ ] **Step 2: Run to verify fail**

Run: `./vendor/bin/pest tests/Builders/Google/Entities/LocalizedStringTest.php`
Expected: FAIL.

- [ ] **Step 3: Implement**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;

class LocalizedString implements Arrayable
{
    /** @var array<int, array{language: string, value: string}> */
    protected array $translations = [];

    public function __construct(
        public string $defaultValue,
        public string $defaultLanguage = 'en-US',
    ) {}

    public static function of(string $defaultValue, string $defaultLanguage = 'en-US'): self
    {
        return new self($defaultValue, $defaultLanguage);
    }

    public function addTranslation(string $language, string $value): self
    {
        $this->translations[] = ['language' => $language, 'value' => $value];

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $payload = [
            'defaultValue' => [
                'language' => $this->defaultLanguage,
                'value'    => $this->defaultValue,
            ],
        ];

        if ($this->translations !== []) {
            $payload['translatedValues'] = $this->translations;
        }

        return $payload;
    }
}
```

- [ ] **Step 4: Run to verify pass**

Expected: 3 passed.

- [ ] **Step 5: Commit**

```bash
git add src/Builders/Google/Entities/LocalizedString.php tests/Builders/Google/Entities/LocalizedStringTest.php
git commit -m "feat: add LocalizedString entity for Google passes"
```

### Task 2.2: Google `Image` entity (hybrid)

**Files:**
- Create: `src/Builders/Google/Entities/Image.php`
- Create: `tests/Builders/Google/Entities/ImageTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;

it('wraps an https URL verbatim', function () {
    $image = Image::fromUrl('https://cdn.example.com/logo.png');

    expect($image->publicUrl())->toBe('https://cdn.example.com/logo.png');
    expect($image->toArray())->toBe([
        'sourceUri' => ['uri' => 'https://cdn.example.com/logo.png'],
    ]);
});

it('refuses local paths for class-level images for now', function () {
    Image::fromLocalPath('/does/not/exist/logo.png');
})->throws(InvalidArgumentException::class);
```

Note: `fromLocalPath` requires the host-signed-route plumbing (added in Milestone 4 when we also do Apple downloads). For now it throws if called outside an object-level context; we'll soften this in M4 once the serve route exists. The Image entity itself carries the local path for later resolution.

Revise the test:

```php
it('captures a local path for later hosting', function () {
    $image = Image::fromLocalPath(__DIR__.'/../../../TestSupport/images/spatie-thumbnail.png');

    expect($image->localPath)->not()->toBeNull();
    expect($image->url)->toBeNull();
});
```

- [ ] **Step 2: Run to verify fail**

Run: `./vendor/bin/pest tests/Builders/Google/Entities/ImageTest.php`
Expected: FAIL.

- [ ] **Step 3: Implement**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Entities;

use Illuminate\Contracts\Support\Arrayable;
use RuntimeException;

class Image implements Arrayable
{
    protected function __construct(
        public readonly ?string $url = null,
        public readonly ?string $localPath = null,
    ) {}

    public static function fromUrl(string $url): self
    {
        return new self(url: $url);
    }

    public static function fromLocalPath(string $path): self
    {
        return new self(localPath: $path);
    }

    public function publicUrl(): string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        throw new RuntimeException(
            'Image::publicUrl() on a local-path image requires the hosted image route. '
            .'Local-path images are only available on object-level builders; '
            .'use Image::fromUrl() for class-level images.'
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['sourceUri' => ['uri' => $this->publicUrl()]];
    }
}
```

- [ ] **Step 4: Run to verify pass**

Expected: 2 passed.

- [ ] **Step 5: Commit**

```bash
git add src/Builders/Google/Entities/Image.php tests/Builders/Google/Entities/ImageTest.php
git commit -m "feat: add Google Image entity (URL mode)"
```

### Task 2.3: `GooglePassClass` abstract base

**Files:**
- Create: `src/Builders/Google/GooglePassClass.php`
- Create: `src/Builders/Google/Validators/GooglePassClassValidator.php`

(No test file yet; M2.4 onwards covers behavior through concrete subclasses.)

- [ ] **Step 1: Implement abstract validator**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

abstract class GooglePassClassValidator
{
    /** @return array<string, array<int, string>> */
    abstract protected function rules(): array;

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function validate(array $payload): array
    {
        return validator($payload, $this->rules())->validate();
    }
}
```

- [ ] **Step 2: Implement abstract class**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;
use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;

/**
 * @phpstan-consistent-constructor
 */
abstract class GooglePassClass
{
    protected string $suffix;

    protected string $reviewStatus = 'UNDER_REVIEW';

    abstract protected static function resourceName(): string;

    abstract protected static function validator(): GooglePassClassValidator;

    abstract protected function compileData(): array;

    public static function make(string $suffix): static
    {
        return new static($suffix);
    }

    public function __construct(string $suffix)
    {
        $this->suffix = $suffix;
    }

    public function id(): string
    {
        return GoogleCredentials::issuerId().'.'.$this->suffix;
    }

    public function save(): static
    {
        $payload = static::validator()->validate($this->compileData() + ['id' => $this->id()]);

        app(GoogleWalletClient::class)->insertClass(static::resourceName(), $this->id(), $payload);

        return $this;
    }

    public function retire(): static
    {
        app(GoogleWalletClient::class)->patchClass(static::resourceName(), $this->id(), [
            'reviewStatus' => 'REJECTED',
        ]);

        return $this;
    }

    /** @return Collection<int, static> */
    public static function all(): Collection
    {
        $raw = app(GoogleWalletClient::class)->listClasses(static::resourceName());

        return collect($raw)->map(fn (array $payload) => static::hydrate($payload));
    }

    public static function find(string $suffix): ?static
    {
        $id = GoogleCredentials::issuerId().'.'.$suffix;

        try {
            $payload = app(GoogleWalletClient::class)->getClass(static::resourceName(), $id);
        } catch (\Spatie\LaravelMobilePass\Exceptions\GoogleWalletApiError $e) {
            if ($e->status === 404) {
                return null;
            }
            throw $e;
        }

        return static::hydrate($payload);
    }

    protected static function hydrate(array $payload): static
    {
        $id     = $payload['id'] ?? '';
        $suffix = Str::after($id, '.');

        $class = new static($suffix);
        $class->applyHydratedPayload($payload);

        return $class;
    }

    /** @param array<string, mixed> $payload */
    abstract protected function applyHydratedPayload(array $payload): void;
}
```

- [ ] **Step 3: Commit**

```bash
git add src/Builders/Google/GooglePassClass.php src/Builders/Google/Validators/GooglePassClassValidator.php
git commit -m "feat: add GooglePassClass + validator base"
```

### Task 2.4: `EventTicketPassClass` (reference implementation)

**Files:**
- Create: `src/Builders/Google/EventTicketPassClass.php`
- Create: `src/Builders/Google/Validators/EventTicketClassValidator.php`
- Create: `tests/Builders/Google/EventTicketPassClassTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassClass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('computes the full id from issuer + suffix', function () {
    expect(EventTicketPassClass::make('ts-2026')->id())->toBe('3388.ts-2026');
});

it('saves the expected payload to Google', function () {
    Http::fake(['*/eventTicketClass' => Http::response([], 200)]);

    EventTicketPassClass::make('ts-2026')
        ->setEventName('The Eras Tour')
        ->setVenueName('King Baudouin Stadium')
        ->setLogoUrl('https://cdn.example.com/logo.png')
        ->save();

    Http::assertSent(function ($request) {
        expect($request['id'])->toBe('3388.ts-2026');
        expect($request['eventName']['defaultValue']['value'])->toBe('The Eras Tour');
        expect($request['venue']['name']['defaultValue']['value'])->toBe('King Baudouin Stadium');
        expect($request['logo']['sourceUri']['uri'])->toBe('https://cdn.example.com/logo.png');

        return true;
    });
});

it('retire() patches reviewStatus to REJECTED', function () {
    Http::fake(['*/eventTicketClass/3388.ts-2026' => Http::response([], 200)]);

    EventTicketPassClass::make('ts-2026')->retire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['reviewStatus'] === 'REJECTED'
    );
});

it('find() returns null on 404', function () {
    Http::fake(['*/eventTicketClass/3388.missing' => Http::response([], 404)]);

    expect(EventTicketPassClass::find('missing'))->toBeNull();
});

it('all() returns a collection hydrated from resources', function () {
    Http::fake(['*/eventTicketClass*' => Http::response([
        'resources' => [
            ['id' => '3388.a', 'eventName' => ['defaultValue' => ['language' => 'en-US', 'value' => 'A']]],
            ['id' => '3388.b', 'eventName' => ['defaultValue' => ['language' => 'en-US', 'value' => 'B']]],
        ],
    ], 200)]);

    $classes = EventTicketPassClass::all();

    expect($classes)->toHaveCount(2);
    expect($classes[0]->getEventName())->toBe('A');
});
```

- [ ] **Step 2: Run to verify fail**

Run: `./vendor/bin/pest tests/Builders/Google/EventTicketPassClassTest.php`
Expected: FAIL.

- [ ] **Step 3: Implement validator**

Create `src/Builders/Google/Validators/EventTicketClassValidator.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class EventTicketClassValidator extends GooglePassClassValidator
{
    protected function rules(): array
    {
        return [
            'id'                   => ['required', 'string'],
            'issuerName'           => ['nullable', 'string'],
            'eventName'            => ['required', 'array'],
            'eventName.defaultValue.value' => ['required', 'string'],
            'venue'                => ['nullable', 'array'],
            'venue.name'           => ['nullable', 'array'],
            'venue.address'        => ['nullable', 'array'],
            'dateTime'             => ['nullable', 'array'],
            'logo'                 => ['nullable', 'array'],
            'heroImage'            => ['nullable', 'array'],
            'hexBackgroundColor'   => ['nullable', 'string'],
            'reviewStatus'         => ['nullable', 'string'],
        ];
    }
}
```

- [ ] **Step 4: Implement class builder**

Create `src/Builders/Google/EventTicketPassClass.php`:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Carbon\Carbon;
use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\Entities\LocalizedString;
use Spatie\LaravelMobilePass\Builders\Google\Validators\EventTicketClassValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassClassValidator;

class EventTicketPassClass extends GooglePassClass
{
    protected ?string $issuerName = null;

    protected ?LocalizedString $eventName = null;

    protected ?LocalizedString $venueName = null;

    protected ?LocalizedString $venueAddress = null;

    protected ?Carbon $startDate = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected ?string $backgroundColor = null;

    protected static function resourceName(): string
    {
        return 'eventTicketClass';
    }

    protected static function validator(): GooglePassClassValidator
    {
        return new EventTicketClassValidator;
    }

    public function setIssuerName(string $issuerName): self
    {
        $this->issuerName = $issuerName;

        return $this;
    }

    public function setEventName(string $value, string $language = 'en-US'): self
    {
        $this->eventName = LocalizedString::of($value, $language);

        return $this;
    }

    public function getEventName(): ?string
    {
        return $this->eventName?->defaultValue;
    }

    public function setVenueName(string $value, string $language = 'en-US'): self
    {
        $this->venueName = LocalizedString::of($value, $language);

        return $this;
    }

    public function setVenueAddress(string $value, string $language = 'en-US'): self
    {
        $this->venueAddress = LocalizedString::of($value, $language);

        return $this;
    }

    public function setStartDate(Carbon $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function setLogoUrl(string $url): self
    {
        $this->logo = Image::fromUrl($url);

        return $this;
    }

    public function setHeroImageUrl(string $url): self
    {
        $this->hero = Image::fromUrl($url);

        return $this;
    }

    public function setBackgroundColor(string $hex): self
    {
        $this->backgroundColor = $hex;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return array_filter([
            'issuerName'         => $this->issuerName,
            'eventName'          => $this->eventName?->toArray(),
            'venue'              => array_filter([
                'name'    => $this->venueName?->toArray(),
                'address' => $this->venueAddress?->toArray(),
            ]) ?: null,
            'dateTime'           => $this->startDate ? ['start' => $this->startDate->toIso8601String()] : null,
            'logo'               => $this->logo?->toArray(),
            'heroImage'          => $this->hero?->toArray(),
            'hexBackgroundColor' => $this->backgroundColor,
            'reviewStatus'       => $this->reviewStatus,
        ], fn ($value) => $value !== null && $value !== []);
    }

    /** @param array<string, mixed> $payload */
    protected function applyHydratedPayload(array $payload): void
    {
        if (isset($payload['eventName']['defaultValue']['value'])) {
            $this->eventName = LocalizedString::of(
                $payload['eventName']['defaultValue']['value'],
                $payload['eventName']['defaultValue']['language'] ?? 'en-US'
            );
        }

        if (isset($payload['venue']['name']['defaultValue']['value'])) {
            $this->venueName = LocalizedString::of($payload['venue']['name']['defaultValue']['value']);
        }

        if (isset($payload['venue']['address']['defaultValue']['value'])) {
            $this->venueAddress = LocalizedString::of($payload['venue']['address']['defaultValue']['value']);
        }

        if (isset($payload['dateTime']['start'])) {
            $this->startDate = Carbon::parse($payload['dateTime']['start']);
        }

        if (isset($payload['logo']['sourceUri']['uri'])) {
            $this->logo = Image::fromUrl($payload['logo']['sourceUri']['uri']);
        }

        if (isset($payload['heroImage']['sourceUri']['uri'])) {
            $this->hero = Image::fromUrl($payload['heroImage']['sourceUri']['uri']);
        }

        if (isset($payload['hexBackgroundColor'])) {
            $this->backgroundColor = $payload['hexBackgroundColor'];
        }

        if (isset($payload['reviewStatus'])) {
            $this->reviewStatus = $payload['reviewStatus'];
        }
    }
}
```

- [ ] **Step 5: Run to verify pass**

Expected: 5 passed.

- [ ] **Step 6: Commit**

```bash
git add src/Builders/Google/EventTicketPassClass.php src/Builders/Google/Validators/EventTicketClassValidator.php tests/Builders/Google/EventTicketPassClassTest.php
git commit -m "feat: add EventTicketPassClass"
```

### Task 2.5: Remaining class builders (Boarding / Loyalty / Offer / Generic)

**Reference implementation:** Task 2.4 (`EventTicketPassClass`). Subagents executing this task must read Task 2.4 in full and use it as the template. The only differences per class below are the `resourceName()`, validator name, and the setter/`compileData()` field list.

For each of `BoardingPassClass`, `LoyaltyPassClass`, `OfferPassClass`, `GenericPassClass`, repeat the pattern from Task 2.4. Field set per Google's reference:

* **BoardingPassClass** (`flightClass`): `issuerName`, `localScheduledDepartureDateTime`, `flightHeader.carrier.airlineCode`, `flightHeader.flightNumber`, `origin.airportIataCode`, `destination.airportIataCode`, logo, hero, background.
* **LoyaltyPassClass** (`loyaltyClass`): `issuerName`, `programName`, `programLogo`, `rewardsTier`, `rewardsTierLabel`, `accountNameLabel`, `accountIdLabel`, `hexBackgroundColor`.
* **OfferPassClass** (`offerClass`): `issuerName`, `title`, `redemptionChannel`, `provider`, `details`, `finePrint`, `logo`, `hexBackgroundColor`.
* **GenericPassClass** (`genericClass`): `issuerName`, `cardTitle`, `subheader`, `header`, `hexBackgroundColor`, `logo`, `heroImage`.

For each class:

- [ ] Write a test file that mirrors `EventTicketPassClassTest`: id computation, save payload assertion, retire, find (404), all.
- [ ] Implement the validator class (one file) using the same pattern as `EventTicketClassValidator`.
- [ ] Implement the class builder with setters for each supported field, `compileData()` assembling the payload, `applyHydratedPayload()` for `find()`/`all()` round-trips.
- [ ] Commit per class (four commits total):

```bash
git commit -m "feat: add BoardingPassClass"
git commit -m "feat: add LoyaltyPassClass"
git commit -m "feat: add OfferPassClass"
git commit -m "feat: add GenericPassClass"
```

### Task 2.6: Milestone 2 quality gate

- [ ] **Step 1: Simplifier pass** on `src/Builders/Google/*PassClass.php` and `src/Builders/Google/Validators/*.php`.
- [ ] **Step 2: Spatie guidelines pass** on same files.
- [ ] **Step 3: Baseline green** (`pest`, `phpstan`, `pint --test`).
- [ ] **Step 4: Cleanup commit** if any:

```bash
git commit -am "chore: simplify M2 class builders" || echo "nothing to commit"
```

---

## Milestone 3 — Google pass (object) builders

### Task 3.1: `GooglePassBuilder` abstract

**Files:**
- Modify: `src/Builders/Google/GooglePassBuilder.php` (was a 3-line stub)
- Create: `src/Builders/Google/Validators/GooglePassObjectValidator.php`
- Create: `src/Actions/Google/CreateGoogleObjectAction.php`

- [ ] **Step 1: Implement object validator base**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

abstract class GooglePassObjectValidator
{
    abstract protected function rules(): array;

    public function validate(array $payload): array
    {
        return validator($payload, $this->rules())->validate();
    }
}
```

- [ ] **Step 2: Implement `CreateGoogleObjectAction`**

```php
<?php

namespace Spatie\LaravelMobilePass\Actions\Google;

use Spatie\LaravelMobilePass\Support\Google\GoogleWalletClient;

class CreateGoogleObjectAction
{
    public function __construct(protected GoogleWalletClient $client) {}

    /** @param array<string, mixed> $payload */
    public function execute(string $resource, string $id, array $payload): array
    {
        return $this->client->insertObject($resource, $id, $payload);
    }
}
```

- [ ] **Step 3: Replace `GooglePassBuilder` stub with the real abstract**

Open `src/Builders/Google/GooglePassBuilder.php` and replace contents with:

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Illuminate\Support\Str;
use Spatie\LaravelMobilePass\Actions\Google\CreateGoogleObjectAction;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Enums\PassType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;
use Spatie\LaravelMobilePass\Support\Google\GoogleCredentials;

/** @phpstan-consistent-constructor */
abstract class GooglePassBuilder
{
    protected ?string $classSuffix = null;

    protected ?string $objectSuffix = null;

    protected ?Barcode $barcode = null;

    protected ?string $state = 'ACTIVE';

    protected PassType $type;

    abstract protected static function validator(): GooglePassObjectValidator;

    abstract protected static function classResource(): string;

    abstract protected static function objectResource(): string;

    abstract protected function compileData(): array;

    public static function make(): static
    {
        return new static;
    }

    public static function name(): string
    {
        return Str::snake(Str::replaceLast('PassBuilder', '', class_basename(static::class)));
    }

    public function platform(): Platform
    {
        return Platform::Google;
    }

    public function setClass(string $suffix): static
    {
        $this->classSuffix = $suffix;

        return $this;
    }

    public function setObjectSuffix(string $suffix): static
    {
        $this->objectSuffix = $suffix;

        return $this;
    }

    public function setBarcode(Barcode $barcode): static
    {
        $this->barcode = $barcode;

        return $this;
    }

    public function objectId(): string
    {
        $suffix = $this->objectSuffix ?? (string) Str::uuid();

        return GoogleCredentials::issuerId().'.'.$suffix;
    }

    public function classId(): string
    {
        if ($this->classSuffix === null) {
            throw new \RuntimeException('Call setClass() before saving a Google pass.');
        }

        return GoogleCredentials::issuerId().'.'.$this->classSuffix;
    }

    public function save(): MobilePass
    {
        $payload = $this->compileGoogleObjectPayload();

        static::validator()->validate($payload);

        app(CreateGoogleObjectAction::class)->execute(
            static::objectResource(),
            $this->objectId(),
            $payload,
        );

        $mobilePassClass = Config::mobilePassModel();

        return $mobilePassClass::query()->create([
            'type'         => $this->type->value,
            'platform'     => Platform::Google,
            'builder_name' => static::name(),
            'content'      => [
                'googleClassType'     => static::classResource(),
                'googleObjectId'      => $this->objectId(),
                'googleClassId'       => $this->classId(),
                'googleObjectPayload' => $payload,
            ],
            'images'        => [],
        ]);
    }

    /** @return array<string, mixed> */
    protected function compileGoogleObjectPayload(): array
    {
        return array_filter(array_merge([
            'id'       => $this->objectId(),
            'classId'  => $this->classId(),
            'state'    => $this->state,
            'barcode'  => $this->barcode?->toArray(),
        ], $this->compileData()), fn ($v) => $v !== null && $v !== []);
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add src/Builders/Google/GooglePassBuilder.php src/Builders/Google/Validators/GooglePassObjectValidator.php src/Actions/Google/CreateGoogleObjectAction.php
git commit -m "feat: implement GooglePassBuilder abstract"
```

### Task 3.2: `EventTicketPassBuilder` (reference object builder)

**Files:**
- Create: `src/Builders/Google/EventTicketPassBuilder.php`
- Create: `src/Builders/Google/Validators/EventTicketObjectValidator.php`
- Create: `tests/Builders/Google/EventTicketPassBuilderTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Builders\Apple\Entities\Barcode;
use Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder;
use Spatie\LaravelMobilePass\Enums\BarcodeType;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('creates a MobilePass row and POSTs the object to Google', function () {
    Http::fake(['*/eventTicketObject' => Http::response([], 200)]);

    $pass = EventTicketPassBuilder::make()
        ->setClass('ts-2026')
        ->setObjectSuffix('john')
        ->setAttendeeName('John Smith')
        ->setSection('B12')
        ->setSeat('Row 8, Seat 22')
        ->setBarcode(Barcode::make(BarcodeType::QR, 'TS-JS'))
        ->save();

    expect($pass->platform)->toBe(Platform::Google);
    expect($pass->content['googleObjectId'])->toBe('3388.john');
    expect($pass->content['googleClassId'])->toBe('3388.ts-2026');

    Http::assertSent(function ($request) {
        expect($request['classId'])->toBe('3388.ts-2026');
        expect($request['id'])->toBe('3388.john');
        expect($request['ticketHolderName'])->toBe('John Smith');

        return true;
    });
});

it('throws when setClass() is not called', function () {
    EventTicketPassBuilder::make()->setAttendeeName('John')->save();
})->throws(RuntimeException::class);
```

- [ ] **Step 2: Run to verify fail.**

Run: `./vendor/bin/pest tests/Builders/Google/EventTicketPassBuilderTest.php`
Expected: FAIL.

- [ ] **Step 3: Implement validator**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google\Validators;

class EventTicketObjectValidator extends GooglePassObjectValidator
{
    protected function rules(): array
    {
        return [
            'id'                => ['required', 'string'],
            'classId'           => ['required', 'string'],
            'state'             => ['nullable', 'string'],
            'ticketHolderName'  => ['nullable', 'string'],
            'seatInfo'          => ['nullable', 'array'],
            'barcode'           => ['nullable', 'array'],
        ];
    }
}
```

- [ ] **Step 4: Implement builder**

```php
<?php

namespace Spatie\LaravelMobilePass\Builders\Google;

use Spatie\LaravelMobilePass\Builders\Google\Validators\EventTicketObjectValidator;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;
use Spatie\LaravelMobilePass\Enums\PassType;

class EventTicketPassBuilder extends GooglePassBuilder
{
    protected PassType $type = PassType::EventTicket;

    protected ?string $attendeeName = null;

    protected ?string $section = null;

    protected ?string $row = null;

    protected ?string $seat = null;

    protected static function validator(): GooglePassObjectValidator
    {
        return new EventTicketObjectValidator;
    }

    protected static function classResource(): string
    {
        return 'eventTicketClass';
    }

    protected static function objectResource(): string
    {
        return 'eventTicketObject';
    }

    public function setAttendeeName(string $name): self
    {
        $this->attendeeName = $name;

        return $this;
    }

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function setSeat(string $seat): self
    {
        $this->seat = $seat;

        return $this;
    }

    public function setRow(string $row): self
    {
        $this->row = $row;

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return array_filter([
            'ticketHolderName' => $this->attendeeName,
            'seatInfo' => array_filter([
                'section' => $this->section,
                'row'     => $this->row,
                'seat'    => $this->seat,
            ]) ?: null,
        ], fn ($v) => $v !== null && $v !== []);
    }
}
```

- [ ] **Step 5: Ensure `PassType::EventTicket` exists**

Check `src/Enums/PassType.php`. It should already include `case EventTicket = 'eventTicket';` from today. If missing, add it (no migration impact).

- [ ] **Step 6: Run tests to verify pass**

Expected: 2 passed.

- [ ] **Step 7: Commit**

```bash
git add src/Builders/Google/EventTicketPassBuilder.php src/Builders/Google/Validators/EventTicketObjectValidator.php tests/Builders/Google/EventTicketPassBuilderTest.php
git commit -m "feat: add Google EventTicketPassBuilder"
```

### Task 3.3: Remaining object builders

**Reference implementation:** Task 3.2 (`EventTicketPassBuilder`). Subagents executing this task must read Task 3.2 in full and use it as the template. Same validator/builder pattern, same test shape; only the per-pass-type setters and resource names change.

For each of `BoardingPassBuilder`, `LoyaltyPassBuilder`, `OfferPassBuilder`, `GenericPassBuilder`:

- [ ] Implement validator file
- [ ] Implement builder with setters for per-pass-type fields:
    * **BoardingPass** (`flightObject`): `passengerName`, `boardingAndSeatingInfo.seatNumber`, `reservationInfo.confirmationCode`.
    * **Loyalty** (`loyaltyObject`): `accountId`, `accountName`, `balance.balanceMicros`, `balance.balanceString`.
    * **Offer** (`offerObject`): `genericObjectField`: merchant-specific; basic setters for title override, barcode.
    * **Generic** (`genericObject`): `header`, `cardTitle`, `subheader`, `notifications.expiryNotification`.
- [ ] Write a test file mirroring `EventTicketPassBuilderTest`: assert DB row, assert POSTed payload.
- [ ] Commit per builder.

### Task 3.4: Register Google builders in config

**Files:**
- Modify: `config/mobile-pass.php`

- [ ] **Step 1: Add the google builders block**

Update the `builders` key:

```php
'builders' => [
    'apple' => [ /* unchanged */ ],
    'google' => [
        'boarding'     => \Spatie\LaravelMobilePass\Builders\Google\BoardingPassBuilder::class,
        'event_ticket' => \Spatie\LaravelMobilePass\Builders\Google\EventTicketPassBuilder::class,
        'generic'      => \Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder::class,
        'loyalty'      => \Spatie\LaravelMobilePass\Builders\Google\LoyaltyPassBuilder::class,
        'offer'        => \Spatie\LaravelMobilePass\Builders\Google\OfferPassBuilder::class,
    ],
],
```

- [ ] **Step 2: Commit**

```bash
git commit -am "feat: register Google builders in config"
```

### Task 3.5: Complete `NotifyGoogleOfPassUpdateAction`

**Files:**
- Modify: `src/Actions/Google/NotifyGoogleOfPassUpdateAction.php` (stub from M1)
- Create: `tests/Actions/Google/NotifyGoogleOfPassUpdateActionTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.service_account_key_path', GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);
});

it('patches the Google object with the current payload', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content'  => [
            'googleClassType'     => 'eventTicketClass',
            'googleObjectId'      => '3388.john',
            'googleObjectPayload' => ['ticketHolderName' => 'Jane Smith'],
        ],
    ]);

    app(NotifyGoogleOfPassUpdateAction::class)->execute($pass);

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && str_ends_with($request->url(), '/eventTicketObject/3388.john')
        && $request['ticketHolderName'] === 'Jane Smith'
    );
});

it('is a no-op when googleObjectId is missing', function () {
    Http::fake(['*' => Http::response([], 200)]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content'  => [],
    ]);

    app(NotifyGoogleOfPassUpdateAction::class)->execute($pass);

    Http::assertNothingSent();
});
```

- [ ] **Step 2: Run to verify pass** (the M1 stub should already handle these)

Expected: 2 passed. If not, tweak `NotifyGoogleOfPassUpdateAction` to match the test expectations.

- [ ] **Step 3: Commit**

```bash
git add tests/Actions/Google/NotifyGoogleOfPassUpdateActionTest.php
git commit -m "test: cover NotifyGoogleOfPassUpdateAction"
```

### Task 3.6: Milestone 3 quality gate

- [ ] **Step 1: Simplifier pass** on `src/Builders/Google/*PassBuilder.php`, `src/Builders/Google/Validators/*Object*.php`, `src/Actions/Google/*.php`.
- [ ] **Step 2: Spatie guidelines pass**.
- [ ] **Step 3: Baseline green**.
- [ ] **Step 4: Cleanup commit** if any.

---

## Milestone 4 — Cross-platform unification on `MobilePass`

### Task 4.1: `$pass->expire()` on Apple

**Files:**
- Modify: `src/Models/MobilePass.php`
- Create: `tests/Models/MobilePassPlatformTest.php`

- [ ] **Step 1: Write failing test (Apple path)**

Create `tests/Models/MobilePassPlatformTest.php`:

```php
<?php

use Illuminate\Support\Facades\Http;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('Apple expire sets voided and expirationDate, triggering APNs push', function () {
    Http::fake();

    $pass = MobilePass::factory()->hasRegistrations(1)->create([
        'platform' => Platform::Apple,
    ]);

    $pass->expire();

    expect($pass->fresh()->content['voided'])->toBeTrue();
    expect($pass->fresh()->content['expirationDate'])->not()->toBeNull();
    Http::assertSent(fn ($request) => $request->method() === 'POST');
});
```

- [ ] **Step 2: Run to verify fail**

Expected: FAIL (`expire()` does not exist).

- [ ] **Step 3: Implement `expire()`**

Add to `src/Models/MobilePass.php`:

```php
public function expire(): self
{
    match ($this->platform) {
        Platform::Apple  => $this->expireAsApple(),
        Platform::Google => $this->expireAsGoogle(),
    };

    return $this;
}

protected function expireAsApple(): void
{
    $content = $this->content;
    $content['voided'] = true;
    $content['expirationDate'] = now()->toIso8601String();

    $this->update([
        'content'    => $content,
        'expired_at' => now(),
    ]);
}

protected function expireAsGoogle(): void
{
    $content = $this->content;
    $content['googleObjectPayload']['state'] = 'EXPIRED';

    $this->update([
        'content'    => $content,
        'expired_at' => now(),
    ]);
}
```

Also add the `expired_at` cast:

```php
protected function casts(): array
{
    return [
        'platform' => Platform::class,
        'content'  => 'json',
        'images'   => 'json',
        'expired_at' => 'datetime',
    ];
}
```

- [ ] **Step 4: Add migration for `expired_at`**

Modify `database/migrations/create_mobile_pass_tables.php.stub`, add inside the `mobile_passes` schema:

```php
$table->timestamp('expired_at')->nullable();
```

(Since the package is pre-1.0, modifying the stub is acceptable. A dedicated "add" migration would be preferable post-1.0.)

- [ ] **Step 5: Run to verify pass**

Run: `./vendor/bin/pest tests/Models/MobilePassPlatformTest.php`
Expected: 1 passed.

- [ ] **Step 6: Add Google expire test**

Append to the same test file:

```php
it('Google expire patches state=EXPIRED', function () {
    Http::fake(['*' => Http::response([], 200)]);
    config()->set('mobile-pass.google.api_base_url', 'https://example.com/walletobjects/v1');
    cache()->put('mobile-pass.google.access-token', 'test-token', 3600);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content'  => [
            'googleClassType'     => 'eventTicketClass',
            'googleObjectId'      => '3388.john',
            'googleObjectPayload' => ['ticketHolderName' => 'Jane'],
        ],
    ]);

    $pass->expire();

    Http::assertSent(fn ($request) => $request->method() === 'PATCH'
        && $request['state'] === 'EXPIRED'
    );
    expect($pass->fresh()->expired_at)->not()->toBeNull();
});
```

- [ ] **Step 7: Make MobilePass::boot platform-aware**

Still in `src/Models/MobilePass.php`, replace `boot()`:

```php
public static function boot(): void
{
    parent::boot();

    static::updated(function (MobilePass $mobilePass) {
        $default = match ($mobilePass->platform) {
            Platform::Apple  => NotifyAppleOfPassUpdateAction::class,
            Platform::Google => NotifyGoogleOfPassUpdateAction::class,
        };

        $configKey = $mobilePass->platform === Platform::Apple
            ? 'notify_apple_of_pass_update'
            : 'notify_google_of_pass_update';

        /** @var class-string $action */
        $action = Config::getActionClass($configKey, $default);

        PushPassUpdateJob::dispatch($mobilePass, $action);
    });
}
```

Add corresponding `use` statements:

```php
use Spatie\LaravelMobilePass\Actions\Apple\NotifyAppleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction;
use Spatie\LaravelMobilePass\Jobs\PushPassUpdateJob;
```

- [ ] **Step 8: Register Google notify action in config**

In `config/mobile-pass.php`, add to the `actions` key:

```php
'notify_google_of_pass_update' => \Spatie\LaravelMobilePass\Actions\Google\NotifyGoogleOfPassUpdateAction::class,
```

- [ ] **Step 9: Run full suite**

Run: `./vendor/bin/pest`
Expected: all existing + new tests pass.

- [ ] **Step 10: Commit**

```bash
git add src/Models/MobilePass.php database/migrations/create_mobile_pass_tables.php.stub config/mobile-pass.php tests/Models/MobilePassPlatformTest.php
git commit -m "feat: platform-aware expire() and queued update push"
```

### Task 4.2: Apple signed download route + controller

**Files:**
- Create: `src/Http/Controllers/Apple/DownloadApplePassController.php`
- Modify: `routes/mobile-pass.php`
- Create: `tests/Http/Controllers/Apple/DownloadApplePassControllerTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Illuminate\Support\Facades\URL;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('serves the pkpass at a signed url', function () {
    $pass = MobilePass::factory()->create();

    $url = URL::signedRoute('mobile-pass.apple.download', ['mobilePass' => $pass->id]);

    $this->get($url)
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.apple.pkpass');
});

it('rejects an unsigned url', function () {
    $pass = MobilePass::factory()->create();

    $this->get(route('mobile-pass.apple.download', ['mobilePass' => $pass->id]))
        ->assertForbidden();
});
```

- [ ] **Step 2: Run to verify fail**

Expected: FAIL.

- [ ] **Step 3: Implement controller**

```php
<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Apple;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Models\MobilePass;

class DownloadApplePassController extends Controller
{
    public function __invoke(Request $request, MobilePass $mobilePass)
    {
        abort_unless($request->hasValidSignature(), 403);

        return $mobilePass->download();
    }
}
```

- [ ] **Step 4: Register route**

In `routes/mobile-pass.php`, inside the `Route::mobilePass` macro group:

```php
use Spatie\LaravelMobilePass\Http\Controllers\Apple\DownloadApplePassController;
// ...
Route::get('apple/{mobilePass}/download', DownloadApplePassController::class)
    ->name('mobile-pass.apple.download');
```

- [ ] **Step 5: Run to verify pass**

Expected: 2 passed.

- [ ] **Step 6: Commit**

```bash
git add src/Http/Controllers/Apple/DownloadApplePassController.php routes/mobile-pass.php tests/Http/Controllers/Apple/DownloadApplePassControllerTest.php
git commit -m "feat: signed Apple pkpass download route"
```

### Task 4.3: `$pass->addToWalletUrl()`

**Files:**
- Modify: `src/Models/MobilePass.php`
- Modify: `tests/Models/MobilePassPlatformTest.php`

- [ ] **Step 1: Write failing tests**

Append to the test file:

```php
it('Apple addToWalletUrl returns a signed download route', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Apple]);

    $url = $pass->addToWalletUrl();

    expect($url)->toContain('/apple/'.$pass->id.'/download');
    expect($url)->toContain('signature=');
});

it('Google addToWalletUrl returns a pay.google.com save URL', function () {
    config()->set('mobile-pass.google.service_account_key_path', \Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures::serviceAccountPath());
    config()->set('mobile-pass.google.issuer_id', '3388');

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content'  => [
            'googleClassType'     => 'eventTicketClass',
            'googleObjectId'      => '3388.john',
        ],
    ]);

    $url = $pass->addToWalletUrl();

    expect($url)->toStartWith('https://pay.google.com/gp/v/save/');
});
```

- [ ] **Step 2: Run to verify fail**

Expected: FAIL.

- [ ] **Step 3: Implement `addToWalletUrl()`**

Add to `MobilePass`:

```php
public function addToWalletUrl(): string
{
    return match ($this->platform) {
        Platform::Apple  => $this->addToAppleWalletUrl(),
        Platform::Google => $this->addToGoogleWalletUrl(),
    };
}

protected function addToAppleWalletUrl(): string
{
    return URL::signedRoute('mobile-pass.apple.download', ['mobilePass' => $this->id]);
}

protected function addToGoogleWalletUrl(): string
{
    $objectResource = str_replace('Class', 'Object', $this->content['googleClassType']);
    $resourceKey    = $objectResource.'s';  // eg eventTicketObjects

    $jwt = app(\Spatie\LaravelMobilePass\Support\Google\GoogleJwtSigner::class)->signSaveUrlJwt([
        $resourceKey => [['id' => $this->content['googleObjectId']]],
    ]);

    return 'https://pay.google.com/gp/v/save/'.$jwt;
}
```

Add the `use Illuminate\Support\Facades\URL;` import.

- [ ] **Step 4: Run to verify pass**

Expected: previously-passing tests still pass, plus 2 new.

- [ ] **Step 5: Commit**

```bash
git add src/Models/MobilePass.php tests/Models/MobilePassPlatformTest.php
git commit -m "feat: unified addToWalletUrl across platforms"
```

### Task 4.4: Milestone 4 quality gate

- [ ] Simplifier pass, guidelines pass, baseline green, cleanup commit if any.

---

## Milestone 5 — Save/remove callbacks

### Task 5.1: Callback event model + migration

**Files:**
- Create: `src/Models/Google/GoogleMobilePassEvent.php`
- Create: `database/factories/GoogleMobilePassEventFactory.php`
- Create: `database/migrations/add_google_wallet_support.php.stub`
- Modify: `src/MobilePassServiceProvider.php`
- Modify: `src/Support/Config.php`
- Modify: `config/mobile-pass.php`
- Create: `tests/Models/Google/GoogleMobilePassEventTest.php`

- [ ] **Step 1: Create dedicated migration**

Create `database/migrations/add_google_wallet_support.php.stub`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('mobile_passes', 'expired_at')) {
            Schema::table('mobile_passes', function (Blueprint $table) {
                $table->timestamp('expired_at')->nullable()->after('download_name');
            });
        }

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
```

Register it in `MobilePassServiceProvider::configurePackage()`:

```php
->hasMigrations(['create_mobile_pass_tables', 'add_google_wallet_support'])
```

(Replace the existing `->hasMigration(...)` call.)

Also, remove the `expired_at` addition from the `create_mobile_pass_tables` stub done in M4, to avoid duplication. Existing users will get it through the new migration.

- [ ] **Step 2: Create model**

```php
<?php

namespace Spatie\LaravelMobilePass\Models\Google;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

/**
 * @property string $event_type
 * @property \Carbon\Carbon $received_at
 * @property array $raw_payload
 */
class GoogleMobilePassEvent extends Model
{
    use HasFactory;
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'json',
            'received_at' => 'datetime',
        ];
    }

    public function mobilePass(): BelongsTo
    {
        return $this->belongsTo(Config::mobilePassModel(), 'mobile_pass_id');
    }

    public function scopeSaves($query)
    {
        return $query->where('event_type', 'save');
    }

    public function scopeRemoves($query)
    {
        return $query->where('event_type', 'remove');
    }
}
```

- [ ] **Step 3: Create factory**

```php
<?php

namespace Spatie\LaravelMobilePass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GoogleMobilePassEventFactory extends Factory
{
    protected $model = GoogleMobilePassEvent::class;

    public function definition(): array
    {
        return [
            'mobile_pass_id' => MobilePass::factory(),
            'event_type'     => 'save',
            'received_at'    => now(),
            'raw_payload'    => [],
        ];
    }
}
```

- [ ] **Step 4: Add model key to config**

```php
'google_mobile_pass_event' => \Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent::class,
```

- [ ] **Step 5: Add resolver to `Support\Config`**

```php
public static function googleMobilePassEventModel(): string
{
    return self::getModelClass('google_mobile_pass_event', GoogleMobilePassEvent::class);
}
```

Add the import.

- [ ] **Step 6: Test save/remove scopes**

```php
<?php

use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;

it('filters by event type using scopes', function () {
    GoogleMobilePassEvent::factory()->create(['event_type' => 'save']);
    GoogleMobilePassEvent::factory()->create(['event_type' => 'remove']);
    GoogleMobilePassEvent::factory()->create(['event_type' => 'save']);

    expect(GoogleMobilePassEvent::saves()->count())->toBe(2);
    expect(GoogleMobilePassEvent::removes()->count())->toBe(1);
});
```

Run: `./vendor/bin/pest tests/Models/Google/GoogleMobilePassEventTest.php`
Expected: pass.

- [ ] **Step 7: Commit**

```bash
git add src/Models/Google/GoogleMobilePassEvent.php database/factories/GoogleMobilePassEventFactory.php database/migrations/add_google_wallet_support.php.stub src/MobilePassServiceProvider.php src/Support/Config.php config/mobile-pass.php tests/Models/Google/GoogleMobilePassEventTest.php
git commit -m "feat: add GoogleMobilePassEvent model + migration"
```

### Task 5.2: Laravel events

**Files:**
- Create: `src/Events/GoogleMobilePassSaved.php`
- Create: `src/Events/GoogleMobilePassRemoved.php`

- [ ] **Step 1: Implement both events**

```php
<?php

namespace Spatie\LaravelMobilePass\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;

class GoogleMobilePassSaved
{
    use Dispatchable;

    public function __construct(
        public MobilePass $mobilePass,
        public GoogleMobilePassEvent $event,
    ) {}
}
```

Repeat as `GoogleMobilePassRemoved`.

- [ ] **Step 2: Commit**

```bash
git add src/Events/GoogleMobilePassSaved.php src/Events/GoogleMobilePassRemoved.php
git commit -m "feat: add Google save/remove Laravel events"
```

### Task 5.3: `VerifyGoogleCallbackRequest` middleware

**Files:**
- Create: `src/Http/Middleware/VerifyGoogleCallbackRequest.php`
- Create: `tests/Http/Middleware/VerifyGoogleCallbackRequestTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php

use Firebase\JWT\JWT;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyGoogleCallbackRequest;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    // In production we'd verify Google's own JWT certs; for the test suite
    // we rely on our service-account fixture as the "Google" signer.
    config()->set('mobile-pass.google.callback_signing_key', GoogleFixtures::publicKey());
});

it('rejects a request with no Authorization header', function () {
    $middleware = new VerifyGoogleCallbackRequest;
    $request = request();

    $middleware->handle($request, fn ($r) => response('ok'));
})->throws(\Illuminate\Auth\AuthenticationException::class);

it('accepts a request with a valid signed JWT', function () {
    $jwt = JWT::encode(
        ['iss' => 'google', 'iat' => time(), 'eventType' => 'save'],
        GoogleFixtures::privateKey(),
        'RS256'
    );

    $middleware = new VerifyGoogleCallbackRequest;
    $request = request()->merge([])->setMethod('POST');
    $request->headers->set('Authorization', 'Bearer '.$jwt);

    $response = $middleware->handle($request, fn ($r) => response('ok'));

    expect($response->getContent())->toBe('ok');
});
```

- [ ] **Step 2: Run to verify fail**

Expected: FAIL.

- [ ] **Step 3: Implement middleware**

```php
<?php

namespace Spatie\LaravelMobilePass\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class VerifyGoogleCallbackRequest
{
    public function handle(Request $request, Closure $next)
    {
        $authorization = (string) $request->header('Authorization');

        if (! str_starts_with($authorization, 'Bearer ')) {
            throw new AuthenticationException('Missing bearer token on Google callback.');
        }

        $jwt = substr($authorization, 7);
        $key = (string) config('mobile-pass.google.callback_signing_key');

        if ($key === '') {
            throw new AuthenticationException('No callback signing key configured.');
        }

        try {
            $decoded = JWT::decode($jwt, new Key($key, 'RS256'));
        } catch (\Throwable $exception) {
            throw new AuthenticationException('Invalid Google callback JWT: '.$exception->getMessage());
        }

        $request->attributes->set('google_callback_claims', (array) $decoded);

        return $next($request);
    }
}
```

Add `'callback_signing_key' => env('MOBILE_PASS_GOOGLE_CALLBACK_SIGNING_KEY')` to the google config.

- [ ] **Step 4: Run to verify pass**

Expected: 2 passed.

- [ ] **Step 5: Commit**

```bash
git add src/Http/Middleware/VerifyGoogleCallbackRequest.php tests/Http/Middleware/VerifyGoogleCallbackRequestTest.php config/mobile-pass.php
git commit -m "feat: add VerifyGoogleCallbackRequest middleware"
```

### Task 5.4: `HandleGoogleCallbackAction` + controller + route

**Files:**
- Create: `src/Actions/Google/HandleGoogleCallbackAction.php`
- Create: `src/Http/Controllers/Google/HandleCallbackController.php`
- Modify: `routes/mobile-pass.php`
- Modify: `config/mobile-pass.php`
- Create: `tests/Http/Controllers/Google/HandleCallbackControllerTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassRemoved;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Tests\TestSupport\Google\GoogleFixtures;

beforeEach(function () {
    config()->set('mobile-pass.google.callback_signing_key', GoogleFixtures::publicKey());
});

it('records a save event and fires the Laravel event', function () {
    Event::fake([GoogleMobilePassSaved::class]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content'  => ['googleObjectId' => '3388.john'],
    ]);

    $jwt = JWT::encode(
        [
            'iss' => 'google',
            'iat' => time(),
            'eventType' => 'save',
            'objectId' => '3388.john',
        ],
        GoogleFixtures::privateKey(),
        'RS256'
    );

    $this->postJson(route('mobile-pass.google.callback'), [], ['Authorization' => 'Bearer '.$jwt])
        ->assertOk();

    expect(GoogleMobilePassEvent::where('mobile_pass_id', $pass->id)->saves()->count())->toBe(1);

    Event::assertDispatched(GoogleMobilePassSaved::class);
});

it('records a remove event and fires the Laravel event', function () {
    Event::fake([GoogleMobilePassRemoved::class]);

    $pass = MobilePass::factory()->create([
        'platform' => Platform::Google,
        'content'  => ['googleObjectId' => '3388.john'],
    ]);

    $jwt = JWT::encode(
        ['iss' => 'google', 'iat' => time(), 'eventType' => 'del', 'objectId' => '3388.john'],
        GoogleFixtures::privateKey(),
        'RS256'
    );

    $this->postJson(route('mobile-pass.google.callback'), [], ['Authorization' => 'Bearer '.$jwt])
        ->assertOk();

    expect(GoogleMobilePassEvent::removes()->count())->toBe(1);

    Event::assertDispatched(GoogleMobilePassRemoved::class);
});
```

- [ ] **Step 2: Run to verify fail**

Expected: FAIL.

- [ ] **Step 3: Implement action**

```php
<?php

namespace Spatie\LaravelMobilePass\Actions\Google;

use Illuminate\Http\Request;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassRemoved;
use Spatie\LaravelMobilePass\Events\GoogleMobilePassSaved;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;
use Spatie\LaravelMobilePass\Support\Config;

class HandleGoogleCallbackAction
{
    public function execute(Request $request): void
    {
        /** @var array<string, mixed> $claims */
        $claims = (array) $request->attributes->get('google_callback_claims', []);

        $objectId = $claims['objectId'] ?? null;
        $eventType = match ($claims['eventType'] ?? null) {
            'save' => 'save',
            'del'  => 'remove',
            default => null,
        };

        if ($objectId === null || $eventType === null) {
            return;
        }

        $pass = $this->resolvePass((string) $objectId);

        if ($pass === null) {
            return;
        }

        $eventModelClass = Config::googleMobilePassEventModel();
        $event = $eventModelClass::query()->create([
            'mobile_pass_id' => $pass->id,
            'event_type'     => $eventType,
            'received_at'    => now(),
            'raw_payload'    => $claims,
        ]);

        $laravelEvent = $eventType === 'save'
            ? new GoogleMobilePassSaved($pass, $event)
            : new GoogleMobilePassRemoved($pass, $event);

        event($laravelEvent);
    }

    protected function resolvePass(string $objectId): ?MobilePass
    {
        $model = Config::mobilePassModel();

        return $model::query()
            ->where('content->googleObjectId', $objectId)
            ->first();
    }
}
```

- [ ] **Step 4: Implement controller**

```php
<?php

namespace Spatie\LaravelMobilePass\Http\Controllers\Google;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\LaravelMobilePass\Actions\Google\HandleGoogleCallbackAction;
use Spatie\LaravelMobilePass\Support\Config;

class HandleCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var class-string<HandleGoogleCallbackAction> $actionClass */
        $actionClass = Config::getActionClass('handle_google_callback', HandleGoogleCallbackAction::class);

        app($actionClass)->execute($request);

        return response()->noContent();
    }
}
```

- [ ] **Step 5: Register route**

In `routes/mobile-pass.php`:

```php
use Spatie\LaravelMobilePass\Http\Controllers\Google\HandleCallbackController;
use Spatie\LaravelMobilePass\Http\Middleware\VerifyGoogleCallbackRequest;
// ...
Route::post('google/callbacks', HandleCallbackController::class)
    ->middleware(VerifyGoogleCallbackRequest::class)
    ->name('mobile-pass.google.callback');
```

- [ ] **Step 6: Register action in config**

```php
'handle_google_callback' => \Spatie\LaravelMobilePass\Actions\Google\HandleGoogleCallbackAction::class,
```

- [ ] **Step 7: Run tests to verify pass**

Expected: 2 passed.

- [ ] **Step 8: Commit**

```bash
git add src/Actions/Google/HandleGoogleCallbackAction.php src/Http/Controllers/Google/HandleCallbackController.php routes/mobile-pass.php config/mobile-pass.php tests/Http/Controllers/Google/HandleCallbackControllerTest.php
git commit -m "feat: Google save/remove callback handler"
```

### Task 5.5: `HasMobilePasses` googleEvents relation

**Files:**
- Modify: `src/Models/MobilePass.php`
- Create: `tests/Models/MobilePassGoogleEventsTest.php`

- [ ] **Step 1: Write failing test**

```php
<?php

use Spatie\LaravelMobilePass\Enums\Platform;
use Spatie\LaravelMobilePass\Models\Google\GoogleMobilePassEvent;
use Spatie\LaravelMobilePass\Models\MobilePass;

it('isCurrentlySavedToGoogleWallet returns true when latest event is save', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);
    GoogleMobilePassEvent::factory()->create(['mobile_pass_id' => $pass->id, 'event_type' => 'save', 'received_at' => now()->subDay()]);

    expect($pass->isCurrentlySavedToGoogleWallet())->toBeTrue();
});

it('isCurrentlySavedToGoogleWallet returns false when latest event is remove', function () {
    $pass = MobilePass::factory()->create(['platform' => Platform::Google]);
    GoogleMobilePassEvent::factory()->create(['mobile_pass_id' => $pass->id, 'event_type' => 'save', 'received_at' => now()->subDays(2)]);
    GoogleMobilePassEvent::factory()->create(['mobile_pass_id' => $pass->id, 'event_type' => 'remove', 'received_at' => now()->subDay()]);

    expect($pass->isCurrentlySavedToGoogleWallet())->toBeFalse();
});
```

- [ ] **Step 2: Implement on `MobilePass`**

```php
public function googleEvents(): HasMany
{
    $modelClass = Config::googleMobilePassEventModel();

    return $this->hasMany($modelClass, 'mobile_pass_id');
}

public function isCurrentlySavedToGoogleWallet(): bool
{
    $latest = $this->googleEvents()->orderByDesc('received_at')->first();

    return $latest !== null && $latest->event_type === 'save';
}
```

- [ ] **Step 3: Run tests and commit**

```bash
git add src/Models/MobilePass.php tests/Models/MobilePassGoogleEventsTest.php
git commit -m "feat: googleEvents relation + isCurrentlySavedToGoogleWallet helper"
```

### Task 5.6: Milestone 5 quality gate

- [ ] Simplifier, guidelines, baseline green, cleanup commit if any.

---

## Milestone 6 — Apple EventTicketPassBuilder for parity

### Task 6.1: Apple EventTicket builder + validator

**Files:**
- Create: `src/Builders/Apple/EventTicketPassBuilder.php`
- Create: `src/Builders/Apple/Validators/EventTicketApplePassValidator.php`
- Modify: `config/mobile-pass.php`
- Create: `tests/Builders/Apple/EventTicketPassBuilderTest.php`

- [ ] **Step 1: Write failing test**

Mirror `AirlinePassBuilderTest` structure. Test for the `eventTicket` dictionary shape, snapshot the pkpass output.

- [ ] **Step 2: Implement validator**

Copy `CouponApplePassValidator` and adjust to the Apple `eventTicket` schema.

- [ ] **Step 3: Implement builder**

Copy `CouponPassBuilder` as a starting point. Replace `PassType::Coupon` with `PassType::EventTicket`. Override `compileData()` to produce `eventTicket` key instead of `coupon`.

- [ ] **Step 4: Register in config**

```php
'apple' => [
    // ...
    'event_ticket' => \Spatie\LaravelMobilePass\Builders\Apple\EventTicketPassBuilder::class,
],
```

- [ ] **Step 5: Run tests, commit**

```bash
git commit -am "feat: add Apple EventTicketPassBuilder"
```

### Task 6.2: Milestone 6 quality gate

- [ ] Simplifier, guidelines, baseline green.

---

## Milestone 7 — Documentation

### Task 7.1: New basic-usage pages

Each page uses the skeleton from `docs/basic-usage/generating-your-first-pass.md`:

1. One-paragraph intro (what + why).
2. Code block showing the happy path.
3. Short prose explaining anything non-obvious.
4. One or two practical gotchas.

Tone: conversational, first-person plural, no em-dashes. Commit per page.

- [ ] **Step 1: `docs/basic-usage/choosing-between-apple-and-google.md`** — table of platform differences, recommendation to publish both where possible.
- [ ] **Step 2: `docs/basic-usage/getting-credentials-from-google.md`** — step-by-step GCP console walkthrough, mapping to each env var.
- [ ] **Step 3: `docs/basic-usage/declaring-google-pass-classes.md`** — explain the Class concept, show declaring an `EventTicketPassClass`, cover `find` / `all` / `retire`.
- [ ] **Step 4: `docs/basic-usage/generating-your-first-google-pass.md`** — create an Object against a declared class, save, redirect.
- [ ] **Step 5: `docs/basic-usage/updating-google-passes.md`** — update model, job pushes PATCH to Google, Google pushes to device.
- [ ] **Step 6: `docs/basic-usage/handing-out-passes.md`** — unified `addToWalletUrl()` walk-through, email, QR code, button examples.
- [ ] **Step 7: `docs/basic-usage/expiring-passes.md`** — cross-platform `expire()`, semantics per platform.

Commit after each page:

```bash
git add docs/basic-usage/<page>.md
git commit -m "docs: add <page> guide"
```

### Task 7.2: New advanced-usage pages

- [ ] **Step 1: `docs/advanced-usage/listening-to-google-save-remove-events.md`** — show both Laravel event listeners and persisted-event queries.
- [ ] **Step 2: `docs/advanced-usage/queueing-update-pushes.md`** — `MOBILE_PASS_QUEUE_CONNECTION` behavior, monitoring jobs.
- [ ] **Step 3: `docs/advanced-usage/hosting-your-own-google-images.md`** — when to use `Image::fromLocalPath`, the class-level limitation, signed-route mechanics.

Commit after each.

### Task 7.3: Update existing docs + README

- [ ] **Step 1: `docs/introduction.md`** — mention Google.
- [ ] **Step 2: `docs/installation-setup.md`** — add Google section.
- [ ] **Step 3: `README.md`** — switch the "iOS and Android" claim from aspirational to accurate, link both platform walkthroughs.

Commit: `docs: update introduction, installation, README for Google`.

### Task 7.4: Milestone 7 quality gate (prose review)

- [ ] Read every new page out loud. Fix anything that sounds stilted.
- [ ] Grep all docs for em-dashes (`—`). Replace with periods/commas/parentheses.
- [ ] Grep for first-person singular ("I "). Convert to "we".
- [ ] Run Spatie guidelines skill on any code blocks inside the docs.

Commit any cleanup: `chore: docs polish`.

---

## Milestone 8 — Final sweep

### Task 8.1: Cross-file simplifier sweep

- [ ] Dispatch `laravel-simplifier` agent against the entire diff since `main` at start of this plan. Target cross-file smells invisible in single-milestone passes:

```
Review the full cumulative diff of this branch against main. Look for cross-file issues:
- Duplicated helper methods that should be consolidated
- Inconsistent naming between Apple and Google parallels
- Methods that could live in a shared trait
- Error messages that differ in tone across files
Apply fixes directly. Do not change public APIs. Do not touch the test fixtures.
```

### Task 8.2: Cross-file Spatie guidelines sweep

- [ ] Apply the Spatie guidelines skill with a whole-diff mindset. Same checklist as per-milestone, but looking for inconsistencies across files.

### Task 8.3: Full baseline

- [ ] Run `./vendor/bin/pest` — all green.
- [ ] Run `./vendor/bin/phpstan analyse --memory-limit=1G` — no errors.
- [ ] Run `./vendor/bin/pint --test` — no style issues.

### Task 8.4: CHANGELOG update

- [ ] Add a `## Unreleased` section at the top of `CHANGELOG.md` summarising the Google parity work, Apple improvements, and migration notes.

### Task 8.5: Final commit

```bash
git add CHANGELOG.md
git commit -m "chore: update changelog for Google Wallet parity"
```

### Task 8.6: Prepare PR

- [ ] Push the branch.
- [ ] Open a PR with description summarising each milestone, pointing to the spec and plan docs, and listing new env vars / migration / config changes.

---

## Scope note: deferred to v1.1

Kept out of v1 deliberately to limit surface area. Tracked as separate follow-up tasks.

### Google local-path image hosting (object-level only)

Spec section 6.4 describes a hybrid image path where `Image::fromLocalPath(...)` on an **object-level** builder causes the package to store the binary on the `MobilePass.images` JSON column and expose it to Google via a signed route (`GET /mobile-pass/google-image/{uuid}/{key}?signature=...`). In v1, `Image::fromLocalPath()` captures the path but `publicUrl()` throws with a message directing users to `Image::fromUrl()`. The serve route + binary plumbing ships in v1.1, as a small additive task built on the signed-route infrastructure already in place from Apple's download route.

### Smart Tap fields

Spec section 13 lists Smart Tap as out of scope for v1. No changes from the spec.

### Issuer notification signing-key fetcher

The v1 callback middleware expects `mobile-pass.google.callback_signing_key` to be populated by the user (the public key Google configures in the Pay & Wallet Business Console). v1.1 can add an automated fetch-and-cache for Google's JWKS, similar to how Laravel Socialite caches OAuth provider keys.

---

## Self-review checklist (run by author)

- Every spec section maps to a milestone.
  - Section 3.1 namespace layout: M1 / M2 / M3 / M4 / M5 / M6.
  - Section 3.2 pass type mapping: M2 / M3 / M6.
  - Sections 4.1 to 4.6: M2 / M3 / M4 / M5.
  - Section 5 configuration: M1 / M3 / M4 / M5.
  - Section 6 internal mechanics: M1 (client, signer, push job), M4 (update push wiring, images note), M5 (callbacks).
  - Section 6.4 image handling: URL mode in v1 (M2), local-path mode deferred to v1.1 (noted above).
  - Section 7 database schema: M4 (`expired_at`), M5 (events table).
  - Section 8 Apple improvements: M4 (signed download, expire, queued push) + M6 (EventTicket).
  - Section 9 testing strategy: every task includes tests.
  - Section 10 docs deliverable: M7 (all pages).
  - Section 12 migration path: M5 migration + docs in M7.
- No "TODO", "TBD", or "similar to previous" shortcuts without explicit reference to a task-with-full-code.
- Method names consistent across tasks (`addToWalletUrl`, `expire`, `setClass`, `retire`, `find`, `all`).
- Tests defined before implementation in every task.
- Every task ends in a commit with a concrete message.
- Quality gates run after every milestone, with a final sweep at the end.
