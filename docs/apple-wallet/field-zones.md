---
title: Field zones
weight: 1
---

Apple splits the fields on a pass across five zones. Each zone sits in a different spot on the pass and gets its own typography, so pick the zone that matches how visible the information needs to be. See Apple's [PassKit Programming Guide](https://developer.apple.com/library/archive/documentation/UserExperience/Conceptual/PassKit_PG/Creating.html) for the full design rationale.

- Header fields sit at the top right of the pass. They stay visible even when passes are stacked in Wallet, so use them for glanceable information only (a flight number, a balance). Up to three per pass.
- Primary fields are the prominent ones on the front of the pass, rendered at the largest size. Use them for the main identity of the pass (origin and destination on a boarding pass, event name on a ticket). Up to three, or two on a boarding pass.
- Secondary fields sit below the primary fields on the front, at a smaller size. Use them for the next layer of important information. Up to four.
- Auxiliary fields are below the secondary fields, smaller still. Use them for less important front-of-pass details (a seat number, a gate letter). Up to four, five on boarding passes.
- Back fields appear on the back of the pass, behind the info button. They have no length limit and support line breaks, URLs, and phone numbers (which Wallet turns into live links). Use them for the long stuff: terms, customer service, refund policy.

## The add methods

Every Apple builder exposes one `add` method per zone:

```php
addHeaderField(string $key, string $value, ?string $label, ?string $changeMessage, ?DateType $dateStyle, ?TimeStyleType $timeStyle, ?bool $showDateAsRelative)
addField(string $key, string $value, FieldType $type = FieldType::Primary, ...)
addSecondaryField(string $key, string $value, ...)
addAuxiliaryField(string $key, string $value, ...)
addBackField(string $key, string $value, ...)
```

`$key` is a free-form identifier you pick. It's unique within the pass, and you'll reference it later when you want to update that field.

`$label` defaults to a title-cased version of the key. Pass a custom label when the key doesn't read nicely on the pass.

`$changeMessage` is the notification the user's device shows when the value of this field changes. Use `:value` as a placeholder for the new value (for example, `'Your gate has changed to :value'`).

`$dateStyle` and `$timeStyle` let you format a value Apple recognises as a date. Pass a `DateType` (none, short, medium, long, or full) and/or a `TimeStyleType` case. Combine with `$showDateAsRelative: true` to render the date as "in 2 hours" rather than an absolute timestamp.
