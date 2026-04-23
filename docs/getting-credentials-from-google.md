---
title: Getting credentials from Google
weight: 6
---

To generate passes for Google Wallet you need two things from Google: a service account key (which the package uses to call the Wallet API on your behalf) and an issuer ID (Google's label for your organization).

You get both by enrolling as an issuer in the Google Pay & Wallet Business Console.

## Create a GCP project and enable the Wallet API

1. Head to the [Google Cloud Console](https://console.cloud.google.com) and create a new project (or pick an existing one).
2. In the API library, search for "Google Wallet API" and click Enable.

## Create a service account

1. In the GCP console, go to IAM & Admin, then Service Accounts.
2. Click "Create Service Account". Give it a descriptive name like "Laravel Mobile Pass".
3. When asked about roles, skip the project-level role. Finish creating the account.
4. Open the service account, go to the Keys tab, click Add Key, then Create new key. Choose JSON. A `.json` file will download. Keep it safe.

## Register as a Wallet issuer

1. Head to the [Google Pay & Wallet Business Console](https://pay.google.com/business/console) and sign in.
2. Pick Google Wallet API and accept the terms.
3. Once your issuer account is created, grab the numeric Issuer ID from the top of the page.
4. Under Users, invite the email address of the service account you just created, and grant it the Developer role. That's what gives your service account the `wallet_object.issuer` scope on your issuer account.

## Configure environment variables

Set the issuer ID and point the package at the key file:

```bash
MOBILE_PASS_GOOGLE_ISSUER_ID=3388000000022000000
MOBILE_PASS_GOOGLE_KEY_PATH=/absolute/path/to/service-account.json
```

If you'd rather not put the file on disk, inline the key contents instead. `MOBILE_PASS_GOOGLE_KEY` accepts either raw JSON or base64-encoded JSON; the package detects the format:

```bash
MOBILE_PASS_GOOGLE_KEY='{"type":"service_account",...}'
```

Or base64-encode the file and store that:

```bash
base64 -i path/to/service-account.json | pbcopy
```

```bash
MOBILE_PASS_GOOGLE_KEY=ewogICJ0eXBlIjogInNlcnZpY2VfYWNjb3VudCIsCi...
```

When both `MOBILE_PASS_GOOGLE_KEY` and `MOBILE_PASS_GOOGLE_KEY_PATH` are set, inline contents win.

## Configure the callback signing key

When a user saves or removes a pass, Google sends a signed request to your app. To verify those requests, you need Google's public signing key.

You'll find it in the Business Console under Settings, then API access. Copy the PEM-encoded public key and set it as:

```bash
MOBILE_PASS_GOOGLE_CALLBACK_SIGNING_KEY="-----BEGIN PUBLIC KEY-----
MIIB...
-----END PUBLIC KEY-----"
```

Without this key, the callback route rejects every incoming request. See [Events](/docs/laravel-mobile-pass/v1/advanced-usage/events) for the full callback flow.

The service account key and the callback signing key are both sensitive. Keep them out of version control.
