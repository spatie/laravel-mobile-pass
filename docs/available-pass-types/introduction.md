---
title: Introduction
weight: 1
---

The package ships a builder for every pass type Apple Wallet and Google Wallet support. Pick the builder that matches the kind of pass you're issuing, and you're done. Every builder has setters specific to its type, on top of the shared `save()`, `addToWalletUrl()`, and `expire()`.

Apple and Google don't name the equivalents the same way, so here's the rough mapping:

| Apple                       | Google                      | Typical use                            |
| --------------------------- | --------------------------- | -------------------------------------- |
| `AirlinePassBuilder`        | `BoardingPassBuilder`       | Flight boarding passes                 |
| `EventTicketPassBuilder`    | `EventTicketPassBuilder`    | Concerts, festivals, sports events     |
| `CouponPassBuilder`         | `OfferPassBuilder`          | Discount codes, limited-time offers    |
| `StoreCardPassBuilder`      | `LoyaltyPassBuilder`        | Loyalty cards, membership programs     |
| `GenericPassBuilder`        | `GenericPassBuilder`        | Anything that doesn't fit the above    |

The Apple and Google pairs aren't interchangeable. Each platform has its own namespace (`Spatie\LaravelMobilePass\Builders\Apple\...` and `Spatie\LaravelMobilePass\Builders\Google\...`), so if you want to support both platforms for the same conceptual pass, you build twice.

Each page in this section walks through one pass type, side by side for Apple and Google. For the full walkthrough of how a pass goes from builder call to wallet, see [Generating your first pass](basic-usage/generating-your-first-pass).
