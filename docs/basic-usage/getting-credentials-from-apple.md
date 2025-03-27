---
title: Getting credentials from Apple
weight: 2
---

In order to generate passes for Apple Wallet, you first need to request a certificate from Apple. This certificate is used to sign the passes you generate.

To request a certificate, you or your organisation must be a member of the [Apple Developer Program](https://developer.apple.com/programs/enroll). 

First, follow [Apple's guide](https://developer.apple.com/help/account/certificates/create-a-certificate-signing-request/) to generate a CSR (Certificate Signing Request) file.

Then, follow these steps to request a certificate from Apple:

1. Head to [Certificates, Identifiers & Profiles](https://developer.apple.com/account/resources/identifiers/list) in the Apple Developer portal and select Identifiers.

2. Click the `+` button to create a new identifier.

3. Select **Pass Type IDs** and click Continue.

4. Provide a description for your key, and an identifier. It's recommended to use a reverse domain name notation, like `pass.be.spatie`. Then click Register.

5. Select your new Pass Type ID from the list. Under **Production Certificates**, select "Create Certificate".

6. Provide the CSR file you generated earlier and click Continue.

7. Now click Download to download the certificate, and double-click it to install it in Keychain Access.

8. Export your keys to a `.p12` file. Open Keychain Access and search for your certificate by the identifier you provided earlier. Expand the item to reveal the private key, then select both items. Right-click the items and select Export. Choose a password and save the file.

![Exporting the certificate](/docs/laravel-mobile-pass/v1/images/exporting-key.gif)

You can now set the `mobile-pass.apple.certificate_path` config variable to point to this `.p12` file, or by setting the `MOBILE_PASS_APPLE_CERTIFICATE_PATH` environment variable. 

You must also set the `mobile-pass.apple.certificate_password` config variable to the password you set when exporting the certificate, or by setting the `MOBILE_PASS_APPLE_CERTIFICATE_PASSWORD` environment variable.

If you prefer, you can use the base64 encoded contents of your `.p12` certificate rather than the path of the file.

```bash
base64 -i path/to/certificate.p12 | pbcopy
```

Then set the `mobile-pass.apple.certificate` config variable to the base64 encoded contents, or by setting the `MOBILE_PASS_APPLE_CERTIFICATE` environment variable.

Remember, the key and its contents as well as the certificate password are sensitive information. Keep them safe.
