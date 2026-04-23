---
title: Getting credentials from Apple
weight: 5
---

Before you can generate passes for Apple Wallet, you need a certificate from Apple. The package uses it to sign every pass you issue.

To request one, you (or your organization) must be enrolled in the [Apple Developer Program](https://developer.apple.com/programs/enroll).

Start by following [Apple's guide](https://developer.apple.com/help/account/certificates/create-a-certificate-signing-request/) to generate a CSR (Certificate Signing Request) file. Once you have that, work through the steps below.

1. Head to [Certificates, Identifiers & Profiles](https://developer.apple.com/account/resources/identifiers/list) in the Apple Developer portal and pick Identifiers.

2. Click the `+` button to create a new identifier.

3. Choose Pass Type IDs and click Continue.

4. Give the key a description and an identifier. Reverse domain notation works well, something like `pass.be.spatie`. Click Register.

5. Select your new Pass Type ID from the list. Under Production Certificates, click "Create Certificate".

6. Upload the CSR file you generated earlier and click Continue.

7. Click Download to grab the certificate, then double-click it to install it in Keychain Access.

8. Export your keys to a `.p12` file. Open Keychain Access, search for your certificate by the identifier you picked earlier, expand the item to reveal the private key, and select both. Right-click, choose Export, pick a password, and save the file.

![Exporting the certificate](/docs/laravel-mobile-pass/v1/images/exporting-key.gif)

Now point the package at that `.p12`. Either set `mobile-pass.apple.certificate_path` in the config, or set the `MOBILE_PASS_APPLE_CERTIFICATE_PATH` env var.

You'll also want to set `mobile-pass.apple.certificate_password` (or `MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD`) to the password you picked during export.

If storing a file on disk is awkward (containers, ephemeral filesystems), you can base64-encode the `.p12` and hand over the contents instead:

```bash
base64 -i path/to/certificate.p12 | pbcopy
```

Then set `mobile-pass.apple.certificate` to the base64 string, or use the `MOBILE_PASS_APPLE_CERTIFICATE` environment variable.

Keep in mind: the key, its contents, and the certificate password are all sensitive. Treat them like any other secret.
