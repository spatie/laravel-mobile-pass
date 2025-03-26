---
title: Introduction
weight: 1
---

This package provides an easy way to generate mobile passes for Apple Wallet and Google Pay. It offers builders for each pass type. Passes are not stored on disk, but saved as models with all pass properties in the `mobile_passes` table.

When a `MobilePass` model is returned in a controller response, the package will automatically generate a pass file and send it to the user. When the user adds the pass to their wallet, Apple will send a registration request to your app, which the package will store in the `mobile_pass_registrations` table. When you update a `MobilePass` model, the package will automatically send a request to apple, to inform the user of the update and to automatically update the pass in the user's wallet.

