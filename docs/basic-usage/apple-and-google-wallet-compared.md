---
title: Apple and Google Wallet compared
weight: 2
---

This package lets you generate passes for both Apple Wallet and Google Wallet. Most apps should publish to both, because your users are split across iOS and Android devices. If you ship only one platform, you exclude roughly half of them.

Building both is straightforward. The `MobilePass` model is shared across platforms, the public API is consistent, and each platform has its own set of builder classes.

## What Apple Wallet does well

- iOS and macOS users
- iPhone boarding passes, event tickets, loyalty cards, and coupons
- Server-side control of every detail in the pass file

## What Google Wallet does well

- Android users (and iOS users with the Google Wallet app)
- Every use case Apple covers, plus Google-specific pass types like Offers and generic passes
- Google-managed push updates, so you do not have to host a device-facing web service

## The two ecosystems differ

Apple and Google approach passes differently, and the package exposes those differences where they matter.

Google has the concept of a **Class** (a shared template for a batch of passes) and an **Object** (one pass for one user). Apple has no equivalent: every Apple pass is standalone. Read [Declaring Google pass classes](/docs/laravel-mobile-pass/v1/google-wallet/declaring-google-pass-classes) for the details.

Apple pushes updates through APNs, and the device then hits your server for the new pass content. That means you host a web service that Apple talks to. Google does the push itself: you PATCH the object on Google's servers and Google notifies the device.

Apple expects the `.pkpass` file to be signed with a certificate from the Apple Developer Program. Google expects a service account with an `iam.gserviceaccount.com` key. See [Getting credentials from Apple](/docs/laravel-mobile-pass/v1/apple-wallet/getting-credentials-from-apple) and [Getting credentials from Google](/docs/laravel-mobile-pass/v1/google-wallet/getting-credentials-from-google).

Once both platforms are set up, the rest of the package works the same for both. `$mobilePass->addToWalletUrl()` returns the right link, `$mobilePass->expire()` does the right thing, and updates to the `MobilePass` model are pushed out automatically.
