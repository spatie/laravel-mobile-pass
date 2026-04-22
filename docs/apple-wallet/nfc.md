---
title: NFC passes
weight: 3
---

Apple Wallet passes can carry an NFC payload that's transmitted when the pass is held up to a compatible reader. This is what makes tap-to-redeem loyalty cards, transit passes, and access control passes work.

NFC is Apple-only. Google Wallet handles its own NFC flow internally and doesn't expose equivalent fields.

## Apple approval is required

Before you reach for this, know that Apple gates NFC behind a separate entitlement called the PassKit NFC Credentials Entitlement. Without it, the `nfc` dictionary is ignored by iPhones at scan time. You have to apply to Apple and describe your use case; only loyalty, transit, access control, and similar are approved.

Without the entitlement, you can still set the payload during development and generate valid pass bundles. The iPhone will just not transmit anything over NFC.

## Setting an NFC payload

```php
$builder->setNfc(
    message: 'TICKET-12345',
    encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...',
);
```

`message` is the string the reader receives when the pass is tapped. Keep it under 64 bytes.

`encryptionPublicKey` is an ECC (P-256) public key in base64-encoded X.509 SubjectPublicKeyInfo format. Apple uses this to negotiate an ephemeral shared key with the reader so the message can be encrypted in transit. You generate this keypair alongside your reader software.

Pass `requiresAuthentication: true` to require Face ID or Touch ID before the NFC payload leaves the device. Useful for high-value passes (access control, transit) where casual proximity taps shouldn't count.

```php
$builder->setNfc(
    message: 'TICKET-12345',
    encryptionPublicKey: 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...',
    requiresAuthentication: true,
);
```

See Apple's [Pass.NFC](https://developer.apple.com/documentation/walletpasses/pass/nfc-data) reference for the full field spec.
