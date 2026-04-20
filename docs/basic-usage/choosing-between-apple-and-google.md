---
title: Choosing between Apple and Google
weight: 2
---

This package lets you generate passes for both Apple Wallet and Google Wallet. Most apps should publish to both platforms, because your users are split across iOS and Android devices.

Here's a quick comparison to help you decide what to build first.

## Pick Apple Wallet for

- iOS and macOS users
- iPhone boarding passes, event tickets, loyalty cards, and coupons
- Cases where you want your server to control every detail of the pass file

## Pick Google Wallet for

- Android users (and iOS users with the Google Wallet app)
- The same use cases as Apple, plus Google-specific pass types like Offers and generic passes

## Why most apps want both

If you only ship one platform, you'll exclude roughly half of your users. Building both is straightforward: the `MobilePass` model is shared, the public API is consistent, and the generation code for each platform lives in its own builder classes.

## The two ecosystems differ

Apple and Google approach passes differently, and the package exposes these differences where they matter.

Google has the concept of a **Class** (a shared template for a batch of passes) and an **Object** (one pass for one user). Apple has no equivalent: every Apple pass is standalone. Read [Declaring Google pass classes](declaring-google-pass-classes) for the details.

Apple pushes updates through APNs, and the device then hits your server for the new pass content. That means you host a web service that Apple talks to. Google does the push itself: you PATCH the object on Google's servers and Google notifies the device.

Apple expects the `.pkpass` file to be signed with a certificate from the Apple Developer Program. Google expects a service account with an `iam.gserviceaccount.com` key. See [Getting credentials from Apple](getting-credentials-from-apple) and [Getting credentials from Google](getting-credentials-from-google).

Once you've set both platforms up, the rest of the package works the same for both. `$pass->addToWalletUrl()` returns the right link, `$pass->expire()` does the right thing, and updates to the `MobilePass` model are pushed out automatically.
